<?php
namespace Base\Route\Dynamic;

class Routes
{
    const SEPARATOR_SLASH = '/';
    
    /**
     * @var \Base\Route\Dynamic\Routes
     */
    protected static $instance;
    
    /**
     * Lista placeholderów obsługiwanych przez routes
     * @var \Base\Route\Dynamic\Placeholders
     */
    protected $placeholders;
    
    /**
     * @var \Base\Route\Dynamic\Route[]
     */
    protected $data = [];
    
    protected $routeStringSeparator = self::SEPARATOR_SLASH;
    
    protected $serviceManager;
    
    /**
     * @return \Laminas\ServiceManager\ServiceManager
     */
    public function getServiceManager()
    {
        $serviceManager = \Base\ServiceManager::getInstance();
        
        $return = $this->serviceManager;
        
        if ($serviceManager instanceof \Laminas\ServiceManager\ServiceManager) {
            $return = $serviceManager;
        }
        
        return $return;
    }

    public function setServiceManager($serviceManager)
    {
        if (!$this->getServiceManager() instanceof \Laminas\ServiceManager\ServiceManager) {
            \Base\ServiceManager::setInstance($serviceManager);
        }
    }
    
    /**
     * @return \Base\Route\Dynamic\Routes
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof Routes) {
            $instance = new Routes();
            $instance->setPlaceholders(new Placeholders());
            
            self::$instance = $instance;
        }

        return self::$instance;
    }
    
    /**
     * @return \Base\Route\Dynamic\Placeholders
     */
    public function getPlaceholders(): \Base\Route\Dynamic\Placeholders
    {
        return $this->placeholders;
    }

    public function setPlaceholders(\Base\Route\Dynamic\Placeholders $placeholders): void
    {
        $this->placeholders = $placeholders;
    }
    
    public function addPlaceholder(Placeholder $placeholder)
    {
        $placeholders = $this->getPlaceholders();
        
        if (!$placeholders->hasPlaceholder($placeholder)) {
            $placeholders->addPlaceholder($placeholder);
        }
    }
    
    /**
     * Pobierz placeholder na podstawie nazwy
     * @param string $name
     * @return \Base\Route\Dynamic\Placeholder|null
     */
    public function getPlaceholder($name)
    {
        $placeholder = $this->getPlaceholders()->getPlaceholderByName($name);
        
        return $placeholder;
    }
    
    /**
     * Pobierz placeholder na podstawie nazwy kodowej
     * @param string $name
     * @return \Base\Route\Dynamic\Placeholder
     */
    public function getPlaceholderByRawName($name)
    {
        $placeholder = $this->getPlaceholders()->getPlaceholderByRawName($name);
        
        return $placeholder;
    }
    
    public function getRouteStringSeparator()
    {
        return $this->routeStringSeparator;
    }

    public function setRouteStringSeparator($routeStringSeparator): void
    {
        $this->routeStringSeparator = $routeStringSeparator;
    }
    
    public function addRoute(Route $route)
    {
        if ($this->hasRoute($route)) {
            throw new \Exception(sprintf("Route o stringu %s i podanych parametrach już istnieje", $route->getRouteString()));
        }
        
        $this->data[] = $route;
    }
    
    public function hasRoute(Route $route)
    {
        $return = false;
        $data = $this->getRoutes();

        foreach ($data as $row) {
            if ($row->getRouteUniqId() === $route->getRouteUniqId()) {
                $return = true;
                
                break;
            }
        }
        
        return $return;
    }
    
    /**
     * @return \Base\Route\Dynamic\Route[]
     */
    public function getRoutes()
    {
        return $this->data;
    }
    
    /**
     * Pobierz tablicę Routes o podanej długości (liczbie parametrów/route parts)
     * @param integer $length
     * @return \Base\Route\Dynamic\Route[]
     */
    public function getRoutesWithGivenLength($length)
    {
        $return = [];
        $data = $this->getRoutes();
        
        foreach ($data as $row) {
            if ($row->getRoutePartsLength() === $length) {
                $return[] = $row;
            }
        }
        
        return $return;
    }
    
    /**
     * Pobierz listę Route, które posiadają parametr o podanej nazwie i wartości
     * @param string $paramName
     * @param mixed $paramValue
     * @return \Base\Route\Dynamic\Route[]
     */
    public function getRoutesWithGivenParamValue($paramName, $paramValue)
    {
        $return = [];
        $routes = $this->getRoutes();
        
        foreach ($routes as $route) {
            $param = $route->getRouteParam($paramName);
            
            if ($param == $paramValue) {
                $return[] = $route;
            }
        }
        
        return $return;
    }
    
