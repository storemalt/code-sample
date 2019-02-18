<?php

namespace App\Http\Controllers;

use App\Budget;
use App\Customer;
use App\HotelPreference;
use App\FlightPreference;
use App\GroundTransportationPreference;
use App\IndexPreference;
use App\Inquiry;
use App\InteractionPreference;
use App\LoyaltyProgram;
use App\Mail\CustomerInquiry;
use App\Persona;
use App\TravelProfile;
use App\TravelPurpose;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;

class CustomerController extends Controller
{
    /**
     * $request \Illuminate\Http\Request Container
     * @var object
     */
    protected $requestData = null;

    /**
     * Container for Customer Model Object
     *
     * @var Customer $this ->customer
     */
    protected $customer = null;

    /**
     * @var array destination countries
     */
    protected $destinations = [];

    /**
     * Define your validation rules in a property in
     * the controller to reuse the rules.
     */
    protected $validationRules = [
      'subject' => 'required|max:255',
      'name' => 'required|max:255',
      'email' => 'required|email|max:255',
      'phone' => 'required|max:255',
      'enquiry' => 'required',
    ];

    /**
     * Define your validation rules in a property in
     * the controller to reuse the rules.
     */
    protected $flightInquiryValidationRules = [
      'name' => 'required|max:255',
      'email' => 'required|email|max:255',
    ];

