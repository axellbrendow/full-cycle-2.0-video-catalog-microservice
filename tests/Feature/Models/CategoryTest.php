<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(Category::class, 1)->create();
        $categories = Category::all();
        $this->assertCount(1, $categories);
        $categoryKeys = array_keys($categories->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            $categoryKeys
        );
    }

    public function testCreateAttributes()
    {
        $category = Category::create([
            'name' => 'test1'
        ]);
        $category->refresh();
        $this->assertEquals(36, strlen($category->id));
        $this->assertEquals('test1', $category->name);
        $this->assertNull($category->description);
        $this->assertTrue($category->is_active);
    }

    public function testCreateNullDescriptionAttribute()
    {
        $category = Category::create([
            'name' => 'test1',
            'description' => null
        ]);
        $category->refresh();
        $this->assertNull($category->description);
    }

    public function testCreateNonNullDescriptionAttribute()
    {
        $category = Category::create([
            'name' => 'test1',
            'description' => 'test_description'
        ]);
        $category->refresh();
        $this->assertEquals('test_description', $category->description);
    }

    public function testCreateFalseIsActiveAttribute()
    {
        $category = Category::create([
            'name' => 'test1',
            'is_active' => false
        ]);
        $category->refresh();
        $this->assertFalse($category->is_active);
    }

    public function testCreateTrueIsActiveAttribute()
    {
        $category = Category::create([
            'name' => 'test1',
            'is_active' => true
        ]);
        $category->refresh();
        $this->assertTrue($category->is_active);
    }

    public function testUpdateNameAttribute()
    {
        /** @var Category $category */
        $category = factory(Category::class, 1)->create([
            'description' => 'test_description',
            'is_active' => false
        ])->first();
        $data = [
            'name' => 'test_name_updated',
            'description' => 'test_description_updated',
            'is_active' => true
        ];
        $category->update($data);

        foreach ($data as $key => $value)
        {
            $this->assertEquals($value, $category->{$key});
        }
    }

    public function testDelete()
    {
        $category = factory(Category::class)->create();
        $category->delete();
        $this->assertNull(Category::find($category->id));

        $category->restore();
        $this->assertNotNull(Category::find($category->id));
    }
}
