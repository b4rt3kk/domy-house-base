<?php
namespace Base\Route\Dynamic;

class RoutePart
{
    /**
     * Oryginalna wartość stringa bez obróbki
     * @var string
     */
    protected $rawString;
    
    /**
     * Wartość stringa po obróbce, dostosowana do użycia jako route part
     * @var string
     */
    protected $string;
    
    /**
     * Oczekiwana wartość route part, podczas poszukiwania pasującego route musi się zgadzać z wartością tego route
     * @var string
     */
    protected $values = [];
    
    /**
     * Kolejność RoutePart w całym stringu liczona od zera
     * @var integer
     */
    protected $index;
    
    protected $serviceManager;
    
    protected $storageKeyPrefix;
    
    public function __construct($rawString)
    {
        if (!empty($rawString)) {
            $this->setRawString($rawString);
            // string pozbawiony wartości parametrów, dostosowany do wyszukiwania
            $this->setString($this->normalizeStringAndAssignValues($rawString));
        }
    }
    
    /**
     * @return \Laminas\ServiceManager\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function setServiceManager($serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getStorageKeyPrefix()
    {
        return $this->storageKeyPrefix;
    }

    public function setStorageKeyPrefix($storageKeyPrefix)
    {
        $this->storageKeyPrefix = $storageKeyPrefix;
    }
    
    public function getRawString()
    {
        return $this->rawString;
    }

    public function getString()
    {
        return $this->string;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function setRawString($rawString): void
    {
        $this->rawString = $rawString;
    }

    public function setString($string): void
    {
        $this->string = $string;
    }

    public function setValues($values): void
    {
        $this->values = $values;
    }
    
    public function setValue($name, $value)
    {
        $this->values[$name] = $value;
    }
    
    public function getValue($name)
    {
        $values = $this->getValues();
        
        return array_key_exists($name, $values) ? $values[$name] : null; 
    }
    
    public function isValueMatching($name, $value)
    {
        $data = $this->getValue($name);
        
        return $data === $value;
    }
    
    /**
     * Sprawdź czy route part posiada przypisane określone wartości 
     * @return boolean
     */
    public function hasSpecifiedValues()
    {
        $return = false;
        $data = $this->getValues();
        
        foreach ($data as $value) {
            if (isset($value)) {
                $return = true;
            }
        }
        
        return $return;
    }
    
    public function getIndex()
    {
        return $this->index;
    }

    public function setIndex($index): void
    {
        $this->index = $index;
    }
    
    /**
     * Pobierz placeholdery ze stringa RoutePart
     * @return array
     */
    public function getPlaceholdersNamesFromString()
    {
        return $this->getPlaceholdersFromString($this->getString());
    }
    
    /**
     * Sprawdź czy część Route posiada placeholdery w swojej treści
     * @return bool
     */
    public function hasPlaceholders()
    {
        $placeholders = $this->getPlaceholdersNamesFromString();
        
        return !empty($placeholders);
    }
    
    public function __toString()
    {
        return $this->getString();
    }
    
    /**
     * Pobierz listę dopasowanych wartości do tego route part i testowanego stringa przyrównując do podstawowego (czystego) stringa dla tego route
     * @param string $stringToTest
     * @param bool $forceSimpleMatching Wymuś matchowanie stringa na podstawie prostego odszukiwania wartości
     * @return \Base\Route\Dynamic\PlaceholderValue[]
     */
    public function getValuesFromString($stringToTest, $forceSimpleMatching = false)
    {
        $return = [];
        // czysty string części routingu
        $string = $this->getString();
        $placeholders = $this->getPlaceholdersNamesFromString();
        $storage = $this->getStorage();
        
        if (sizeof($placeholders) > 1 && !$forceSimpleMatching) {
            $storageKey = $this->getStorageKeyName();
            
            if ($storage->hasItem($storageKey)) {
                // pobranie wartości z cache
                $routePartVariants = unserialize($storage->getItem($storageKey));
            } else {
                // wszystkie możliwe warianty route stringa po podstawieniu odpowiednich wartości
                $routePartVariants = [$string => []];

                // pobierz listę wszystkich możliwych wariantów dla route stringa
                $this->getAllRoutePartVariants($routePartVariants);

                $storage->addItem($storageKey, serialize($routePartVariants));
            }
            
            foreach ($routePartVariants as $routeString => $routeStringData) {
                if ($stringToTest === $routeString) {
                    // odnaleziono poszukiwane wartości route stringa
                    $return = $routeStringData['values'];
                    break;
                }
            }
        } else {
            // w przypadku gdy w stringu występuje tylko 1 placeholder nie ma potrzeby na generowanie wszystkich możliwych wariantów
            // lub gdy wymuszono proste matchowanie wartości
            $return = $this->getPlaceholderValueFromString($stringToTest);
        }
        
        return $return;
    }
    
