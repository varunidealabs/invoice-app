<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanySetup
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for authenticated users
        if (auth()->check()) {
            // If user doesn't have a company and is not on company setup routes
            if (!auth()->user()->hasCompany() && !$request->routeIs('company.*')) {
                return redirect()->route('company.create')
                    ->with('info', 'Please set up your company profile to continue.');
            }
        }

        return $next($request);
    }
}