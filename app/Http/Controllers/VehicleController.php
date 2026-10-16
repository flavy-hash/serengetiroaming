<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Contracts\View\View;

class VehicleController extends Controller
{
    /**
     * /vehicles — the company's fleet.
     */
    public function __invoke(): View
    {
        return view('pages.vehicles', [
            'vehicles' => Vehicle::published()->ordered()->get(),
        ]);
    }
}
