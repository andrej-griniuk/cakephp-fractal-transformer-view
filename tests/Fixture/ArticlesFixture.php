<?php

declare(strict_types=1);

namespace FractalTransformerView\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Short description for class.
 */
class ArticlesFixture extends TestFixture
{
    /**
     * records property
     *
     * @var array
     */
    public $records = [
        ['title' => 'First Article', 'body' => 'First Article Body', 'published' => 'Y'],
        ['title' => 'Second Article', 'body' => 'Second Article Body', 'published' => 'Y'],
        ['title' => 'Third Article', 'body' => 'Third Article Body', 'published' => 'Y'],
    ];
}