    /**
     * Zwróć string, który może być wykorzystany jako route part, wyłuskując przekazane wartości placeholderów o ile je przekazano i zapisując je w $values
     * @param string $string
     * @return string
     */
    protected function normalizeStringAndAssignValues($string)
    {
        $return = $string;
        $placeholders = $this->getPlaceholdersFromString($string);
        
        foreach ($placeholders as $placeholder) {
            $placeholderName = str_replace(['{', '}'], '', $placeholder);
            
            
            if (strpos($placeholderName, '=') !== false) {
                $tmp = explode('=', $placeholderName);
                $rawPlaceholderName = '{' . $tmp[0] . '}';
                $value = $tmp[1];
                
                $return = str_replace($placeholder, $rawPlaceholderName, $return);
                $this->setValue($rawPlaceholderName, $value);
            }
        }
        
        return $return;
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
     * Pobierz tablicę wszystkich możliwych wartości dla route stringa
     * @param array $variants
     * @return array Tablica wszystkich możliwych wartości route stringa, gdzie klucz to routeString, a wartości to kolejna tablica zawierająca values
     */
    protected function getAllRoutePartVariants(&$variants)
    {
        $hasPlaceholders = false;
        
        foreach (array_keys($variants) as $string) {
            $placeholders = $this->getPlaceholdersFromString($string);
            
            if (!empty($placeholders)) {
                $hasPlaceholders = true;
            }
        }
        
        if (!$hasPlaceholders) {
            return $variants;
        }
        
        $routes = Routes::getInstance();
        
        // pobranie pierwszego nieuzupełnionego placeholdera i podstawienie jego wartości dla wszystkich kluczy tablicy wariantów
        // oraz ich zduplikowanie jeśli jest to lista wartości
        foreach (array_keys($variants) as $variant) {
            // pobranie placeholderów z tego wariantu i wykorzystanie pierwszego nieuzupełnionego
            $variantPlaceholders = $this->getPlaceholdersFromString($variant);
            
            $placeholder = $routes->getPlaceholder(str_replace(['{', '}'], '', $variantPlaceholders[0]));
            
            $currentVariantValues = array_key_exists('values', $variants[$variant]) ? $variants[$variant]['values'] : [];
            // odnalezione wartości
            $variantValues = [];
            
            foreach ($placeholder->getValues() as $value) {
                $variantValues[str_replace('{' . $placeholder->getName() . '}', $value->getValue(), $variant)] = [
                    'values' => array_merge($currentVariantValues, [
                        '{' . $placeholder->getName() . '}' => $value,
                    ]),
                ];
            }
            
            // zastąpienie obecnego klucza odnalezionymi wartościami
            unset($variants[$variant]);
            $variants = array_merge($variants, $variantValues);
        }
        
        $this->getAllRoutePartVariants($variants);
    }
    
    /**
     * Pobierz adapter cache
     * @return \Laminas\Cache\Storage\Adapter\AbstractAdapter
     */
    protected function getStorage()
    {
        $dynamicRoute = \Base\Route\DynamicRoute::getInstance();
        /* @todo Do ogarnięcia w inny sposób */
        $serviceManager = $dynamicRoute->getServiceManager();
        
        if ($serviceManager instanceof \Laminas\ServiceManager\ServiceManager) {
            $storageFactory = $serviceManager->get(\Laminas\Cache\Service\StorageAdapterFactoryInterface::class);
            /* @var $storageFactory \Laminas\Cache\Service\StorageAdapterFactory */

            $config = $serviceManager->get('Config')['cache'];

            $cache = $storageFactory->createFromArrayConfiguration($config);
        }
        
        return $cache;
    }
    
    /**
     * Pobierz wartość placeholdera lub placeholderów. 
     * Metoda sprawdza się w przypadku, gdzie istnieje tylko 1 znacznik w treści RoutePart.
     * Przy większej liczbie parametrów może zwracać złą liczbę, jak i wartości parametrów.
     * @param string $testedString
     * @return \Base\Route\Dynamic\PlaceholderValue[]
     */
    protected function getPlaceholderValueFromString($testedString)
    {
        $placeholders = $this->getPlaceholdersNamesFromString();
        
        if (sizeof($placeholders) > 1) {
            //throw new \Exception("Ta metoda nie obsługuje wyszukiwania wartości znaczników dla stringów posiadających więcej jak jeden znacznik");
        }
        
        $return = [];
        $routes = Routes::getInstance();
        $routePartString = $this->getString();

        // wartości dla placeholderów
        $matchedValues = [];

        // expression zamienia wszystkie znaczniki na znaczniki wyszukiwania
        $pattern = '#^' . preg_replace("#\{[^\}]+\}#", '([a-zA-Z0-9\-\_]+)', str_replace(['-', '_'], ['\-', '\_'], $routePartString)) . '$#';

        preg_match($pattern, $testedString, $matchedValues);

        if (sizeof($placeholders) !== sizeof(array_unique($matchedValues))) {
            //throw new \Exception("Coś poszło nie tak... Odnaleziono więcej wartości placeholderów niż placeholderów");
            //return [];
        }
        
        // sprawdzenie czy znalezione placeholdery mają zgodne wartości (słownikowe) z tymi odnalezionymi 
        foreach ($placeholders as $key => $placeholderName) {
            $placeholderObject = $routes->getPlaceholderByRawName($placeholderName);

            $return[$placeholderName] = null;

            if ($placeholderObject instanceof Placeholder) {
                foreach ($matchedValues as $matchedValue) {
                    $value = $placeholderObject->getValueByValue($matchedValue);
                    
                    if (empty($value)) {
                        continue;
                    }
                    
                    $return[$placeholderName] = $value;
                    // odnaleziono wartość, przerwanie dalszego wyszukiwania
                    break;
                }
            }
        }
        
        return $return;
    }
    
    protected function getStorageKeyName()
    {
        $prefix = $this->getStorageKeyPrefix();
        $string = $this->getString();
        
        return $prefix . md5($string);
    }
}
