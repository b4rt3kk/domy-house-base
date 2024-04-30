<?php
namespace Base;

class Dictionary extends Logic\AbstractLogic
{
    const DEFAULT_ID_KEY = 'id';
    const DEFAULT_SEPARATOR = ' ';
    const DEFAULT_LANGUAGE_CODE = 'pl_pl';
    
    protected $modelName;
    
    protected $dictionaryName;
    
    protected $languageCode = 'pl_pl';
    
    protected $idKey = self::DEFAULT_ID_KEY;
    
    protected $nameFields = [];
    
    protected $where = [];
    
    protected $separator = self::DEFAULT_SEPARATOR;
    
    protected $namedDictionaryCallable;
    
    protected $useCache = true;
    
    public function init()
    {
        $this->reset();
    }
    
    public function reset()
    {
        $this->setModelName(null);
        $this->setDictionaryName(null);
        $this->setLanguageCode(self::DEFAULT_LANGUAGE_CODE);
        $this->setIdKey(self::DEFAULT_ID_KEY);
        $this->setNameFields([]);
        $this->setWhere([]);
        $this->setSeparator(self::DEFAULT_SEPARATOR);
    }
    
    /**
     * Callable do pobierania danych ze słownika podstawowego, 
     * czyli takiego, którego wpisy wyszukiwane są na podstawie nazwy, a nie modelu
     * @return function
     */
    public function getNamedDictionaryCallable()
    {
        return $this->namedDictionaryCallable;
    }

    public function setNamedDictionaryCallable($namedDictionaryCallable)
    {
        $this->namedDictionaryCallable = $namedDictionaryCallable;
    }
    
    public function getModelName()
    {
        return $this->modelName;
    }

    public function getDictionaryName()
    {
        return $this->dictionaryName;
    }

    public function getIdKey()
    {
        return $this->idKey;
    }

    public function getNameFields()
    {
        return $this->nameFields;
    }

    public function getSeparator()
    {
        return $this->separator;
    }

    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
    }

    public function setDictionaryName($dictionaryName)
    {
        $this->dictionaryName = $dictionaryName;
    }

    public function setIdKey($idKey)
    {
        $this->idKey = $idKey;
    }

    public function setNameFields($nameFields)
    {
        $this->nameFields = $nameFields;
    }

    public function setSeparator($separator)
    {
        $this->separator = $separator;
    }
    
    public function getWhere()
    {
        return $this->where;
    }

    public function setWhere($where)
    {
        $this->where = $where;
    }
    
    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    public function setLanguageCode($languageCode)
    {
        $this->languageCode = $languageCode;
    }
    
    public function getUseCache()
    {
        return $this->useCache;
    }

    public function setUseCache($useCache): void
    {
        $this->useCache = $useCache;
    }
    
    public function clearCache()
    {
        $storage = $this->getStorage();
        
        try {
            switch (get_class($storage)) {
                case \Laminas\Cache\Storage\Adapter\Filesystem::class:
                    $options = $storage->getOptions();
                    /* @var $options \Laminas\Cache\Storage\Adapter\FilesystemOptions */

                    $storage->flush();
                    break;
            }
        } catch (\Exception $e) {
            // tymczasowo pominięcie błędów czyszczenia cache
        }
    }
        
    public function getDictionary()
    {
        $name = $this->getDictionaryName();
        $cacheKey = $this->getDictionaryCacheKey();
        $return = [];
        
        $storage = $this->getStorage();
        
        if ($this->getUseCache() && $storage->hasItem($cacheKey)) {
            $return = unserialize($storage->getItem($cacheKey));
        } else if (!empty($name)) {
            // jeśli określono parametr name to pobieranie wartości słownikowych na podstawie wstrzykniętego callable
            $return = $this->getNamedDictionary();
        } else {
            $model = $this->getModel();
            $idKey = $this->getIdKey();
            $nameFields = $this->getNameFields();
            $separator = $this->getSeparator();

            $select = $this->getSelect();

            $data = $model->fetchAll($select);

            foreach ($data as $row) {
                $name = null;

                foreach ($nameFields as $nameField) {
                    $name .= $row->{$nameField} . $separator;
                }

                $return[$row->{$idKey}] = trim($name, $separator);
            }
        }
        
        if ($this->getUseCache() && !$storage->hasItem($cacheKey)) {
            $storage->addItem($cacheKey, serialize($return));
        }
        
        return $return;
    }
    
    /**
     * @return \Base\Db\Table\AbstractModel
     */
    protected function getModel()
    {
        $modelName = $this->getModelName();
        
        if (empty($modelName)) {
            throw new \Exception(
                    "Nazwa modelu słownikowego nie może być pusta. Być może nie podałeś wartości dla dictionaryName? " .
                    "Słownik musi zawierać nazwę modelu [modelName] lub nazwę słownika [name]."
                );
        }
        
        $model = $this->getServiceManager()->get($this->getModelName());
        
        if (!$model instanceof \Base\Db\Table\AbstractModel) {
            throw new \Exception(sprintf("Model słownika %s musi dziedziczyć po %s", $modelName, \Base\Db\Table\AbstractModel::class));
        }
        
        return $model;
    }
    
    /**
     * Pobierz select dla tego słownika
     * @return \Laminas\Db\Sql\Select
     */
    protected function getSelect()
    {
        $model = $this->getModel();
        $where = $this->getWhere();
        
        $select = $model->select();

        if (!empty($where)) {
            $select->where($where);
        }
        
        return $select;
    }
    
    /**
     * Pobierz customowy słownik dla określonego name
     * @return array
     */
    protected function getNamedDictionary()
    {
        $return = [];
        $callable = $this->getNamedDictionaryCallable();
        
        if (empty($callable)) {
            throw new \Exception("Nie określono callable dla customowego słownika z określonym name. W przypadku podania name w fabryce słownika należy dodać odpowiednie callable.");
        }
        
        if (is_array($callable)) {
            $return = call_user_func($callable, $this);
        } else {
            $return = $callable($this);
        }
        
        return $return;
    }
    
    /**
     * Pobierz klucz cache dla tego słownika.
     * Klucz generowany jest na podstawie parametrów konfiguracyjnych lub dla named dictionary na podstawie jego nazwy
     * @return string
     */
    protected function getDictionaryCacheKey()
    {
        $cacheKey = $this->getDictionaryName();
        
        if (empty($cacheKey)) {
            $select = $this->getSelect();
            // w przypadku gdy jest to słownik oparty o model 
            // klucz cache generowany jest na podstawie select stringa
            $cacheKey = $select->getSqlString();
        }
        
        return md5($cacheKey);
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
}
