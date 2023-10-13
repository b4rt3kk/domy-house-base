<?php
namespace Base\Route\Dynamic;

class Placeholder
{
    const DB_GET_VALUE_BY_VALUE = 1;
    const DB_GET_VALUE_BY_PARAM = 2;
    
    protected static $callableEvents = [
        self::DB_GET_VALUE_BY_VALUE,
        self::DB_GET_VALUE_BY_PARAM,
    ];
    
    protected $name;
    
    protected $rawName;
    
    protected $values;
    
    protected $assembledValue;
    
    protected $modelName;
    
    protected $callables = [];
    
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
        return $this->values;
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
            throw new \Exception("Nazwa modelu nie może być pusta");
        }
        
        $serviceManager = $this->getServiceManager();
        
        $model = $serviceManager->get($modelName);
        /* @var $model \Base\Db\Table\AbstractModel */
        
        if (!$model instanceof \Base\Db\Table\AbstractModel) {
            throw new \Exception(sprintf("Model musi dziedziczyć po %s", \Base\Db\Table\AbstractModel::class));
        }
        
        return $model;
    }
}
