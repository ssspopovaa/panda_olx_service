<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscribeRequest;
use App\Services\SubscriptionServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    private SubscriptionServiceInterface $service;

    public function __construct(SubscriptionServiceInterface $service)
    {
        $this->service = $service;
    }

    public function showSubscribeForm()
    {
        return view('subscribe');
    }

    public function subscribe(SubscribeRequest $request)
    {
        try {
            $this->service->createSubscriptionFromRequest($request->validated());
            return redirect()->route('subscribe.form')->with('success', 'Verification email sent!');
        } catch (\Throwable $e) {
            Log::error('Subscription failed: ' . $e->getMessage());
            return redirect()->route('subscribe.form')->with('error', $e->getMessage());
        }
    }

    public function verify(Request $request)
    {
        $token = $request->query('token');
        if (!$token) {
            return redirect('/')->with('error', 'Token required');
        }
        try {
            $this->service->verifySubscriptionByToken($token);
            return view('verified');
        } catch (\Throwable $e) {
            return redirect('/')->with('error', 'Invalid token');
        }
    }

    public function showSubscriptionsForm()
    {
        return view('subscriptions');
    }

    public function listSubscriptions(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $email = $request->input('email');
        $subs = $this->service->listSubscriptionsByEmail($email);
        return view('subscriptions', ['subscriptions' => $subs]);
    }
}
