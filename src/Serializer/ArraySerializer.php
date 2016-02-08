<?php
namespace FractalTransformerView\Serializer;

use League\Fractal\Serializer\ArraySerializer as Serializer;

class ArraySerializer extends Serializer
{
    /**
     * Serialize a collection.
     *
     * @param string $resourceKey resource key
     * @param array  $data data
     *
     * @return array
     */
    public function collection($resourceKey, array $data)
    {
        return $data;
    }
}
