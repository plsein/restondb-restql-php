<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Contracts\Auth\Factory as Auth;
use App\Security\JwtAuth;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($this->auth->guard($guard)->guest()) {
            $token = $this->validateServiceJwt($request);
            if (empty($token) || !is_array($token) || !count($token) > 0) {
                throw_if(
                    (empty($token) || !is_array($token) || !count($token) > 0),
                    new Exception('Unauthorized')
                );
                // return response('Unauthorized.', 401);
            }
        }

        return $next($request);
    }

    private function validateServiceJwt($request) {
        // $headers = $request->headers->all();
        $authToken = $request->header('Authorization');
        $jwtToken = trim(str_replace(['Bearer ', 'Token ', 'Basic '], '', $authToken));
        return JwtAuth::validateJwt($jwtToken);
    }

}
