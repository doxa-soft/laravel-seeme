<?php

use DoxaSoft\LaravelSeeMe\Http\Controllers\DeliveryReportController;
use DoxaSoft\LaravelSeeMe\Http\Controllers\InboundSmsController;
use DoxaSoft\LaravelSeeMe\Http\Middleware\ValidateSeeMeIp;
use Illuminate\Support\Facades\Route;

Route::middleware(ValidateSeeMeIp::class)->group(function () {

    if (config('seeme.webhooks.delivery_report.enabled')) {
        Route::get(
            config('seeme.webhooks.delivery_report.path', 'seeme/delivery-report'),
            DeliveryReportController::class,
        )->name('seeme.delivery-report');
    }

    if (config('seeme.webhooks.inbound_sms.enabled')) {
        Route::get(
            config('seeme.webhooks.inbound_sms.path', 'seeme/inbound'),
            InboundSmsController::class,
        )->name('seeme.inbound-sms');
    }

});
