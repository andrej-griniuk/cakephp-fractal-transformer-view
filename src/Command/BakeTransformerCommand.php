<?php
declare(strict_types=1);

namespace FractalTransformerView\Command;

use Bake\Command\SimpleBakeCommand;
use Cake\Console\Arguments;
use Cake\Database\Exception\DatabaseException;
use Cake\Utility\Inflector;

class BakeTransformerCommand extends SimpleBakeCommand
{
    /**
     * The pathFragment appended to the plugin/app path.
     *
     * @var string
     */
    protected $pathFragment = 'Model/Transformer/';

    /**
     * Get the generated object's name.
     *
     * @return string
     */
    public function name(): string
    {
        return 'transformer';
    }

    /**
     * Get the generated object's filename without the leading path.
     *
     * @param string $name The name of the object being generated
     * @return string
     */
    public function fileName(string $name): string
    {
        return $name . 'Transformer.php';
    }

    /**
     * Get the template name.
     *
     * @return string
     */
    public function template(): string
    {
        return 'FractalTransformerView.transformer';
    }

    /**
     * Get template data.
     *
     * @param \Cake\Console\Arguments $arguments The arguments for the command
     * @return array
     * @phpstan-return array<string, mixed>
     */
    public function templateData(Arguments $arguments): array
    {
        $parentData = parent::templateData($arguments);
        $data = [];

        $plugin = $this->plugin;
        if ($plugin) {
            $plugin .= '.';
        }

        $modelName = Inflector::pluralize($arguments->getArgument('name'));
        if ($this->getTableLocator()->exists($plugin . $modelName)) {
            $modelObj = $this->getTableLocator()->get($plugin . $modelName);
        } else {
            $modelObj = $this->getTableLocator()->get($plugin . $modelName, [
                'connectionName' => $this->connection,
            ]);
        }

        try {
            $schema = $modelObj->getSchema();
            $data['columns'] = $schema->columns();
            $data['entityClass'] = $modelObj->getEntityClass();
            $data['entityName'] = substr($data['entityClass'], strrpos($data['entityClass'], '\\') + 1);
        } catch (DatabaseException) {
            $data['columns'] = ['id'];
            $data['entityClass'] = 'Cake\\ORM\\Entity';
            $data['entityName'] = 'Entity';
        }
        $data['entityVariable'] = Inflector::variable($data['entityName']);

        return array_merge($parentData, $data);
    }
}
