<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyRobotsDirectives
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('admin', 'admin/*')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        } elseif ($response->isSuccessful() && str_starts_with($response->headers->get('Content-Type', ''), 'text/html')) {
            $response->headers->set('X-Robots-Tag', 'index, follow');
        }

        return $response;
    }
}
