<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PersonsController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/persons/Index');
    }

    public function getJsonList()
    {
        $query = Person::query()->orderByDesc('id');

        if ($searchText = request('search_text')) {
            $query->where('name', 'like', '%' . $searchText . '%');
        }

        return $query->paginate((int) request('paginate_by', 10));
    }

    public function edit($personId)
    {
        $person = is_numeric($personId) ? Person::findOrFail($personId) : new Person();

        return Inertia::render('admin/persons/Edit', [
            'person' => $person,
        ]);
    }

    public function save(Request $request, $personId = null)
    {
        $payload = [
            'name' => $request->input('name'),
            'beatified_at' => $request->input('beatified_at') ?: null,
            'canonized_at' => $request->input('canonized_at') ?: null,
            'published' => (int) (bool) $request->input('published'),
        ];

        if (is_numeric($personId)) {
            $person = Person::findOrFail($personId);
            $person->update($payload);
        } else {
            $person = Person::create($payload);
        }

        return response()->json([
            'message' => __('admin.record_saved_successfully'),
            'person' => $person->fresh(),
        ]);
    }

    public function delete($personId)
    {
        $person = Person::findOrFail($personId);
        $person->delete();

        return response()->json([
            'message' => __('admin.record_deleted_successfully'),
        ]);
    }
}
