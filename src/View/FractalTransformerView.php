<?php
namespace FractalTransformerView\View;

use Cake\Event\EventManager;
use Cake\Network\Request;
use Cake\Network\Response;
use FractalTransformerView\Serializer\ArraySerializer;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Query;
use Cake\ORM\ResultSet;
use Cake\Utility\Hash;
use Cake\View\JsonView;
use Exception;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

/**
 * FractalTransformerView class
 */
class FractalTransformerView extends JsonView
{
    /**
     * Constructor
     *
     * @param \Cake\Network\Request $request Request instance.
     * @param \Cake\Network\Response $response Response instance.
     * @param \Cake\Event\EventManager $eventManager EventManager instance.
     * @param array $viewOptions An array of view options
     */
    public function __construct(
        Request $request = null,
        Response $response = null,
        EventManager $eventManager = null,
        array $viewOptions = []
    ) {
        parent::__construct($request, $response, $eventManager, $viewOptions);

        $this->_specialVars[] = '_transform';
    }

    /**
     * Get transform class name for given var by figuring out which entity it belongs to. Return FALSE otherwise
     *
     * @param $var
     * @return bool|string
     */
    protected function getTransformerClass($var)
    {
        $entity = null;
        if ($var instanceof Query) {
            $entity = $var->repository()->newEntity();
        } elseif ($var instanceof ResultSet) {
            $entity = $var->first();
        } elseif ($var instanceof EntityInterface) {
            $entity = $var;
        } elseif (is_array($var)) {
            $entity = reset($var);
        }

        if (!$entity || !is_object($entity)) {
            return false;
        }

        $entityClass = get_class($entity);
        $transformerClass = str_replace('\\Model\\Entity\\', '\\Model\\Transformer\\', $entityClass) . 'Transformer';

        if (!class_exists($transformerClass)) {
            return false;
        }

        return $transformerClass;
    }

    /**
     * Get transformer for given var
     *
     * @param $var
     * @param bool $varName
     * @return bool
     * @throws Exception
     */
    protected function getTransformer($var, $varName = false)
    {
        $_transform = $this->get('_transform');
        $transformerClass = $varName
            ? Hash::get((array)$_transform, $varName)
            : $_transform;

        if (is_null($transformerClass)) {
            $transformerClass = $this->getTransformerClass($var);
        }

        if ($transformerClass === false) {
            return false;
        }

        if (!class_exists($transformerClass)) {
            throw new Exception(sprintf('Invalid Transformer class: %s', $transformerClass));
        }

        $transformer = new $transformerClass;
        if (!($transformer instanceof TransformerAbstract)) {
            throw new Exception(sprintf('Transformer class not instance of TransformerAbstract: %s',
                $transformerClass));
        }

        return $transformer;
    }

    /**
     * Transform var using given manager
     *
     * @param Manager $manager
     * @param $var
     * @param bool $varName
     * @return array
     * @throws Exception
     */
    protected function transform(Manager $manager, $var, $varName = false)
    {
        if (!$transformer = $this->getTransformer($var, $varName)) {
            return $var;
        }

        if (is_array($var) || $var instanceof Query || $var instanceof ResultSet) {
            $resource = new Collection($var, $transformer);
        } elseif ($var instanceof EntityInterface) {
            $resource = new Item($var, $transformer);
        } else {
            throw new Exception('Unserializable variable');
        }

        return $manager->createData($resource)->toArray();
    }

    /**
     * Returns data to be serialized.
     *
     * @param bool $serialize
     * @return array|mixed
     * @throws Exception
     */
    protected function _dataToSerialize($serialize = true)
    {
        $data = parent::_dataToSerialize($serialize);

        $serializer = new ArraySerializer();
        $manager = new Manager();
        $manager->setSerializer($serializer);

        if (is_array($data)) {
            foreach ($data as $varName => &$var) {
                $var = $this->transform($manager, $var, $varName);
            }
            unset($var);
        } else {
            $data = $this->transform($manager, $data);
        }

        return $data;
    }
}
