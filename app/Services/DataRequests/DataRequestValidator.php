<?php

namespace App\Services\DataRequests;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Resolvers\DataClassValidationRulesResolver;
use Spatie\LaravelData\Resolvers\DataValidatorResolver;

class DataRequestValidator extends DataValidatorResolver
{
    public function __construct(
        protected DataClassValidationRulesResolver $dataValidationRulesResolver,
        protected Request $request
    ) {
    }

    /** @param  class-string<\Spatie\LaravelData\Data>  $dataClass */
    public function execute(string $dataClass, Arrayable|array $payload): Validator
    {
        if ($payload instanceof Request) {
            $payload = array_merge($payload->toArray(), $this->request->route()->parameters ?? []);
        }

        $payload = $payload instanceof Arrayable ? $payload->toArray() : $payload;

        $rules = $this->dataValidationRulesResolver
            ->execute($dataClass, $payload)
            ->toArray();

        $validator = ValidatorFacade::make(
            $payload,
            $rules,
            method_exists($dataClass, 'messages') ? app()->call([$dataClass, 'messages']) : [],
            method_exists($dataClass, 'attributes') ? app()->call([$dataClass, 'attributes']) : []
        );

        $dataClass::withValidator($validator);

        return $validator;
    }
}
