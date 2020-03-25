# log SDK for php

----

[![Latest Stable Version](https://packagist.org/packages/xyqweb/log)](https://packagist.org/packages/xyqweb/log)


### Run environment
- PHP 7.1+.

### Install Log PHP SDK

	composer require xyqweb/log
	
- If you use the ***composer*** to manage project dependencies, run the following command in your project's root directory:

        composer require xyqweb/log

   You can also declare the dependency on Log SDK for PHP in the `composer.json` file.

        "require": {
            "xyqweb/log": "~0.1"
        }

   Then run `composer install` to install the dependency. After the Composer Dependency Manager is installed, import the dependency in your PHP code: 

        require_once __DIR__ . '/vendor/autoload.php';
        
## Quick use

### Initialize an LogClient

#### Load in normal mode
     
```php

<?php
$log = new \xyqWeb\log\Log([
    'driver'  => 'ssdb',//only accept file or ssdb
    'host'    => 'xx.xxx.xxx.xxx',//ssdb only
    'port'    => 'xxxxx',//ssdb only
    'project' => 'xxx',//your project name
    'key'     => 'xxxx',//ssdb only
    'path'    => 'path'//log path
]);
$log->write('test.log', ['content' => 'this is test content']);
// You can add subdirectories here
$log->write('test/test.log', ['content' => 'this is test content']);


$log = new \xyqWeb\log\Log([
    'driver'  => 'ssdb',//only accept file or ssdb
    'host'    => 'xx.xxx.xxx.xxx',//ssdb only
    'port'    => 'xxxxx',//ssdb only
    'project' => 'xxx',//your project name
    'key'     => 'xxxx',//ssdb only
    'path'    => 'path'//log path
]);
$log->write('test.log', ['content' => 'this is test content']);
// You can add subdirectories here
$log->write('test/test.log', ['content' => 'this is test content']);
```

#### Load in normal mode yii2
```php
'components' => [
    'yiiLog' => [
        'class' => 'xyqWeb\log\YiiLog',
        'config'=>[
            'driver'  => 'ssdb',//only accept file or ssdb
            'host'    => 'xx.xxx.xxx.xxx',//ssdb only
            'port'    => 'xxxxx',//ssdb only
            'project' => 'xxx',//your project name
            'key'     => 'xxxx',//ssdb only
            'path'    => 'path'//log path
        ]
    ]
]
```


```php
Yii::$app->yiiLog->->write('test.log', ['content' => 'this is test content']);
Yii::$app->yiiLog->write('test/test.log', ['content' => 'this is test content']);
```
