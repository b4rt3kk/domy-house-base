<?php
namespace Base\Route;

class DynamicRoute implements \Laminas\Router\Http\RouteInterface
{
    protected static $instance;
    
    protected $options = [];
    
    protected $assembledParams = [];
    
    public function __construct($options = [])
    {
        $this->setOptions($options);
    }
    
    /**
     * @param array $options
     * @return \Base\Route\DynamicRoute
     */
    public static function getInstance($options = [])
    {
        if (empty(self::$instance)) {
            self::$instance = new static($options);
        }
        
        return self::$instance;
    }
    
    public static function factory($options = []): \Laminas\Router\RouteInterface
    {
        return self::getInstance($options);
    }
    
    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options): void
    {
        $this->options = $options;
    }
    
    /**
     * Utwórz adres url na podstawie przekazanych parametrów lub null gdy nie udało się dopasować route do parametrów
     * @param array $params
     * @param array $options
     * @return string|null
     */
    public function assemble(array $params = [], array $options = [])
    {
        $url = null;
        $routes = $this->getRoutes();
        
        $route = $routes->assembleRoute($params, $options);
        /* @var $route \Base\Route\Dynamic\Route */
        
        if (!empty($route)) {
            // ustawienie wartości pobranych z Route
            $assembledParams = array_merge($route->getRouteAssembledParams(), $route->getRouteParams());
            
            $rawRouteString = $route->getRouteStringNormalized();
            $url = '/' . $rawRouteString;

            foreach ($assembledParams as $assembledParamName => $assembledParamValue) {
                $url = str_replace('{' . $assembledParamName . '}', $assembledParamValue, $url);
            }
        }
        
        return $url;
    }

    /**
     * Ustaw parametry dla znalezionego Route (metoda match)
     * @param array $assembledParams
     * @return void
     */
    public function setAssembledParams(array $assembledParams): void
    {
        $this->assembledParams = $assembledParams;
    }
    
    /**
     * Pobierz tablicę parametrów (nazwa => wartość) znalezionych podczas określania obecnego Route (metoda match)
     * @return array
     */
    public function getAssembledParams(): array
    {
        return $this->assembledParams;
    }

    /**
     * Zmatchuj request z route
     * @param \Laminas\Stdlib\RequestInterface $request
     * @param string $pathOffset
     * @return \Laminas\Router\Http\RouteMatch|null
     */
    public function match(\Laminas\Stdlib\RequestInterface $request, $pathOffset = null)
    {
        $baseUrl = \Base\BaseUrl::getInstance();
        $routes = $this->getRoutes();
        $routeParams = [];
        $routeParamsIds = [];
        $params = [];
        // pobranie nazwy subdomeny
        $subdomain = $baseUrl->getSubdomain();
        
        if (!method_exists($request, 'getUri')) {
            return null;
        }
        
        // Get the URL and its path part.
        $uri = $request->getUri();
        $path = $uri->getPath();

        if ($pathOffset != null) {
            $path = substr($path, $pathOffset);
        }
        
        // Get the array of path segments.
        $segments = explode('/', $path);
        
        // usunięcie pustych stringów z url
        foreach ($segments as $key => $segment) {
            if (strlen($segment) === 0) {
                unset($segments[$key]);
            }
        }
        
        if (empty($segments)) {
            return null;
        }
        
        if (!empty($subdomain)) {
            // przypisanie wyszukiwanej subdomeny
            $params['parents'] = ['subdomain' => $subdomain];
        }
        
        $route = $routes->matchRoute(implode($routes->getRouteStringSeparator(), $segments), $params);
        /* @var $route \Base\Route\Dynamic\Route */
        
        if (empty($route)) {
            return null;
        }
        
        $assembledParams = $route->getRouteAssembledParams();
        
        foreach ($assembledParams as $assembledParam) {
            /* @var $assembledParam \Base\Route\Dynamic\Param */
            $name = $assembledParam->getParamName();
            $value = $assembledParam->getParamValue();
            $id = $assembledParam->getValue()->getParamByName('id');
            
            $routeParams[$name] = $value;
            $routeParamsIds[$name] = $id;
        }
        
        // ustawienie ustalonych parametrów znalezionego Route
        $this->setAssembledParams(array_merge($routeParams, [
            'idRoutingRule' => $route->getRouteParam('id'),
        ]));
        
        $routeMatch = new \Laminas\Router\Http\RouteMatch(array_merge([
            'controller' => \Application\Controller\LandingController::class,
            'action' => 'index',
            'idRoutingRule' => $route->getRouteParam('id'),
            'isStaticRoute' => false,
            'routeParams' => $routeParams,
            'routeParamsIds' => $routeParamsIds,
        ], $routeParams));
        
        return $routeMatch;
    }
    
    /**
     * @return \Base\Route\Dynamic\Routes
     */
    protected function getRoutes()
    {
        $routes = Dynamic\Routes::getInstance();
        
        return $routes;
    }
}
