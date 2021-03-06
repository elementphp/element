<h1 align="center">
  <a href="https://elementphp.com">
  <img src="https://elementphp.com/images/logo.png" alt="element" width="200"></a>
  <br>
</h1>

<h4 align="center">MVC Framework for PHP</h4>

<p align="center">

  <a style="margin-left:5px" href="https://img.shields.io/badge/php-7%2B-green.svg?longCache=true&style=flat-square">
    <img src="https://img.shields.io/badge/php-7%2B-green.svg?longCache=true&style=flat-square"
         alt="php version 7 plus">
  </a>
  <a style="margin-left:5px" href="https://img.shields.io/badge/stable-1.2.2-green.svg?longCache=true&style=flat-square">
    <img src="https://img.shields.io/badge/stable-1.2.2-green.svg?longCache=true&style=flat-square"
         alt="stable version 1.2.2">
  </a>

  <a style="margin-left:5px" href="https://img.shields.io/github/issues/paul7337/element.svg?longCache=false&style=flat-square">
    <img src="https://img.shields.io/github/issues/elementphp/element.svg?longCache=false&style=flat-square"
         alt="open issues">
  </a>

 <a style="margin-left:5px" href="https://img.shields.io/badge/licence-bsd3-green.svg?longCache=true&style=flat-square">
    <img src="https://img.shields.io/badge/licence-bsd3-green.svg?longCache=true&style=flat-square"
         alt="licence bsd 3">
  </a>

 <a style="margin-left:5px" href="https://img.shields.io/badge/contributions-welcome-green.svg?longCache=true&style=flat-square">
    <img src="https://img.shields.io/badge/contributions-welcome-green.svg?longCache=true&style=flat-square"
         alt="contributions welcome">
  </a>

</p>

<br />

##	What is it?
A lightweight MVC framework for PHP that can be easily deployed and updated within any hosting.

<br />

## Getting Started

#### Prerequisites

* <b>Apache webserver</b> (must have mod_rewrite enabled) <br />
* <b>PHP V7+</b> (probably works on lower versions, but untested).

#### GitHub
Clone or download repository.

#### Composer

1. If you haven't already done so, install Composer
2. Create a directory in your webspace to contain your files
3. Create a file named composer.json. See example below (name is optional and can be changed)
4. Open command prompt or terminal, cd to directory and input the following command (without quotes)
    
    i.  Windows: 'composer install'
    
    ii. Linux/Mac: 'php composer.phar install'

```json
{
    "name" : "element",
    "require": {
        "elementphp/element": "dev-master"
	},
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

<br />

## Quick Start
Navigate to <b><i>app > configuration > configuration.php</i></b> and update <b>host</b> and <b>root</b> in the <b>domain</b> section of the $config array.
<br /><br />
E.g On localhost in a subdirectory named element ...<br />
host = 'localhost' <br />
root = 'element'

```php
"domain" =>[
	"host" => "localhost/",
	"root" => "element"
],
```
<br />

## Documentation



**Full documentation can be found [here](https://elementphp.com/documentation)**


#### Model

(Example.php)
```php
<?php 

namespace element\mvc;

class Example extends Model {
    public $id;
    public $message;
}

```


#### View 

(index.tpl)
```html
<!DOCTYPE html>
<html>
    <head>
        <title>View Example</title>
    </head>
    <body>
        <p>{$message}</p>
    </body>
</html>
```


#### Controller

(IndexController.php)
```php

<?php 

namespace element\mvc;

class IndexController extends Controller {
    
    public function indexAction() {
        
        /*
           If id is AI primary key we shouldn't explicitly set it.
        */
        
        // create and save model
        $model = new Example();
        $model->message = "Hi there!";
        $model->save(true);
        
        // retrieve model
        // kind of redundant, because we have it in $model, just to explain! :)
        $example = Example::getById($model->id);

        // pass our message to view
        $this->view->assign('message', $example->message);
    }
} 
```

<br >

## Versioning
The versioning scheme we use is [SemVer](http://semver.org/).

<br >

## Built with
[Smarty](https://www.smarty.net/)

<br >

## Authors
Paul Lawton - Creator

<br >

## Contributing
Please read [CONTRIBUTING.md](https://github.com/elementphp/element/blob/master/CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

<br >

## Licence
Open Source under BSD 3 Licence

