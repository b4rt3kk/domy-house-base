<?php

namespace Base\Services\Auth;

use Base\Db\Table\AbstractModel;
use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Crypt\Password\PasswordInterface;
use Laminas\ServiceManager\ServiceManager;

abstract class AbstractAuthAdapter implements AdapterInterface
{
    const EVENT_LOGIN_SUCCESS = 1;
    const EVENT_LOGIN_FAILED = 2;
    const EVENT_REGISTER_SUCCESS = 3;
    const EVENT_USER_LOCKED = 4;
    const EVENT_LOGOUT = 5;
    const EVENT_IDENTITY_FOUND = 6;
    const EVENT_IDENTITY_NOT_FOUND = 7;

    protected static $callableEvents = [
        self::EVENT_LOGIN_SUCCESS,
        self::EVENT_LOGIN_FAILED,
        self::EVENT_REGISTER_SUCCESS,
        self::EVENT_USER_LOCKED,
        self::EVENT_LOGOUT,
        self::EVENT_IDENTITY_FOUND,
        self::EVENT_IDENTITY_NOT_FOUND,
    ];
    
    protected $serviceManager;
    
    protected $login;
    
    protected $password;
    
    protected $modelName;
    
    protected $loginColumnName = 'login';
    
    protected $passwordColumnName = 'password';
    
    protected $isVirtualColumnName = 'is_virtual';
    
    protected $cryptClass = \Laminas\Crypt\Password\Bcrypt::class;
    
    protected $callables = [];
    
    protected $userRow;
    
    protected $failedLoginsLimit = 5;
    
    protected $lockTime = 600;
    
    protected $additionalData = [];

    /**
     * Parametry WHERE dla zapytania, które zostaną wykonane na wierszu użytkownika.
     * Jeśli warunki nie zostaną spełnione, to zostanie zwróony pusty wiersz
     * @var array
     */
    protected $rowPreConditions = [
            /*
              'NOT ghost',
             * 
             */
    ];

    /**
     * Parametry (kolumny), które zostaną wykonane na wyszukanym wierszu dla użytkownika.
     * Parametry muszą być w postaci tablicy tablic o kluczach condition: nazwa wiersza => oczekiwana wartość
     * W przypadku gdy wartość wynikowa jest różna od oczekiwanej wtedy wyrzucany jest błąd o treści z klucza tablicy message
     * @var array
     */
    protected $rowPostConditions = [
            /*
              [
              'condition' => ['is_locked' => false],
              'message' => 'User is locked',
              ],
             * 
             */
    ];
    
    protected $options = [];
    
