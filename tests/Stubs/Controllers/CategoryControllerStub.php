<?php

namespace Tests\Stubs\Controllers;

use App\Http\Controllers\Api\BasicCrudController;
use Illuminate\Database\Eloquent\Model;
use Tests\Stubs\Models\CategoryStub;

class CategoryControllerStub extends BasicCrudController
{
    private $rules = [
        'name' => 'required|max:255',
        'description' => 'nullable'
    ];

    protected function model(): string
    {
        return CategoryStub::class;
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
