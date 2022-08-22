<?php

namespace App\PackageWrappers\SpatieDataLaravel;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Spatie\LaravelData\Normalizers\ArraybleNormalizer as SpatieArraybleNormalizer;

class ArraybleNormalizer extends SpatieArraybleNormalizer
{
    public function normalize(mixed $value): ?array
    {
        if (! $value instanceof Arrayable) {
            return null;
        }

        return $value instanceof Request ? array_merge(
            $value->toArray(),
            $value->route()->parameters ?? []
        ) : $value->toArray();
    }
}
