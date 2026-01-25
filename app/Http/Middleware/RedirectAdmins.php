<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectAdmins
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is authenticated and is an admin
        if (auth()->check() && auth()->user()->role === 'administrator') {
            // If they're trying to access home page and not explicitly viewing site
            if ($request->route()->getName() === 'home' && !$request->get('view_site')) {
                return redirect()->route('admin.dashboard.index');
            }
        }

        return $next($request);
    }
}
