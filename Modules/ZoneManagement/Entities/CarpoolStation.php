<?php

namespace Modules\ZoneManagement\Entities;

use Illuminate\Database\Eloquent\Model;

class CarpoolStation extends Model
{
    protected $table = 'carpool_stations';

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'zone_id'
    ];
}