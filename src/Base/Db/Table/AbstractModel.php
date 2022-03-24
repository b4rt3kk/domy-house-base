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
    
    protected $useCache = true;
    
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
    
    public function getUseCache()
    {
        return $this->useCache;
    }

    public function setUseCache($useCache)
    {
        $this->useCache = !empty($useCache);
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
        
        if (empty($sequenceName)) {
            switch ($platform) {
                case 'Postgresql':
                    $sequenceName = $table . '_' . $primaryKey . '_seq';
                    break;
                default:
                    throw new \Exception(sprintf("Dla platformy %s nie określono sposobu pobierania nazwy sekwencji i nie została ona określona w konfiguracji", $platform));
            }
        }
        
        return $sequenceName;
    }
    
    /**
     * Sprawdź czy tabela do której przypisany jest model istnieje w bazie danych
     * @return boolean
     * @throws \Exception
     */
    public function isTableExists()
    {
        $return = false;
        $tableGateway = $this->getTableGateway();
        $tableName = $this->getTableName();
        
        $platform = $tableGateway->getAdapter()
                ->getDriver()
                ->getDatabasePlatformName();
        
        $adapter = $this->getTableGateway()->getAdapter()->getDriver()->getConnection();
        
        switch ($platform) {
            case 'Postgresql':
                $sql = "SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = '%s' AND table_name = '%s');";
                $result = $adapter->execute(sprintf($sql, $tableGateway->getTable()->getSchema(), $tableGateway->getTable()->getTable()));
                
                $return = $result->current()['exists'];
                break;
            default:
                throw new \Exception(sprintf("Dla platformy %s nie określono sposobu sprawdzania istnienia tabeli", $platform));
        }
        
        return $return;
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
            
            if ($authenticationService->getIdentity()) {
                $idUser = $authenticationService->getIdentity()->id;
            }
            
            if (!empty($idUser)) {
                $data[$this->getCreatorColumnName()] = $idUser;
            }
        }
        
        // usunięcie kolumn z pustymi wartościami
        foreach ($data as $key => $value) {
            if (empty($value) && $value !== 0 && $data[$key] !== '0') {
                unset($data[$key]);
            }
        }

        $insertData = array_intersect_key($data, $columns);
        
        $tableGateway->insert($insertData);
        
        $id = $tableGateway->getAdapter()
            ->getDriver()
            ->getLastGeneratedValue($this->getSequenceName());
        
        if ($this->getUseCache()) {
            $this->clearCache();
        }
        
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
        $storage = $this->getStorage();
        
        if (!$where instanceof \Laminas\Db\Sql\Select) {
            $select = $this->select();
            
            if (!empty($where)) {
                $select->where($where);
            }
        } else {
            $select = $where;
        }
        
        $select->limit(1);
        
        $cacheKey = $this->getCacheKey($select);
        
        $row = $storage->getItem($cacheKey);
        
        if (empty($row) || !$this->getUseCache()) {
            $tableGateway = $this->getTableGateway();
            $data = $tableGateway->selectWith($select);
            /* @var $data \Laminas\Db\ResultSet\ResultSet */
            $row = $data->current();
            
            $storage->setItem($cacheKey, $row);
        }
        
        return $row;
    }
    
    /**
     * @param \Laminas\Db\Sql\Select $where
     * @return \Laminas\Db\ResultSet\ResultSet
     */
    public function fetchAll($where = null)
    {
        $storage = $this->getStorage();
        
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
        
        $cacheKey = $this->getCacheKey($select);
        
        if ($this->getUseCache()) {
            // w przypadku gdy używane jest cache
            if (empty($storage->getItem($cacheKey))) {
                // cache nie jest jeszcze uzupełniony
                $data = $this->prepareResultSetForCaching($tableGateway->selectWith($select));
                
                $storage->setItem($cacheKey, $data);
            } else {
                // dane istnieją w cache, pobranie ich do zwrócenia
                $data = $storage->getItem($cacheKey);
            }
        } else {
            // cache nie jest używane
            // pobranie danych bezpośrednio z bazy danych
            $data = $tableGateway->selectWith($select);
            /* @var $data \Laminas\Db\ResultSet\ResultSet */
        }
        
        return $data;
    }
    
    /**
     * @param array|mixed $data
     * @param array|mixed $where
     * @return integer Liczba wierszy, których dotyczyło zapytanie
     */
    public function update($data, $where)
    {
        $tableGateway = $this->getTableGateway();
        $columns = $this->getTableColumns();
        
        // usunięcie kolumn nie występujących w tej tabeli
        // lub z pustymi wartościami
        foreach (array_keys($data) as $key) {
            if (!in_array($key, array_keys($columns))) {
                unset($data[$key]);
                continue;
            }
            
            if ((empty($data[$key]) && $data[$key] !== 0 && $data[$key] !== '0')) {
                // podstawienie null gdy przekazana wartość ma być pusta
                $data[$key] = new \Laminas\Db\Sql\Expression("NULL");
            }
        }
        
        $return = $tableGateway->update($data, $where);
        
        if ($this->getUseCache()) {
            // w przypadku używania cache wyczyszczenie danych
            $this->clearCache();
        }
        
        return $return;
    }
    
    public function clearCache()
    {
        $storage = $this->getStorage();
        
        switch (get_class($storage)) {
            case \Laminas\Cache\Storage\Adapter\Filesystem::class:
                $options = $storage->getOptions();
                /* @var $options \Laminas\Cache\Storage\Adapter\FilesystemOptions */
                
                $storage->flush();
                break;
        }
    }
    
    public function insertMultiple($data)
    {
        $entity = $this->getEntity();
        
        
    }
    
    /**
     * Pobierz adapter cache
     * @return \Laminas\Cache\Storage\Adapter\AbstractAdapter
     */
    protected function getStorage()
    {
        $storageFactory = $this->getServiceManager()->get(\Laminas\Cache\Service\StorageAdapterFactoryInterface::class);
        /* @var $storageFactory \Laminas\Cache\Service\StorageAdapterFactory */
        
        $config = $this->getServiceManager()->get('Config')['cache'];
        
        $cache = $storageFactory->createFromArrayConfiguration($config);
        
        return $cache;
    }
    
    protected function getCachePrefix()
    {
        return md5(get_class($this)) . '_';
    }
    
    protected function getCacheKey($select)
    {
        $prefix = $this->getCachePrefix();
        $key = $prefix;
        
        if ($select instanceof \Laminas\Db\Sql\Select) {
            $key .= md5($select->getSqlString());
        } else {
            $key .= md5(serialize($select));
        }
        
        return $key;
    }
    
    protected function prepareResultSetForCaching(\Laminas\Db\ResultSet\ResultSet $resultSet)
    {
        $return = new \Base\Db\ResultSet\ResultSet();
        $return->setArrayObjectPrototype($resultSet->getArrayObjectPrototype());
        
        $iterator = new \Base\Db\ResultSet\Iterator();
        
        foreach ($resultSet as $row) {
            $prototype = clone $return->getArrayObjectPrototype();
            
            if (!empty($row)) {
                $prototype->exchangeArray($row->toArray());
                $iterator->add($prototype);
            }
        }
        
        $return->initialize($iterator);
        
        return $return;
    }
}
