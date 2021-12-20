<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    /** @var Category */
    private $category;

    public function testIndex()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.show', ['category' => $category->id]));

        $response
            ->assertStatus(200)
            ->assertJson($category->toArray());
    }

    public function testValidationNameRequired()
    {
        $data = ['name' => ''];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->category = factory(Category::class)->create();
        $this->assertInvalidationInUpdateAction($data, 'required');
    }

    public function testValidationNameLength()
    {
        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->category = factory(Category::class)->create();
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
    }

    public function testValidationIsActive()
    {
        $data = ['is_active' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->category = factory(Category::class)->create();
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    public function testStoreWithDefaultValues()
    {
        $data = ['name' => 'test'];
        $response = $this->assertStore(
            $data,
            $data + ['description' => null, 'is_active' => true, 'deleted_at' => null]
        );
        $response->assertJsonStructure(['created_at', 'updated_at']);
    }

    public function testStoreWithSpecificValues()
    {
        $data = ['name' => 'test', 'description' => 'description', 'is_active' => false];
        $response = $this->assertStore(
            $data,
            $data + ['deleted_at' => null]
        );
        $response->assertJsonStructure(['created_at', 'updated_at']);
    }

    public function testUpdate()
    {
        $newCategoryData = ['name' => 'a', 'description' => 'test', 'is_active' => true];
        $this->category = factory(Category::class)->create([
            'description' => 'description',
            'is_active' => false
        ]);
        $response = $this->assertUpdate(
            $newCategoryData,
            $newCategoryData + ['deleted_at' => null],
            $newCategoryData + ['deleted_at' => null]
        );
        $response->assertJsonStructure(['created_at', 'updated_at']);
    }

    public function testUpdateWithEmptyDescription()
    {
        $newCategoryData = ['name' => 'test', 'description' => ''];
        $newCategoryDataInDb = array_merge($newCategoryData, ['description' => null]);
        $this->category = factory(Category::class)->create([
            'description' => 'description',
            'is_active' => false
        ]);
        $response = $this->assertUpdate(
            $newCategoryData,
            $newCategoryDataInDb + ['deleted_at' => null],
            $newCategoryDataInDb + ['deleted_at' => null]
        );
        $response->assertJsonStructure(['created_at', 'updated_at']);
    }

    public function testDestroy()
    {
        $category = factory(Category::class)->create();
        $response = $this->json(
            'DELETE',
            route('categories.destroy', ['category' => $category->id])
        );
        $response->assertStatus(204);

        $this->assertNull(Category::find($category->id));
        $this->assertNotNull(Category::withTrashed()->find($category->id));
    }

    protected function routeStore(): string
    {
        return route('categories.store');
    }

    protected function routeUpdate(): string
    {
        return route('categories.update', ['category' => $this->category->id]);
    }

    protected function model(): string
    {
        return Category::class;
    }
}
