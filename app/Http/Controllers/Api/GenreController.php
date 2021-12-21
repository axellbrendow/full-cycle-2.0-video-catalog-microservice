<?php

namespace App\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends BasicCrudController
{
    private $rules = [
        'name' => 'required|max:255',
        'is_active' => 'boolean',
        'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
    ];

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        /** @var Genre $obj */
        $obj = \DB::transaction(function () use ($request, $validatedData) {
            $obj = $this->model()::create($validatedData);
            $this->handleRelations($obj, $request);
            return $obj;
        });
        $obj->refresh();
        return $obj;
    }

    public function update(Request $request, $id)
    {
        /** @var Genre $obj */
        $obj = $this->findOrFail($id);
        $validatedData = $this->validate($request, $this->rulesUpdate());
        return \DB::transaction(function () use ($obj, $request, $validatedData) {
            $obj->update($validatedData);
            $this->handleRelations($obj, $request);
            return $obj;
        });
    }

    protected function handleRelations(Genre $genre, Request $request) {
        $genre->categories()->sync($request->get('categories_id'));
    }

    protected function model(): string
    {
        return Genre::class;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }
}
