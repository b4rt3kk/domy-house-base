<?php
namespace Base\Route\Dynamic;

class Placeholder
{
    const DB_GET_VALUE_BY_VALUE = 1;
    const DB_GET_VALUE_BY_PARAM = 2;
    const DB_GET_VALUES = 3;
    
    protected static $callableEvents = [
        self::DB_GET_VALUE_BY_VALUE,
        self::DB_GET_VALUE_BY_PARAM,
        self::DB_GET_VALUES,
    ];
    
    protected $name;
    
    protected $rawName;
    
    protected $values;
    
    protected $assembledValue;
    
    protected $modelName;
    
    protected $valuesPaginationLimit = 100;
    
    protected $callables = [];
    
    protected $storageKeyPrefix = 'placeholder_';
    
    protected $modelColumnName = 'url_string';
    
    protected $parentPlaceholder;
    
    /**
     * @var \Laminas\ServiceManager\ServiceManager
     */
    protected $serviceManager;
    
    public function getModelName()
    {
        return $this->modelName;
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

    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
    }

    public function setServiceManager($serviceManager)
    {
        if (!$this->getServiceManager() instanceof \Laminas\ServiceManager\ServiceManager) {
            \Base\ServiceManager::setInstance($serviceManager);
        }
    }
    
    public function getCallables()
    {
        return $this->callables;
    }

    public function setCallables(array $callables)
    {
        foreach ($callables as $event => $callable) {
            $this->addCallable($event, $callable);
        }
    }

    public function addCallable($event, $callable)
    {
        if (!is_callable($callable)) {
            throw new \Exception('Event has to be callable');
        }
        
        if (!in_array($event, self::$callableEvents)) {
            throw new \Exception('There is no callable event by given type');
        }
        
        $this->callables[$event] = $callable;
    }
    
    public function getCallable($event)
    {
        $callables = $this->getCallables();
        
        if (!in_array($event, self::$callableEvents)) {
            throw new \Exception('There is no callable event by given type');
        }
        
        return array_key_exists($event, $callables) ? $callables[$event] : null;
    }
    
    public function getValuesPaginationLimit()
    {
        return $this->valuesPaginationLimit;
    }

    public function setValuesPaginationLimit(int $valuesPaginationLimit)
    {
        $this->valuesPaginationLimit = $valuesPaginationLimit;
    }
    
    public function getStorageKeyPrefix()
    {
        return $this->storageKeyPrefix;
    }

    public function setStorageKeyPrefix($storageKeyPrefix)
    {
        $this->storageKeyPrefix = $storageKeyPrefix;
    }
    
    public function getModelColumnName()
    {
        return $this->modelColumnName;
    }

    public function setModelColumnName($modelColumnName)
    {
        $this->modelColumnName = $modelColumnName;
    }
    
    public function getParentPlaceholder()
    {
        return $this->parentPlaceholder;
    }

    public function setParentPlaceholder($parentPlaceholder)
    {
        $this->parentPlaceholder = $parentPlaceholder;
    }
    
    /**
     * Nazwa placeholdera
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Pobierz wszystkie możliwe wartości dla tego placeholdera
     * @return \Base\Route\Dynamic\PlaceholderValue[]
     */
    public function getValues()
    {
        $values = $this->values;
        
        return $values;
    }
    
    /**
     * Sprawdź czy placeholder ma przypisaną listę możliwych wartości, które może przyjąć
     * @return boolean
     */
    public function hasValues()
    {
        return !empty($this->values);
    }
    
