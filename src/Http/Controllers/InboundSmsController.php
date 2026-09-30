<?php

namespace DoxaSoft\LaravelSeeMe\Http\Controllers;

use DoxaSoft\LaravelSeeMe\Events\InboundSmsReceived;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class InboundSmsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        InboundSmsReceived::dispatch(
            (string) $request->query('message', ''),
            (string) $request->query('number', ''),
            (string) $request->query('destination', ''),
            (string) $request->query('timestamp', ''),
        );

        return response('', 200);
    }
}
