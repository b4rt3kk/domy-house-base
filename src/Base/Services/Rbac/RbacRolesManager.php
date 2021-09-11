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
