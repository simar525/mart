<?php

namespace App\Http\Middleware;

use App\Methods\Maintenance;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (settings('maintenance')->status && !authAdmin()) {
            return redirect()->route('home');
        }
        return $next($request);
    }
}
