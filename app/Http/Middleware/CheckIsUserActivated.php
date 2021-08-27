<?php

namespace App\Http\Middleware;

use Closure;

class CheckIsUserActivated
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
        if ((auth()->user() == false)) {
            return redirect()->route('login')->with('message','please login!');
        }

        if ((auth()->user()->is_active == false)) {
            return redirect()->route('not-activated')->with('message','please ask an administrator for activation!');
        }

        return $next($request);
    }
}
