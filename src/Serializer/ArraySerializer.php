<?php

declare(strict_types=1);

namespace FractalTransformerView\Serializer;

use League\Fractal\Serializer\ArraySerializer as Serializer;

class ArraySerializer extends Serializer
{
    /**
     * Serialize a collection.
     *
     * @param mixed $resourceKey Resource key
     * @param array  $data Data
     * @return array
     */
    public function collection(?string $resourceKey, array $data): array
    {
        return $data;
    }
}
