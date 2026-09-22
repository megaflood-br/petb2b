<?php

namespace App\Http\Middleware;

use App\Support\Settings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SiteMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Settings::maintenanceEnabled()) {
            return $next($request);
        }

        if ($this->isExempt($request)) {
            return $next($request);
        }

        return response()
            ->view('pages.maintenance', [
                'message' => Settings::maintenanceMessage(),
            ], 503)
            ->header('Retry-After', '3600');
    }

    private function isExempt(Request $request): bool
    {
        if ($request->user()?->role === 'admin') {
            return true;
        }

        return $request->is([
            'login',
            'logout',
            'forgot-password',
            'reset-password',
            'reset-password/*',
            'confirm-password',
            'verify-email',
            'verify-email/*',
            'admin',
            'admin/*',
            'livewire/*',
            'webhooks/*',
            'up',
        ]);
    }
}
