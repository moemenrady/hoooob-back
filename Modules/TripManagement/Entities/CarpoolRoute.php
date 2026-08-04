<?php

namespace Modules\TripManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Modules\UserManagement\Entities\User;
use Modules\VehicleManagement\Entities\Vehicle;


class CarpoolRoute extends Model
{
    use HasFactory, HasSpatial;

    protected $fillable = [
      'user_id',
        'trip_request_id',
        'start_location',
        'end_location',
        'route_points',
          'start_address',
          'end_address',
          'is_ac',
          'is_smoking_allowed',
          'seats_available',
      'start_time',
    'end_time',
      'ride_type',
      'has_music',
    'has_screen_entertainment',
    'allow_luggage',
    'allowed_gender',
    'allowed_age_min',
    'allowed_age_max',
    'seats_available',
    'ride_type',
    'price',
    'status',
      'rest_stops',
       'is_trip_started',
      'trip_started_at',
      'vehicle_id',
      'encoded_polyline',
    ];

    protected $spatial = [
        'start_location',
        'end_location',
    ];
  

    protected $casts = [
        'start_location' => Point::class,
        'end_location' => Point::class,

        'route_points' => 'array',
        'start_time' => 'datetime',
      'is_ac' => 'boolean',
    'is_smoking_allowed' => 'boolean',
      'end_time' => 'datetime',
       'has_music' => 'boolean',
    'has_screen_entertainment' => 'boolean',
    'allow_luggage' => 'boolean'
    ];

    public function trip()
    {
        return $this->hasMany(TripRequest::class, 'carpool_route_id', 'id');
    }
   public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
  
  public function passengers()
{
    return $this->hasMany(CarpoolPassenger::class, 'carpool_route_id');
}
  public function vehicle()
{
    return $this->belongsTo(Vehicle::class);
}

}
