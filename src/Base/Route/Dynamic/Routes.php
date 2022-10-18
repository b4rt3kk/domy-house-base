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
     * @return \Base\Route\Dynamic\Placeholder
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
     * @return RouteMatch
     */
    public function matchRoute($routeString, $params = [])
    {
        $routeMatch = new RouteMatch();
        
        return $routeMatch->match($routeString, $params);
    }
    
    public function assembleRoute($params)
    {
        
    }
}