    /**
     * 
     * @param string $stringToTest
     * @param int $offset
     * @return \Base\Route\Dynamic\PlaceholderValue[]
     * @throws \Exception
     */
    public function getValuesWithOffset($stringToTest, $offset = 0, $options = [])
    {
        $options = array_merge([
            'force_db' => false,
            'min_similarity' => 0,
        ], $options);
        
        if ($offset < 0) {
            throw new \Exception("Offset nie może być mniejszy od zera");
        }
        
        $values = $this->getValues();
        
        if (empty($values) || $options['force_db']) {
            $values = $this->getValuesFromDb($stringToTest, $offset, $options);
        } else if (is_array($values)) {
            $values = array_slice($values, $offset);
        }
        
        return $values;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function setValues($values): void
    {
        $this->values = $values;
    }
    
    /**
     * Czysta nazwa placeholdera
     * @return string
     */
    public function getRawName()
    {
        return $this->rawName;
    }

    public function setRawName($rawName): void
    {
        $this->rawName = $rawName;
    }
    
    public function addValue(PlaceholderValue $value)
    {
        $value->setPlaceholderName($this->getName());
        
        $this->values[] = $value;
    }
    
    /**
     * Pobierz obiekt wartości dla wskazanej wartości
     * @param string $value
     * @return \Base\Route\Dynamic\PlaceholderValue
     */
    public function getValueByValue($value, $data = [])
    {
        $return = null;
        $values = $this->getValues();
        
        if (empty($values)) {
            $return = $this->callEvent(self::DB_GET_VALUE_BY_VALUE, array_merge($data, ['value' => $value]));
        } else {
            foreach ($values as $rowValue) {
                if ($rowValue->getValue() === $value) {
                    $return = $rowValue;

                    break;
                }
            }
        }
        
        return $return;
    }
    
    public function hasValue($value)
    {
        $rowValue = $this->getValueByValue($value);
        
        return !empty($rowValue);
    }
    
    /**
     * Pobierz \Base\Route\Dynamic\PlaceholderValue na podstawie wartości parametru
     * @param string $paramName
     * @param mixed $paramValue
     * @return \Base\Route\Dynamic\PlaceholderValue
     */
    public function getValueByParam($paramName, $paramValue)
    {
        $return = null;
        $values = $this->getValues();
        
        if (empty($values)) {
            $return = $this->callEvent(self::DB_GET_VALUE_BY_PARAM, ['param_name' => $paramName, 'param_value' => $paramValue]);
        } else {
            foreach ($values as $value) {
                if ($value->hasParamWithValue($paramName, $paramValue)) {
                    $return = $value;

                    break;
                }
            }
        }
        
        
        return $return;
    }
    
    public function callEvent($event, $params = [])
    {
        $callable = $this->getCallable($event);
        
        if (!empty($callable)) {
            return call_user_func($callable, $this, $params);
        }
    }
    
    public function getAssembledValue()
    {
        return $this->assembledValue;
    }

    public function setAssembledValue($assembledValue): void
    {
        $this->assembledValue = $assembledValue;
    }
    
    /**
     * @return \Base\Db\Table\AbstractModel
     * @throws \Exception
     */
    public function getModel()
    {
        $modelName = $this->getModelName();
        
        if (empty($modelName)) {
            throw new \Exception(sprintf("Nazwa modelu dla placeholdera %s nie może być pusta", $this->getName()));
        }
        
        $serviceManager = $this->getServiceManager();
        
        $model = $serviceManager->get($modelName);
        /* @var $model \Base\Db\Table\AbstractModel */
        
        if (!$model instanceof \Base\Db\Table\AbstractModel) {
            throw new \Exception(sprintf("Model musi dziedziczyć po %s", \Base\Db\Table\AbstractModel::class));
        }
        
        return $model;
    }
    
    /**
     * Pobierz listę wartości najbardziej zbliżonych do testowanego stringa z bazy danych
     * @param string $stringToTest
     * @param integer $offset
     * @return \Base\Route\Dynamic\PlaceholderValue[]
     * @throws \Exception
     */
    public function getValuesFromDb($stringToTest = null, $offset = 0, $options = [])
    {
        $options = array_merge([
            'min_similarity' => 0,
        ], $options);
        
        $return = [];
        $model = $this->getModel();
        $columnName = $this->getModelColumnName();
        $limit = $this->getValuesPaginationLimit();
        
        if (empty($columnName)) {
            throw new \Exception("Brak nazwy kolumny dla wartości placeholdera");
        }
        
        $where = new \Laminas\Db\Sql\Where();
        
        $where->expression('NOT ghost', []);
        $where->expression("similarity({$columnName}, ?) > ?", [$stringToTest, $options['min_similarity']]);
        
        $columns = [
            'id',
            'url_string',
            'name',
            'similarity' => new \Laminas\Db\Sql\Expression("similarity({$columnName}, ?)", [$stringToTest]),
        ];
        
        $select = $model->select()
                ->columns($columns)
                ->where($where)
                ->order(new \Laminas\Db\Sql\Expression("similarity({$columnName}, ?) DESC", [$stringToTest]));
                
        if (!empty($limit)) {
            $select->limit($limit);
        }
        
        if (!empty($offset)) {
            $select->offset($offset);
        }
        
        $data = $model->fetchAll($select);
        
        if ($data->count() > 0) {
            foreach ($data as $row) {
                $value = new PlaceholderValue();
                $value->setValue($row->url_string);
                $value->setName($row->name);
                $value->setParam('id', $row->id);
                $value->setParam('similarity', $row->similarity);
                $value->setParam('testedString', $stringToTest);
                
                $return[] = $value;
            }
        }
        
        return $return;
    }
    
    /**
     * Pobierz adapter cache
     * @return \Laminas\Cache\Storage\Adapter\AbstractAdapter|null
     */
    protected function getStorage()
    {
        $cache = null;
        
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
    
    protected function getStorageKeyName()
    {
        $prefix = $this->getStorageKeyPrefix();
        $string = spl_object_hash($this);
        
        return $prefix . md5($string);
    }
}
