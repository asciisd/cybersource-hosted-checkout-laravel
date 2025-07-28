<?php

namespace Asciisd\Cybersource\Http\Controllers;

use Asciisd\Cybersource\Events\CybersourceHostedCheckoutApproved;
use Asciisd\Cybersource\Events\CybersourceHostedCheckoutCancelled;
use Asciisd\Cybersource\Events\CybersourceHostedCheckoutDeclined;
use Asciisd\Cybersource\Events\CybersourceHostedCheckoutError;
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
        } elseif ($request->decision === 'ERROR') {
            event(new CybersourceHostedCheckoutError($request->all()));
        } elseif ($request->decision === 'CANCEL') {
            event(new CybersourceHostedCheckoutCancelled($request->all()));
        } else {
            event(new CybersourceHostedCheckoutDeclined($request->all()));
        }

        return redirect(config('cybersource.redirect_url'));
    }

    public function handleNotification(Request $request)
    {
        // Log the incoming request for debugging
        logger()->info('Cybersource notification received', [
            'transaction_id' => $request->input('transaction_id'),
            'reference_number' => $request->input('req_reference_number'),
            'decision' => $request->input('decision'),
            'signed_field_names' => $request->input('signed_field_names'),
            'signature' => $request->input('signature'),
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
        } elseif ($request->decision === 'ERROR') {
            logger()->info('Firing CybersourceHostedCheckoutError event for ERROR decision', [
                'decision' => $request->decision,
                'reason_code' => $request->input('reason_code'),
                'message' => $request->input('message'),
            ]);
            event(new CybersourceHostedCheckoutError($request->all()));
        } elseif ($request->decision === 'CANCEL') {
            logger()->info('Firing CybersourceHostedCheckoutCancelled event for CANCEL decision', [
                'decision' => $request->decision,
                'message' => $request->input('message'),
                'reference_number' => $request->input('req_reference_number'),
            ]);
            event(new CybersourceHostedCheckoutCancelled($request->all()));
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
