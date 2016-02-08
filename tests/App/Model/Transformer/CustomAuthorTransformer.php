<?php
namespace FractalTransformerView\Test\App\Model\Transformer;

use FractalTransformerView\Test\App\Model\Entity\Author;
use League\Fractal\TransformerAbstract;

class CustomAuthorTransformer extends TransformerAbstract
{
    /**
     * Creates a response item for each instance
     *
     * @param Author $author post entity
     * @return array transformed post
     */
    public function transform(Author $author)
    {
        return $author->toArray();
    }
}
