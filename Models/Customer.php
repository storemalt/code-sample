<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends AbstractCustomer
{

    /**
     * pseudo deletion, sets column deleted_at to timestamp
     */
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'customers';

    /**
     * The database primary key value.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ip_address',
        'name',
        'email',
        'phone',
        'persona_id',
        'persona_id',
        'birthday',
        'gender',
        'work_email',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'education',
        'relationship_status',
        'interests',
        'occupation',
        'company',
        'specialization',
        'skype',
        'facebook',
        'twitter',
        'instagram',
        'tags',
        'created_by'
    ];

    /**
     * IP Address formatter, returns IP Address
     * Converts binary to string readable format
     *
     * @param $ipAddress string standard ipv4 or ipv6
     * @return integer
     */
    public function getIpAddressAttribute($ipAddress)
    {
        return long2ip($ipAddress);
    }

    /**
     * IP Address formatter
     * Converts string to binary format
     *
     * @param $ipAddress IP address
     */
    public function setIpAddressAttribute($ipAddress)
    {
        $this->attributes['ip_address'] = ip2long($ipAddress);
    }
}
