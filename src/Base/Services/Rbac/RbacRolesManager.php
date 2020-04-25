<?php
namespace Base\Services\Rbac;

use Base\Logic\AbstractLogic;

class RbacRolesManager extends AbstractLogic
{
    protected $modelRolesName;
    
    protected $modelHierarchyName;
    
    protected $modelPermissionsName;
    
    protected $modelRolePermissionsName;
    
    protected $modelUserRolesName;
    
    protected $nameColumn = 'name';
    
    protected $conditions = [
        'NOT ghost',
    ];
    
    public function getModelRolesName()
    {
        return $this->modelRolesName;
    }

    public function getModelHierarchyName()
    {
        return $this->modelHierarchyName;
    }

    public function setModelRolesName($modelRolesName)
    {
        $this->modelRolesName = $modelRolesName;
    }

    public function setModelHierarchyName($modelHierarchyName)
    {
        $this->modelHierarchyName = $modelHierarchyName;
    }
    
    public function getModelPermissionsName()
    {
        return $this->modelPermissionsName;
    }

    public function setModelPermissionsName($modelPermissionsName)
    {
        $this->modelPermissionsName = $modelPermissionsName;
    }
    
    public function getModelRolePermissionsName()
    {
        return $this->modelRolePermissionsName;
    }

    public function setModelRolePermissionsName($modelRolePermissionsName)
    {
        $this->modelRolePermissionsName = $modelRolePermissionsName;
    }
    
    public function getModelUserRolesName()
    {
        return $this->modelUserRolesName;
    }

    public function setModelUserRolesName($modelUserRolesName)
    {
        $this->modelUserRolesName = $modelUserRolesName;
    }
    
    public function getConditions()
    {
        return $this->conditions;
    }

    public function setConditions($conditions)
    {
        $this->conditions = $conditions;
    }
    
    public function getNameColumn()
    {
        return $this->nameColumn;
    }

    public function setNameColumn($nameColumn)
    {
        $this->nameColumn = $nameColumn;
    }
    
    /**
     * @return \Laminas\Db\ResultSet\ResultSet
     */
    public function getRolesData()
    {
        $model = $this->getModelRoles();
        $conditions = $this->getConditions();
        
        $select = $model->select();
        
        foreach ($conditions as $condition) {
            $select->where($condition);
        }
        
        $data = $model->fetchAll($select);
        
        return $data;
    }
    
    public function getRoleParentsData($idRole)
    {
        $modelHierarchy = $this->getModelHierarchy();
        $modelRoles = $this->getModelRoles();
        $primaryKey = $modelRoles->getPrimaryKey();
        $nameHierarchy = $modelHierarchy->getTableGateway()
            ->getTable()
            ->getTable();
        $nameRoles = $modelRoles->getTableGateway()
            ->getTable();
        
        $select = $modelHierarchy->select()
            ->columns([])
            ->join(['r' => $nameRoles], "r.{$primaryKey} = {$nameHierarchy}.id_role_parent")
            ->where("NOT {$nameHierarchy}.ghost")
            ->where('NOT r.ghost')
            ->where(["{$nameHierarchy}.id_role_child" => $idRole]);
        
        $data = $modelHierarchy->fetchAll($select);
        
        return $data;
    }
    
    public function getRoleChildrensData($idRole)
    {
        
    }
    
    public function getRolePermissionsData($idRole)
    {
        $modelPermissions = $this->getModelPermissions();
        $modelRolePermissions = $this->getModelRolePermissions();
        $primaryKey = $modelPermissions->getPrimaryKey();
        
        $namePermissions = $modelPermissions->getTableGateway()
            ->getTable()
            ->getTable();
        
        $nameRolePermissions = $modelRolePermissions->getTableGateway()
            ->getTable();
        
        $select = $modelPermissions->select()
            ->join(['rp' => $nameRolePermissions], "rp.id_permission = {$namePermissions}.{$primaryKey}", [])
            ->where('NOT rp.ghost')
            ->where("NOT {$namePermissions}.ghost")
            ->where(['rp.id_role' => $idRole]);
        
        $data = $modelPermissions->fetchAll($select);
        
        return $data;
    }
    
    public function getUserRolesData($idUser)
    {
        $modelUserRoles = $this->getModelUserRoles();
        $modelRoles = $this->getModelRoles();
        
        $nameRoles = $modelRoles->getTableGateway()
            ->getTable()
            ->getTable();
        
        $nameUserRoles = $modelUserRoles->getTableGateway()
            ->getTable();
        
        if (!empty($idUser)) {
            $select = $modelRoles->select()
                ->join(['ur' => $nameUserRoles], "ur.id_role = {$nameRoles}.id", [])
                ->where('NOT ur.ghost')
                ->where("NOT {$nameRoles}.ghost")
                ->where(['ur.id_user' => $idUser]);
        } else {
            $select = $modelRoles->select()
                ->where(['code' => RbacManager::DEFAULT_ROLE_CODE])
                ->where('NOT ghost');
        }
        
        $data = $modelRoles->fetchAll($select);
        
        return $data;
    }
    
    public function getPrimaryKey()
    {
        $model = $this->getModelRoles();
        
        return $model->getPrimaryKey();
    }
    
    /**
     * @return \Base\Db\Table\AbstractModel
     */
    protected function getModelRoles()
    {
        $name = $this->getModelRolesName();
        
        return $this->getModel($name);
    }
    
    /**
     * @return \Base\Db\Table\AbstractModel
     */
    protected function getModelHierarchy()
    {
       $name = $this->getModelHierarchyName();
       
       return $this->getModel($name);
    }
    
    /**
     * @return \Base\Db\Table\AbstractModel
     */
    protected function getModelPermissions()
    {
        $name = $this->getModelPermissionsName();
        
        return $this->getModel($name);
    }
    
    /**
     * @return \Base\Db\Table\AbstractModel
     */
    protected function getModelRolePermissions()
    {
        $name = $this->getModelRolePermissionsName();
        
        return $this->getModel($name);
    }
    
    /**
     * @return \Base\Db\Table\AbstractModel
     */
    protected function getModelUserRoles()
    {
        $name = $this->getModelUserRolesName();
        
        return $this->getModel($name);
    }

    /**
     * @return \Base\Db\Table\AbstractModel
     * @throws \Exception
     */
    protected function getModel($modelName)
    {
        $serviceManager = $this->getServiceManager();
        
        if (empty($modelName)) {
            throw new \Exception('Model name cannot be empty');
        }
        
        $model = $serviceManager->get($modelName);
        
        if (!$model instanceof \Base\Db\Table\AbstractModel) {
            throw new \Exception(sprintf('Model has to extend %s', \Base\Db\Table\AbstractModel::class));
        }
        
        return $model;
    }
}
