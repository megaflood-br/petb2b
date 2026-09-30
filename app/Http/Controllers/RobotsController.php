<?php

namespace App\Http\Controllers;

use App\Support\Settings;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /admin/',
            'Disallow: /minha-empresa',
            'Disallow: /minha-empresa/',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /livewire/',
            '',
        ];

        $extra = Settings::robotsExtra();
        if ($extra !== '') {
            $lines[] = $extra;
            $lines[] = '';
        }

        $lines[] = 'Sitemap: '.url('/sitemap.xml');

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
