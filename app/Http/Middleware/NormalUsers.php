<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class NormalUsers
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
        if(Auth::user()->utype!="user"){
            return redirect()->guest('/');
        }
        return $next($request);
    }
}
