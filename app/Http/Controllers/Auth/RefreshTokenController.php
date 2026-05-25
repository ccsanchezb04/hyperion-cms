<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RefreshTokenController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $request->user()
            ->currentAccessToken()
            ->delete();

        $token = $user
            ->createToken('hyperion-token')
            ->plainTextToken;

        return response()->json([
            'token' => $token
        ]);
    }
}
