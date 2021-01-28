<?php
declare(strict_types=1);

namespace FractalTransformerView\Test\TestCase\View;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Exception;
use FractalTransformerView\Serializer\ArraySerializer;
use FractalTransformerView\View\FractalTransformerView;
use League\Fractal\Manager;
use stdClass;

/**
 * FractalTransformerViewTest
 *
 * @property \Cake\ORM\Table Articles
 * @property \Cake\ORM\Table Authors
 */
class FractalTransformerViewTest extends TestCase
{
    public $fixtures = ['plugin.FractalTransformerView.Articles', 'plugin.FractalTransformerView.Authors'];

    public function setUp(): void
    {
        parent::setUp();
        Configure::write('debug', false);

        $this->Articles = TableRegistry::getTableLocator()->get('Articles');
        $this->Authors = TableRegistry::getTableLocator()->get('Authors');
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetTransformerClassFromQuery()
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
    public function testGetTransformerClassFromResultSet()
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
    public function testGetTransformerClassFromEmptyResultSet()
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
    public function testGetTransformerClassFromEntity()
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
    public function testGetTransformerClassFromEntitiesArray()
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
    public function testGetTransformerClassFromEntityWithNoTransformer()
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
    public function testGetTransformerClassFromEmptyVar()
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
    public function testGetTransformerClassFromEmptyArray()
    {
        $view = new FractalTransformerView();

        $this->assertNull($this->protectedMethodCall($view, 'getTransformerClass', [[]]));
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetTransformerByVar()
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
    public function testGetTransformerByVarName()
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
    public function testGetNoTransformerByVarName()
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
    public function testGetTransformerByVarNameNotFound()
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
    public function testGetTransformerByVarNameInvalid()
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
    public function testTransformCollection()
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
    public function testTransformItem()
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
    public function testTransformWithNoTransformer()
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
    public function testTransformInvalid()
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
    public function testDataToSerializeArray()
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
    public function testDataToSerializeSingle()
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
