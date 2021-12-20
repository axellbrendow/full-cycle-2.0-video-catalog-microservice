<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    /** @var CastMember */
    private $castMember;

    public function testIndex()
    {
        $castMember = factory(CastMember::class)->create();
        $response = $this->get(route('cast_members.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$castMember->toArray()]);
    }

    public function testShow()
    {
        $castMember = factory(CastMember::class)->create();
        $response = $this->get(route('cast_members.show', ['cast_member' => $castMember->id]));

        $response
            ->assertStatus(200)
            ->assertJson($castMember->toArray());
    }

    public function testValidationNameAndTypeRequired()
    {
        $data = ['name' => '', 'type' => ''];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->castMember = factory(CastMember::class)->create();
        $this->assertInvalidationInUpdateAction($data, 'required');
    }

    public function testValidationType()
    {
        $data = ['type' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'in');
        $this->castMember = factory(CastMember::class)->create();
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    public function testStore()
    {
        $data = ['name' => 'test', 'type' => CastMember::TYPE_DIRECTOR];
        $response = $this->assertStore(
            $data,
            $data + ['deleted_at' => null]
        );
        $response->assertJsonStructure(['created_at', 'updated_at']);
    }

    public function testUpdate()
    {
        $newCastMemberData = ['name' => 'a', 'type' => CastMember::TYPE_ACTOR];
        $this->castMember = factory(CastMember::class)->create([
            'type' => CastMember::TYPE_DIRECTOR
        ]);
        $response = $this->assertUpdate(
            $newCastMemberData,
            $newCastMemberData + ['deleted_at' => null],
            $newCastMemberData + ['deleted_at' => null]
        );
        $response->assertJsonStructure(['created_at', 'updated_at']);
    }

    public function testDestroy()
    {
        $castMember = factory(CastMember::class)->create();
        $response = $this->json(
            'DELETE',
            route('cast_members.destroy', ['cast_member' => $castMember->id])
        );
        $response->assertStatus(204);

        $this->assertNull(CastMember::find($castMember->id));
        $this->assertNotNull(CastMember::withTrashed()->find($castMember->id));
    }

    protected function routeStore(): string
    {
        return route('cast_members.store');
    }

    protected function routeUpdate(): string
    {
        return route('cast_members.update', ['cast_member' => $this->castMember->id]);
    }

    protected function model(): string
    {
        return CastMember::class;
    }
}
