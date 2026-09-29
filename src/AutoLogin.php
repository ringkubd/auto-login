<?php

namespace Anwar\AutoLogin;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AutoLogin
{
    /**
     * Generate a new auto-login token for the given credentials.
     *
     * The plaintext token is returned to the caller exactly once; only a
     * SHA-256 hash is persisted, so a database leak cannot be replayed.
     *
     * @param  string  $email
     * @param  string  $password
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateToken($email, $password)
    {
        $table = $this->usersTable();

        $failure = response()->json([
            'status'  => false,
            'message' => 'Check your email and password.',
        ], 403);

        if (!is_string($email) || !is_string($password) || $email === '' || $password === '') {
            return $failure;
        }

        $userData = \DB::table($table)->where('email', $email)->first();

        // Generic failure: never reveal whether the email exists.
        if (!$userData || empty($userData->password) || !Hash::check($password, $userData->password)) {
            return $failure;
        }

        // Only usable accounts may obtain a token.
        if (config('autologin.require_active', true)) {
            if (property_exists($userData, 'active') && !$userData->active) {
                return $failure;
            }
            if (property_exists($userData, 'is_delete') && $userData->is_delete) {
                return $failure;
            }
        }

        $plainToken = Str::random(64);

        \DB::table($table)->where('id', $userData->id)->update([
            'app_token'     => hash('sha256', $plainToken),
            'app_reference' => request()->url(),
        ]);

        return response()->json([
            'status'    => true,
            'app_token' => $plainToken,
        ]);
    }

    /**
     * Resolve the configured users table name.
     *
     * Supports both the historical `autologin.*` config key (published file)
     * and the package default `auto-login.*` key.
     *
     * @return string
     */
    public function usersTable()
    {
        $table = config('autologin.users_table');

        if (empty($table)) {
            $table = config('auto-login.users_table', 'users');
        }

        return $table ?: 'users';
    }
}
