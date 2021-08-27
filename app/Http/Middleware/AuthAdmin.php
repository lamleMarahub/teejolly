<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
//use Illuminate\Auth\Middleware\Authenticate as Middleware;


class AuthAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) return redirect('/login');

        if (!Auth::user()->isActive()) return redirect()->route('not-activated')->with('message','please ask an administrator for activation!');

        if (!Auth::user()->isAdmin()) return redirect('/home');
        
        return $next($request);
    }
}