    public function setRoutes($routes)
    {
        foreach ($routes as $route) {
            $this->addRoute($route);
        }
    }
    
    /**
     * Pobierz listę placeholderów dla stringa routingu
     * @param string $string
     * @return \Base\Route\Dynamic\Placeholder[]
     */
    public function getPlaceholdersFromString($string)
    {
        $return = [];
        $placeholders = $this->getPlaceholders();
        
        foreach ($placeholders->getData() as $placeholder) {
            /* @var $placeholder \Base\Route\Dynamic\Placeholder */
            if (strpos($string, $placeholder->getRawName()) !== false) {
                $return[] = $placeholder;
            }
        }
        
        return $return;
    }
    
    /**
     * Wyszukaj Route na podstawie przekazanego stringa oraz parametrów.
     * W przypadku przekazaniu w parametrach klucza o nazwie `parents` sprawdzane są dodatkowo wartości pod kątem zgodności z wskazanymi wartościami parents
     * @param string $routeString String, który jest pełną ścieżką z podstawionymi wartościami, na podstawie której zostanie wyszukane odpowiednie Route
     * @param array $params
     * @return \Base\Route\Dynamic\Route
     */
    public function matchRoute($routeString, array $params = [])
    {
        $routeMatch = new RouteMatch();
        
        return $routeMatch->match($routeString, $params);
    }
    
    /**
     * Znajdź odpowiednie Route na podstawie przekazanych parametrów i opcji.
     * W opcjach można (i należy) przekazać w tablicy dla klucza `parents` kolejną tablicę, która zawiera tablicę klucz => wartość,
     * gdzie wartość to nazwa parametru, a wartość to wartość parametru. Minimalnie wymagana jest wartość dla subdomain => name.
     * @param array $params
     * @param array $options
     * @return \Base\Route\Dynamic\Route
     */
    public function assembleRoute(array $params = [], array $options = [])
    {
        $storage = $this->getStorage();
        
        $cacheKey = $this->getCacheKey(['params' => $params, 'options' => $options]);
        
        if ($storage->hasItem($cacheKey)) {
            //return $storage->getItem($cacheKey);
        }
        
        $return = null;
        $placeholders = $this->getValidPlaceholdersFromArray($params);
        $routes = $this->getRoutesWithGivenPlaceholders($placeholders);
        
        foreach ($routes as $route) {
            $rowRoute = clone $route;
            /* @var $rowRoute Route */
            $routeString = $rowRoute->getRouteStringNormalized();
            
            if ($_SERVER['REMOTE_ADDR'] == '46.205.211.187') {
                //diee($params, $options, $route);
            }

            // podmiana znaczników na wartości
            foreach ($params as $placeholder => $value) {
                if (is_array($value)) {
                    $strValue = $routeString;
                    
                    foreach ($value as $valueKey => $valueValue) {
                        $strValue = str_replace('{' . $valueKey . '}', $valueValue, $strValue);
                    }
                    
                    $value = $strValue;
                }
                
                $routeString = str_replace('{' . $placeholder . '}', $value, $routeString);
            }
            
            $values = $rowRoute->getRouteValuesFromString($routeString);
            
            if (empty($values)) {
                // nie odnaleziono prawidłowych wartości
                continue;
            }

            $state = $rowRoute->isRouteValid($routeString, $values, $options);
            
            if ($state->getIsValid()) {
                $rowRoute->setRouteAssembledParams($params);
                
                $return = $rowRoute;
                break;
            }
        }
        
        $storage->setItem($cacheKey, $return);
        
        return $return;
    }
    
