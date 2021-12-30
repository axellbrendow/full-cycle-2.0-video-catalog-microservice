<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestUploads;
use Tests\Traits\TestValidations;
use function factory;
use function route;

class VideoControllerTest extends BaseVideoControllerTestCase
{
    use TestValidations, TestSaves, TestUploads;

    public function testIndex()
    {
        $response = $this->get(route('videos.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->video->toArray());
    }

    public function testValidationRequired()
    {
        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => '',
            'categories_id' => '',
            'genres_id' => '',
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
    }

    public function testValidationMax()
    {
        $data = [
            'title' => str_repeat('a', 256),
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
    }

    public function testValidationInteger()
    {
        $data = [
            'duration' => 'a',
        ];
        $this->assertInvalidationInStoreAction($data, 'integer');
        $this->assertInvalidationInUpdateAction($data, 'integer');
    }

    public function testValidationYearLaunched()
    {
        $data = [
            'year_launched' => 'a',
        ];
        $this->assertInvalidationInStoreAction($data, 'date_format', ['format' => 'Y']);
        $this->assertInvalidationInUpdateAction($data, 'date_format', ['format' => 'Y']);
    }

    public function testValidationOpened()
    {
        $data = [
            'opened' => 's',
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    public function testValidationRating()
    {
        $data = [
            'rating' => 0,
        ];
        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    public function testValidationCategoriesId()
    {
        $data = [
            'categories_id' => 'a',
        ];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = [
            'categories_id' => [100],
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $category = factory(Category::class)->create();
        $category->delete();
        $data = ['categories_id' => [$category->id]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testValidationGenresId()
    {
        $data = [
            'genres_id' => 'a',
        ];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = [
            'genres_id' => [100],
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testValidationVideoField()
    {
        $this->assertInvalidationFile(
            'video_file',
            'mp4',
            12,
            'mimetypes', ['values' => 'video/mp4']
        );
    }

    public function testStoreWithoutFiles()
    {
        $response = $this->assertStore(
            $this->sendData,
            $this->dbData + ['opened' => false]
        );
        $response->assertJsonStructure(['created_at', 'updated_at']);
        $this->assertHasCategory(
            $response->json('id'),
            $this->sendData['categories_id'][0],
        );
        $this->assertHasGenre(
            $response->json('id'),
            $this->sendData['genres_id'][0],
        );

        $this->assertStore(
            $this->sendData + ['opened' => true],
            $this->dbData + ['opened' => true]
        );

        $this->assertStore(
            $this->sendData + ['rating' => Video::RATING_LIST[1]],
            $this->dbData + ['rating' => Video::RATING_LIST[1]]
        );
    }

    public function testUpdateWithoutFiles()
    {
        $response = $this->assertUpdate(
            $this->sendData,
            $this->dbData + ['opened' => false]
        );
        $response->assertJsonStructure(['created_at', 'updated_at']);
        $this->assertHasCategory(
            $response->json('id'),
            $this->sendData['categories_id'][0],
        );
        $this->assertHasGenre(
            $response->json('id'),
            $this->sendData['genres_id'][0],
        );

        $this->assertUpdate(
            $this->sendData + ['opened' => true],
            $this->dbData + ['opened' => true]
        );

        $this->assertUpdate(
            $this->sendData + ['rating' => Video::RATING_LIST[1]],
            $this->dbData + ['rating' => Video::RATING_LIST[1]]
        );
    }

    public function testStoreWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();

        $response = $this->json(
            'POST',
            $this->routeStore(),
            $this->sendData + $files
        );

        $response->assertStatus(201);
        $id = $response->json('id');
        foreach ($files as $file) {
            \Storage::assertExists("$id/{$file->hashName()}");
        }
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();

        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $this->sendData + $files
        );
        $response->assertStatus(200);

        $id = $response->json('id');
        foreach ($files as $file) {
            \Storage::assertExists("$id/{$file->hashName()}");
        }
    }

    protected function getFiles()
    {
        return [
            'video_file' => UploadedFile::fake()->create("video_file.mp4")
        ];
    }

    protected function assertHasCategory($videoId, $categoryId)
    {
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoryId,
            'video_id' => $videoId,
        ]);
    }

    protected function assertHasGenre($videoId, $genreId)
    {
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genreId,
            'video_id' => $videoId,
        ]);
    }

//    public function testRollbackStore()
//    {
//        $controller = \Mockery::mock(VideoController::class)
//            ->makePartial()
//            ->shouldAllowMockingProtectedMethods();
//
//        $controller
//            ->shouldReceive('validate')
//            ->withAnyArgs()
//            ->andReturn($this->sendData);
//
//        $controller
//            ->shouldReceive('rulesStore')
//            ->withAnyArgs()
//            ->andReturn([]);
//
//        $controller->shouldReceive('handleRelations')
//            ->once()
//            ->andThrow(new TestException());
//
//        $request = \Mockery::mock(Request::class);
//
//        $request->shouldReceive('get')
//            ->withAnyArgs()
//            ->andReturnNull();
//
//        $hasError = false;
//        try {
//            $controller->store($request);
//        } catch (TestException $exception) {
//            $this->assertCount(1, Video::all());
//            $hasError = true;
//        } finally {
//            $this->assertTrue($hasError);
//        }
//    }
//
//    public function testRollbackUpdate()
//    {
//        $video = factory(Video::class)->create();
//        $controller = \Mockery::mock(VideoController::class)
//            ->makePartial()
//            ->shouldAllowMockingProtectedMethods();
//
//        $controller
//            ->shouldReceive('findOrFail')
//            ->withAnyArgs()
//            ->andReturn($video);
//
//        $controller
//            ->shouldReceive('validate')
//            ->withAnyArgs()
//            ->andReturn([
//                'name' => 'test',
//            ]);
//
//        $controller
//            ->shouldReceive('rulesUpdate')
//            ->withAnyArgs()
//            ->andReturn([]);
//
//        $controller->shouldReceive('handleRelations')
//            ->once()
//            ->andThrow(new TestException());
//
//        $request = \Mockery::mock(Request::class);
//
//        $hasError = false;
//        try {
//            $controller->update($request, 1);
//        } catch (TestException $exception) {
//            $this->assertCount(1, Genre::all());
//            $hasError = true;
//        } finally {
//            $this->assertTrue($hasError);
//        }
//    }

//    public function testSyncCategories()
//    {
//        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();
//
//        /** @var Genre $genre */
//        $genre = factory(Genre::class)->create();
//        $genre->categories()->sync($categoriesId);
//
//        $response = $this->json(
//            'POST',
//            $this->routeStore(),
//            array_merge($this->sendData, [
//                'genres_id' => [$genre->id],
//                'categories_id' => [$categoriesId[0]],
//            ])
//        );
//        $this->assertDatabaseHas('category_video', [
//            'category_id' => $categoriesId[0],
//            'video_id' => $response->json('id'),
//        ]);
//
//        $response = $this->json(
//            'PUT',
//            route('videos.update', ['video' => $response->json('id')]),
//            array_merge($this->sendData, [
//                'genres_id' => [$genre->id],
//                'categories_id' => [$categoriesId[1], $categoriesId[2]],
//            ])
//        );
//        $this->assertDatabaseMissing('category_video', [
//            'category_id' => $categoriesId[0],
//            'video_id' => $response->json('id')
//        ]);
//        $this->assertDatabaseHas('category_video', [
//            'category_id' => $categoriesId[1],
//            'video_id' => $response->json('id'),
//        ]);
//        $this->assertDatabaseHas('category_video', [
//            'category_id' => $categoriesId[2],
//            'video_id' => $response->json('id'),
//        ]);
//    }
//
//    public function testSyncGenres()
//    {
//        $genres = factory(Genre::class, 3)->create();
//        $genresId = $genres->pluck('id')->toArray();
//
//        /** @var Category $category */
//        $category = factory(Category::class)->create();
//        $genres->each(function ($genre) use ($category) {
//            $genre->categories()->sync($category->id);
//        });
//
//        $response = $this->json(
//            'POST',
//            $this->routeStore(),
//            array_merge($this->sendData, [
//                'categories_id' => [$category->id],
//                'genres_id' => [$genresId[0]],
//            ])
//        );
//        $this->assertDatabaseHas('genre_video', [
//            'genre_id' => $genresId[0],
//            'video_id' => $response->json('id'),
//        ]);
//
//        $response = $this->json(
//            'PUT',
//            route('videos.update', ['video' => $response->json('id')]),
//            array_merge($this->sendData, [
//                'categories_id' => [$category->id],
//                'genres_id' => [$genresId[1], $genresId[2]],
//            ])
//        );
//        $this->assertDatabaseMissing('genre_video', [
//            'genre_id' => $genresId[0],
//            'video_id' => $response->json('id')
//        ]);
//        $this->assertDatabaseHas('genre_video', [
//            'genre_id' => $genresId[1],
//            'video_id' => $response->json('id'),
//        ]);
//        $this->assertDatabaseHas('genre_video', [
//            'genre_id' => $genresId[2],
//            'video_id' => $response->json('id'),
//        ]);
//    }

    public function testDestroy()
    {
        $response = $this->json(
            'DELETE',
            route('videos.destroy', ['video' => $this->video->id])
        );
        $response->assertStatus(204);

        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::withTrashed()->find($this->video->id));
    }

    protected function routeStore(): string
    {
        return route('videos.store');
    }

    protected function routeUpdate(): string
    {
        return route('videos.update', ['video' => $this->video->id]);
    }

    protected function model(): string
    {
        return Video::class;
    }
}
