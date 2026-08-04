<?php

namespace Modules\ZoneManagement\Http\Controllers\Api\New\Driver;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ZoneManagement\Entities\CarpoolStation;
use Illuminate\Support\Facades\Validator;

class CarpoolStationController extends Controller
{


    /**
     * Search stations by name or closest to lat/lng
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = CarpoolStation::query();

        // Search by name if q exists
        if ($request->has('q') && $request->q) {
            $query->where('name', 'LIKE', $request->q . '%');
        }

        // Search by nearest lat/lng if provided
        if ($request->has('lat') && $request->has('lng') && $request->lat && $request->lng) {
            $lat = $request->lat;
            $lng = $request->lng;

            // Haversine formula to calculate distance
            $query->selectRaw("*, ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance", [$lat, $lng, $lat])
                ->orderBy('distance')
                ->limit(10);
        } else {
            $query->latest();
        }

        $stations = $query->get();

        return response()->json($stations);
    }
}