    public function assemble($params = [], $options = [])
    {
        $return = null;
        $foundRoute = null;
        $placeholders = $this->getValidPlaceholdersFromArray($params);
        $routes = $this->getRoutesWithGivenPlaceholders($placeholders);
        $values = [];
        
        foreach ($placeholders as $placeholderName => $placeholder) {
            $value = $placeholder->getValueByValue($params[str_replace(['{', '}'], '', $placeholderName)], array_merge($params, $options));
            
            if (is_array($value)) {
                /** @todo Szybki fix - bo tutaj nie powinno być więcej jak jedno value - błąd w bazie */
                $value = $value[0];
            }
            
            $values[$placeholderName] = $value;
        }
        
        if ($_SERVER['REMOTE_ADDR'] == '46.205.211.187') {
            //diee($values);
            //diee($params, $options, $route);
        }

        //diee($routes);
        foreach ($routes as $route) {
            $rowRoute = clone $route;
            $normalizedString = $rowRoute->getRouteStringNormalized();
            
            if (!empty($options['subdomain'])) {
                // sprawdzenie czy zgadza się subdomena
                if ($rowRoute->getRouteParam('subdomain') != $options['subdomain']) {
                    // pominięcie tej route jeśli domena jest inna
                    continue;
                }
            }
            
            // podmiana parametrów na odnalezione wartości
            foreach ($values as $name => $value) {
                $normalizedString = str_replace($name, $value->getValue(), $normalizedString);
            }
            
            if (strpos($normalizedString, '{') !== false) {
                // route posiada nieodnalezione wartości placeholderów
                // pominięcie
                continue;
            }
            
            $rowRoute->setRouteAssembledParams($params);
            
            $foundRoute = $rowRoute;
            break;
        }
        
        if (!empty($foundRoute)) {
            $routeString = $foundRoute->getRouteStringNormalized();
            $assembledParams = $foundRoute->getRouteAssembledParams();
            
            foreach ($assembledParams as $paramName => $paramValue) {
                $routeString = str_replace('{' . $paramName . '}', $paramValue, $routeString);
            }
            
            $return = '/' . trim($routeString, '/');
        }
        
        return $return;
    }
    
    /**
     * Pobierz tablicę dostępnych dla routingów placeholderów na podstawie tablicy, gdzie klucz to nazwa placeholdera
     * @param array $placeholders
     * @return \Base\Route\Dynamic\Placeholder[]|null
     */
    protected function getValidPlaceholdersFromArray(array $placeholders)
    {
        $return = [];
        
        foreach ($placeholders as $name => $value) {
            $placeholder = $this->getPlaceholder($name);
            
            if (is_object($placeholder)) {
                $placeholder = clone $placeholder;
            }
            
            if (!empty($placeholder) && $placeholder->hasValue($value)) {
                $placeholder->setAssembledValue($value);
                $return[$placeholder->getRawName()] = $placeholder;
            }
        }
        
        return $return;
    }
    
    /**
     * Pobierz tablicę Route, która zawiera jedynie Route, które posiadają placeholdery przekazane w parametrze bez żadnych dodatkowych/nadmiarowych
     * @param array $placeholders
     * @return \Base\Route\Dynamic\Route[]
     */
    protected function getRoutesWithGivenPlaceholders(array $placeholders)
    {
        $return = [];
        $data = $this->getRoutes();
        
        foreach ($data as $route) {
            // stringi placeholderów z route stringa
            $valid = true;
            // unikalne (usunięte duplikaty) nazwy placeholderów dla Route
            $routePlaceholders = array_unique($route->getCleanPlaceholders());
            $matchedPlaceholders = [];
            
            foreach (array_keys($placeholders) as $placeholder) {
                $matchedPlaceholders[$placeholder] = 0;
                
                foreach ($routePlaceholders as $routePlaceholder) {
                    if ($placeholder === $routePlaceholder) {
                        // znaleziono placeholder w Route
                        $matchedPlaceholders[$placeholder]++;
                    }
                }
            }
            
            foreach ($matchedPlaceholders as $matchedPlaceholder) {
                if ($matchedPlaceholder <= 0) {
                    // pominięcie routes dla których nie znaleziono odpowiedniej liczby parametrów
                    continue 2;
                }
            }
            
            foreach ($routePlaceholders as $routePlaceholder) {
                if (!in_array($routePlaceholder, array_keys($placeholders))) {
                    // pominięcie routes, dla których brakuje odpowiednich parametrów
                    continue 2;
                }
            }
            
            if ($valid) {
                $return[] = clone $route;
            }
        }
        
        return $return;
    }
    
    /**
     * Pobierz adapter cache
     * @return \Laminas\Cache\Storage\Adapter\AbstractAdapter
     */
    protected function getStorage()
    {
        $storageFactory = $this->getServiceManager()->get(\Laminas\Cache\Service\StorageAdapterFactoryInterface::class);
        /* @var $storageFactory \Laminas\Cache\Service\StorageAdapterFactory */
        
        $config = $this->getServiceManager()->get('Config')['cache'];
        
        $cache = $storageFactory->createFromArrayConfiguration($config);
        
        return $cache;
    }
    
    protected function getCachePrefix()
    {
        return md5(get_class($this)) . '_';
    }
    
    protected function getCacheKey($data)
    {
        $prefix = $this->getCachePrefix();
        
        $key  = $prefix;
        $key .= md5(serialize($data));
        
        return $key;
    }
}
