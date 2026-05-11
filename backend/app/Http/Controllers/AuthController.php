<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\PatientRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class AuthController extends Controller
{
    public function __construct(private readonly PatientRepository $patients)
    {
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $patient = $this->patients->findByLogin($request->input('login'));

        if ($patient === null || $patient->birth_date->toDateString() !== $request->input('password')) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        /** @var JWTGuard $guard */
        $guard = auth('api');

        return response()->json(['token' => $guard->login($patient)]);
    }
}
