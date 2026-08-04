@forelse($drivers as $driver)
    <li class="driver-details">
        <label class="form-check" data-id="{{$driver->id}}">
            <img class="form-check-img svg"
                 src="{{$driver?->driverTrips()?->whereIn('current_status',[ACCEPTED,ONGOING])->where('type', RIDE_REQUEST)->first() ? asset('/public/assets/admin-module/img/maps/paper-plane.svg') :asset('/public/assets/admin-module/img/maps/idle.svg') }}"
                 alt="">
            <div class="form-check-label">
                <h5 class="zone-name">{{$driver->full_name ??  ($driver->first_name ? $driver->first_name .' '.$driver->last_name : "N/A" ) }}
                    <span
                        class="badge badge-info">{{Carbon\Carbon::parse($driver->created_at)->diffInMonths(Carbon\Carbon::now())<6 ? translate("New") : ""}}</span>
                </h5>
                <div class="d-flex flex-wrap gap-2">
                    <div class="w-100">
                        <span>{{translate("phone")}}</span>
                        <span>:</span>
                        <span>{{$driver->phone}}</span>
                    </div>
                    <div>
                        <span>{{translate("Vehicle No")}}</span>
                        <span>:</span>
                        <span>{{$driver?->vehicle?->licence_plate_number ?? "N/A"}}</span>
                    </div>
                    <span class="fs-8">|</span>
                    <div>
                        <span>{{translate("Model")}}</span>
                        <span>:</span>
                        <span>{{$driver?->vehicle?->model?->name ?? "N/A"}}</span>
                    </div>
                </div>
            </div>
        </label>
    </li>
@empty
    <div class="d-flex justify-content-center mt-2" id="">
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="d-flex flex-column align-items-center gap-20">
                <img width="38"
                     src="{{ asset('/public/assets/admin-module/img/svg/driver-man.svg') }}"
                     alt="">
                <p class="fs-12">{{ translate('no driver found') }}</p>
            </div>
        </div>
    </div>
@endforelse
