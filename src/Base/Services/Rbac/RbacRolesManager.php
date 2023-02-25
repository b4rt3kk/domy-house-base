<?php
namespace Base\Services\Rbac;

use Base\Logic\AbstractLogic;

abstract class RbacRolesManager extends AbstractLogic
{
    protected $roleNameColumn = 'code';
    
    protected $permissionNameColumn = 'name';
    
    protected $primaryKey = 'id';
    
    public function setRoleNameColumn($roleNameColumn)
    {
        $this->roleNameColumn = $roleNameColumn;
    }

    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
    }

    /**
     * Pobierz nazwę kolumny, w której przechowywana jest nazwa roli
     * @return string
     */
    public function getRoleNameColumn()
    {
        return $this->roleNameColumn;
    }
    
    /**
     * Pobierz nazwę klucza głównego dla roli
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }
    
    /**
     * Pobierz nazwę kolumny, w której przechowywana jest nazwa kolumny dla uprawnienia
     * @return string
     */
    public function getPermissionNameColumn()
    {
        return $this->permissionNameColumn;
    }

    public function setPermissionNameColumn($permissionNameColumn)
    {
        $this->permissionNameColumn = $permissionNameColumn;
    }
    
    /**
     * Pobierz listę uprawnień dla roli, które posiadają parametr * oznaczający dowolną wartość
     * @param integer $idRole
     * @return \Laminas\Db\ResultSet\ResultSet
     */
    public function getAnyPermissionsData($idRole)
    {
        $dataRolePermissions = $this->getRolePermissionsData($idRole);
        $permissionColumnName = $this->getPermissionNameColumn();
        
        $resultSet = new \Laminas\Db\ResultSet\ResultSet();
        $resultSet->setArrayObjectPrototype($dataRolePermissions->getArrayObjectPrototype());
        
        $foundPermissions = [];
        
        foreach ($dataRolePermissions as $rowRolePermission) {
            if (strpos($rowRolePermission->{$permissionColumnName}, RbacManager::ANY_PARAM) !== false) {
                $foundPermissions[] = clone $rowRolePermission;
            }
        }
        
        $resultSet->initialize($foundPermissions);
        
        return $resultSet;
    }
    
    /**
     * Sprawdź czy rola posiada uprawnienia zawierające \Base\Services\Rbac\RbacManager::ANY_PARAM w swojej treści
     * @param integer $idRole
     * @return boolean
     */
    public function hasAnyPermissions($idRole)
    {
        $data = $this->getAnyPermissionsData($idRole);
        
        return $data->count() > 0;
    }
    
    public function isAnyPermissionAllowed($idRole, $permission)
    {
        if (!$this->hasAnyPermissions($idRole)) {
            // rola nie posiada uprawnień zawierających \Base\Services\Rbac\RbacManager::ANY_PARAM
            return false;
        }
        
        $data = $this->getAnyPermissionsData($idRole);
        $permissionNameColumn = $this->getPermissionNameColumn();
        
        foreach ($data as $row) {
            $permissionName = preg_quote($row->{$permissionNameColumn});
            
            $pattern = '#' . str_replace('\\' . RbacManager::ANY_PARAM, '.*', $permissionName) . '#';
            
            if (preg_match($pattern, $permission) !== false) {
               return true;
            }
        }
        
        return false;
    }
        
    /**
     * Pobierz listę wszystkich dostępnych ról
     * @return \Laminas\Db\ResultSet\ResultSet
     */
    abstract public function getRolesData();
    
    /**
     * Pobierz listę rodziców dla roli o id podanym w parametrze
     * @param integer $idRole
     * @return array
     */
    abstract public function getRoleParentsNames($idRole);
    
    /**
     * Pobierz listę dzieci dla roli o id podanym w parametrze
     * @param integer $idRole
     * @return array
     */
    abstract public function getRoleChildrensNames($idRole);
    
    /**
     * Pobierz listę uprawnień przypisanych do roli o id podanym w parametrze
     * @param integer $idRole
     * @return \Laminas\Db\ResultSet\ResultSet
     */
    abstract public function getRolePermissionsData($idRole);
    
    /**
     * Pobierz listę ról podpiętych do wybranego użytkownika
     * @param integer $idUser
     * @return \Laminas\Db\ResultSet\ResultSet
     */
    abstract public function getUserRolesData($idUser);
    
    /**
     * Pobierz wiersz dla roli na podstawie jego nazwy
     * @param string $name
     */
    abstract public function getRoleByNameRow($name);
}
