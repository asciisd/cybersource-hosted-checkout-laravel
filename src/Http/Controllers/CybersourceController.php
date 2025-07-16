<?php

namespace Asciisd\Cybersource\Http\Controllers;

use Asciisd\Cybersource\Events\NotificationReceived;
use Asciisd\Cybersource\Events\TransactionApproved;
use Asciisd\Cybersource\Events\TransactionDeclined;
use Asciisd\Cybersource\Facades\Cybersource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class CybersourceController extends Controller
{
    public function handleResponse(Request $request)
    {
        if (!Cybersource::verifySignature($request->all())) {
            Log::error('Cybersource response signature verification failed.', $request->all());
            return response('Invalid signature.', 400);
        }

        $payload = $request->all();

        if ($payload['decision'] === 'ACCEPT') {
            event(new TransactionApproved($payload));
        } else {
            event(new TransactionDeclined($payload));
        }

        return response('Response received.');
    }

    public function handleNotification(Request $request)
    {
        if (!Cybersource::verifySignature($request->all())) {
            Log::error('Cybersource notification signature verification failed.', $request->all());
            return response('Invalid signature.', 400);
        }

        event(new NotificationReceived($request->all()));

        return response('Notification received.');
    }
} 