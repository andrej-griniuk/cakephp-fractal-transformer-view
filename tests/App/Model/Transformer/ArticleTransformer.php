<?php
namespace FractalTransformerView\Test\App\Model\Transformer;

use FractalTransformerView\Test\App\Model\Entity\Article;
use League\Fractal\TransformerAbstract;

class ArticleTransformer extends TransformerAbstract
{
    /**
     * Creates a response item for each instance
     *
     * @param Article $article post entity
     * @return array transformed post
     */
    public function transform(Article $article)
    {
        return [
            'title' => $article->get('title')
        ];
    }
}
