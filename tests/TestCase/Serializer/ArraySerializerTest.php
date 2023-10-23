<?php
declare(strict_types=1);

namespace FractalTransformerView\Test\TestCase\Serializer;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use FractalTransformerView\Serializer\ArraySerializer;

/**
 * ArraySerializerTest
 */
class ArraySerializerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Configure::write('debug', false);
    }

    public function testCollection(): void
    {
        $serializer = new ArraySerializer();
        $data = ['test'];

        $this->assertEquals($data, $serializer->collection('data', $data));
    }
}
