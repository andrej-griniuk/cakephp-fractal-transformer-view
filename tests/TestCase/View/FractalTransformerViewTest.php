<?php
namespace FractalTransformerView\Test\TestCase\View;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\View\View;
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

    public $fixtures = ['plugin.FractalTransformerView.articles', 'plugin.FractalTransformerView.authors'];

    public function setUp()
    {
        parent::setUp();
        Configure::write('debug', false);

        $this->Articles = TableRegistry::get('Articles');
        $this->Authors = TableRegistry::get('Authors');
    }

    public function testGetTransformerClassFromQuery()
    {
        $query = $this->Articles->find();

        $view = new FractalTransformerView();

        $this->assertEquals(
            'FractalTransformerView\Test\App\Model\Transformer\ArticleTransformer',
            $this->protectedMethodCall($view, 'getTransformerClass', [$query])
        );
    }

    public function testGetTransformerClassFromResultSet()
    {
        $resultSet = $this->Articles->find()->all();

        $view = new FractalTransformerView();

        $this->assertEquals(
            'FractalTransformerView\Test\App\Model\Transformer\ArticleTransformer',
            $this->protectedMethodCall($view, 'getTransformerClass', [$resultSet])
        );
    }

    public function testGetTransformerClassFromEmptyResultSet()
    {
        $resultSet = $this->Articles->find()->where(['id' => -1])->all();

        $view = new FractalTransformerView();

        $this->assertEquals(
            false,
            $this->protectedMethodCall($view, 'getTransformerClass', [$resultSet])
        );
    }

    public function testGetTransformerClassFromEntity()
    {
        $entity = $this->Articles->newEntity();

        $view = new FractalTransformerView();

        $this->assertEquals(
            'FractalTransformerView\Test\App\Model\Transformer\ArticleTransformer',
            $this->protectedMethodCall($view, 'getTransformerClass', [$entity])
        );
    }

    public function testGetTransformerClassFromEntitiesArray()
    {
        $entities = [$this->Articles->newEntity()];

        $view = new FractalTransformerView();

        $this->assertEquals(
            'FractalTransformerView\Test\App\Model\Transformer\ArticleTransformer',
            $this->protectedMethodCall($view, 'getTransformerClass', [$entities])
        );
    }

    public function testGetTransformerClassFromEntityWithNoTransformer()
    {
        $entity = $this->Authors->newEntity();

        $view = new FractalTransformerView();

        $this->assertEquals(
            false,
            $this->protectedMethodCall($view, 'getTransformerClass', [$entity])
        );
    }

    public function testGetTransformerClassFromEmptyVar()
    {
        $view = new FractalTransformerView();

        $this->assertEquals(
            false,
            $this->protectedMethodCall($view, 'getTransformerClass', [false])
        );
    }

    public function testGetTransformerClassFromEmptyArray()
    {
        $view = new FractalTransformerView();

        $this->assertSame(
            false,
            $this->protectedMethodCall($view, 'getTransformerClass', [[]])
        );
    }

    public function testGetTransformerByVar()
    {
        $entity = $this->Articles->newEntity();

        $view = new FractalTransformerView();

        $this->assertInstanceOf(
            'FractalTransformerView\Test\App\Model\Transformer\ArticleTransformer',
            $this->protectedMethodCall($view, 'getTransformer', [$entity])
        );
    }

    public function testGetTransformerByVarName()
    {
        $entity = $this->Authors->newEntity();

        $view = new FractalTransformerView();
        $view->set('_transform', ['author' => '\FractalTransformerView\Test\App\Model\Transformer\CustomAuthorTransformer']);

        $this->assertInstanceOf(
            'FractalTransformerView\Test\App\Model\Transformer\CustomAuthorTransformer',
            $this->protectedMethodCall($view, 'getTransformer', [$entity, 'author'])
        );
    }

    public function testGetNoTransformerByVarName()
    {
        $entity = $this->Articles->newEntity();

        $view = new FractalTransformerView();
        $view->set('_transform', ['article' => false]);

        $this->assertEquals(
            false,
            $this->protectedMethodCall($view, 'getTransformer', [$entity, 'article'])
        );
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid Transformer class: NotExistingTransformer
     */
    public function testGetTransformerByVarNameNotFound()
    {
        $entity = $this->Articles->newEntity();

        $view = new FractalTransformerView();
        $view->set('_transform', ['article' => 'NotExistingTransformer']);

        $this->protectedMethodCall($view, 'getTransformer', [$entity, 'article']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Transformer class not instance of TransformerAbstract: \FractalTransformerView\Test\App\Model\Table\ArticlesTable
     */
    public function testGetTransformerByVarNameInvalid()
    {
        $entity = $this->Articles->newEntity();

        $view = new FractalTransformerView();
        $view->set('_transform', ['article' => '\FractalTransformerView\Test\App\Model\Table\ArticlesTable']);

        $this->protectedMethodCall($view, 'getTransformer', [$entity, 'article']);
    }

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
     * @expectedException Exception
     * @expectedExceptionMessage Unserializable variable
     */
    public function testTransformInvalid()
    {
        $serializer = new ArraySerializer();
        $manager = new Manager();
        $manager->setSerializer($serializer);

        $view = new FractalTransformerView();
        $view->set('_transform', ['std' => '\FractalTransformerView\Test\App\Model\Transformer\CustomAuthorTransformer']);

        $this->protectedMethodCall($view, 'transform', [$manager, new stdClass(), 'std']);
    }

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
                'author' => $author
            ],
            $this->protectedMethodCall($view, '_dataToSerialize')
        );
    }

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
     * @param View $obj object
     * @param string $name method to call
     * @param array $args arguments to pass to the method
     * @return mixed
     */
    public function protectedMethodCall($obj, $name, array $args = [])
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }
}
