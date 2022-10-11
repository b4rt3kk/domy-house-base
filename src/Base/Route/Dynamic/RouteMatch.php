<?php
namespace Base\Route\Dynamic;

class RouteMatch
{
    public function match($routeString, $params = [])
    {
        $return = null;
        $routes = $this->getRoutes();
        $separator = $routes->getRouteStringSeparator();
        
        $parts = explode($separator, $routeString);
        
        $dataRoutes = $routes->getRoutesWithGivenLength(sizeof($parts));
        
        foreach ($dataRoutes as $rowRoute) {
            $routeParts = $rowRoute->getRouteParts();
            
            $values = [];
            /* @var $values \Base\Route\Dynamic\PlaceholderValue[] Wielowymiarowa tablica route values, gdzie jej klucz to index route part, a wartości to znalezione wartości */
            
            for ($i = 0; $i < sizeof($parts); $i++) {
                $placeholderValues = $this->getMappedPlaceholders($routeParts[$i], $parts[$i]);
                
                $values[$i] = $placeholderValues;
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
    
    /**
     * Znajdź wartości route part stringa na podstawie part string (stringa z podstawionymi wartościami)
     * Wynikowa tablica to para nazwa placeholdera => wartość placeholdera
     * @param string $routePartString
     * @param string $partString
     * @param array $params
     * @return \Base\Route\Dynamic\PlaceholderValue[]
     * @throws \Exception
     */
    protected function getMappedPlaceholders($routePartString, $partString)
    {
        $return = [];
        
        // wartości dla placeholderów
        $matchedValues = [];
        // lista znalezionych placeholderów
        $matchedPlaceholders = [];
        
        // klucze dla tablic $matchedValues oraz $matchedPlaceholders powinny być sobie zgodne
        
        // wyszukanie placeholderów
        preg_match_all("#\{[^\}]+\}#", $routePartString, $matchedPlaceholders);
        
        // expression zamienia wszystkie znaczniki na znaczniki wyszukiwania
        $pattern = '#^' . preg_replace("#\{[^\}]+\}#", '([a-zA-Z0-9\-\_]+)', $routePartString) . '$#';
        
        preg_match($pattern, $partString, $matchedValues);
        
        if (!empty($matchedValues)) {
            if (in_array($partString, $matchedValues) !== false) {
                unset($matchedValues[array_search($partString, $matchedValues)]);
                $matchedValues = array_values($matchedValues);
            }
        }
        
        if (sizeof($matchedPlaceholders[0]) !== sizeof($matchedValues)) {
            throw new \Exception("Coś poszło nie tak... Odnaleziono więcej wartości placeholderów niż placeholderów");
        }
        
        // sprawdzenie czy znalezione placeholdery mają zgodne wartości (słownikowe) z tymi odnalezionymi 
        foreach ($matchedPlaceholders[0] as $key => $placeholderName) {
            $placeholderObject = $this->getRoutes()->getPlaceholderByRawName($placeholderName);
            
            $return[$placeholderName] = null;
            
            if ($placeholderObject instanceof Placeholder) {
                $value = $placeholderObject->getValueByValue($matchedValues[$key]);
                
                $return[$placeholderName] = $value;
            }
        }
        
        return $return;
    }
}
