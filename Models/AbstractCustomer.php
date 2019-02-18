<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AbstractCustomer extends Model
{
    /**
     * Relation: one is to many
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inquiries()
    {
        return $this->hasMany('App\Inquiry');
    }

    /**
     * Returns Persona Model
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function persona()
    {
        return $this->belongsTo('App\Persona');
    }

    /**
     * Returns Budget Model
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function budget()
    {
        return $this->hasOne('App\Budget');
    }

    /**
     * Returns Destination Preference Model
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function destinationPreference()
    {
        return $this->hasOne('App\HotelPreference');
    }

    /**
     * Returns Flight Preference Model
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function flightPreference()
    {
        return $this->hasOne('App\FlightPreference');
    }

    /**
     * Returns Ground Transportation Preference Model
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function groundTransportationPreference()
    {
        return $this->hasOne('App\GroundTransportationPreference');
    }

    /**
     * Returns Index Preference Model
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function indexPreference()
    {
        return $this->hasOne('App\IndexPreference');
    }

    /**
     * Returns Interaction Preference Model
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function interactionPreference()
    {
        return $this->hasOne('App\InteractionPreference');
    }

    /**
     * Returns LoyaltyProgram Model
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function loyaltyProgram()
    {
        return $this->hasOne('App\LoyaltyProgram');
    }

    /**
     * Returns TravelProfile Model
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function travelProfile()
    {
        return $this->hasOne('App\TravelProfile');
    }
}
