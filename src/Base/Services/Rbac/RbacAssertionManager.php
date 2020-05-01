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
    
    protected $delimiter = self::DEFAULT_DELIMITER;
    
    protected $creatorIdName = 'created_by';
    
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
            case self::MODE_OWN_GROUP:
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
