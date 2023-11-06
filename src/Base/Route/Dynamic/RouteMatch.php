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
        ini_set("display_errors", "1");
        if ($_SERVER['REMOTE_ADDR'] == '46.205.211.187') {
            //diee($routeString);
        }
        $return = null;
        $routes = $this->getRoutes();
        $separator = $routes->getRouteStringSeparator();
        
        $parts = explode($separator, $routeString);
        
        $dataRoutes = $routes->getRoutesWithGivenLength(sizeof($parts));
        
        foreach ($dataRoutes as $rowRouteClean) {
            $rowRoute = clone $rowRouteClean;
            /* @var $rowRoute Route */
            $values = $rowRoute->getRouteValuesFromString($routeString);
            /* @var $values \Base\Route\Dynamic\PlaceholderValue[] */
            // Wielowymiarowa tablica route values, gdzie jej klucz to index route part, a wartości to znalezione wartości
            
            if (empty($values)) {
                continue;
            }
            
            // sprawdzenie czy któraś z odnalezionych wartości jest tablicą - zdarza się to w przypadku gdy jest kilka wartości o tej samej nazwie, ale różnych parametrach
            // i odnalezienie właściwej wartości dla zebranej puli parametrów
            foreach ($values as $key => $value) {
                if (is_array($value)) {
                    if ($_SERVER['REMOTE_ADDR'] == '46.205.211.187') {
                        //diee($key, $value, $values);
                    }
                }
            }
            
            if ($_SERVER['REMOTE_ADDR'] == '46.205.211.187') {
                //echo '<pre>';
                //var_dump($routeString, $values);
                //echo '</pre>';
                //echo '<br/><br/>';
            }

            // sprawdzenie poprawności Route względem odnalezionych wartości
            $state = $rowRoute->isRouteValid($routeString, $values, $params);
            
            if (!$state->getIsValid()) {
                if ($_SERVER['REMOTE_ADDR'] == '46.205.211.187') {
                    //var_dump("MESSAGE", $state->getMessage());
                    //echo '<pre>';
                    //var_dump($values);
                    //echo '</pre>';
                    //echo '<br/><br/>';
                }
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
