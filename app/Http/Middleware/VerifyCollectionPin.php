<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Hash;

class VerifyCollectionPin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return ApiResponse::error('Unauthorized', 401);
        }

        if (!$user->collection_pin) {
            return response()->json([
                'success' => false,
                'message' => 'PIN belum diatur',
                'requires_setup' => true
            ], 403);
        }

        $pin = $request->header('X-Collection-Pin') ?? $request->input('pin');

        if (!$pin) {
            return response()->json([
                'success' => false,
                'message' => 'PIN diperlukan',
                'requires_pin' => true
            ], 403);
        }

        if (!Hash::check($pin, $user->collection_pin)) {
            return ApiResponse::error('PIN salah', 403);
        }

        return $next($request);
    }
}
