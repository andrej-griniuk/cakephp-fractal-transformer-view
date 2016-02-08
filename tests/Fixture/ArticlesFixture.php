<?php
namespace FractalTransformerView\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Short description for class.
 *
 */
class ArticlesFixture extends TestFixture
{

    /**
     * fields property
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer'],
        'title' => ['type' => 'string', 'null' => true],
        'body' => 'text',
        'published' => ['type' => 'string', 'length' => 1, 'default' => 'N'],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]]
    ];

    /**
     * records property
     *
     * @var array
     */
    public $records = [
        ['title' => 'First Article', 'body' => 'First Article Body', 'published' => 'Y'],
        ['title' => 'Second Article', 'body' => 'Second Article Body', 'published' => 'Y'],
        ['title' => 'Third Article', 'body' => 'Third Article Body', 'published' => 'Y']
    ];
}
