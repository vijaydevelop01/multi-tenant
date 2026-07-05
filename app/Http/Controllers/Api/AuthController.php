<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Search every registered tenant for this email
        $matchedUser   = null;
        $matchedClient = null;

        foreach (Client::all() as $client) {
            TenantService::connect($client);

            $user = User::on('tenant')->where('email', $request->email)->first();

            if ($user) {
                $matchedUser   = $user;
                $matchedClient = $client;
                break;
            }
        }

        if (!$matchedUser || !Hash::check($request->password, $matchedUser->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'data'    => null,
            ], 401);
        }

        TenantService::connect($matchedClient);

        $token = $matchedUser->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user'  => $matchedUser,
            ],
        ], 200);
    }

    public function logout(Request $request)
    {
        $bearerToken = $request->bearerToken();

        if (!$bearerToken) {
            return response()->json([
                'success' => false,
                'message' => 'No token provided',
            ], 401);
        }

        [$tokenId, $plainToken] = array_pad(explode('|', $bearerToken, 2), 2, null);

        $tokenHash = hash('sha256', $plainToken);

        foreach (Client::all() as $client) {
            TenantService::connect($client);

            $accessToken = PersonalAccessToken::on('tenant')
                ->where('id', $tokenId)
                ->where('token', $tokenHash)
                ->first();

            if ($accessToken) {
                $accessToken->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Logged out successfully',
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired token',
        ], 401);
    }
}
