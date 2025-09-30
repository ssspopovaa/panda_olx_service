<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubscribeRequest;
use App\Services\SubscriptionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    private SubscriptionServiceInterface $service;

    public function __construct(SubscriptionServiceInterface $service)
    {
        $this->service = $service;
    }

    public function subscribe(SubscribeRequest $request): JsonResponse
    {
        try {
            $this->service->createSubscriptionFromRequest($request->validated());
            return response()->json(['status' => 'ok', 'message' => 'Verification email sent'], 202);
        } catch (\Throwable $e) {
            Log::error('Subscription failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function verify(Request $request): JsonResponse
    {
        $token = $request->query('token');

        if (!$token) {
            return response()->json(['error' => 'token_required'], 400);
        }
        $this->service->verifySubscriptionByToken($token);

        return response()->json(['status' => 'verified']);
    }

    public function listByEmail(Request $request): JsonResponse
    {
        $email = $request->query('email');
        if (!$email) return response()->json(['error' => 'email required'], 400);
        $subs = $this->service->listSubscriptionsByEmail($email);
        return response()->json(['data' => $subs]);
    }
}
