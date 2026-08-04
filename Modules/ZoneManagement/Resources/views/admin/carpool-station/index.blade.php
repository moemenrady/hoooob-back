@extends('adminmodule::layouts.master')

@section('title', translate('carpool_stations'))

@push('css_or_js')
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="fs-22 mb-3 text-capitalize">{{ translate('carpool_stations') }}</h2>

            <div class="row g-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="{{ url()->current() }}" class="search-form search-form_style-two"
                                    method="GET">
                                    <div class="input-group search-form__input_group">
                                        <span class="search-form__icon">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                            value="{{ $search }}" name="search" id="search"
                                            placeholder="{{ translate('search_by_station_name') }}">
                                    </div>
                                    <button type="submit"
                                        class="btn btn-primary search-submit">{{ translate('search') }}</button>
                                </form>

                                <div class="d-flex flex-wrap gap-3">
                                    <button type="button" class="btn btn-primary text-capitalize" data-bs-toggle="modal"
                                        data-bs-target="#addStationModal">
                                        <i class="bi bi-plus fs-16"></i> {{ translate('add_new_station') }}
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive mt-3">
                                <table class="table table-borderless align-middle">
                                    <thead class="table-light align-middle">
                                        <tr>
                                            <th class="sl">{{ translate('SL') }}</th>
                                            <th>{{ translate('station_name') }}</th>
                                            <th>{{ translate('latitude') }}</th>
                                            <th>{{ translate('longitude') }}</th>
                                            <th>{{ translate('zone') }}</th>
                                            <th class="text-center action">{{ translate('action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($stations as $key => $station)
                                            <tr>
                                                <td class="sl">{{ $stations->firstItem() + $key }}</td>
                                                <td>{{ $station->name }}</td>
                                                <td>{{ $station->latitude }}</td>
                                                <td>{{ $station->longitude }}</td>
                                                <td><span
                                                        class="badge bg-soft-info text-info">{{ $station->zone_id ?? 'N/A' }}</span>
                                                </td>
                                                <td class="action">
                                                    <div class="d-flex justify-content-center gap-2 align-items-center">
                                                        <a href="{{ route('admin.carpool-stations.edit', ['id' => $station->id]) }}"
                                                            class="btn btn-outline-info btn-action">
                                                            <i class="bi bi-pencil-fill"></i>
                                                        </a>
                                                        <button data-id="delete-{{ $station->id }}"
                                                            data-message="{{ translate('want_to_delete_this_station?') }}"
                                                            type="button"
                                                            class="btn btn-outline-danger btn-action form-alert">
                                                            <i class="bi bi-trash-fill"></i>
                                                        </button>
                                                        <form
                                                            action="{{ route('admin.carpool-stations.delete', ['id' => $station->id]) }}"
                                                            id="delete-{{ $station->id }}" method="post">
                                                            @csrf
                                                            @method('delete')
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6">
                                                    <div
                                                        class="d-flex flex-column justify-content-center align-items-center gap-2 py-3">
                                                        <img src="{{ asset('public/assets/admin-module/img/empty-icons/no-data-found.svg') }}"
                                                            alt="" width="100">
                                                        <p class="text-center">{{ translate('no_data_available') }}</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $stations->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addStationModal" tabindex="-1" aria-labelledby="addStationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStationModalLabel">{{ translate('add_new_carpool_station') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.carpool-stations.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ translate('station_name') }}</label>
                            <input type="text" name="name" class="form-control"
                                placeholder="{{ translate('ex: Downtown') }}" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ translate('latitude') }}</label>
                                <input type="number" step="any" name="latitude" class="form-control"
                                    placeholder="30.0444" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ translate('longitude') }}</label>
                                <input type="number" step="any" name="longitude" class="form-control"
                                    placeholder="31.2357" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ translate('select_zone') }}</label>
                            <select name="zone_id" class="form-control js-select" required>
                                @foreach ($zones as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ translate('close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ translate('save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.js-select').select2({
                placeholder: "{{ translate('select_zone') }}",
                width: '100%'
            });
        });
    </script>
@endsection
