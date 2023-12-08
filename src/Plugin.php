<?php
declare(strict_types=1);

namespace FractalTransformerView;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use FractalTransformerView\Command\BakeTransformerCommand;

/**
 * Plugin for FractalTransformerView
 */
class Plugin extends BasePlugin
{
    /**
     * Add console commands for the plugin.
     *
     * @param \Cake\Console\CommandCollection $commands The command collection to update
     * @return \Cake\Console\CommandCollection
     */
    public function console(CommandCollection $commands): CommandCollection
    {
        if (class_exists('Bake\Command\SimpleBakeCommand')) {
            $commands->add('bake transformer', BakeTransformerCommand::class);
        }

        return $commands;
    }
}
