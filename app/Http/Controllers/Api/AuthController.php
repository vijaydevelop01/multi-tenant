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
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        TenantService::connect($matchedClient);

        $token = $matchedUser->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $matchedUser,
        ], 200);
    }
}
