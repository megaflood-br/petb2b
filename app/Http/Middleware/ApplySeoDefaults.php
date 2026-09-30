<?php

namespace App\Http\Middleware;

use App\Support\Seo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplySeoDefaults
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        Seo::applyDefaults();

        if ($request->routeIs('general.search', 'claim.register', 'login', 'register', 'password.request', 'password.reset')) {
            Seo::noindex();
        }

        return $next($request);
    }

    private function shouldSkip(Request $request): bool
    {
        return $request->is([
            'admin',
            'admin/*',
            'livewire/*',
            'webhooks/*',
            'up',
            'robots.txt',
            'sitemap.xml',
            'favicon.ico',
        ]);
    }
}
