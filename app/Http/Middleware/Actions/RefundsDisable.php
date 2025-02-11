<?php

namespace App\Http\Middleware\Actions;

use Closure;
use Illuminate\Http\Request;

class RefundsDisable
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        abort_if(!@settings('actions')->refunds, 404);
        return $next($request);
    }
}
