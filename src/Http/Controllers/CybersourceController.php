<?php

namespace Asciisd\Cybersource\Http\Controllers;

use Asciisd\Cybersource\Events\CybersourceHostedCheckoutApproved;
use Asciisd\Cybersource\Events\CybersourceHostedCheckoutDeclined;
use Asciisd\Cybersource\Events\CybersourceHostedCheckoutNotificationReceived;
use Asciisd\Cybersource\Facades\Cybersource;
use Illuminate\Http\Request;

class CybersourceController
{
    public function handleResponse(Request $request)
    {
        if (! Cybersource::verifySignature($request->all())) {
            abort(403);
        }

        if ($request->decision === 'ACCEPT') {
            event(new CybersourceHostedCheckoutApproved($request->all()));
        } else {
            event(new CybersourceHostedCheckoutDeclined($request->all()));
        }

        return redirect(config('cybersource.redirect_url'));
    }

    public function handleNotification(Request $request)
    {
        if (! Cybersource::verifySignature($request->all())) {
            abort(403);
        }

        event(new CybersourceHostedCheckoutNotificationReceived($request->all()));

        return response()->json(['status' => 'ok']);
    }
}
