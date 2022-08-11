<?php
namespace Base\Route;

class RouteSet
{
    protected $routeParams;
    
    protected $routeString;
    
    protected $routeParts = [];
    
    protected $params = [];
    
    /**
     * Pobierz route part o określonym indexie
     * @param integer $index
     * @return \Base\Route\RoutePart
     */
    public function get($index)
    {
        $data = $this->getRouteParts();
        
        foreach ($data as $row) {
            if ($row->getIndex() === $index) {
                return $row;
            }
        }
        
        return null;
    }
    
    public function length()
    {
        return sizeof($this->routeParts);
    }
    
    public function getRouteString()
    {
        return $this->routeString;
    }

    public function setRouteString($routeString): void
    {
        $this->routeString = $routeString;
    }
    
    public function getRouteParams()
    {
        return $this->routeParams;
    }

    public function setRouteParams($routeParams): void
    {
        $this->routeParams = $routeParams;
    }

    public function addRoutePart(RoutePart $routePart)
    {
        $this->routeParts[$routePart->getIndex()] = $routePart;
        ksort($this->routeParts);
    }
    
    /**
     * Pobierz elementy składowe routingu
     * @return \Base\Route\RoutePart[]
     */
    public function getRouteParts()
    {
        return $this->routeParts;
    }
    
    public function getParams()
    {
        return $this->params;
    }

    public function setParams($params): void
    {
        $this->params = $params;
    }
    
    /**
     * Sprawdź czy podane w parametrze RouteSet zawiera w sobie obecny RouteSet
     * @param RouteSet $routeSet
     * @return boolean
     */
    public function isRouteSetMatching(RouteSet $routeSet)
    {
        $matchedParts = 0;
        $matchedParams = [];
        $routeParts = $this->getRouteParts();
        $routeSetParts = $routeSet->getRouteParts();
        
        foreach ($routeParts as $routePart) {
            $rawString = $routePart->getRawString();
            $routeSetPart = $routeSetParts[$routePart->getIndex()];
            /* @var $routeSetPart \Base\Route\RoutePart */
            
            // obecnie route part może posiadać tylko jeden placeholder
            /* @todo Wprowadzić możliwość wielu placeholders w jednym route part */
            $routeSetPartValues = $routeSetPart->getAllPossibleRouteValues();
            
            if (in_array($rawString, $routeSetPartValues)) {
                // wartość obecnej route part z porównywanym route part z podanego route set się zgadza
                $matchedParts++;
            }
        }
        
        return $this->length() === $routeSet->length() && $this->length() === $matchedParts;
    }
    
    public function autodiscoverParams()
    {
        
    }
}
