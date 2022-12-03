<?php
namespace Base\Route\Dynamic;

class Route
{
    protected $routeString;
    
    protected $routeParams = [];
    
    /**
     * @var RoutePart[]
     */
    protected $routeParts = [];
    
    protected $partsSeparator = Routes::SEPARATOR_SLASH;
    
    protected $routeAssembledString;
    
    protected $routeAssembledParams = [];
    
    public function __construct($routeString, $routeParams = [])
    {
        $this->setRouteString($routeString);
        $this->setRouteParams($routeParams);
    }
    
    public function getRouteString()
    {
        return $this->routeString;
    }

    public function setRouteString($routeString): void
    {
        $separator = $this->getPartsSeparator();
        
        $this->routeString = $routeString;
        $this->setRouteParts(explode($separator, $routeString));
    }
    
    public function getRouteParams()
    {
        return $this->routeParams;
    }

    public function setRouteParams($routeParams): void
    {
        $this->routeParams = $routeParams;
    }
    
    public function getRouteParam($name)
    {
        return array_key_exists($name, $this->routeParams) ? $this->routeParams[$name] : null;
    }
    
    /**
     * Wygeneruj ID route na podstawie route stringa oraz jej parametrów
     * @return string
     */
    public function getRouteUniqId()
    {
        $routeString = $this->getRouteString();
        $params = $this->getRouteParams();
        
        return md5($routeString . serialize($params));
    }
    
    /**
     * @return RoutePart[]
     */
    public function getRouteParts()
    {
        return $this->routeParts;
    }
    
    /**
     * Pobierz długość route
     * @return integer
     */
    public function getRoutePartsLength()
    {
        return sizeof($this->getRouteParts());
    }

    public function getPartsSeparator()
    {
        return $this->partsSeparator;
    }

    public function setPartsSeparator($partsSeparator): void
    {
        $this->partsSeparator = $partsSeparator;
    }
    
    /**
     * Pobierz listę placeholderów z route stringa
     * @return array
     */
    public function getPlaceholders()
    {
        $routeString = $this->getRouteString();
        $matches = [];
        
        preg_match_all("#\{[^\}]+\}#", $routeString, $matches);
        
        return array_key_exists(0, $matches) && is_array($matches[0]) ? $matches[0] : $matches;
    }
    
