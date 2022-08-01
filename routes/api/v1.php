<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::name("api.v1")->group(function () {
    Route::prefix("consultant")->namespace("Consultant")->group(function (){
        Route::get("/", 'ConsultantController@index');
        Route::post("/", 'ConsultantController@reportConsultant');
        Route::post("/total_net_earnings_fixed_cost", 'ConsultantController@totalNetEarningsFixedCost');
        Route::post("/total_net_earnings", 'ConsultantController@totalNetEarnings');
    });
});
