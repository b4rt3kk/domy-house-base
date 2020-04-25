<?php
namespace Base\Services\Auth;

use Base\Db\Table\AbstractModel;
use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Result;
use Laminas\Crypt\Password\PasswordInterface;
use Laminas\ServiceManager\ServiceManager;

class AuthAdapter implements AdapterInterface
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
        'NOT ghost',
    ];
    
    /**
     * Parametry (kolumny), które zostaną wykonane na wyszukanym wierszu dla użytkownika.
     * Parametry muszą być w postaci tablicy tablic o kluczach condition: nazwa wiersza => oczekiwana wartość
     * W przypadku gdy wartość wynikowa jest różna od oczekiwanej wtedy wyrzucany jest błąd o treści z klucza tablicy message
     * @var array
     */
    protected $rowPostConditions = [
        [
            'condition' => ['is_locked' => false],
            'message' => 'User is locked',
        ],
    ];
    
    /**
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setServiceManager($serviceManager)
    {
        $this->serviceManager = $serviceManager;
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
    
    public function authenticate()
    {
        $login = $this->getLogin();
        $password = $this->getPassword();
        $passwordColumn = $this->getPasswordColumnName();
        $postConditions = $this->getRowPostConditions();
        
        $rowUser = $this->getUserByLoginRow($login);
        
        if (empty($rowUser)) {
            $result = new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null, ['Invalid credentials']);
            $this->callEvent(self::EVENT_IDENTITY_NOT_FOUND);
            
            return $result;
        }
        
        $this->setUserRow($rowUser);
        $this->callEvent(self::EVENT_IDENTITY_FOUND);
        
        foreach ($postConditions as $condition) {
            foreach ($condition['condition'] as $columnName => $columnValue) {
                if ($rowUser->{$columnName} !== $columnValue) {
                    $result = new Result(Result::FAILURE, null, [$condition['message']]);
                    $this->callEvent(self::EVENT_LOGIN_FAILED);

                    return $result;
                }
            }
        }
        
        $crypt = $this->getCrypt();
        
        if ($crypt->verify($password, $rowUser->{$passwordColumn})) {
            $result = new Result(Result::SUCCESS, $rowUser, ['Authenticated successfully']);
            $this->callEvent(self::EVENT_LOGIN_SUCCESS);
            
            return $result;
        }
        
        $result = new Result(Result::FAILURE_CREDENTIAL_INVALID, null, ['Invalid credentials']);
        $this->callEvent(self::EVENT_LOGIN_FAILED);
        
        return $result;
    }
    
    public function register()
    {
        $loginColumn = $this->getLoginColumnName();
        $passwordColumn = $this->getPasswordColumnName();
        $crypt = $this->getCrypt();
        
        $login = $this->getLogin();
        $passwordClean = $this->getPassword();
        
        if (empty($login)) {
            throw new \Exception('Login cannot be empty');
        }
        
        if (empty($passwordClean)) {
            throw new \Exception('Password cannot be empty');
        }
        
        $password = $crypt->create($passwordClean);
        
        $rowUser = $this->getUserByLoginRow($login);
        
        if (!empty($rowUser)) {
            throw new \Exception('User already exists');
        }
        
        $model = $this->getModel();
        $table = $model->getEntity();
        
        $data = array_merge($this->getAdditionalData(), [
            $loginColumn => $login,
            $passwordColumn => $password,
        ]);
        
        $table->exchangeArray($data);
        
        $id = $model->createRow($table);
        
        $row = $this->getUserByIdRow($id);
        
        $this->setUserRow($row);
        
        $this->callEvent(self::EVENT_REGISTER_SUCCESS);
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
}
