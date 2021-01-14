<?php
declare(strict_types=1);

namespace FractalTransformerView\View;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\ResultSetDecorator;
use Cake\ORM\Query;
use Cake\ORM\ResultSet;
use Cake\Utility\Hash;
use Cake\View\JsonView;
use Exception;
use FractalTransformerView\Serializer\ArraySerializer;
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
     * Get transform class name for given var by figuring out which entity it belongs to. Return FALSE otherwise
     *
     * @param  \Cake\ORM\Query|\Cake\ORM\ResultSet|\Cake\Datasource\ResultSetDecorator|\Cake\Datasource\EntityInterface $var variable
     * @return string|null
     */
    protected function getTransformerClass($var): ?string
    {
        $entity = null;
        if ($var instanceof Query) {
            $entity = $var->getRepository()->newEmptyEntity();
        } elseif ($var instanceof ResultSetDecorator) {
            $entity = $var->first();
        } elseif ($var instanceof ResultSet) {
            $entity = $var->first();
        } elseif ($var instanceof EntityInterface) {
            $entity = $var;
        } elseif (is_array($var)) {
            $entity = reset($var);
        }

        if (!$entity || !is_object($entity)) {
            return null;
        }

        $entityClass = get_class($entity);
        $transformerClass = str_replace('\\Model\\Entity\\', '\\Model\\Transformer\\', $entityClass) . 'Transformer';

        if (!class_exists($transformerClass)) {
            return null;
        }

        return $transformerClass;
    }

    /**
     * Get transformer for given var
     *
     * @param mixed $var Variable
     * @param string|null $varName Variable name
     * @return \League\Fractal\TransformerAbstract|null
     * @throws \Exception
     */
    protected function getTransformer($var, $varName = null): ?TransformerAbstract
    {
        $_transform = $this->get('_transform');
        $transformerClass = $varName
            ? Hash::get((array)$_transform, $varName)
            : $_transform;

        if (is_null($transformerClass)) {
            $transformerClass = $this->getTransformerClass($var);
        }

        if (!$transformerClass) {
            return null;
        }

        if (!class_exists($transformerClass)) {
            throw new Exception(sprintf('Invalid Transformer class: %s', $transformerClass));
        }

        $transformer = new $transformerClass();
        if (!($transformer instanceof TransformerAbstract)) {
            throw new Exception(
                sprintf(
                    'Transformer class not instance of TransformerAbstract: %s',
                    $transformerClass
                )
            );
        }

        return $transformer;
    }

    /**
     * Transform var using given manager
     *
     * @param \League\Fractal\Manager $manager Manager
     * @param mixed $var Variable
     * @param string|null $varName Variable name
     * @return array
     * @throws \Exception
     */
    protected function transform(Manager $manager, $var, $varName = null)
    {
        $transformer = $this->getTransformer($var, $varName);
        if (!$transformer) {
            return $var;
        }

        if (is_array($var) || $var instanceof Query || $var instanceof ResultSet || $var instanceof ResultSetDecorator) {
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
     * @param array|string $serialize The name(s) of the view variable(s) that need(s) to be serialized.
     * @return mixed The data to serialize.
     * @throws \Exception
     */
    protected function _dataToSerialize($serialize)
    {
        $data = parent::_dataToSerialize($serialize);

        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());

        $includes = $this->get('_includes');
        if ($includes) {
            $manager->parseIncludes($includes);
        }

        if (is_array($data)) {
            foreach ($data as $varName => $var) {
                $data[$varName] = $this->transform($manager, $var, $varName);
            }
        } else {
            $data = $this->transform($manager, $data);
        }

        return $data;
    }
}
