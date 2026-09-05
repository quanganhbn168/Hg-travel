<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SeoController extends Controller
{
    // Normally served directly by the web server. These routes only read existing files.
    public function sitemap(): BinaryFileResponse
    {
        abort_unless(is_file(public_path('sitemap.xml')), 404);

        return response()->file(public_path('sitemap.xml'), ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): BinaryFileResponse
    {
        abort_unless(is_file(public_path('robots.txt')), 404);

        return response()->file(public_path('robots.txt'), ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
