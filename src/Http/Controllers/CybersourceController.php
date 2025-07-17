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

        logger()->info('Cybersource handleResponse', $request->all());

        return redirect(config('cybersource.redirect_url'));
    }

    public function handleNotification(Request $request)
    {
        // Log the incoming request for debugging
        logger()->info('Cybersource notification received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        // Check signature verification
        $signatureValid = Cybersource::verifySignature($request->all());
        logger()->info('Cybersource signature verification', [
            'valid' => $signatureValid,
            'signature' => $request->signature,
            'signed_field_names' => $request->signed_field_names,
        ]);

        if (! $signatureValid) {
            logger()->error('Cybersource notification signature verification failed');
            abort(403);
        }

        // Always fire the notification received event
        logger()->info('Firing CybersourceHostedCheckoutNotificationReceived event');
        event(new CybersourceHostedCheckoutNotificationReceived($request->all()));

        // Check the decision and fire appropriate events
        if ($request->decision === 'ACCEPT') {
            logger()->info('Firing CybersourceHostedCheckoutApproved event for ACCEPT decision');
            event(new CybersourceHostedCheckoutApproved($request->all()));
        } else {
            logger()->info('Firing CybersourceHostedCheckoutDeclined event for non-ACCEPT decision', [
                'decision' => $request->decision,
            ]);
            event(new CybersourceHostedCheckoutDeclined($request->all()));
        }

        logger()->info('Cybersource handleNotification completed successfully');

        return response()->json(['status' => 'ok']);
    }
}
