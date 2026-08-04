<?php

namespace Modules\ZoneManagement\Http\Controllers\Web\New\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ZoneManagement\Entities\CarpoolStation;
use Modules\ZoneManagement\Entities\Zone;

class CarpoolStationController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->search;

        $stations = CarpoolStation::when($search, function ($q) use ($search) {
            $q->where('name', 'like', "%$search%");
        })
        ->latest()
        ->paginate(10);

        $zones = Zone::pluck('name','id');

        return view('zonemanagement::admin.carpool-station.index', compact('stations','zones','search'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'zone_id' => 'required'
        ]);

        CarpoolStation::create($request->all());

        return redirect()->back()->with('success','Station created successfully');
    }


    public function edit($id)
    {
        $station = CarpoolStation::findOrFail($id);
        $zones = Zone::pluck('name','id');

        return view('zonemanagement::admin.carpool-station.edit', compact('station','zones'));
    }


    public function update(Request $request, $id)
    {
        $station = CarpoolStation::findOrFail($id);

        $station->update($request->all());

        return redirect()->route('admin.carpool-stations.index')
            ->with('success','Station updated successfully');
    }


    public function destroy($id)
    {
        $station = CarpoolStation::findOrFail($id);
        $station->delete();

        return redirect()->back()->with('success','Station deleted successfully');
    }
}