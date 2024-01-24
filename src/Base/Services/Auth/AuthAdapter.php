<?php
namespace Base\Services\Auth;

use Base\Db\Table\AbstractModel;
use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Result;
use Laminas\Crypt\Password\PasswordInterface;
use Laminas\ServiceManager\ServiceManager;

class AuthAdapter extends AbstractAuthAdapter
{
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
        
        if ($rowUser->{$this->getIsVirtualColumnName()}) {
            // użytkownik wirtualny/systemowy
            // taki użytkownik nie powinien posiadać żadnych ról, nie ma hasła, 
            // więc nie powinien mieć żadnych uprawnień
            $result = new Result(Result::SUCCESS, $rowUser, ['Authenticated successfully']);
            $this->callEvent(self::EVENT_LOGIN_SUCCESS);
            
            return $result;
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
        
        $adapter = $this->getServiceManager()->get('main');
        /* @var $adapter \Laminas\Db\Adapter\Adapter */

        $adapter->getDriver()->getConnection()->beginTransaction();
        
        try {
            $id = $model->createRow($table);

            $row = $this->getUserByIdRow($id);

            $this->setUserRow($row);

            $this->callEvent(self::EVENT_REGISTER_SUCCESS);
            
            $adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $adapter->getDriver()->getConnection()->rollback();
            throw $e;
        }
    }
}
