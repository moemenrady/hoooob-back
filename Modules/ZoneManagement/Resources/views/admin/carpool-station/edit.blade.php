@extends('adminmodule::layouts.master')
@section('title', translate('edit_station'))

@section('content')
<div class="main-content">
    <div class="container-fluid">
        <h2 class="fs-22 mb-3">{{ translate('edit_carpool_station') }}</h2>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.carpool-stations.update', $station->id) }}" method="POST">
                    @csrf
                    @method('put')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ translate('station_name') }}</label>
                            <input type="text" name="name" value="{{ $station->name }}" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ translate('zone') }}</label>
                            <select name="zone_id" class="form-control js-select">
                                @foreach($zones as $id => $name)
                                    <option value="{{ $id }}" {{ $station->zone_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ translate('latitude') }}</label>
                            <input type="number" step="any" name="latitude" value="{{ $station->latitude }}" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ translate('longitude') }}</label>
                            <input type="number" step="any" name="longitude" value="{{ $station->longitude }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-3">
                        <button type="reset" class="btn btn-secondary">{{ translate('reset') }}</button>
                        <button type="submit" class="btn btn-primary">{{ translate('update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection