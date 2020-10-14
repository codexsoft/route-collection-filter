<?php

namespace CodexSoft\RouteCollectionFilter;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Routing\Route;

class RouteCollectionFilterTest extends TestCase
{

    public function testApply()
    {
        $routes = new \Symfony\Component\Routing\RouteCollection();
        $routes->add('', new Route('/v1/aaa'));

        $filtered = (new RouteCollectionFilter(new NullLogger()))->apply($routes, [
            (new RouteFilter())
                ->setAllowedMethods(['POST'])
                ->setAllowedHosts(['api.localhost',])
                //->setControllerClassInterfacesWhitelist([SomeInterface::class,])
                ->setControllerClassNamespacesWhitelist(['Some\Namespace',])
                ->setAllowedPathPrefixes(['/v1/','/v2/',]),

            (new RouteFilter())
                ->setAllowedMethods(['GET'])
                ->setAllowedHosts(['api.localhost',])
                //->setControllerClassInterfacesWhitelist([OtherInterface::class, ThirdInterface::class])
                ->setControllerClassNamespacesWhitelist(['Other\Namespace',]),
        ]);

        self::assertEquals(0, $filtered->count());
    }
}
