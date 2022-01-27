<?php

namespace Base\Command\Migrations;

/**
 * Używanie komend:
 * https://docs.laminas.dev/laminas-cli/intro/
 * 
 * Przykład: ./vendor/bin/laminas [--container=<path>] <command-name>
 */
abstract class AbstractMigrations extends \Base\Command\Command
{
    const COL_ID = 'id';
    const COL_FILE_NAME = 'file_name';
    const COL_NAME = 'name';
    const COL_INDEX = 'index';
    const COL_IS_EXECUTED = 'is_executed';
    
    protected $modelName;
    
    protected $tableMapping = [
        self::COL_ID => self::COL_ID,
        self::COL_FILE_NAME => self::COL_FILE_NAME,
        self::COL_NAME => self::COL_NAME,
        self::COL_INDEX => self::COL_INDEX,
        self::COL_IS_EXECUTED => self::COL_IS_EXECUTED,
    ];
    
    protected $columns = [
        self::COL_ID,
        self::COL_FILE_NAME,
        self::COL_NAME,
        self::COL_INDEX,
        self::COL_IS_EXECUTED,
    ];
    
    public function getModelName()
    {
        return $this->modelName;
    }

    public function getTableMapping()
    {
        return $this->tableMapping;
    }

    public function setModelName($modelName): void
    {
        $this->modelName = $modelName;
    }

    public function setTableMapping($tableMapping): void
    {
        $this->tableMapping = $tableMapping;
    }
    
    /**
     * Pobierz nazwę katalogu w którym przechowywane są pliki migracji
     * @return string
     * @throws \Exception
     */
    public function getMigrationsDir()
    {
        $dir = $this->getServiceManager()->get('ApplicationConfig')['migrations']['dir'];
        
        if (empty($dir)) {
            throw new \Exception("W konfiguracji aplikacji nie określono wartości dla ['migrations']['dir']");
        }
        
        if (!is_dir($dir)) {
            throw new \Exception(sprintf("Lokalizacja %s nie jest katalogiem", $dir));
        }
        
        if (is_writable($dir)) {
            throw new \Exception(sprintf("Katalog %s nie jest zapisywalny. Ustaw uprawnienia na 777.", $dir));
        }
        
        if (!is_readable($dir)) {
            throw new \Exception(sprintf("Katalog %s nie jest odczytywalny. Ustaw uprawnienia na 777.", $dir));
        }
        
        return $dir;
    }
    
    /**
     * Pobierz lokalizację pliku z zapytaniami sql do uruchomienia na samym początku aplikacji (jednorazowo)
     * @return string
     * @throws \Exception
     */
    public function getInitSqlFileDir()
    {
        $sql = $this->getServiceManager()->get('ApplicationConfig')['migrations']['init_sql_file_dir'];
        
        if (empty($sql)) {
            throw new \Exception("W konfiguracji aplikacji nie określono wartości dla ['migrations']['init_sql_file_dir']");
        }
        
        if (!is_file($sql)) {
            throw new \Exception(sprintf("Lokalizacja %s nie jest plikiem.", $sql));
        }
        
        if (!is_readable($sql)) {
            throw new \Exception(sprintf("Plik %s nie jest odczytywalny. Ustaw uprawnienia na 777.", $sql));
        }
        
        return $sql;
    }
    
    protected function getColumns()
    {
        return $this->columns;
    }
    
    /**
     * Pobierz klasę modelu dla przechowywania migracji
     * @return \Base\Db\Table\AbstractModel
     * @throws \Exception
     */
    protected function getModel()
    {
        $modelName = $this->getModelName();
        
        if (empty($modelName)) {
            throw new \Exception("Nazwa modelu nie może być pusta");
        }
        
        $model = $this->getServiceManager()->get($modelName);
        
        if (!$model instanceof \Base\Db\Table\AbstractModel) {
            throw new \Exception(sprintf("Klasa modelu musi dziedziczyć po %s", \Base\Db\Table\AbstractModel::class));
        }
        
        return $model;
    }
    
    /**
     * Sprawdź czy tabela przypisana do modelu dla migracji istnieje
     * @return boolean
     */
    protected function isTableExists()
    {
        $model = $this->getModel();
        
        return $model->isTableExists();
    }
    
