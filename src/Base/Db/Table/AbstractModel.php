<?php
namespace Base\Db\Table;

use Laminas\Db\TableGateway\TableGatewayInterface;

abstract class AbstractModel
{
    protected $data;
    
    protected $tableGateway;
    
    protected $primaryKey = 'id';
    
    protected $sequenceName;
    
    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->setTableGateway($tableGateway);
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
    
    public function fetchRow($where)
    {
        if (!$where instanceof \Laminas\Db\Sql\Select) {
            $select = $this->select()
                ->where($where);
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
    public function fetchAll($where)
    {
        if (!$where instanceof \Laminas\Db\Sql\Select) {
            $select = $this->select()
                ->where($where);
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
        
        $tableGateway->update($data, $where);
    }
}
