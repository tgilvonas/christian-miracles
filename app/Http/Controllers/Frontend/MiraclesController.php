<?php

namespace App\Http\Controllers\Frontend;

use Inertia\Inertia;

class MiraclesController
{
    public function index()
    {
        return Inertia::render('Miracles');
    }
}
