<?php
namespace Base\Db\Table;

use Laminas\Db\TableGateway\TableGatewayInterface;

abstract class AbstractModel
{
    protected $data;
    
    protected $tableGateway;
    
    protected $primaryKey = 'id';
    
    protected $sequenceName;
    
    protected $serviceManager;
    
    protected $creatorColumnName = 'created_by';
    
    public function __construct(TableGatewayInterface $tableGateway, $serviceManager = null)
    {
        $this->setTableGateway($tableGateway);
        $this->setServiceManager($serviceManager);
    }
    
    /**
     * @return \Laminas\ServiceManager\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function setServiceManager($serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getCreatorColumnName()
    {
        return $this->creatorColumnName;
    }

    public function setCreatorColumnName($creatorColumnName)
    {
        $this->creatorColumnName = $creatorColumnName;
    }
    
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * @return \Laminas\Db\TableGateway\TableGateway
     */
    public function getTableGateway()
    {
        return $this->tableGateway;
    }

    /**
     * @return \Base\Db\Table\AbstractEntity
     */
    public function getEntity()
    {
        $tableGateway = $this->getTableGateway();
        $resultSetPrototype = $tableGateway->getResultSetPrototype();
        /* @var $resultSetPrototype \Laminas\Db\ResultSet\ResultSet */
        $entity = $resultSetPrototype->getArrayObjectPrototype();
        
        return $entity;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setTableGateway($tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }
    
    public function getSequenceName()
    {
        $sequenceName = $this->sequenceName;
        $tableGateway = $this->getTableGateway();
        $platform = $tableGateway->getAdapter()
            ->getDriver()
            ->getDatabasePlatformName();
        
        $primaryKey = $this->getPrimaryKey();
        $table = $tableGateway->getTable();
        
        if ($table instanceof \Laminas\Db\Sql\TableIdentifier) {
            $table = $table->getTable();
        }
        
        switch ($platform) {
            case 'Postgresql':
                if (empty($sequenceName)) {
                    $sequenceName = $table . '_' . $primaryKey . '_seq';
                }
                break;
        }
        
        return $sequenceName;
    }

    public function setSequenceName($sequenceName)
    {
        $this->sequenceName = $sequenceName;
    }
    
    /**
     * Create table row and returns its id
     * @param \Base\Db\Table\AbstractEntity $entity
     * @return integer
     */
    public function createRow(AbstractEntity $entity)
    {
        $tableGateway = $this->getTableGateway();
        $data = $entity->getData();
        $columns = $this->getTableColumns();
        $serviceManager = $this->getServiceManager();
        
        if ($serviceManager instanceof \Laminas\ServiceManager\ServiceManager) {
            $authenticationService = $serviceManager->get(\Laminas\Authentication\AuthenticationService::class);
            $idUser = $authenticationService->getIdentity()->id;
            
            if (!empty($idUser)) {
                $data[$this->getCreatorColumnName()] = $idUser;
            }
        }
        
        $insertData = array_intersect_key($data, $columns);
        
        $tableGateway->insert($insertData);
        
        $id = $tableGateway->getAdapter()
            ->getDriver()
            ->getLastGeneratedValue($this->getSequenceName());
        
        return $id;
    }
    
    /**
     * @return \Laminas\Db\Metadata\Object\ColumnObject[]
     */
    public function getTableColumns()
    {
        $return = [];
        $tableGateway = $this->getTableGateway();
        $adapter = $tableGateway->adapter;
        $table = $tableGateway->getTable();
        $schema = null;
        
        if ($table instanceof \Laminas\Db\Sql\TableIdentifier) {
            $schema = $table->getSchema();
            $table = $table->getTable();
        }
        
        $metaData = \Laminas\Db\Metadata\Source\Factory::createSourceFromAdapter($adapter);
        
        $columns = $metaData->getColumns($table, $schema);
        
        foreach ($columns as $column) {
            $name = $column->getName();
            
            $return[$name] = $column;
        }
        
        return $return;
    }
    
    public function getTableName()
    {
        $tableGateway = $this->getTableGateway();
        $table = $tableGateway->getTable();
        $schema = null;
        
        if ($table instanceof \Laminas\Db\Sql\TableIdentifier) {
            $schema = $table->getSchema();
            $table = $table->getTable();
        }
        
        $name = null;
        
        if (!empty($schema)) {
            $name .= $schema . '.';
        }
        
        $name .= $table;
        
        return $name;
    }
    
    /**
     * @return \Laminas\Db\Sql\Select
     */
    public function select()
    {
        $tableGateway = $this->getTableGateway();
        $table = $tableGateway->getTable();
        
        $select = new \Laminas\Db\Sql\Select($table);
        
        return $select;
    }
    
    /**
     * @param \Laminas\Db\Sql\Select $where
     * @return \Base\Db\Table\AbstractEntity
     */
    public function fetchRow($where = null)
    {
        if (!$where instanceof \Laminas\Db\Sql\Select) {
            $select = $this->select();
            
            if (!empty($where)) {
                $select->where($where);
            }
        } else {
            $select = $where;
        }
        
        $select->limit(1);
        
        $tableGateway = $this->getTableGateway();
        $data = $tableGateway->selectWith($select);
        /* @var $data \Laminas\Db\ResultSet\ResultSet */
        
        return $data->current();
    }
    
    /**
     * @param \Laminas\Db\Sql\Select $where
     * @return \Laminas\Db\ResultSet\ResultSet
     */
    public function fetchAll($where = null)
    {
        if ($where instanceof \Laminas\Db\Sql\Combine) {
            $connection = $this->getTableGateway()->getAdapter()->getDriver()->getConnection();
            $data = $connection->execute($where->getSqlString());
            
            $prototype = $this->getTableGateway()->getResultSetPrototype();
            /* @var $prototype \Laminas\Db\ResultSet\ResultSet */
            $prototype->initialize($data);
            
            return $prototype;
        } else if (!$where instanceof \Laminas\Db\Sql\Select) {
            $select = $this->select();
            
            if (!empty($where)) {
                $select->where($where);
            }
        } else {
            $select = $where;
        }
        
        $tableGateway = $this->getTableGateway();
        $data = $tableGateway->selectWith($select);
        /* @var $data \Laminas\Db\ResultSet\ResultSet */
        
        return $data;
    }
    
    public function update($data, $where)
    {
        $tableGateway = $this->getTableGateway();
        $columns = $this->getTableColumns();
        
        // usunięcie kolumn nie występujących w tej tabeli
        foreach (array_keys($data) as $key) {
            if (!in_array($key, array_keys($columns))) {
                unset($data[$key]);
            }
        }
        
        $tableGateway->update($data, $where);
    }
}
