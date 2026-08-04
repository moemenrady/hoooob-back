<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\ZoneManagement\Http\Controllers\Api\New\Driver\ZoneController;
use Modules\ZoneManagement\Http\Controllers\Api\New\Driver\CarpoolStationController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'driver'], function () {
    Route::group(['prefix' => 'zone', 'middleware' => ['auth:api', 'maintenance_mode']], function () {

        Route::controller(ZoneController::class)->group(function () {
            Route::get('/list', 'list');
        });
    });
    Route::group(['prefix' => 'carpool-station', 'middleware' => ['auth:api',]], function () {

        Route::controller(CarpoolStationController::class)->group(function () {
            Route::get('/search', 'search');
        });
    });
});
