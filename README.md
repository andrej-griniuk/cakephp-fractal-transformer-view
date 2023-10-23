[![Build Status](https://img.shields.io/travis/andrej-griniuk/cakephp-fractal-transformer-view/master.svg?style=flat-square)](https://travis-ci.org/andrej-griniuk/cakephp-fractal-transformer-view)
[![Coverage Status](https://codecov.io/gh/andrej-griniuk/cakephp-fractal-transformer-view/branch/master/graph/badge.svg)](https://codecov.io/gh/andrej-griniuk/cakephp-fractal-transformer-view)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)

# FractalTransformerView plugin for CakePHP

This plugin is a thin wrapper for `JsonView` that allows using [Fractal transformers][fractal-transformer] for your API output. What is [Fractal][fractal]?

> Fractal provides a presentation and transformation layer for complex data output, the like found in RESTful APIs, and works really well with JSON. Think of this as a view layer for your JSON/YAML/etc.
> When building an API it is common for people to just grab stuff from the database and pass it to json_encode(). This might be passable for “trivial” APIs but if they are in use by the public, or used by mobile applications then this will quickly lead to inconsistent output.


## Requirements

- CakePHP 4.0+ (use ~1.0 for CakePHP 3.1+)

## Installation

You can install this plugin into your CakePHP application using [Composer][composer].

```bash
composer require andrej-griniuk/cakephp-fractal-transformer-view
```

## Usage
To enable the plugin set `FractalTransformerView.FractalTransformer` class name for viewBuilder. Then you just do what you would normally do in your [data views](http://book.cakephp.org/4/en/views/json-and-xml-views.html) - specify which view vars you want to get serialized by setting `serialize` view builder option. E.g.:

```php
namespace App\Controller;

class ArticlesController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        
        $this->loadComponent('RequestHandler');
        
        $this->viewBuilder()->setClassName('FractalTransformerView.FractalTransformer');
    }

    public function index()
    {
        // Set the view vars that have to be serialized.
        $this->set('articles', $this->paginate());
        // Specify which view vars JsonView should serialize.
        $this->viewBuilder()->setOption('serialize', ['articles']);
    }
}
```

The view will look for transformer class starting with entity name. E.g.:

```php
namespace App\Model\Transformer;

use App\Model\Entity\Article;
use League\Fractal\TransformerAbstract;

class ArticleTransformer extends TransformerAbstract
{
    /**
     * Creates a response item for each instance
     *
     * @param Article $article post entity
     * @return array transformed post
     */
    public function transform(Article $article)
    {
        return [
            'title' => $article->get('title')
        ];
    }
}
```

If transformer class not found the variable is serialized the normal way.

Custom transformer class name can be set by defining `transformer` view builder option:

```php
$this->viewBuilder()->setOption('transform', ['articles' => '\App\Model\Transformer\CustomArticleTransformer']);
```

You can also define if you don't want to use transformer for certain variables:

```php
$this->viewBuilder()->setOption('transform', ['articles' => false]);
```

You can set a custom serializer (class name or object) via `serializer` view builder option:

```php
$this->viewBuilder()->setOption('serializer', new CustomSerializer());
```
## Baking Transformers

To bake transformers you must include the plugin in your src/Application.php file. Add the following to your bootstrap method:

```php
$this->addPlugin('FractalTransformerView');
```

You must also have the [cakephp/bake](https://packagist.org/packages/cakephp/bake) composer package installed.

You can now run `bin/cake bake transformer YOUR_MODEL` to create transformers.

## Bugs & Feedback

https://github.com/andrej-griniuk/cakephp-fractal-transformer-view/issues

## Credits

Inspired by @josegonzalez [Using Fractal to transform entities for custom api endpoints](http://josediazgonzalez.com/2015/12/01/using-fractal-to-transform-entities-for-custom-api-endpoints/).

## License

Copyright (c) 2016, [Andrej Griniuk][andrej-griniuk] and licensed under [The MIT License][mit].

[cakephp]:http://cakephp.org
[composer]:http://getcomposer.org
[fractal]:http://fractal.thephpleague.com/
[fractal-transformer]:http://fractal.thephpleague.com/transformers/
[mit]:http://www.opensource.org/licenses/mit-license.php
[andrej-griniuk]:https://github.com/andrej-griniuk
