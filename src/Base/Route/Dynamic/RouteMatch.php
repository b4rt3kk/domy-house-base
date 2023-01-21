<?php
namespace Base\Route\Dynamic;

class RouteMatch
{
    /**
     * Znajdź Route na podstawie route string i przekazanych parametrów.
     * Obecnie obsługiwanym parametrem jest tablica o kluczu parents zawierająca kolejną tablicę, gdzie klucz to nazwa rodzica, a wartość jest wartością rodzica (np. subdomain)
     * @param string $routeString
     * @param array $params
     * @return \Base\Route\Dynamic\Route
     */
    public function match($routeString, $params = [])
    {
        $return = null;
        $routes = $this->getRoutes();
        $separator = $routes->getRouteStringSeparator();
        
        $parts = explode($separator, $routeString);
        
        $dataRoutes = $routes->getRoutesWithGivenLength(sizeof($parts));
        
        foreach ($dataRoutes as $rowRouteClean) {
            $rowRoute = clone $rowRouteClean;
            /* @var $rowRoute Route */
            $values = $rowRoute->getRouteValuesFromString($routeString);
            /* @var $values \Base\Route\Dynamic\PlaceholderValue[] Wielowymiarowa tablica route values, gdzie jej klucz to index route part, a wartości to znalezione wartości */
            
            if (empty($values)) {
                continue;
            }
            
            // sprawdzenie poprawności Route względem odnalezionych wartości
            $state = $rowRoute->isRouteValid($routeString, $values, $params);
            
            if (!$state->getIsValid()) {
                continue;
            }

            $assembledValues = [];
            
            $rowRoute->setRouteAssembledString($routeString);
            
            foreach ($values as $routeIndex => $routeValues) {
                foreach ($routeValues as $value) {
                    /* @var $value \Base\Route\Dynamic\PlaceholderValue */
                    
                    $param = new Param();
                    $param->setParamName($value->getPlaceholderName());
                    $param->setParamValue($value->getValue());
                    $param->setRoutePartIndex($routeIndex);
                    $param->setValue($value);
                    
                    $assembledValues[] = $param;
                }
            }
            
            $rowRoute->setRouteAssembledParams($assembledValues);
            
            $return = $rowRoute;
            
            break;
        }
        
        return $return;
    }
    
    /**
     * 
     * @return \Base\Route\Dynamic\Routes
     */
    protected function getRoutes()
    {
        $routes = Routes::getInstance();
        
        return $routes;
    }
}
