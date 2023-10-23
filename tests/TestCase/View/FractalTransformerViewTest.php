<?php
declare(strict_types=1);

namespace FractalTransformerView\Test\TestCase\View;

use Cake\TestSuite\TestCase;
use Exception;
use FractalTransformerView\Serializer\ArraySerializer;
use FractalTransformerView\View\FractalTransformerView;
use League\Fractal\Manager;
use stdClass;

/**
 * FractalTransformerViewTest
 */
class FractalTransformerViewTest extends TestCase
{
    public $fixtures = ['plugin.FractalTransformerView.Articles', 'plugin.FractalTransformerView.Authors'];

    /**
     * @var \Cake\ORM\Table
     */
    protected $Articles;

    /**
     * @var \Cake\ORM\Table
     */
    protected $Authors;

    public function setUp(): void
    {
        parent::setUp();

        $this->Articles = $this->getTableLocator()->get('Articles');
        $this->Authors = $this->getTableLocator()->get('Authors');
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetTransformerClassFromQuery(): void
    {
        $query = $this->Articles->find();

        $view = new FractalTransformerView();

        $this->assertEquals(
            'FractalTransformerView\Test\App\Model\Transformer\ArticleTransformer',
            $this->protectedMethodCall($view, 'getTransformerClass', [$query])
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetTransformerClassFromResultSet(): void
    {
        $resultSet = $this->Articles->find()->all();

        $view = new FractalTransformerView();

        $this->assertEquals(
            'FractalTransformerView\Test\App\Model\Transformer\ArticleTransformer',
            $this->protectedMethodCall($view, 'getTransformerClass', [$resultSet])
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetTransformerClassFromCollection(): void
    {
        $collection = $this->Articles->find()->all()->take(2);

        $view = new FractalTransformerView();

        $this->assertEquals(
            'FractalTransformerView\Test\App\Model\Transformer\ArticleTransformer',
            $this->protectedMethodCall($view, 'getTransformerClass', [$collection])
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetTransformerClassFromEmptyResultSet(): void
    {
        $resultSet = $this->Articles->find()->where(['id' => -1])->all();

        $view = new FractalTransformerView();

        $this->assertEquals(
            false,
            $this->protectedMethodCall($view, 'getTransformerClass', [$resultSet])
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetTransformerClassFromEntity(): void
    {
        $entity = $this->Articles->newEmptyEntity();

        $view = new FractalTransformerView();

        $this->assertEquals(
            'FractalTransformerView\Test\App\Model\Transformer\ArticleTransformer',
            $this->protectedMethodCall($view, 'getTransformerClass', [$entity])
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetTransformerClassFromEntitiesArray(): void
    {
        $entities = [$this->Articles->newEmptyEntity()];

        $view = new FractalTransformerView();

        $this->assertEquals(
            'FractalTransformerView\Test\App\Model\Transformer\ArticleTransformer',
            $this->protectedMethodCall($view, 'getTransformerClass', [$entities])
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetTransformerClassFromEntityWithNoTransformer(): void
    {
        $entity = $this->Authors->newEmptyEntity();

        $view = new FractalTransformerView();

        $this->assertEquals(
            false,
            $this->protectedMethodCall($view, 'getTransformerClass', [$entity])
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetTransformerClassFromEmptyVar(): void
    {
        $view = new FractalTransformerView();

        $this->assertEquals(
            false,
            $this->protectedMethodCall($view, 'getTransformerClass', [false])
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetTransformerClassFromEmptyArray(): void
    {
        $view = new FractalTransformerView();

        $this->assertNull($this->protectedMethodCall($view, 'getTransformerClass', [[]]));
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetTransformerByVar(): void
    {
        $entity = $this->Articles->newEmptyEntity();

        $view = new FractalTransformerView();

        $this->assertInstanceOf(
            'FractalTransformerView\Test\App\Model\Transformer\ArticleTransformer',
            $this->protectedMethodCall($view, 'getTransformer', [$entity])
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetTransformerByVarName(): void
    {
        $entity = $this->Authors->newEmptyEntity();

        $view = new FractalTransformerView();
        $view->setConfig('transform', ['author' => '\FractalTransformerView\Test\App\Model\Transformer\CustomAuthorTransformer']);

        $this->assertInstanceOf(
            'FractalTransformerView\Test\App\Model\Transformer\CustomAuthorTransformer',
            $this->protectedMethodCall($view, 'getTransformer', [$entity, 'author'])
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetNoTransformerByVarName(): void
    {
        $entity = $this->Articles->newEmptyEntity();

        $view = new FractalTransformerView();
        $view->setConfig('transform', ['article' => false]);

        $this->assertEquals(
            false,
            $this->protectedMethodCall($view, 'getTransformer', [$entity, 'article'])
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetTransformerByVarNameNotFound(): void
    {
        $this->expectExceptionMessage('Invalid Transformer class: NotExistingTransformer');
        $this->expectException(Exception::class);

        $entity = $this->Articles->newEmptyEntity();

        $view = new FractalTransformerView();
        $view->setConfig('transform', ['article' => 'NotExistingTransformer']);

        $this->protectedMethodCall($view, 'getTransformer', [$entity, 'article']);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetTransformerByVarNameInvalid(): void
    {
        $this->expectExceptionMessage('Transformer class not instance of TransformerAbstract: \FractalTransformerView\Test\App\Model\Table\ArticlesTable');
        $this->expectException(Exception::class);

        $entity = $this->Articles->newEmptyEntity();

        $view = new FractalTransformerView();
        $view->setConfig('transform', ['article' => '\FractalTransformerView\Test\App\Model\Table\ArticlesTable']);

        $this->protectedMethodCall($view, 'getTransformer', [$entity, 'article']);
    }

    /**
     * @throws \ReflectionException
     */
    public function testTransformCollection(): void
    {
        $serializer = new ArraySerializer();
        $manager = new Manager();
        $manager->setSerializer($serializer);

        $entities = $this->Articles->find();

        $view = new FractalTransformerView();

        $this->assertEquals(
            [
                ['title' => 'First Article'],
                ['title' => 'Second Article'],
                ['title' => 'Third Article'],
            ],
            $this->protectedMethodCall($view, 'transform', [$manager, $entities])
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testTransformItem(): void
    {
        $serializer = new ArraySerializer();
        $manager = new Manager();
        $manager->setSerializer($serializer);

        $entity = $this->Articles->find()->first();

        $view = new FractalTransformerView();

        $this->assertEquals(
            ['title' => 'First Article'],
            $this->protectedMethodCall($view, 'transform', [$manager, $entity])
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testTransformWithNoTransformer(): void
    {
        $serializer = new ArraySerializer();
        $manager = new Manager();
        $manager->setSerializer($serializer);

        $entity = $this->Authors->find()->first();

        $view = new FractalTransformerView();

        $this->assertEquals(
            $entity,
            $this->protectedMethodCall($view, 'transform', [$manager, $entity])
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testTransformInvalid(): void
    {
        $this->expectExceptionMessage('Unserializable variable');
        $this->expectException(Exception::class);

        $serializer = new ArraySerializer();
        $manager = new Manager();
        $manager->setSerializer($serializer);

        $view = new FractalTransformerView();
        $view->setConfig('transform', ['std' => '\FractalTransformerView\Test\App\Model\Transformer\CustomAuthorTransformer']);

        $this->protectedMethodCall($view, 'transform', [$manager, new stdClass(), 'std']);
    }

    /**
     * @throws \ReflectionException
     */
    public function testDataToSerializeArray(): void
    {
        $article = $this->Articles->find()->first();
        $author = $this->Authors->find()->first();

        $view = new FractalTransformerView();
        $view->set(compact('article', 'author'));
        $view->set('_serialize', ['article', 'author']);

        $this->assertEquals(
            [
                'article' => ['title' => 'First Article'],
                'author' => $author,
            ],
            $this->protectedMethodCall($view, '_dataToSerialize', [['article', 'author']])
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testDataToSerializeSingle(): void
    {
        $article = $this->Articles->find()->first();

        $view = new FractalTransformerView();
        $view->set(compact('article'));
        $view->set('_serialize', 'article');

        $this->assertEquals(
            ['title' => 'First Article'],
            $this->protectedMethodCall($view, '_dataToSerialize', ['article'])
        );
    }

    /**
     * Call a protected method on an object
     *
     * @param  object $obj  object
     * @param  string $name method to call
     * @param  array  $args arguments to pass to the method
     * @return mixed
     * @throws \ReflectionException
     */
    public function protectedMethodCall(object $obj, string $name, array $args = [])
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
}
