<?php

use Illuminate\Support\Facades\Route;
use Modules\TripManagement\Http\Controllers\Api\Customer\TripRequestController;
use Modules\TripManagement\Http\Controllers\Api\Driver\TripRequestController as DriverTripController;
use Modules\TripManagement\Http\Controllers\Api\New\Customer\ParcelRefundController;
use Modules\TripManagement\Http\Controllers\Api\PaymentController;
use Modules\TripManagement\Http\Controllers\Api\UploadController;
use Modules\TripManagement\Http\Controllers\Api\New\Driver\CarpoolingExtensionController;

//upload files
Route::post('/upload', [UploadController::class, 'upload']);


/**
 * CUSTOMER API LIST
 */
Route::group(['prefix' => 'customer', 'middleware' => ['auth:api', 'maintenance_mode']], function () {
    Route::get('drivers-near-me', [TripRequestController::class, 'driversNearMe']);
    Route::group(['prefix' => 'ride'], function () {
        Route::controller(TripRequestController::class)->group(function () {
            Route::post('get-estimated-fare', 'getEstimatedFare');
            Route::post('create', 'createRideRequest');
            Route::put('ignore-bidding', 'ignoreBidding');
            Route::get('bidding-list/{trip_request_id}', 'biddingList');
            Route::put('update-status/{trip_request_id}', 'rideStatusUpdate');
            Route::get('details/{trip_request_id}', 'rideDetails');
            Route::get('list', 'rideList');
            Route::get('final-fare', 'finalFareCalculation');
            Route::post('trip-action', 'requestAction');
            Route::get('ride-resume-status', 'rideResumeStatus');
            Route::put('arrival-time', 'arrivalTime');
            Route::put('coordinate-arrival', 'coordinateArrival');
            Route::put('apply-coupon', 'applyCoupon');
            Route::put('cancel-coupon', 'cancelCoupon');
            Route::get('ongoing-parcel-list', 'pendingParcelList');
            Route::get('unpaid-parcel-list', 'unpaidParcelRequest');
            Route::put('received-returning-parcel/{trip_request_id}', 'receivedReturningParcel');
        });
        Route::post('track-location', [DriverTripController::class, 'trackLocation']);
        Route::get('payment', [PaymentController::class, 'payment']);
        Route::get('digital-payment', [PaymentController::class, 'digitalPayment'])->withoutMiddleware('auth:api');
    });
    Route::group(['prefix' => 'parcel'], function () {
        Route::controller(ParcelRefundController::class)->group(function () {
            Route::group(['prefix' => 'refund'], function () {
                Route::post('create', 'createParcelRefundRequest');
            });
        });
    });
});


/**
 * DRIVER API LIST
 */
Route::group(['prefix' => 'driver', 'middleware' => ['auth:api', 'maintenance_mode']], function () {
    Route::get('current-trips-with-passengers', [CarpoolingExtensionController::class, 'getDriverCarpoolRidesWithPassengers']);
    Route::post('last-ride-details', [DriverTripController::class, 'lastRideDetails']);
    Route::group(['prefix' => 'ride', 'middleware' => ['auth:api', 'maintenance_mode']], function () {
        Route::controller(DriverTripController::class)->group(function () {
            Route::post('bid', 'bid');
            Route::post('trip-action', 'requestAction');
            Route::put('update-status', 'rideStatusUpdate');
            Route::post('match-otp', 'matchOtp');
            Route::post('track-location', 'trackLocation');
            Route::get('details/{ride_request_id}', 'rideDetails');
            Route::get('list', 'rideList');
            Route::get('pending-ride-list', 'pendingRideList');
//            Route::get('ride-resume-status', 'rideResumeStatus');
            Route::put('ride-waiting', 'rideWaiting');
            Route::put('arrival-time', 'arrivalTime');
            Route::put('coordinate-arrival', 'coordinateArrival');
            Route::get('overview', 'tripOverView');
            Route::post('ignore-trip-notification', 'ignoreTripNotification');
            Route::get('ongoing-parcel-list', 'pendingParcelList');
            Route::get('unpaid-parcel-list', 'unpaidParcelRequest');
            Route::put('returned-parcel', 'returnedParcel');
            Route::put('resend-otp', 'resendOtp');
        });
        Route::get('final-fare', [TripRequestController::class, 'finalFareCalculation']);
        Route::get('payment', [PaymentController::class, 'payment']);

        Route::controller(\Modules\TripManagement\Http\Controllers\Api\New\Driver\TripRequestController::class)->group(function () {
            Route::get('current-ride-status', 'currentRideStatus');
        });
    });

    Route::get('pending-ride-list-test', [DriverTripController::class, 'test']);
});

Route::middleware(['auth:api'])->group(function () {

    // Driver routes
    Route::prefix('driver')->group(function () {
        Route::post('register-route', [CarpoolingExtensionController::class, 'registerDriverRoute']);
        Route::post('start-trip', [CarpoolingExtensionController::class, 'beginTrip']);
        Route::post('end-trip', [CarpoolingExtensionController::class, 'endTrip']);
        Route::get('trip-summary/{trip_request_id}', [CarpoolingExtensionController::class, 'tripSummary']);
        Route::get('trip-schedule', [CarpoolingExtensionController::class, 'getTripSchedule']);
        Route::get('carpool-routes', [CarpoolingExtensionController::class, 'getDriverCarpoolRidesWithPassengers']);
        Route::post('review', [CarpoolingExtensionController::class, 'reviewPassengerRequest']);

    });

    // Passenger routes
    Route::prefix('passenger')->group(function () {
        Route::post('find-match', [CarpoolingExtensionController::class, 'findMatchingRidesForPassenger']);
        Route::post('join', [CarpoolingExtensionController::class, 'joinTrip']);
        Route::post('match-otp', [CarpoolingExtensionController::class, 'matchPassengerOtp']);
        Route::post('drop', [CarpoolingExtensionController::class, 'dropPassenger']);
        Route::post('suggest-dropoff', [CarpoolingExtensionController::class, 'suggestDropoff']);
              Route::get('trips', [CarpoolingExtensionController::class, 'getUserTrips']);

    });

});

Route::post('ride/store-screenshot', [TripRequestController::class, 'storeScreenshot'])->middleware('auth:api');
