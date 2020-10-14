<?php


namespace CodexSoft\RouteCollectionFilter;


use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Route;

//use function Stringy\create as str;

class RouteFilter
{
    /** @var string[] */
    private array $controllerClassNamespacesWhitelist = [];

    /** @var string[] */
    private array $controllerClassInterfacesWhitelist = [];

    /** @var string[] */
    private array $controllerClassAllowedPathPrefixes = [];

    /** @var string[] */
    private array $allowedPathPrefixes = [];

    /** @var string[] */
    private array $allowedHosts = [];

    /** @var string[] */
    private array $allowedMethods = [];

    private function routeName(Route $route): string
    {
        return \implode(' ', $route->getMethods()).' '.$route->getHost().$route->getPath();
    }

    /**
     * Check if the string starts with the given substring.
     *
     * EXAMPLE: <code>
     * UTF8::str_starts_with('ΚόσμεMiddleEnd', 'Κόσμε'); // true
     * UTF8::str_starts_with('ΚόσμεMiddleEnd', 'κόσμε'); // false
     * </code>
     *
     * @param string $haystack <p>The string to search in.</p>
     * @param string $needle   <p>The substring to search for.</p>
     *
     * @psalm-pure
     *
     * @return bool
     */
    public static function str_starts_with(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }

        if ($haystack === '') {
            return false;
        }

        return \strncmp($haystack, $needle, \strlen($needle)) === 0;
    }

    /**
     * Returns true if the string begins with any of $substrings, false otherwise.
     *
     * - case-sensitive
     *
     * @param string $str        <p>The input string.</p>
     * @param array  $substrings <p>Substrings to look for.</p>
     *
     * @psalm-pure
     *
     * @return bool
     *              <p>Whether or not $str starts with $substring.</p>
     */
    public static function str_starts_with_any(string $str, array $substrings): bool
    {
        if ($str === '') {
            return false;
        }

        if ($substrings === []) {
            return false;
        }

        foreach ($substrings as &$substring) {
            if (self::str_starts_with($str, $substring)) {
                return true;
            }
        }

        return false;
    }

    public function match(Route $route, LoggerInterface $logger): bool
    {
        $endpointClass = $route->getDefault('_controller');

        if (!\class_exists($endpointClass)) {
            $logger->debug('Endpoint '.$this->routeName($route).' skipped because its controller '.$endpointClass.' is not a class');
            return false;
        }

        if ($this->allowedMethods && !\array_intersect($route->getMethods(), $this->allowedMethods)) {
            $logger->debug('Endpoint '.$this->routeName($route).' skipped because its host '.$route->getHost().' is not in allowed methods: '.\implode(', ', $this->allowedMethods));
            return false;
        }

        if ($this->allowedHosts && !\in_array($route->getHost(), $this->allowedHosts)) {
            $logger->debug('Endpoint '.$this->routeName($route).' skipped because its host '.$route->getHost().' is not in allowed hosts: '.\implode(', ', $this->allowedHosts));
            return false;
        }

        if ($this->allowedPathPrefixes && !self::str_starts_with_any($route->getPath(), $this->allowedPathPrefixes)) {
            $logger->debug('Endpoint '.$this->routeName($route).' skipped because its path does not start with allowed prefixes: '.\implode(', ', $this->allowedPathPrefixes));
            return false;
        }

        if ($this->controllerClassInterfacesWhitelist && !\array_intersect($this->controllerClassInterfacesWhitelist, \class_implements($endpointClass))) {
            $logger->debug('Endpoint '.$this->routeName($route).' skipped because its controller '.$endpointClass.' does not implement '.\implode(', ', $this->controllerClassInterfacesWhitelist));
            return false;
        }

        //if ($this->controllerClassAllowedPathPrefixes && !str((new \ReflectionClass($endpointClass))->getFileName())->startsWithAny($this->controllerClassAllowedPathPrefixes)) {
        if ($this->controllerClassAllowedPathPrefixes && !self::str_starts_with_any((new \ReflectionClass($endpointClass))->getFileName(), $this->controllerClassAllowedPathPrefixes)) {
            $logger->debug('Endpoint '.$this->routeName($route).' skipped because its controller defined in file '.(new \ReflectionClass($endpointClass))->getFileName().' that is outside paths whitelist');
            return false;
        }

        //if ($this->controllerClassNamespacesWhitelist && !str($endpointClass)->startsWithAny($this->controllerClassNamespacesWhitelist)) {
        if ($this->controllerClassNamespacesWhitelist && !self::str_starts_with_any($endpointClass, $this->controllerClassNamespacesWhitelist)) {
            $logger->debug('Endpoint '.$this->routeName($route).' skipped because it is not in namespaces whitelist');
            return false;
        }

        $logger->info('Endpoint '.$this->routeName($route).' added '.$endpointClass);
        return true;
    }

    /**
     * @param string[] $controllerClassAllowedPathPrefixes
     *
     * @return self
     */
    public function setControllerClassAllowedPathPrefixes(array $controllerClassAllowedPathPrefixes
    ): self {
        $this->controllerClassAllowedPathPrefixes = $controllerClassAllowedPathPrefixes;
        return $this;
    }

    /**
     * @param string[] $controllerClassInterfacesWhitelist
     *
     * @return self
     */
    public function setControllerClassInterfacesWhitelist(array $controllerClassInterfacesWhitelist
    ): self {
        $this->controllerClassInterfacesWhitelist = $controllerClassInterfacesWhitelist;
        return $this;
    }

    /**
     * @param string[] $controllerClassNamespacesWhitelist
     *
     * @return self
     */
    public function setControllerClassNamespacesWhitelist(array $controllerClassNamespacesWhitelist
    ): self {
        $this->controllerClassNamespacesWhitelist = $controllerClassNamespacesWhitelist;
        return $this;
    }

    /**
     * @param string[] $allowedPathPrefixes
     *
     * @return self
     */
    public function setAllowedPathPrefixes(array $allowedPathPrefixes): self
    {
        $this->allowedPathPrefixes = $allowedPathPrefixes;
        return $this;
    }

    /**
     * @param string[] $allowedHosts
     *
     * @return self
     */
    public function setAllowedHosts(array $allowedHosts): self
    {
        $this->allowedHosts = $allowedHosts;
        return $this;
    }

    /**
     * @param string[] $allowedMethods
     *
     * @return self
     */
    public function setAllowedMethods(array $allowedMethods): self
    {
        $this->allowedMethods = $allowedMethods;
        return $this;
    }
}