    protected $storageContainerName;

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
    
    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        if (is_array($options) && !empty($options)) {
            foreach ($options as $name => $value) {
                $this->setOption($name, $value);
            }
        }
    }
    
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        
        $methodName = 'set';
        $chunks = explode('_', $name);
        
        foreach ($chunks as $chunk) {
            $methodName .= ucfirst($chunk);
        }
        
        if (method_exists($this, $methodName)) {
            $this->{$methodName}($value);
        }
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setLogin($login)
    {
        $this->login = $login;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getModelName()
    {
        return $this->modelName;
    }

    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
    }

    public function getLoginColumnName()
    {
        return $this->loginColumnName;
    }

    public function getPasswordColumnName()
    {
        return $this->passwordColumnName;
    }

    public function setLoginColumnName($loginColumnName)
    {
        $this->loginColumnName = $loginColumnName;
    }

    public function setPasswordColumnName($passwordColumnName)
    {
        $this->passwordColumnName = $passwordColumnName;
    }

    public function getIsVirtualColumnName()
    {
        return $this->isVirtualColumnName;
    }

    public function setIsVirtualColumnName($isVirtualColumnName)
    {
        $this->isVirtualColumnName = $isVirtualColumnName;
    }

    public function getRowPreConditions()
    {
        return $this->rowPreConditions;
    }

    public function getRowPostConditions()
    {
        return $this->rowPostConditions;
    }

    public function setRowPreConditions($rowPreConditions)
    {
        $this->rowPreConditions = $rowPreConditions;
    }

    public function setRowPostConditions($rowPostConditions)
    {
        $this->rowPostConditions = $rowPostConditions;
    }

    public function getCryptClass()
    {
        return $this->cryptClass;
    }

    public function setCryptClass($cryptClass)
    {
        $this->cryptClass = $cryptClass;
    }

    public function getFailedLoginsLimit()
    {
        return $this->failedLoginsLimit;
    }

    public function setFailedLoginsLimit($failedLoginsLimit)
    {
        $this->failedLoginsLimit = $failedLoginsLimit;
    }
    
    public function getStorageContainerName()
    {
        return $this->storageContainerName;
    }

    public function setStorageContainerName($storageContainerName)
    {
        $this->storageContainerName = $storageContainerName;
    }

    /**
     * @return \Base\Db\Table\AbstractEntity
     */
    public function getUserRow()
    {
        return $this->userRow;
    }

    public function setUserRow($userRow)
    {
        $model = $this->getModel();
        $primaryKey = $model->getPrimaryKey();

        $row = $this->getUserByIdRow($userRow->{$primaryKey});

        $this->userRow = $row;
    }

    public function getLockTime()
    {
        return $this->lockTime;
    }

    public function setLockTime($lockTime)
    {
        $this->lockTime = $lockTime;
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
     * @return PasswordInterface
     * @throws \Exception
     */
    public function getCrypt()
    {
        $cryptClass = $this->getCryptClass();

        if (!class_exists($cryptClass)) {
            throw new \Exception(sprintf('Class %s doesnt exists', $cryptClass));
        }

        $crypt = new $cryptClass();

        if (!$crypt instanceof PasswordInterface) {
            throw new \Exception(sprintf('Crypt method has to implement %s', PasswordInterface::class));
        }

        return $crypt;
    }

    public function getAdditionalData()
    {
        return $this->additionalData;
    }

    public function setAdditionalData(array $additionalData)
    {
        $this->additionalData = $additionalData;
    }
    
    /**
     * @return AbstractModel
     */
    public function getModel()
    {
        $serviceManager = $this->getServiceManager();
        $modelName = $this->getModelName();

        $model = $serviceManager->get($modelName);

        return $model;
    }
    
    public function callEvent($event)
    {
        $callable = $this->getCallable($event);
        
        if (!empty($callable)) {
            call_user_func($callable, $this);
        }
    }
    
    public function updateUserRow($data)
    {
        $model = $this->getModel();
        $row = $this->getUserRow();
        $primaryKey = $model->getPrimaryKey();
        
        $id = $row->{$primaryKey};
        
        $model->update($data, [
            $primaryKey => $id,
        ]);
        
        $this->setUserRow($this->getUserByIdRow($id));
    }
    
    public function getPropertiesValues()
    {
        $return = [];
        $reflectionClass = new \ReflectionClass($this);
        $properties = $reflectionClass->getProperties();
        
        if (!empty($properties)) {
            foreach ($properties as $property) {
                /* @var $property \ReflectionProperty */
                $name = $property->getName();
                $methodName = 'get' . ucfirst($name);
                
                if (method_exists($this,$methodName)) {
                    $value = $this->{$methodName}();
                    
                    $return[$name] = $value;
                }
            }
        }
        
        return $return;
    }
    
    public function setPropertiesValues(array $data)
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $methodName = 'set' . ucfirst($key);
                
                if (method_exists($this, $methodName)) {
                    $this->{$methodName}($value);
                }
            }
        }
    }
    
    /**
     * Przeprowadź logowanie użytkownika
     */
    abstract public function authenticate();
    
    /**
     * Przeprowadź rejestrację użytkownika
     */
    abstract public function register();
    
    protected function getUserByLoginRow($login)
    {
        $model = $this->getModel();
        
        if (!$model instanceof AbstractModel) {
            throw new \Exception(sprintf('There is no model name provided or class doesnt implements %s', AbstractModel::class));
        }
        
        $columnName = $this->getLoginColumnName();
        $where = $this->getRowPreConditions();
        
        $select = $model->select()
            ->where([$columnName => $login])
            ->where($where);
        
        $row = $model->fetchRow($select);
        
        return $row;
    }
    
    protected function getUserByIdRow($id)
    {
        $model = $this->getModel();
        
        if (!$model instanceof AbstractModel) {
            throw new \Exception(sprintf('There is no model name provided or class doesnt implements %s', AbstractModel::class));
        }
        
        $primaryKey = $model->getPrimaryKey();
        
        $select = $model->select()
            ->where([$primaryKey => $id]);
        
        $row = $model->fetchRow($select);
        
        return $row;
    }
    
    /**
     * Pobierz obiekt sesji przechowujący dane adaptera
     * @return \Laminas\Session\Container
     */
    protected function getStorageContainer()
    {
        $containerName = $this->getStorageContainerName();

        $container = new \Laminas\Session\Container($containerName);

        return $container;
    }
}
