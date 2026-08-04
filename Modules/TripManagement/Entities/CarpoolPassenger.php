<?php

namespace Modules\TripManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use Modules\UserManagement\Entities\User;

class CarpoolPassenger extends Model
{
    use HasFactory, HasSpatial;

    protected $fillable = [
       
        'user_id',
        'pickup_location',
        'dropoff_location',
        'status',
        'fare',
        'otp',
        'arrived_at',
        'left_at',
      'seats_count',
      'carpool_route_id',
      'driver_decision'
    ];

    protected $spatial = [
        'pickup_location',
        'dropoff_location',
    ];
  
     protected $casts = [
        'pickup_location' => Point::class,
        'dropoff_location' => Point::class,
        'arrived_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trip()
    {
        return $this->belongsTo(TripRequest::class, 'trip_request_id');
    }
  

public function route()
{
    return $this->belongsTo(CarpoolRoute::class, 'carpool_route_id');
}

}