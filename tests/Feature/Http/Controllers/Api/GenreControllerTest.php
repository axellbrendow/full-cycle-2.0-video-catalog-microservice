<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    /** @var Genre */
    private $genre;

    public function testIndex()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$genre->toArray()]);
    }

    public function testShow()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.show', ['genre' => $genre->id]));

        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray());
    }

    public function testValidationNameRequired()
    {
        $data = ['name' => ''];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->genre = factory(Genre::class)->create();
        $this->assertInvalidationInUpdateAction($data, 'required');
    }

    public function testValidationNameLength()
    {
        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->genre = factory(Genre::class)->create();
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
    }

    public function testValidationIsActive()
    {
        $data = ['is_active' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->genre = factory(Genre::class)->create();
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    public function testStoreWithDefaultValues()
    {
        $data = ['name' => 'test'];
        $response = $this->assertStore(
            $data,
            $data + ['is_active' => true, 'deleted_at' => null]
        );
        $response->assertJsonStructure(['created_at', 'updated_at']);
    }

    public function testStoreWithSpecificValues()
    {
        $data = ['name' => 'test', 'is_active' => false];
        $response = $this->assertStore(
            $data,
            $data + ['deleted_at' => null]
        );
        $response->assertJsonStructure(['created_at', 'updated_at']);
    }

    public function testUpdate()
    {
        $newGenreData = ['name' => 'a', 'is_active' => true];
        $this->genre = factory(Genre::class)->create([
            'is_active' => false
        ]);
        $response = $this->assertUpdate(
            $newGenreData,
            $newGenreData + ['deleted_at' => null],
            $newGenreData + ['deleted_at' => null]
        );
        $response->assertJsonStructure(['created_at', 'updated_at']);
    }

    public function testDestroy()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->json(
            'DELETE',
            route('genres.destroy', ['genre' => $genre->id])
        );
        $response->assertStatus(204);

        $this->assertNull(Genre::find($genre->id));
        $this->assertNotNull(Genre::withTrashed()->find($genre->id));
    }

    protected function routeStore(): string
    {
        return route('genres.store');
    }

    protected function routeUpdate(): string
    {
        return route('genres.update', ['genre' => $this->genre->id]);
    }

    protected function model(): string
    {
        return Genre::class;
    }
}