    /**
     * Sprawdź czy $testedRouteString odpowiada liście odnalezionych wartości przekazanej w $matchedValues
     * W parametrach można przekazać w kluczu `parents` listę rodziców, którą muszą zgadzać się z  $matchedValues
     * Lista rodziców jest również uzupełniane o te, które są powiązane z przekazanymi wartościami
     * @param string $testedRouteString
     * @param array $matchedValues
     * @param array $params
     * @return \Base\Response\State
     */
    public function isRouteValid($testedRouteString, $matchedValues, $params = [])
    {
        $state = new \Base\Response\State();
        
        $isValid = true;
        $routeParts = $this->getRouteParts();
        $mappedRouteParts = $routeParts;
        $separator = $this->getPartsSeparator();
        $routeParams = $this->getRouteParams();
        // tablica rodziców dla znalezionych wartości
        $parents = array_key_exists('parents', $params) ? $params['parents'] : [];
        // tablica z testowanych route parts
        $testedRouteParts = explode($separator, $testedRouteString);
        
        // sprawdzanie odbywa się dla każdego route part osobno
        foreach ($matchedValues as $routeIndex => $values) {
            /* @var $values \Base\Route\Dynamic\PlaceholderValue[] */
            
            // pobranie wartości rodziców dla odnalezionych wartości route part i odłożenie ich w osobnej tablicy
            foreach ($values as $placeholder => $value) {
                if (empty($value)) {
                    // w przypadku gdy nie udało się odnaleźć wartości
                    $state->setMessage(sprintf("Brak wartości dla %s", $placeholder));
                    $isValid = false;
                    // przerwanie dalszego przetwarzania tej części route part
                    break 2;
                }
                
                $mappedRouteParts[$routeIndex] = str_replace($this->getPlaceholderFromName($value->getPlaceholderName()), $value->getValue(), $mappedRouteParts[$routeIndex]);
                
                // jeśli wartość ma rodzica to odłożenie wartości tego rodzica do osobnej tablicy
                // tablica przechowuje rodziców globalnie - niezależnie w którym route part występuje
                if ($value->hasParentValue()) {
                    $parentValue = $value->getParentValue();
                    $parents[$parentValue->getPlaceholderName()] = $parentValue->getValue();
                }
            }
            
            // porównanie testowanego stringa ze znalezionymi wartościami podanymi w parametrze $matchedValues
            $testedString = $testedRouteParts[$routeIndex];
            
            foreach ($values as $value) {
                $testedString = str_replace($this->getPlaceholderFromName($value->getPlaceholderName()), $value->getValue(), $testedString);
            }
            
            if ($testedRouteParts[$routeIndex] !== $testedString) {
                // testowany route part jest różny od tego, dla którego przekazano wartości do przetestowania
                $isValid = false;
                // przerwanie dalszego przetwarzania - jeden błędy route part oznacza, że cały route part jest błędny
                $state->setMessage(sprintf("Testowany route part %s jest różny od odnalezionych wartości %s", $testedRouteParts[$routeIndex], $testedString));
                break;
            }
        }
        
        if ($isValid) {
            // sprawdzenie parametrów dodatkowych względem parametrów route
            foreach ($params['parents'] as $name => $value) {
                if (array_key_exists($name, $routeParams) && $routeParams[$name] !== $value) {
                    $state->setMessage(sprintf("Niezgodna wartość parametru route % z przekazanym %s", $routeParams[$name], $value));
                    
                    $isValid = false;
                    break;
                }
            }
        }
        
        if ($isValid) {
            // w tym momencie jeśli route jest prawidłowe to znaczy, że wszystkie przekazane wartości są prawidłowe i zgadzają się z testowanym stringiem
            // sprawdzamy wszystkie $matchedValues czy posiadają rodziców o odnalezionych wcześniej wartościach
            $matchedParents = 0;
            $parentsToMatch = 0;
            
            foreach ($parents as $parentPlaceholderName => $parentTestedValue) {
                foreach ($matchedValues as $values) {
                    foreach ($values as $value) {
                        // jeśli value jest rodzicem to sprawdzenie czy jego wartość jest zgodna z odnalezionymi wcześniej wartościami rodziców
                        if ($value->getPlaceholderName() === $parentPlaceholderName) {
                            // istnieje taka odnaleziona value, należy ją sprawdzić z wartością
                            $parentsToMatch++;
                            
                            if ($value->getValue() === $parentTestedValue) {
                                // wartość value jest zgodna z odnalezioną wartością parent
                                $matchedParents++;
                            }
                        }
                    }
                }
            }
            
            if ($parentsToMatch !== $matchedParents) {
                $state->setMessage("Liczba pasujących rodziców różni się od liczby wszystkich odnalezionych rodziców");
                $isValid = false;
            }
        }
        
        if (empty($matchedValues)) {
            $isValid = false;
        }
        
        $state->setIsValid($isValid);
        
        return $state;
    }
    
    public function getRouteAssembledString()
    {
        return $this->routeAssembledString;
    }

    public function getRouteAssembledParams()
    {
        return $this->routeAssembledParams;
    }

    public function setRouteAssembledString($routeAssembledString): void
    {
        $this->routeAssembledString = $routeAssembledString;
    }

    public function setRouteAssembledParams($routeAssembledParams): void
    {
        $this->routeAssembledParams = $routeAssembledParams;
    }
    
    public function setRouteAssembledParam($name, $value)
    {
        $this->routeAssembledParams[$name] = $value;
    }
    
    protected function setRouteParts($routeParts): void
    {
        foreach ($routeParts as $routePart) {
            $part = new RoutePart($routePart);
            $this->addRoutePart($part);
        }
    }
    
    protected function addRoutePart(RoutePart $routePart)
    {
        $this->routeParts[] = $routePart;
    }
    
    protected function getPlaceholderFromName($placeholderName)
    {
        return '{' . $placeholderName . '}';
    }
}
