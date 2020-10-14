<?php


namespace CodexSoft\RouteCollectionFilter;


use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Routing\RouteCollection;

class RouteCollectionFilter
{
    private LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param RouteCollection $routeCollection
     * @param RouteFilter|RouteFilter[] $routeFilters
     *
     * @return RouteCollection
     */
    public function apply(RouteCollection $routeCollection, $routeFilters): RouteCollection
    {
        if (!\is_array($routeFilters) && $routeFilters instanceof RouteFilter) {
            $routeFilters = [$routeFilters];
        }

        $filteredRoutesCollection = new RouteCollection();

        foreach ($routeCollection as $routeName => $route) {
            foreach ($routeFilters as $routeFilter) {
                if ($routeFilter->match($route, $this->logger)) {
                    $filteredRoutesCollection->add($routeName, $route);
                    continue 2;
                }
            }
        }

        return $filteredRoutesCollection;
    }
}
