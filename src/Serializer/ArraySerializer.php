<?php
namespace FractalTransformerView\Serializer;

use League\Fractal\Serializer\ArraySerializer as Serializer;

class ArraySerializer extends Serializer
{

    public function collection($resourceKey, array $data)
    {
        return $data;
    }

}
