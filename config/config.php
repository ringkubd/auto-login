<?php

/*
 * Auto-login package configuration.
 *
 * NOTE: the package service provider merges this file under BOTH the
 * `auto-login` key (package default) and, for backward compatibility, code
 * reads `autologin.*` if it has been published to config/autologin.php.
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Users table
    |--------------------------------------------------------------------------
    | Table holding the accounts that may use auto-login.
    */
    'users_table' => 'users',

    /*
    |--------------------------------------------------------------------------
    | Redirect target
    |--------------------------------------------------------------------------
    | Where a successfully auto-logged-in user is sent.
    */
    'redirect_to' => '/',

    /*
    |--------------------------------------------------------------------------
    | Require active accounts
    |--------------------------------------------------------------------------
    | When true (recommended), only users with `active = 1` and
    | `is_delete = 0` can obtain or consume a token.
    */
    'require_active' => true,

    /*
    |--------------------------------------------------------------------------
    | Shared secret (optional)
    |--------------------------------------------------------------------------
    | When set, `POST auto-login/generate_token` must send a matching
    | `X-AutoLogin-Secret` header. Intended for trusted server-to-server calls.
    | Leave empty to disable.
    */
    'shared_secret' => '',

    /*
    |--------------------------------------------------------------------------
    | Token length
    |--------------------------------------------------------------------------
    */
    'token_length' => 64,
];
