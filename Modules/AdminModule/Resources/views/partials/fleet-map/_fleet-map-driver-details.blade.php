<button type="button" class="btn customer-back-btn">
    <img src="{{asset('/public/assets/admin-module/img/maps/left-arrow.svg')}}"
         class="svg" alt=""> Driver List
</button>
<div class="customer-details-media">
    <img src="{{onErrorImage(
                $driver?->profile_image,
                asset('storage/app/public/driver/profile') . '/' . $driver?->profile_image,
                asset('public/assets/admin-module/img/avatar/avatar.png'),
                'driver/profile/',
            )}}"
         alt="">
    <div class="customer-details-media-content">
        <div class="d-flex gap-2">
            <h6>
                <a href="{{route('admin.driver.show', ['id' => $driver->id])}}">
                    {{$driver?->first_name . ' ' . $driver?->last_name}}
                </a>
            </h6>
            <div
                class="badge badge-pill badge-info ms-2">{{Carbon\Carbon::parse($driver->created_at)->diffInMonths(Carbon\Carbon::now())<6 ? translate("New") : ""}}</div>

        </div>
        <div class="my-2 d-flex gap-2">
            <div
                class="badge badge-success">{{translate("Level")}} - {{$driver->level->name ?? translate('no_level_found')}}</div>
            <div class="gap-1 ms-2">
                <i class="bi bi-star-fill text-warning"></i>
                {{ number_format($driver->receivedReviews->avg('rating'), 1) }}
            </div>
        </div>
        <small>{{$driver?->phone ?? "N/A"}}</small>
    </div>
</div>
<hr>
<div class="customer-details-media-info-card-body px-0 pt-0">
    <ul class="customer-details-media-info-card-body-list">
        <li>
            <span class="key">{{translate("Joining Date")}}</span>
            <span>:</span>
            <span class="value">{{ date('d M Y',strtotime($driver->created_at)) }}</span>
        </li>
        <li>
            <span class="key">{{translate("Service")}}</span>
            <span>:</span>
            <span class="value">
                 @if($driver?->driverDetails?->service)
                    @if(in_array('ride_request',$driver?->driverDetails?->service) && in_array('parcel',$driver?->driverDetails?->service))
                        {{translate("Ride Request")}}, {{translate("Parcel")}}
                    @elseif(in_array('ride_request',$driver?->driverDetails?->service))
                        {{translate("Ride Request")}}
                    @elseif(in_array('parcel',$driver?->driverDetails?->service))
                        {{translate("Parcel")}}
                    @endif
                @else
                    {{translate("Ride Request")}}, {{translate("Parcel")}}
                @endif
            </span>
        </li>
        <li>
            <span class="key">{{translate("Vehicle Category")}}</span>
            <span>:</span>
            <span class="value">{{$driver?->vehicle?->category->name}}</span>
        </li>
        <li>
            <span class="key">{{translate("Vehicle Number")}}</span>
            <span>:</span>
            <span class="value">{{$driver?->vehicle?->licence_plate_number}}</span>
        </li>
        <li>
            <span class="key">{{translate("Vehicle Brand")}}</span>
            <span>:</span>
            <span class="value">{{ $driver?->vehicle?->brand?->name }}</span>
        </li>
        <li>
            <span class="key">{{translate("Vehicle Model")}}</span>
            <span>:</span>
            <span class="value">{{$driver?->vehicle?->brand?->name}}</span>
        </li>
    </ul>
</div>
@php($trip = $driver?->driverTrips()?->whereIn('current_status',[ACCEPTED,ONGOING])->first())
@if($trip)
    <div class="customer-details-media-info-card shadow-none bg-input">
        <div class="customer-details-media-info-card-header">
            <span>{{translate("Ongoing Trip")}}</span>
            <span>
                {{translate("ID")}} #{{$trip->ref_id}}
                <a href="{{route('admin.trip.show', ['type' => ALL, 'id' => $trip->id, 'page' => 'summary'])}}"
                   target="_blank">
                    <img
                        src="{{asset('/public/assets/admin-module/img/maps/up-right-arrow-square.svg')}}"
                        class="svg" alt="">
                </a>
            </span>
        </div>
        <div class="customer-details-media-info-card-body">
            <ul class="customer-details-media-info-card-body-list border-list">
                <li>
                    <img src="{{asset('/public/assets/admin-module/img/maps/gps.svg')}}"
                         alt="" class="svg">
                    <span class="value">{{$trip?->coordinate?->pickup_address}}</span>
                </li>
                <li>
                    <img
                        src="{{asset('/public/assets/admin-module/img/maps/paper-plane-2.svg')}}"
                        alt="" class="svg">
                    <span class="value">{{$trip->coordinate->destination_address}}</span>
                </li>
            </ul>
        </div>
    </div>
@endif
