<?php

declare(strict_types=1);

use Cake\Cache\Engine\FileEngine;
use Cake\Database\Connection;
use Cake\Database\Driver\Sqlite;
use Cake\Log\Engine\FileLog;

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
require dirname(__DIR__) . '/vendor/cakephp/cakephp/src/basics.php';
require dirname(__DIR__) . '/vendor/autoload.php';

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
define('ROOT', dirname(__DIR__));
define('APP_DIR', 'src');

define('APP', rtrim(sys_get_temp_dir(), DS) . DS . APP_DIR . DS);
if (!is_dir(APP)) {
    mkdir(APP, 0770, true);
}
define('TESTS', ROOT . DS . 'tests' . DS);

define('CONFIG', dirname(__FILE__) . DS . 'config' . DS);

define('TMP', ROOT . DS . 'tmp' . DS);
if (!is_dir(TMP)) {
    mkdir(TMP, 0770, true);
}

define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);

define('CAKE_CORE_INCLUDE_PATH', ROOT . '/vendor/cakephp/cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);

Cake\Core\Configure::write('debug', true);

$cache = [
    'default' => [
        'engine' => 'File',
        'path' => CACHE,
    ],
    '_cake_core_' => [
        'className' => 'File',
        'prefix' => 'crud_myapp_cake_core_',
        'path' => CACHE . 'persistent/',
        'serialize' => true,
        'duration' => '+10 seconds',
    ],
    '_cake_model_' => [
        'className' => 'File',
        'prefix' => 'crud_my_app_cake_model_',
        'path' => CACHE . 'models/',
        'serialize' => 'File',
        'duration' => '+10 seconds',
    ],
];

Cake\Cache\Cache::setConfig($cache);

// Ensure default test connection is defined
if (!getenv('db_class')) {
    putenv('db_class=Cake\Database\Driver\Sqlite');
    putenv('db_dsn=sqlite:///:memory:');
}

Cake\Datasource\ConnectionManager::setConfig('test', [
    'className' => Connection::class,
    'driver' => SQLite::class,
    'persistent' => false,
    'timezone' => 'UTC',
    //'encoding' => 'utf8mb4',
    'database' => TMP . DS . 'db.sqlite',
    'flags' => [],
    'cacheMetadata' => false,
    'quoteIdentifiers' => true,
    'log' => false,
]);


Cake\Core\Configure::write(
    'App',
    [
        'namespace' => 'FractalTransformerView\Test\App',
        'paths' => [
            'plugins' => [ROOT . 'Plugin' . DS],
            'templates' => [ROOT . 'templates' . DS],
        ],
    ]
);
Cake\Core\Configure::write(
    'Log',
    [
        'debug' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'debug',
            'url' => env('LOG_DEBUG_URL', null),
            'scopes' => false,
            'levels' => ['notice', 'info', 'debug'],
        ],
        'email' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'emails',
            'url' => env('LOG_DEBUG_URL', null),
            'scopes' => 'email',
            'levels' => ['notice', 'info', 'debug'],
        ],
        'error' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'error',
            'url' => env('LOG_ERROR_URL', null),
            'scopes' => false,
            'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
        ],
        // To enable this dedicated query log, you need set your datasource's log flag to true
        'queries' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'queries',
            'url' => env('LOG_QUERIES_URL', null),
            'scopes' => ['queriesLog'],
        ],
    ]
);

Cake\Core\Plugin::getCollection()->add(new \FractalTransformerView\Plugin());

$migrator = new Migrations\TestSuite\Migrator();

// Simple setup for with no plugins
$migrator->run(['plugin' => 'FractalTransformerView']);
