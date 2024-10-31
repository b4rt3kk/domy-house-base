<?php
namespace Base\Route\Dynamic;

class Route
{
    /**
     * Czysty route string bez podstawionych wartości
     * @var string
     */
    protected $routeString;
    
    protected $routeParams = [];
    
    /**
     * @var RoutePart[]
     */
    protected $routeParts = [];
    
    protected $partsSeparator = Routes::SEPARATOR_SLASH;
    
    protected $routeAssembledString;
    
    protected $routeAssembledParams = [];
    
    protected $serviceManager;
    
    public function __construct($routeString, $routeParams = [])
    {
        $this->setRouteString($routeString);
        $this->setRouteParams($routeParams);
    }
    
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
    
    public function getRouteString()
    {
        return $this->routeString;
    }
    
    /**
     * Pobierz oryginalny routeString w postaci znormalizowanej (pozbawionej dodatkowych parametrów)
     * @return $string
     */
    public function getRouteStringNormalized()
    {
        $routeString = $this->getRouteString();
        $matches = [];
        
        preg_match_all("#\{[^\}]+\}#", $routeString, $matches);
        
        foreach ($matches[0] as $match) {
            if (strpos($match, '=') !== false) {
                $chunks = explode('=', str_replace(['{', '}'], '', $match));
                $replacement = '{' . $chunks[0] . '}';
                
                $routeString = str_replace($match, $replacement, $routeString);
            }
        }
        
        return $routeString;
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
     * Pobierz listę placeholderów z route stringa pozbawionych przypisanych wartości
     * @return array
     */
    public function getCleanPlaceholders()
    {
        $return = [];
        $placeholders = $this->getPlaceholders();
        
        foreach ($placeholders as $placeholder) {
            if (strpos($placeholder, '=') !== false) {
                $tmp = explode('=', str_replace(['{', '}'], '', $placeholder));
                $placeholder = '{' . $tmp[0] . '}';
            }
            
            $return[] = $placeholder;
        }
        
        return $return;
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
        
        if (!$this->isRouteStringValid($testedRouteString, $params)) {
            $isValid = false;
        }
        
        if ($isValid) {
            // sprawdzanie odbywa się dla każdego route part osobno
            foreach ($matchedValues as $routeIndex => $values) {
                /* @var $values \Base\Route\Dynamic\PlaceholderValue[] */

                if (empty($values) && !empty($mappedRouteParts[$routeIndex])) {
                    // w odnalezionych wartościach dla route brak wartości dla RoutePart o tym indexie
                    // a jednocześnie ten route part wymaga wartości znacznikowych
                    if ($mappedRouteParts[$routeIndex]->hasPlaceholders()) {
                        $state->setMessage(sprintf("Brak wartości dla part o indexie %s i treści %s", $routeIndex, $mappedRouteParts[$routeIndex]->getString()));
                        $isValid = false;
                        // przerwanie dalszego przetwarzania tej części route part
                        break;
                    }
                }

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

                        if (!array_key_exists($parentValue->getPlaceholderName(), $parents)) {
                            // klucz mógł być zdefiniowany wcześniej, więc jego przypisanie odbywa się dopiero po sprawdzeniu czy nie istnieje
                            $parents[$parentValue->getPlaceholderName()] = $parentValue->getValue();
                        }
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
        }
        
        if ($isValid) {
            // sprawdzenie parametrów dodatkowych względem parametrów route
            foreach ($parents as $name => $value) {
                if (array_key_exists($name, $routeParams) && $routeParams[$name] !== $value) {
                    $state->setMessage(sprintf("Niezgodna wartość parametru route %s z przekazanym %s", $routeParams[$name], $value));
                    
                    $isValid = false;
                    break;
                }
            }
        }
        
        if ($isValid) {
            // w tym momencie jeśli route jest prawidłowe to znaczy, że wszystkie przekazane wartości są prawidłowe i zgadzają się z testowanym stringiem
            // sprawdzamy wszystkie $matchedValues czy posiadają rodziców o odnalezionych wcześniej wartościach
            // należy sprawdzić jeszcze zależności wartości względem siebie na podstawie wcześniej odłożonych rodziców
            foreach ($matchedValues as $matchedValuesArray) {
                // iteracja po samych wartościach
                foreach ($matchedValuesArray as $matchedValue) {
                    /* @var $matchedValue \Base\Route\Dynamic\PlaceholderValue */
                    if (array_key_exists($matchedValue->getPlaceholderName(), $parents) && $matchedValue->getValue() !== $parents[$matchedValue->getPlaceholderName()]) {
                        $isValid = false;
                        
                        $state->setMessage(sprintf(
                            "Odnaleziona wartość rodzica dla %s wskazuje na %s tymczasem przekazano %s #1", 
                            $matchedValue->getPlaceholderName(),
                            $parents[$matchedValue->getPlaceholderName()],
                            $matchedValue->getValue()
                        ));
                    }
                    
                    // dodatkowo jeśli Value posiada rodzica sprawdzenie poprawności jego wartości
                    if ($matchedValue->hasParentValue()) {
                        $parentValue = $matchedValue->getParentValue();
                        
                        if (in_array($parentValue->getPlaceholderName(), array_keys($parents))) {
                            // Nazwa ParentValue znajduje się w tablicy odnalezionych wcześniej rodziców
                            if ($parentValue->getValue() !== $parents[$parentValue->getPlaceholderName()]) {
                                $isValid = false;
                                
                                $state->setMessage(sprintf(
                                    "Odnaleziona wartość rodzica dla %s wskazuje na %s tymczasem przekazano %s #2",
                                    $parentValue->getPlaceholderName(),
                                    $parents[$parentValue->getPlaceholderName()],
                                    $parentValue->getValue()
                                ));
                            }
                        }
                    }
                }
            }
        }
        
        if (empty($matchedValues)) {
            $isValid = false;
        }
        
        $state->setIsValid($isValid);
        
        return $state;
    }
    
    /**
     * Sprawdzenie poprawności stringa względem Routingu
     * @param string $testedRouteString
     * @param array $params
     * @return boolean
     */
    public function isRouteStringValid($testedRouteString, $params = [])
    {
        $isValid = true;
        // separator poszczególnych częsci stringa
        $separator = $this->getPartsSeparator();
        // liczba części/elementów (RoutePart) dla tego Route po rozbiciu po separatorze
        $length = $this->getRoutePartsLength();
        $stringLength = $this->getStringPartsLength($testedRouteString);
        // pobranie sprametryzowanych części stringa dla tego Route
        $routeParts = $this->getRouteParts();
        // tablica rodziców dla znalezionych wartości
        $parents = array_key_exists('parents', $params) ? $params['parents'] : [];
        // poszczególne części testowanego stringa
        $testedRouteStringParts = explode($separator, $testedRouteString);
        
        if ($length !== $stringLength) {
            // długość części testowanego stringa i stringa dla tego route różnią się
            $isValid = false;
        }
        
        if ($isValid) {
            $routeValuesFromTestedString = $this->getRouteValuesFromString($testedRouteString);
            
            // sprawdzenie poprawności poszczególnych odnalezionych wartości z wartościami RoutePart
            for ($i = 0; $i < $length; $i++) {
                $routePart = clone $routeParts[$i];
                /* @var $routePart \Base\Route\Dynamic\RoutePart */
                //$routePart->setServiceManager(null);
                $routePartValues = $routePart->getValuesFromString($testedRouteStringParts[$i], true);
                // predefiniowane wartości dla tego route - z wymuszonymi odgórnie wartościami
                $routePartSpecifiedValues = $routePart->getValues();
                
                if (!empty($routePartValues)) {
                    foreach ($routePartValues as $placeholderValue) {
                        if ($placeholderValue instanceof PlaceholderValue && $placeholderValue->hasParentValue()) {
                            $placeholderParentValue = $placeholderValue->getParentValue();
                            $parents[$placeholderParentValue->getName()] = $placeholderParentValue;
                        }
                    }
                }
                
                if (sizeof($routePartValues) !== sizeof($routeValuesFromTestedString[$i])) {
                    // niezgodna liczba parametrów względem RoutePart i wartości Route dla testowanego stringa
                    $isValid = false;
                }
                
                // porównanie wartości RoutePart z wartościami odnalezionymi dla testowanego stringa
                foreach ($routeValuesFromTestedString[$i] as $placeholderName => $routeValueFromTestedString) {
                    if (!isset($routePartValues[$placeholderName])) {
                        // testowany placeholder nie istnieje, całość routingu jest nieprawidłowa
                        $isValid = false;
                        break;
                    }
                    
                    if ($isValid && !empty($routePartSpecifiedValues) && $routePartSpecifiedValues[$placeholderName] !== $routeValueFromTestedString->getValue()) {
                        // predefiniowana wartość dla RoutePart jest niezgodna z wartością przekazaną
                        $isValid = false;
                        break;
                    }
                    
                    if ($isValid) {
                        if (is_array($routePartValues[$placeholderName])) {
                            // wartości z part są tablicą - sprawdzenie czy te wartości z całości route się zgadzają
                            foreach ($routePartValues[$placeholderName] as $routeValue) {
                                if ($routeValueFromTestedString->getValue() !== $routeValue->getValue()) {
                                    $isValid = false;
                                }
                            }
                        } else if ($routePartValues[$placeholderName]->getValue() !== $routeValueFromTestedString->getValue()) {
                            $isValid = false;
                        }
                    }
                    /*
                    if ($isValid && $routePartValues[$placeholderName]->getValue() !== $routeValueFromTestedString->getValue()) {
                        // wartości nie są zgodne, całość tej części stringa jest nieprawidłowa
                        $isValid = false;
                        break;
                    }
                     * 
                     */
                }
            }
        }

        return $isValid;
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
    
    /**
     * Pobierz tablicę wartości dla poszczególnych części routeStringa w rozbiciu na ich pozycję w routeStringu.
     * Sprawdzanie odbywa się bez sprawdzania poprawności względem obecnego route, w rozbiciu na poszczególne partie route (rozdzielone określonym dla route separatorem)
     * @param string $routeString
     * @return \Base\Route\Dynamic\PlaceholderValue[] Wielowymiarowa tablica route values, gdzie jej klucz to index route part, a wartości to znalezione wartości
     */
    public function getRouteValuesFromString($routeString)
    {
        $routeParts = $this->getRouteParts();
        $separator = $this->getPartsSeparator();
        $stringParts = explode($separator, $routeString);
        $values = [];
        
        for ($i = 0; $i < sizeof($stringParts); $i++) {
            //$placeholderValues = $this->getMappedPlaceholders($routeParts[$i], $stringParts[$i]);
            $placeholderValues = $routeParts[$i]->getValuesFromString($stringParts[$i], true);
            
            //$placeholderValues = $routeParts[$i]->matchValues($stringParts[$i]);
            //diee($placeholderValues);
            //diee($placeholderValues, $routeString);
            if (empty($placeholderValues)) {
                continue;
            }
            
            if ($_SERVER['REMOTE_ADDR'] == '89.66.2.241') {
                if ($routeString == 'dzialki-do-sprzedania/lubelskie/dzialki-na-sprzedaz_wolnica') {
                    // @todo Odkomentować i sprawdzić na trzeźwo
                    //diee($routeString, $routeParts[$i]->matchValues($stringParts[$i]));
                }
            }
            
            
            if ($_SERVER['REMOTE_ADDR'] == '89.66.2.241') {
                //var_dump($routeParts[$i]->matchValues($stringParts[$i]));
                if ($stringParts[$i] == 'dzialki-na-sprzedaz_wolnica') {
                    //diee($routeParts->matchValues($stringToTest));
                    //diee($placeholderValues);
                    //var_dump($this->routeString);echo '<br/>';
                    //var_dump($stringParts[$i], $placeholderValues);echo '<br/>';
                }
            }
            
            if ($routeParts[$i] instanceof RoutePart) {
                if ($routeParts[$i]->hasSpecifiedValues()) {
                    // w przypadku gdy route part ma określone stałe wartości
                    
                    if (empty($routeParts[$i]->getServiceManager())) {
                        $routeParts[$i]->setServiceManager($this->getServiceManager());
                    }
                    
                    foreach ($placeholderValues as $name => $value) {
                        $routePartValue = $routeParts[$i]->getValue($name);
                        if ($value instanceof \Base\Route\Dynamic\PlaceholderValue && $routePartValue !== $value->getValue()) {
                            continue;
                        }
                    }
                }
            }

            $values[$i] = $placeholderValues;
        }
        
        // przefiltrowanie wartości, tak by nie zwracały wieloznacznych wyników
        if (!empty($values)) {
            $values = $this->filterValues($values);
        }
        
        return $values;
    }
    
    /**
     * Pobierz tablicę wszystkich możliwych wartości dla route stringa w postaci tablicy stringów
     * @param array $variants
     * @return array Tablica wszystkich możliwych wartości route stringa
     */
    public function getAllValidRouteVariants()
    {
        $routeParts = $this->getRouteParts();
        $variants = [];
        $routeParams = $this->getRouteParams();
        
        foreach ($routeParts as $index => $routePart) {
            $variants[$index][$routePart->getString()] = [];
            
            $routePart->getAllRoutePartVariants($variants[$index]);
        }
        
        // pierwsze odnalezione wartości RoutePart są zarazem początkiem route stringa
        // route stringi generowane są na kluczu, ponieważ w wartości zachowujemy dostępne wartości dla tego wariantu w postaci kolejnej tablicy obiektów \Base\Route\Dynamic\PlaceholderValue
        $foundRouteStrings = [];
        $parents = [];
        
        foreach ($variants[0] as $key => $variant) {
            // przefiltrowanie tej tablicy wariantów w celu odnalezienia ewentualnych rodziców dla wartości i zachowanie jedynie tych zgodnych
            // z parametrami przekazanymi do tego Route
            if (!empty($variant['values'])) {
                foreach ($variant['values'] as $variantValue) {
                    /* @var $variantValue \Base\Route\Dynamic\PlaceholderValue */
                    if ($variantValue->hasParentValue()) {
                        $parentValue = $variantValue->getParentValue();
                        $parents[$parentValue->getPlaceholderName()] = $parentValue;
                        
                        // wstępne przefiltrowanie parametrów na podstawie zgodnych routeParams
                        if (in_array($parentValue->getPlaceholderName(), array_keys($routeParams))) {
                            if ($parentValue->getValue() !== $routeParams[$parentValue->getPlaceholderName()]) {
                                // pominięcie niedopasowanych wyników
                                // dla tego wariantu, ponieważ conajmniej jeden z parametrów jest niezgodny z odnalezionymi
                                continue 2;
                            } else {
                                $variant['values']['{' . $parentValue->getPlaceholderName() . '}'] = $parentValue;
                            }
                        }
                        
                        $foundRouteStrings[$key] = $variant['values'];
                    }
                }
            }
        }
        
        $index = 1;
        
        while ($index < sizeof($variants)) {
            // pomnożenie każdego route o każdą kolejną wartość z kolejnego RoutePart
            // przy okazji sprawdzenie poprawności odpowiednich rodziców dla wartości
            $newValues = [];
            
            foreach ($foundRouteStrings as $key => $routeValues) {
                foreach ($variants[$index] as $variantString => $variant) {
                    $variantValues = $variant['values'];
                    /* @var $variantValues \Base\Route\Dynamic\PlaceholderValue[] */
                    
                    // sprawdzenie rodziców
                    foreach ($variantValues as $variantValueKey => $variantValueValue) {
                        if ($variantValueValue->hasParentValue()) {
                            // sprawdzenie czy rodzic ma odpowiednią wartość dla odnalezionych wartości dla tego route stringa
                            $variantValueValueParent = $variantValueValue->getParentValue();
                            
                            if (!in_array('{' . $variantValueValueParent->getPlaceholderName() . '}', array_keys($routeValues))) {
                                // brak rodzica o tej samej nazwie placeholdera co odnalezione wartości dla tego route
                                // całkowite pominięcie tego route
                                continue 2;
                            }
                            
                            // istnieje rodzic dla wartości wariantu oraz posiada odnalezioną wartość dla sprawdzanego route
                            if ($routeValues['{' . $variantValueValueParent->getPlaceholderName() . '}']->getValue() !== $variantValueValueParent->getValue()) {
                                // wartość rodzica jest różna od odpowiadającej wartości odnalezionej dla route
                                // całkowite pominięcie tego route
                                continue 2;
                            }
                        }
                    }
                    
                    // utworzenie nowej tablicy do zastąpienia poprzedniej
                    $newKey = $key . $this->getPartsSeparator() . $variantString;
                    $newValues[$newKey] = array_merge($routeValues, $variantValues);
                }
            }
            
            // przepisanie znalezionych wartości jako kolejne route stringi
            $foundRouteStrings = $newValues;
            $index++;
        }
        
        return array_keys($foundRouteStrings);
    }
    
    /**
     * Pobierz tablicę wartości, dla placeholderów, dla których przypisano już określoną wartość 
     * @return array
     */
    public function getRouteValuesFromRouteString()
    {
        $return = [];
        
        $routeString = $this->getRouteString();
        $placeholders = $this->getPlaceholdersFromString($routeString);
        $routes = $this->getRoutes();
        
        if (!empty($placeholders)) {
            foreach ($placeholders as $placeholder) {
                // placeholder ma przypisaną stałą wartość
                if (strpos($placeholder, '=') !== false) {
                    $chunks = explode('=', trim($placeholder, '{}'));
                    
                    $placeholderObject = $routes->getPlaceholder($chunks[0]);
                    $placeholderValue = $placeholderObject->getValueByValue($chunks[1]);
                    
                    $return[$placeholderObject->getRawName()] = $placeholderValue;
                }
            }
        }
        
        return $return;
    }

    protected function setRouteParts($routeParts): void
    {
        foreach ($routeParts as $index => $routePart) {
            $part = new RoutePart($routePart);
            $part->setIndex($index);
            
            $part->setServiceManager($this->getServiceManager());
            
            $this->addRoutePart($part);
        }
    }
    
    protected function addRoutePart(RoutePart $routePart)
    {
        $routePart->setServiceManager($this->getServiceManager());
        $this->routeParts[] = $routePart;
    }
    
    protected function getPlaceholderFromName($placeholderName)
    {
        return '{' . $placeholderName . '}';
    }
    
    /**
     * Pobierz listę placeholderów ze stringa
     * @param string $string
     * @return array
     */
    protected function getPlaceholdersFromString($string)
    {
        // lista znalezionych placeholderów
        $matchedPlaceholders = [];

        // wyszukanie placeholderów
        preg_match_all("#\{[^\}]+\}#", $string, $matchedPlaceholders);

        return array_key_exists(0, $matchedPlaceholders) ? $matchedPlaceholders[0] : $matchedPlaceholders;
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
        // @todo Ta metoda jest do naprawy, należy lecieć po wartośćiach placeholderów i w pętli podmieniać po kolei na wszystkie kombinacje
        // i sprawdzać czy zmontowany string zgadza się z $partString
        // matchedPlaceholders jest ok i zostaje i na tej podstawie pobieramy Placeholder i wszystkie value z niego
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
            //throw new \Exception("Coś poszło nie tak... Odnaleziono więcej wartości placeholderów niż placeholderów");
            return [];
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
    
    /**
     * Pobierz liczbę części stringa w rozbiciu po separatorze
     * @param string $routeString
     * @return integer
     */
    protected function getStringPartsLength($routeString)
    {
        $separator = $this->getPartsSeparator();
        
        return sizeof(explode($separator, $routeString));
    }
    
    protected function filterValues($values)
    {
        $return = [];
        $valuesWithoutIndexing = $this->getValuesWithoutRouteIndexing($values);
        
        foreach ($values as $routeIndex => $routeValues) {
            $return[$routeIndex] = [];
            
            foreach ($routeValues as $placeholderName => $placeholderValue) {
                $filteredPlaceholderValue = $placeholderValue;
                
                if (is_array($placeholderValue)) {
                    foreach ($placeholderValue as $value) {
                        /* @var $value \Base\Route\Dynamic\PlaceholderValue */
                        if ($value->hasParentValue()) {
                            // można określić, która wartość jest prawidłowa tylko w przypadku, gdy istnieje wartość nadrzędna
                            $parentValue = $value->getParentValue();
                            $matchedParentValue = $valuesWithoutIndexing['{' . $parentValue->getPlaceholderName() . '}'];
                            
                            if ($matchedParentValue instanceof \Base\Route\Dynamic\PlaceholderValue) {
                                if ($matchedParentValue->getValue() == $parentValue->getValue()) {
                                    // wartość rodzica jest zgodna z tą odnalezioną w pozostałych częściach route stringa
                                    // to ją należy uznać za poprawną
                                    $filteredPlaceholderValue = $value;
                                    // można przerwać dalsze sprawdzanie dla tego placeholdera
                                    break;
                                }
                            }
                        }
                    }
                }
                
                $return[$routeIndex][$placeholderName] = $filteredPlaceholderValue;
            }
        }
        
        return $return;
    }
    
    /**
     * Przefiltreuj wartości i je pobierz bez indexowania, w której cześci route stringa występują
     * @param array $values
     * @return array
     */
    protected function getValuesWithoutRouteIndexing($values)
    {
        $return = [];
        
        foreach ($values as $routeIndex => $routeValues) {
            foreach ($routeValues as $placeholderName => $placeholderValue) {
                $return[$placeholderName] = $placeholderValue;
            }
        }
        
        return $return;
    }
}
