<?php

namespace App\Http\Controllers\Admin;

use App\Models\Miracle;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class MiraclesController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/miracles/Index');
    }

    public function edit($miracleId)
    {
        if (is_numeric($miracleId)) {
            $miracle = Miracle::with(['translations', 'texts', 'locations'])->findOrFail($miracleId);
        } else {
            $miracle = new Miracle();
            $miracle->translations = [];
            $miracle->texts = [];
            $miracle->locations = [];
        }

        return Inertia::render('admin/miracles/Edit', [
            'miracle' => $miracle,
        ]);
    }
}
