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
            'categories' => $this->serviceCatalog->categories(),
        ]);
    }

    public function category(string $category): View
    {
        $categoryData = $this->serviceCatalog->category($category);

        abort_unless($categoryData, 404);

        return view('frontend.services.category', [
            'category' => $categoryData,
            'categories' => $this->serviceCatalog->categories(),
            'services' => $this->serviceCatalog->byCategory($category),
        ]);
    }

    public function show(string $service): View
    {
        $serviceData = $this->serviceCatalog->find($service);

        abort_unless($serviceData, 404);

        return view('frontend.services.show', [
            'service' => $serviceData,
            'category' => $this->serviceCatalog->category($serviceData['category_slug']),
            'relatedServices' => array_values(array_filter(
                $this->serviceCatalog->byCategory($serviceData['category_slug']),
                fn (array $related): bool => $related['slug'] !== $serviceData['slug'],
            )),
        ]);
    }
}
