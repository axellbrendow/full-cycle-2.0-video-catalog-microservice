<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    private $category;

    // This method is executed only one time before everything
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    // This method is executed before each test method
    protected function setUp(): void
    {
        parent::setUp();
        $this->category = new Category();
    }

    // This method is executed after each test method
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // This method is executed only one time after everything
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    public function testFillableAttribute()
    {
        $fillable = ['name', 'description', 'is_active'];
        $this->assertEquals($fillable, $this->category->getFillable());
    }

    public function testCastsAttribute()
    {
        $casts = ['id' => 'string', 'is_active' => 'boolean'];
        $this->assertEquals($casts, $this->category->getCasts());
    }

    public function testDatesAttribute()
    {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        foreach ($dates as $date)
        {
            $this->assertContains($date, $this->category->getDates());
        }
        $this->assertCount(count($dates), $this->category->getDates());
    }

    public function testIncrementingAttribute()
    {
        $this->assertEquals(false, $this->category->getIncrementing());
    }

    public function testIfUseTraits()
    {
        $traits = [SoftDeletes::class, Uuid::class];
        $categoryTraits = array_keys(class_uses(Category::class));
        $this->assertEquals($traits, $categoryTraits);
    }
}