    /**
     * Pobierz listę pełnych ścieżek dla wszystkich plików w katalogu migracji
     * @return array
     */
    protected function getMigrationsFiles()
    {
        $files = [];
        
        $dir = $this->getMigrationsDir();
        $iterator = new \DirectoryIterator($dir);
        
        foreach ($iterator as $file) {
            /* @var $file \DirectoryIterator */
            if ($file->isDot() || $file->isDir()) {
                // pominięcie katalogów
                continue;
            }
            
            $info = pathinfo($file);
            
            if ($info['extension'] !== 'php') {
                // pominięcie plików nie będących plikami php
                continue;
            }
            
            $path = $file->getPath();
            $name = $file->getBasename();
            
            $filePath = $path . DIRECTORY_SEPARATOR . $name;
            
            $files[] = $filePath;
        }
        
        return $files;
    }
    
    /**
     * Pobierz klasę migracji na podstawie nazwy pliku (pełna ścieżka do pliku)
     * @param string $fileName
     * @return \Base\Migration\AbstractMigration
     * @throws \Exception
     */
    protected function getMigrationClass($fileName)
    {
        if (!is_file($fileName)) {
            throw new \Exception(sprintf("Plik %s nie istnieje", $fileName));
        }
        
        if (!is_readable($fileName)) {
            throw new \Exception(sprintf("Plik %s nie został ustawiony do odczytu. Zmień jego uprawnienia na 777.", $fileName));
        }
        
        $info = pathinfo($fileName);
        $file = $info['filename'];
        
        require_once $fileName;
        
        $class = new $file();
        /* @var $class \Base\Migration\AbstractMigration */
        
        if (!$class instanceof \Base\Migration\AbstractMigration) {
            throw new \Exception(sprintf("Klasa migracji %s musi dziedziczyć po %s", $fileName, \Base\Migration\AbstractMigration::class));
        }
        
        $chunks = explode('_', $file);
        $index = $chunks[sizeof($chunks) - 1];
        
        if (empty($index)) {
            throw new \Exception("Nazwa pliku migracji musi zawierać niepusty index (ostatni string w nazwie pliku poprzedzony podkreślnikiem [_])");
        }
        
        $class->setFileName($fileName);
        $class->setIndex($index);
        
        return $class;
    }
    
    /**
     * Pobierz wierszmigracji na podstawie nazwy pliku migracji
     * @param string $fileName
     * @return \Base\Db\Table\AbstractEntity
     */
    protected function getMigrationByFileNameRow($fileName)
    {
        $model = $this->getModel();
        
        $select = $model->select()
                ->where(['file_name' => $fileName]);
        
        $row = $model->fetchRow($select);
        
        return $row;
    }
    
    /**
     * Sprawdź czy migracja o podanej nazwie pliku istnieje w bazie
     * @param string $fileName
     * @return boolean
     */
    protected function hasMigrationRow($fileName)
    {   
        $row = $this->getMigrationByFileNameRow($fileName);
        
        return !empty($row);
    }
    
    /**
     * Oznacz wiersz migracji jako wykonany
     * @param integer $idMigration
     */
    protected function setMigrationRowExecuted($idMigration)
    {
        $model = $this->getModel();
        
        $model->update([$this->getMappedColumnName(self::COL_IS_EXECUTED) => '1'], [$this->getMappedColumnName(self::COL_ID) => $idMigration]);
    }
    
    /**
     * Dodaj plik migracji do bazy
     * @param array $data
     * @return integer Id utworzonej migracji
     */
    protected function addMigrationRow($data = [])
    {
        $model = $this->getModel();
        
        $entity = $model->getEntity();
        $entity->exchangeArray($data);

        $id = $model->createRow($entity);

        return $id;
    }
    
    /**
     * Pobierz zmapowaną nazwę kolumny dla tabeli migracji
     * @param string $columnName
     * @return string
     * @throws \Exception
     */
    protected function getMappedColumnName($columnName)
    {
        $columnsMapping = $this->getTableMapping();
        
        $name = $columnsMapping[$columnName];
        
        if (empty($name)) {
            throw new \Exception(sprintf("Nie określono mapowania dla kolumny %s", $columnName));
        }
        
        return $name;
    }
}
