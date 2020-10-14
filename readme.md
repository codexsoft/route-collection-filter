# Symfony RouteCollection Filter

This library provides tool to filter RouteCollection by declarative constraints.

## Usage

```php
<?php

use CodexSoft\RouteCollectionFilter\RouteCollectionFilter;
use CodexSoft\RouteCollectionFilter\RouteFilter;
use Psr\Log\NullLogger;

$routes = new \Symfony\Component\Routing\RouteCollection();

$filtered = (new RouteCollectionFilter(new NullLogger()))->apply($routes, [
    (new RouteFilter())
        ->setAllowedMethods(['POST'])
        ->setAllowedHosts(['api.localhost',])
        ->setControllerClassInterfacesWhitelist([SomeInterface::class,])
        ->setControllerClassNamespacesWhitelist(['Some\Namespace',])
        ->setAllowedPathPrefixes(['/v1/','/v2/',]),

    (new RouteFilter())
        ->setAllowedMethods(['GET'])
        ->setAllowedHosts(['api.localhost',])
        ->setControllerClassInterfacesWhitelist([OtherInterface::class, ThirdInterface::class])
        ->setControllerClassNamespacesWhitelist(['Other\Namespace',]),
]);
```

## Installation

```shell script
composer require codexsoft/route-collection-filter
``` 
