<?php
declare(strict_types=1);

namespace Tests\Traits;

use Illuminate\Foundation\Testing\TestResponse;

trait TestSaves
{
    protected abstract function model(): string;
    protected abstract function routeStore(): string;
    protected abstract function routeUpdate(): string;

    protected function assertStore(
        array $sendData,
        array $expectedDatabaseData,
        array $expectedResponseData = null
    ): TestResponse
    {
        $response = $this->json('POST', $this->routeStore(), $sendData);
        if ($response->status() !== 201)
        {
            throw new \Exception("Response status must be 201, given {$response->status()}:
{$response->content()}");
        }
        $this->assertInDatabase($response, $expectedDatabaseData);
        $this->assertJsonResponseContent($response, $expectedDatabaseData, $expectedResponseData);
        return $response;
    }

    protected function assertUpdate(
        array $sendData,
        array $expectedDatabaseData,
        array $expectedResponseData = null
    ): TestResponse
    {
        $response = $this->json('PUT', $this->routeUpdate(), $sendData);
        if ($response->status() !== 200)
        {
            throw new \Exception("Response status must be 200, given {$response->status()}:
{$response->content()}");
        }
        $this->assertInDatabase($response, $expectedDatabaseData);
        $this->assertJsonResponseContent($response, $expectedDatabaseData, $expectedResponseData);
        return $response;
    }

    private function assertInDatabase(TestResponse $response, array $expectedDatabaseData)
    {
        $model = $this->model();
        $table = (new $model)->getTable();
        $this->assertDatabaseHas($table, $expectedDatabaseData + ['id' => $response->json('id')]);
    }

    private function assertJsonResponseContent(
        TestResponse $response,
        array $expectedDatabaseData,
        array $expectedResponseData = null
    )
    {
        $expectedResponseData = $expectedResponseData ?? $expectedDatabaseData;
        $response->assertJsonFragment($expectedResponseData + ['id' => $response->json('id')]);
    }
}
