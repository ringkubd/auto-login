<?php

namespace Anwar\AutoLogin\Controllers;

use Anwar\AutoLogin\AutoLogin;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AutoLoginController extends BaseController
{
    use DispatchesJobs, ValidatesRequests;

    /**
     * Exchange email + password for an auto-login token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateToken(Request $request)
    {
        // Optional shared secret for server-to-server calls.
        $expected = (string) config('autologin.shared_secret', '');
        if ($expected !== '') {
            $provided = (string) $request->header('X-AutoLogin-Secret', '');
            if (!hash_equals($expected, $provided)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthorized.',
                ], 401);
            }
        }

        $validate = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'error'  => $validate->errors(),
            ], 422);
        }

        $autologin = new AutoLogin();

        return $autologin->generateToken($request->email, $request->password);
    }

    /**
     * Consume an auto-login token and authenticate the matching user.
     *
     * Backward compatible with legacy plaintext tokens: if a token does not
     * match a hashed value, it is compared against legacy plaintext values and
     * transparently upgraded to a hash on first successful use.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function autoLogin(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'app_token' => 'required|string',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'error'  => $validate->errors(),
            ], 422);
        }

        $autologin = new AutoLogin();
        $table = $autologin->usersTable();
        $token = (string) $request->app_token;

        $user = DB::table($table)->where('app_token', hash('sha256', $token))->first();

        // Transparent legacy migration: accept old plaintext tokens once and
        // re-store them as a hash.
        if (!$user && strlen($token) === 60) {
            $legacy = DB::table($table)->where('app_token', $token)->first();
            if ($legacy) {
                DB::table($table)->where('id', $legacy->id)->update([
                    'app_token' => hash('sha256', $token),
                ]);
                $user = $legacy;
            }
        }

        if (!$user) {
            return response()->json([
                'status' => false,
                'error'  => 'Login Failed',
            ], 403);
        }

        // Only usable accounts may be logged in.
        if (config('autologin.require_active', true)) {
            if ((property_exists($user, 'active') && !$user->active)
                || (property_exists($user, 'is_delete') && $user->is_delete)) {
                return response()->json([
                    'status' => false,
                    'error'  => 'Login Failed',
                ], 403);
            }
        }

        auth()->loginUsingId($user->id);

        // Prevent session fixation.
        $request->session()->regenerate();

        return redirect(config('autologin.redirect_to', '/'));
    }
}