    /**
     * Modify the globally used view variable here
     *
     * initialization found on app\Providers\AppServiceProvider
     * @return void
     */
    public function __construct()
    {
        View::share('title', 'Customer');
        $this->middleware('auth', ['except' => ['create', 'enquiry', 'store', 'update', 'enquiryConfirmation']]);
        $this->middleware('verified', ['except' => ['create', 'enquiry', 'store', 'update', 'enquiryConfirmation']]);
        $this->destinations = config('params.destination_countries');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $perPage = 100;
        $customers = ($request->status)
          ? Customer::withTrashed()->paginate($perPage)
          : Customer::latest()->paginate($perPage);

        return view('customer.index', ['customers' => $customers]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customer = new Customer();
        return view('customer.create')->with([
          'customer' => $customer,
          'destinations' => $this->destinations,
          'action' => 'CustomerController@store',
        ]);
    }

    /**
     * Show the form for creating a new inquiry resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function enquiry(Request $request)
    {
        if ($request->enquiry == 'sent') {
            return view('customer.enquiry-confirmation');
        }

        $customer = new Customer();
        return view('customer.enquiry')->with([
          'customer' => $customer,
          'destinations' => $this->destinations,
          'action' => 'CustomerController@store',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     * @throws
     */
    public function store(Request $request)
    {
        if ((env('APP_ENV') === 'production') && !empty($request->get('g-recaptcha-response'))) {
            $captchaResponse = $this->getCaptcha($request->get('g-recaptcha-response'));

            if (!($captchaResponse->success == true && $captchaResponse->score > 0.5)) {
                return redirect('/customer/enquiry')->with('error', 'Only customer(human) can send enquiries!');
            }
        }

        $rules = $this->validationRules;
        if (!empty($request->flight_inquiry)) {
            $rules = $this->flightInquiryValidationRules;
        }

        $request->validate($rules);
        $this->save($request);

        // determine which redirect page if logged in
        if (Auth::check()) {
            return redirect('/customer')->with('success', 'Customer Inquiry submitted');
        } else {
            return redirect()->route('customer.enquiry', ['enquiry' => 'sent']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Customer $customer
     * @return \Illuminate\Http\Response
     */
    public function profile(Customer $customer)
    {
        if (empty($customer->persona->id)) {
            return redirect()
              ->route('customer.edit', ['id' => $customer->id])
              ->with('error', 'Set Persona for customer first.');
        }

        $travelPurposes = [
          'site_seeing' => '10',
          'food' => '20',
          'landscape_nature' => '30',
          'festival_season' => '40',
          'experience_local_culture' => '50',
          'visiting_popular_attractions' => '60',
        ];

        $hotelPreferences = [
          'food_preferences' => 'Local Food',
          'food Notes' => 'No Beef',
          'hotel_preferences' => '4 Star and Above',
          'hotel_location_preference' => 'Near City Center',
          'hotel_room_preference' => 'Non-smoking',
          'hotel_floor_preference' => 'High Floor',
          'hotel_brand' => 1,
          'locality' => 0,
          'within_budget' => 0,
          'breakfast_inclusive' => 1,
        ];

        $interactionPreferences = [
          'marketing' => 'Via Email',
          'emergency' => 'Via Phone Call',
          'time' => 'From 1pm to 2pm',
          'allow_email' => 'Yes',
          'allow_phone_call' => 'Yes',
        ];

        $indexPreferences = [
          'air_pollution_index' => '49',
          'safety_situation' => 'Very Safe',
          'traffic_condition' => 'Moderate, heavy during rush hours',
          'political_stability' => 'peaceful/stable',
        ];

        $groundTranspoPreferences = [
          'type' => 'Van/SUV',
          'service' => 'executive',
          'company' => 'Great Travel East',
          'account' => 'cash',
        ];

        $flightPreferences = [
          'airline' => 'Singapore Airlines',
          'private_charter' => 'Yes',
          'class' => 'Business',
          'seat' => 'Window',
          'meal' => 'Vegetarian',
          'special_request' => 'Allergic: Chicken',
        ];


        return view('customer.profile', [
          'customer' => $customer,
          'travelPurposes' => $travelPurposes,
          'hotelPreferences' => $hotelPreferences,
          'interactionPreferences' => $interactionPreferences,
          'indexPreferences' => $indexPreferences,
          'groundTranspoPreferences' => $groundTranspoPreferences,
          'flightPreferences' => $flightPreferences,
          'action' => 'CustomerController@update',
          'destinations' => $this->destinations,
          'attributes' => ['recency', 'frequency', 'monetary']
        ]);
    }

    /**
     * Show the form for editing profile resource.
     *
     * @param  \App\Customer $customer
     * @return \Illuminate\Http\Response
     */
    public function profileUpdate(Customer $customer)
    {
        $budget = Budget::firstOrNew(['customer_id' => $customer->id]);
        $hotelPreference = HotelPreference::firstOrNew(['customer_id' => $customer->id]);
        $flightPreference = FlightPreference::firstOrNew(['customer_id' => $customer->id]);
        $groundTransportationPreference = GroundTransportationPreference::firstOrNew(['customer_id' => $customer->id]);
        $indexPreference = IndexPreference::firstOrNew(['customer_id' => $customer->id]);
        $interactionPreference = InteractionPreference::firstOrNew(['customer_id' => $customer->id]);
        $loyaltyProgram = LoyaltyProgram::firstOrNew(['customer_id' => $customer->id]);
        $travelProfile = TravelProfile::firstOrNew(['customer_id' => $customer->id]);
        $travelPurpose = TravelPurpose::firstOrNew(['customer_id' => $customer->id]);

        return view('customer.profile.profile-edit', [
          'budget' => $budget,
          'customer' => $customer,
          'hotelPreference' => $hotelPreference,
          'flightPreference' => $flightPreference,
          'groundTransportationPreference' => $groundTransportationPreference,
          'indexPreference' => $indexPreference,
          'interactionPreference' => $interactionPreference,
          'loyaltyProgram' => $loyaltyProgram,
          'travelProfile' => $travelProfile,
          'travelPurpose' => $travelPurpose,
          'attributes' => ['recency', 'frequency', 'monetary'],
          'actionBudget' => 'BudgetController@store',
          'actionHotelPreference' => 'HotelPreferenceController@store',
          'actionFlightPreference' => 'FlightPreferenceController@store',
          'actionGroundTransportationPreference' => 'GroundTransportationPreferenceController@store',
          'actionIndexPreference' => 'IndexPreferenceController@store',
          'actionInteractionPreference' => 'InteractionPreferenceController@store',
          'actionLoyaltyProgram' => 'LoyaltyProgramController@store',
          'actionTravelProfile' => 'TravelProfileController@store',
          'actionTravelPurpose' => 'TravelPurposeController@store',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Customer $customer
     * @return \Illuminate\Http\Response
     */
    public function edit(Customer $customer)
    {
        $personas = Persona::all()->pluck('name', 'id')->toArray();

        return view('customer.edit', [
          'customer' => $customer,
          'personas' => $personas,
          'action' => 'CustomerController@update',
          'destinations' => $this->destinations,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Customer $customer
     * @throws
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customer $customer)
    {
        $this->validate($request, $this->flightInquiryValidationRules);

        $customer->fill($request->all());
        $customer->update();

        return redirect()->route('customer.edit', ['id' => $customer->id])->with('success', 'Customer updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Customer $customer
     * @throws
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
        $this->customer = $customer;

        try {
            DB::transaction(function () {
                $customerIDs = $this->customer->inquiries;
                if (!empty($customerIDs)) {
                    $customerIDs = $customerIDs->pluck('id', 'id')->toArray();
                    Inquiry::destroy($customerIDs);
                }
                $this->customer->delete();
            });
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
        }

        return redirect('customer')->with('success', 'Customer deleted!');
    }

    private function getCaptcha($captchaResponse)
    {
        $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='
          . env('GOOGLE_CAPTCHA_SECRET_KEY')
          . '&response='
          . $captchaResponse);

        $response = json_decode($response);
        return $response;
    }

    /**
     * Saves customer data to model and sends email to the ff:
     * Admin email and JTB Staff email
     *
     * @param Request $request
     * @return bool
     */
    private function save(Request $request): bool
    {
        $this->requestData = $request;

        try {
            DB::transaction(function () {
                $customer = Customer::where('email', $this->requestData->email)->first();

                if (empty($customer->id)) {
                    $customer = new Customer();
                }
                $customer->fill($this->requestData->all());

                if ($customer->save()) {
                    $inquiry = new Inquiry();
                    $inquiry->fill($this->requestData->all());
                    $inquiry->customer_id = $customer->id;
                    $inquiry->traveling_date_from = $inquiry->toTimestamp($this->requestData->traveling_date_from);
                    $inquiry->traveling_date_to = $inquiry->toTimestamp($this->requestData->traveling_date_to);
                    $inquiry->adult_count = empty($this->requestData->adult_count)
                      ? 0 : $this->requestData->adult_count;
                    $inquiry->child_count = empty($this->requestData->child_count)
                      ? 0 : $this->requestData->child_count;
                    $inquiry->created_by = (Auth::id()) ? Auth::id() : 1;
                    $inquiry->save();

                    $message = 'Thank you for your enquiry. Our team will contact you as soon as possible.';

                    // send email to customer
                    Mail::to($customer->email)
                      ->send(new CustomerInquiry($customer, $inquiry, $message));

                    $adminMessage = 'New customer enquiry has been received. Details below:';

                    // send email to admin
                    Mail::to(config('params.jtb_staff_email'))
                      ->cc(config('params.admin_email'))
                      ->send(new CustomerInquiry($customer, $inquiry, $adminMessage, 'admin'));
                }
            });
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            return false;
        }

        return true;
    }
}
