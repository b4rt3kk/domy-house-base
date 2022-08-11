<?php
namespace Base\Route;

abstract class DynamicRoute implements \Laminas\Router\Http\RouteInterface
{
    const SEPARATOR_SLASH = "/";
    const SEPARATOR_HASH = "#";
    
    protected static $instance;
    
    protected $options = [];
    
    protected $partsSeparator = self::SEPARATOR_SLASH;
    
    protected $placeholderExpression = '#\{[a-zA-Z0-9\_\#]+\}#';
    
    protected $serviceManager;
    
    public function __construct($options = [])
    {
        $this->setOptions($options);
    }
    
    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return \Laminas\ServiceManager\ServiceManager
     */
    public function getServiceManager() : \Laminas\ServiceManager\ServiceManager
    {
        return $this->serviceManager;
    }

    public function setServiceManager($serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
        
    public function getPartsSeparator()
    {
        return $this->partsSeparator;
    }

    public function setPartsSeparator($partsSeparator)
    {
        $this->partsSeparator = $partsSeparator;
    }
    
    public function getPlaceholderExpression()
    {
        return $this->placeholderExpression;
    }

    public function setPlaceholderExpression($placeholderExpression)
    {
        $this->placeholderExpression = $placeholderExpression;
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
    
    public function assemble(array $params = [], array $options = []): mixed
    {
        ;
    }
    
    public function match(\Laminas\Stdlib\RequestInterface $request, $pathOffset = null)
    {
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
        $routeSet = $this->getRouteSet($path);
        
        // pobierz listę routingów o podanej długości
        $dataRoutes = $this->getRoutesWithGivenLength($routeSet->length());
        
        foreach ($dataRoutes as $rowRouteWithGivenLength) {
            if ($routeSet->isRouteSetMatching($rowRouteWithGivenLength)) {
                // przepisanie parametrów wyszukanego route, zgodnego z obecnym
                $routeSet->setRouteParams($rowRouteWithGivenLength->getRouteParams());
                
                foreach ($rowRouteWithGivenLength->getRouteParts() as $routePart) {
                    // przepisanie placeholderów na obecny route set
                    $routeSet->get($routePart->getIndex())->setPlaceholders($routePart->getPlaceholders());
                }
                
                $routeSet->autodiscoverParams();
            }
        }
        
        diee($routeSet);
        foreach ($dataRoutes as $index => $rowRoute) {
            $routeSetElement = $routeSet->get($index);
            
            if ($routeSetElement !== null) {
                $routeSetElement->setRawString($rowRoute);
            }
        }
        
        diee($routeSet, $dataRoutes->toArray());
        // usunięcie pustych stringów z url
        foreach ($segments as $key => $segment) {
            if (strlen($segment) === 0) {
                unset($segments[$key]);
            }
        }

        $segmentParams = array_values($segments);

        if ($this->hasStaticPage(implode('/', $segmentParams))) {
            // pobranie strony statycznej, jeśli istnieje, tj. wygenerowanej indywidualnie dla routingu
            $routeMatch = $this->getStaticRouteMatch($segmentParams);
        } else {
            // pobranie strony dynamicznej, tj. z dynamicznymi parametrami określonymi na routingu
            $routeMatch = $this->getDynamicRouteMatch($segmentParams);
        }

        return $routeMatch;
    }
    
    public static function factory($options = []): \Laminas\Router\RouteInterface
    {
        return self::getInstance($options);
    }
    
    public function getAssembledParams(): array
    {
        ;
    }
    
    /**
     * Pobierz zestaw routingów dla route stringa
     * @param string $routeString
     * @return \Base\Route\RouteSet
     */
    public function getRouteSet($routeString)
    {
        $return = new RouteSet();
        $return->setRouteString(trim($routeString, self::SEPARATOR_SLASH));
        
        $separator = $this->getPartsSeparator();
        // zamiana zwieloktrotnionych separatorów na pojedyncze
        $string = preg_replace("#{$separator}+#", $separator, $routeString);
        
        $parts = explode($separator, trim($string, $separator));
        
        foreach ($parts as $index => $string) {
            $routePart = new RoutePart();
            $routePart->setRawString($string);
            $routePart->setIndex($index);
            $routePart->setPlaceholderExpression($this->getPlaceholderExpression());
            
            $placeholders = $this->getPlaceholdersFromString($string);
            
            foreach ($placeholders as $placeholder) {
                // ustalenie wszystkich wartości placeholdera
                $placeholder->setValuesData($this->getPlaceholderValues($placeholder->getName()));
            }
            
            $routePart->setPlaceholders($placeholders);
            
            $return->addRoutePart($routePart);
        }
        
        return $return;
    }
    
    /**
     * Pobierz nazwę dla obecnej subdomeny
     * @return string
     */
    public function getSubdomainName()
    {
        $baseUrl = \Base\BaseUrl::getInstance();
        /* @var $baseUrl \Base\BaseUrl */
        
        if (!$baseUrl->getServiceManager() instanceof \Laminas\ServiceManager\ServiceManager) {
            $baseUrl->setServiceManager($this->getServiceManager());
        }

        return $baseUrl->getSubdomain();
    }
    
    /**
     * Pobierz listę placeholderów ze stringa
     * @param string $string
     * @return \Base\Route\Placeholder[]
     */
    protected function getPlaceholdersFromString($string)
    {
        $return = [];
        $matches = [];
        
        preg_match_all($this->getPlaceholderExpression(), $string, $matches);
        
        if (!empty($matches[0])) {
            foreach ($matches[0] as $match) {
                $placeholder = new Placeholder();
                $placeholder->setRawName($match);
                
                $return[] = $placeholder;
            }
        }
        
        return $return;
    }
    
    /**
     * Pobierz listę routingów z długością określoną w parametrze
     * @return \Base\Route\RouteSet[]
     */
    abstract public function getRoutesWithGivenLength($length = null) : array;
    
    abstract public function getPlaceholderValues($placeholder, $params = []);
}
