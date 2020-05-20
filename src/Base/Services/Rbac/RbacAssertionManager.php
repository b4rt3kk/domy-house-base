<?php
namespace Base\Services\Rbac;

use Base\Logic\AbstractLogic;
use Laminas\Permissions\Rbac\Rbac;

class RbacAssertionManager extends AbstractLogic
{
    const DEFAULT_DELIMITER = '.';
    
    const MODE_ALL = 'all';
    const MODE_OWN = 'own';
    const MODE_OWN_GROUP = 'own_group';
    
    protected $accessModes = [
        self::MODE_ALL,
        self::MODE_OWN,
        self::MODE_OWN_GROUP,
    ];
    
    protected $delimiter = self::DEFAULT_DELIMITER;
    
    protected $creatorIdName = 'created_by';
    
    protected $callables = [];
    
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }
    
    public function getCreatorIdName()
    {
        return $this->creatorIdName;
    }
    
    /**
     * Nazwa klucza z przekazanych parametrów do funkcji assert(), która zawiera id osoby tworzącej/posiadającej dany zasób/rekord
     * @param string $creatorIdName
     */
    public function setCreatorIdName($creatorIdName)
    {
        $this->creatorIdName = $creatorIdName;
    }
    
    public function addAccessMode($mode)
    {
        $this->accessModes[] = $mode;
    }
    
    /**
     * Pobierz listę dostępnych trybów dostępu do danego zasobu
     * @return array
     */
    public function getAccessModes()
    {
        return $this->accessModes;
    }
    
    public function getCallables()
    {
        return $this->callables;
    }

    public function setCallables($callables)
    {
        foreach ($callables as $mode => $callable) {
            $this->addCallable($mode, $callable);
        }
    }
    
    /**
     * Dodaj callable, które sprawdza czy użytkownik ma dostęp do danego zasobu dla danego truby dostępu (mode)
     * @param string $mode
     * @param callable $callable
     * @throws \Exception
     */
    public function addCallable($mode, $callable)
    {
        $modes = $this->getAccessModes();
        
        if (!in_array($mode, $modes)) {
            throw new \Exception(sprintf("There is no access mode by name %s", $mode));
        }
        
        if (!is_callable($callable)) {
            throw new \Exception('You should provide callable method');
        }
        
        $this->callables[$mode] = $callable;
    }
    
    /**
     * @param string $mode
     * @return \Base\Services\Rbac\Assertion\AssertionCallableInterface|null
     */
    public function getCallable($mode)
    {
        $callables = $this->getCallables();
        
        return array_key_exists($mode, $callables) ? $callables[$mode] : null;
    }

        
    public function assert(Rbac $rbac, $permission, $params = [])
    {
        $permissionParams = $this->getPermissionParams($permission);
        $authenticationService = $this->getServiceManager()->get(\Laminas\Authentication\AuthenticationService::class);
        /* @var $authenticationService \Laminas\Authentication\AuthenticationService */
        $identity = $authenticationService->getIdentity();
        
        $isGranted = false;
        
        switch ($permissionParams['mode']) {
            case self::MODE_ALL:
                $isGranted = true;
                break;
            case self::MODE_OWN:
                if (!empty($identity)) {
                    if ($params[$this->getCreatorIdName()] == $identity->id) {
                        $isGranted = true;
                    }
                }
                break;
            default:
                $callable = $this->getCallable($permissionParams['mode']);
                
                if (is_callable($callable)) {
                    $isGranted = $callable($this, $rbac, $permission, $params);
                }
                break;
        }
        
        return $isGranted;
    }
    
    protected function getPermissionParams($permission)
    {
        $chunks = explode($this->getDelimiter(), $permission);
        
        $return = [
            'route_name' => isset($chunks[0]) ? $chunks[0] : null,
            'action_name' => isset($chunks[1]) ? $chunks[1] : null,
            'mode' => isset($chunks[2]) ? $chunks[2] : null,
        ];
        
        return $return;
    }
}
