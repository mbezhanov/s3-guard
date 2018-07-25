<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

class AuthenticateWithBasicAuth
{
    protected $auth;

    public function __construct(AuthFactory $auth)
    {
        $this->auth = $auth;
    }

    public function handle($request, Closure $next, $guard = null)
    {
        return $this->auth->guard($guard)->basic('username', ['name' => $request->getHost()]) ?: $next($request);
    }
}
