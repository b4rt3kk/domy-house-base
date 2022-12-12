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
        $return = null;
        $placeholders = $this->getValidPlaceholdersFromArray($params);
        $routes = $this->getRoutesWithGivenPlaceholders($placeholders);
        
        foreach ($routes as $route) {
            $rowRoute = clone $route;
            /* @var $rowRoute Route */
            $routeString = $rowRoute->getRouteStringNormalized();
            
            // podmiana znaczników na wartości
            foreach ($params as $placeholder => $value) {
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
            $placeholder = clone $this->getPlaceholder($name);
            
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
}
