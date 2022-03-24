<?php
namespace Base\Services\Rbac;

use Base\Logic\AbstractLogic;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Permissions\Rbac\Rbac;

class RbacManager extends AbstractLogic
{
    const DEFAULT_ROLE_CODE = 'guest';
    
    /**
     * @var Rbac
     */
    protected $rbac;
    
    /**
     * @var StorageInterface
     */
    protected $cache;
    
    protected $assertionManagers = [];
    
    protected $isInitialized = false;
    
    protected $rolesManager;
    
    public function getRbac(): Rbac
    {
        return $this->rbac;
    }

    public function getCache(): StorageInterface
    {
        return $this->cache;
    }

    public function getAssertionManagers()
    {
        return $this->assertionManagers;
    }

    public function setRbac(Rbac $rbac)
    {
        $this->rbac = $rbac;
    }

    public function setCache(StorageInterface $cache)
    {
        $this->cache = $cache;
    }
    
    public function clearAssertionManagers()
    {
        $this->assertionManagers = [];
    }

    public function setAssertionManagers($assertionManagers)
    {
        $this->clearAssertionManagers();
        
        foreach ($assertionManagers as $assertionManager) {
            $this->addAssertionManager($assertionManager);
        }
    }
    
    public function addAssertionManager(AbstractRbacAssertionManager $assertionManager)
    {
        $this->assertionManagers[] = $assertionManager;
    }
    
    public function getIsInitialized()
    {
        return $this->isInitialized;
    }

    public function setIsInitialized($isInitialized)
    {
        $this->isInitialized = $isInitialized;
    }
    
    /**
     * @return \Base\Services\Rbac\RbacRolesManager
     */
    public function getRolesManager()
    {
        return $this->rolesManager;
    }

    public function setRolesManager($rolesManager)
    {
        $this->rolesManager = $rolesManager;
    }
    
    public function init()
    {
        $rbac = new Rbac();
        $rbac->setCreateMissingRoles(true);
        
        $rolesManager = $this->getRolesManager();
        
        if (!$rolesManager instanceof RbacRolesManager) {
            throw new \Exception(sprintf("Role manager must inherits from %s class", RbacRolesManager::class));
        }
        
        $dataRoles = $rolesManager->getRolesData();
        $nameColumn = $rolesManager->getRoleNameColumn();
        $permissionNameColumn = $rolesManager->getPermissionNameColumn();
        $primaryKey = $rolesManager->getPrimaryKey();
        
        $roles = [];
        
        // dodanie wszystkich dostępnych ról
        foreach ($dataRoles as $rowRole) {
            $roles[] = $rowRole;
            $rbac->addRole($rowRole->{$nameColumn});
        }
        
        // dodanie uprawnień dla ról
        foreach ($roles as $rowRole) {
            $roleName = $rowRole->{$nameColumn};
            $idRole = $rowRole->{$primaryKey};
            $role = $rbac->getRole($roleName);
            
            // nazwy ról dziedziczycących po tej roli
            $dataChildrenNames = $rolesManager->getRoleChildrensNames($idRole);
            
            // nazwy ról rodziców
            $dataParentsNames = $rolesManager->getRoleParentsNames($idRole);
            
            foreach ($dataParentsNames as $rowParentName) {
                $roleParent = $rbac->getRole($rowParentName);
                $role->addParent($roleParent);
            }
            
            $dataPermissions = $rolesManager->getRolePermissionsData($idRole);
            
            foreach ($dataPermissions as $rowPermission) {
                $rbac->getRole($roleName)->addPermission($rowPermission->{$permissionNameColumn});
            }
        }
        
        $this->setRbac($rbac);
        $this->setIsInitialized(true);
    }
    
    public function isGranted($user, $permission, $params = [])
    {
        if (!$this->getIsInitialized()) {
            $this->init();
        }
        
        $rbac = $this->getRbac();
        $isGranted = false;
        
        $rolesManager = $this->getRolesManager();
        $nameColumn = $rolesManager->getRoleNameColumn();
        $assertionManagers = $this->getAssertionManagers();
        $idUser = null;
        
        if (empty($user)) {
            $authenticationService = $this->getServiceManager()->get(\Laminas\Authentication\AuthenticationService::class);
            /* @var $authenticationService \Laminas\Authentication\AuthenticationService */
            $user = $authenticationService->getIdentity();
        }
        
        if (!empty($user)) {
            $idUser = $user->id;
        }
        
        $userRoles = $rolesManager->getUserRolesData($idUser);
        
        foreach ($userRoles as $rowRole) {
            if ($rbac->isGranted($rowRole->{$nameColumn}, $permission)) {
                if (empty($params)) {
                    $isGranted = true;
                    break;
                }
            
                foreach ($assertionManagers as $assertionManager) {
                    if ($assertionManager->assert($rbac, $permission, $params)) {
                        $isGranted = true;
                        break 2;
                    }
                }
            }
        }
        
        return $isGranted;
    }
}
