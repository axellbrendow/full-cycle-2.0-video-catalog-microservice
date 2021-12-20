<?php
declare(strict_types=1);

namespace Tests\Traits;

use Illuminate\Foundation\Testing\TestResponse;

trait TestValidations
{
    protected abstract function routeStore(): string;
    protected abstract function routeUpdate(): string;

    protected function assertInvalidationInStoreAction(
        array $data,
        string $rule,
        array $ruleParams = []
    )
    {
        $response = $this->json('POST', $this->routeStore(), $data);
        $fields = array_keys($data);
        $this->assertInvalidationsFields($response, $fields, $rule, $ruleParams);
    }

    protected function assertInvalidationInUpdateAction(
        array $data,
        string $rule,
        array $ruleParams = []
    )
    {
        $response = $this->json('PUT', $this->routeUpdate(), $data);
        $fields = array_keys($data);
        $this->assertInvalidationsFields($response, $fields, $rule, $ruleParams);
    }

    protected function assertInvalidationsFields(
        TestResponse $response,
        array $fields,
        string $rule,
        array $ruleParams = []
    )
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors($fields);

        foreach ($fields as $field)
        {
            $fieldName = str_replace('_', ' ', $field);
            $response->assertJsonFragment([
                \Lang::get("validation.{$rule}", ['attribute' => $fieldName] + $ruleParams)
            ]);
        }
    }
}
