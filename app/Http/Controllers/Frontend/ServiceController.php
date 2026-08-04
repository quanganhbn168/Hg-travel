<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\TravelServiceCatalog;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function __construct(private readonly TravelServiceCatalog $serviceCatalog) {}

    public function index(): View
    {
        return view('frontend.services.index', [
            'services' => $this->serviceCatalog->all(),
        ]);
    }
}
