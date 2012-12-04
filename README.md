**This bundle is in development stage!!! Do not use it in production.**

The JsonSchema Bundle
=====================

The purpose of this bundle is to provide a service which allow you to generate json schema based on validation metadata.

If the question is about the purpose of Json schema, well, I can only advise you to take a look at the official website: http://json-schema.org/

Installation
------------
Add the following to your composer.json:
``` json
{
    "mininmum-stability": "dev",
    "require": {
        "knplabs/json-schema-bundle": "dev-master"
    }
}
```

and run `composer.phar update knplabs/json-schema-bundle`

Usage
-----
``` php
public function indexAction()
{
    $json = $this->get('json_schema.generator')->generate('App\\Entity\\User');

    return new JsonResponse($json);
}
```

Contributors
------------
 - Gildas Quéméner [gquemener](https://github.com/gquemener)
