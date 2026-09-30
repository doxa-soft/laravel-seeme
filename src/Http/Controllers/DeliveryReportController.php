<?php

namespace DoxaSoft\LaravelSeeMe\Http\Controllers;

use DoxaSoft\LaravelSeeMe\Enums\DeliveryStatus;
use DoxaSoft\LaravelSeeMe\Events\DeliveryReportReceived;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class DeliveryReportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        DeliveryReportReceived::dispatch(
            $request->query('reference'),
            (string) $request->query('number', ''),
            (string) $request->query('sender', ''),
            DeliveryStatus::from((int) $request->query('code', 0)),
            (string) $request->query('message', ''),
            (float) $request->query('price', 0),
            (string) $request->query('timestamp', ''),
            $request->has('mccmnc') ? (int) $request->query('mccmnc') : null,
            (int) $request->query('split', 1),
        );

        return response('', 200);
    }
}
