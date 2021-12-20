<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(Genre::class, 1)->create();
        $genres = Genre::all();
        $this->assertCount(1, $genres);
        $genreKeys = array_keys($genres->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
                'id',
                'name',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            $genreKeys
        );
    }

    public function testCreateAttributes()
    {
        $genre = Genre::create([
            'name' => 'test1'
        ]);
        $genre->refresh();
        $this->assertEquals(36, strlen($genre->id));
        $this->assertEquals('test1', $genre->name);
        $this->assertTrue($genre->is_active);
    }

    public function testCreateFalseIsActiveAttribute()
    {
        $genre = Genre::create([
            'name' => 'test1',
            'is_active' => false
        ]);
        $genre->refresh();
        $this->assertFalse($genre->is_active);
    }

    public function testCreateTrueIsActiveAttribute()
    {
        $genre = Genre::create([
            'name' => 'test1',
            'is_active' => true
        ]);
        $genre->refresh();
        $this->assertTrue($genre->is_active);
    }

    public function testUpdateNameAttribute()
    {
        $genre = factory(Genre::class, 1)->create([
            'is_active' => false
        ])->first();
        $data = [
            'name' => 'test_name_updated',
            'is_active' => true
        ];
        $genre->update($data);

        foreach ($data as $key => $value)
        {
            $this->assertEquals($value, $genre->{$key});
        }
    }

    public function testDelete()
    {
        $genre = factory(Genre::class)->create();
        $genre->delete();
        $this->assertNull(Genre::find($genre->id));

        $genre->restore();
        $this->assertNotNull(Genre::find($genre->id));
    }
}
