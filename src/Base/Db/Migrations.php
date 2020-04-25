<?php
namespace Base\Db;

use \Laminas\Log\Logger;
use \Laminas\Log\Writer\Stream;

class Migrations
{
    const DEFAULT_PREFIX = 'Migration';
    
    const DEFAULT_SUFFIX = '.php';
    
    protected $migrationsDir;
    
    protected $migrationsModel;
    
    protected $serviceManager;
    
    protected $prefix = self::DEFAULT_PREFIX;
    
    protected $logger;
    
    protected $console;
    
    protected $suffix = self::DEFAULT_SUFFIX;
    
    public function __construct($serviceManager)
    {
        $this->setServiceManager($serviceManager);
        $this->init();
    }
    
    public function init()
    {
        $this->initLogger();
    }
    
    public function getMigrationsDir()
    {
        return $this->migrationsDir;
    }

    public function getMigrationsModel()
    {
        return $this->migrationsModel;
    }

    public function setMigrationsDir($migrationsDir)
    {
        $this->migrationsDir = $migrationsDir;
    }

    public function setMigrationsModel($migrationsModel)
    {
        $this->migrationsModel = $migrationsModel;
    }
    
    public function getPrefix()
    {
        return $this->prefix;
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }
    
    public function getSuffix()
    {
        return $this->suffix;
    }

    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
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
    
    /**
     * @return \Laminas\Log\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }
    
    public function setLogger(\Laminas\Log\Logger $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * @return \Laminas\Console\Adapter\AdapterInterface
     */
    public function getConsole()
    {
        return $this->console;
    }

    public function setConsole($console)
    {
        $this->console = $console;
    }

    /**
     * Przeszukaj katalog migracji i uruchom wszystkie nieuruchomione jeszcze migracje
     * @throws \Base\Db\Exception
     */
    public function migrate()
    {
        $serviceManager = $this->getServiceManager();
        $migrations = $this->getMigrations();
        $adapter = $serviceManager->get(\Laminas\Db\Adapter\AdapterInterface::class);
        /* @var $adapter \Laminas\Db\Adapter\Adapter */
        $logger = $this->getLogger();
        
        foreach ($migrations as $migration) {
            $idRow = null;
            $class = $this->getMigrationClass($migration);
            
            $queries = $class->getQueries();
            
            if (empty($queries)) {
                // brak zapytań do uruchomienia
                continue;
            }
            
            if (!$class->getIsInitial()) {
                $row = $this->getMigrationRow($migration['path'], [
                    'name' => $class->getName(),
                    'file_name' => $migration['path'],
                    'author' => $class->getAuthor(),
                    'index' => $class->getIndex(),
                ]);
                
                if ($row->is_inserted) {
                    // ta migracja została już uruchomiona i prawidłowo wykonana
                    continue;
                }
                
                $idRow = $row->id;
            } else if ($this->isInitialized()) {
                // zainicjalizowano już bazę danych, przygotowując tabele dla migracji
                // pominięcie tej migracji
                continue;
            }
            
            $logger->log(Logger::INFO, sprintf('Running migration %s', $migration['class_name']));
            
            $adapter->getDriver()
                ->getConnection()
                ->beginTransaction();
            
            try {
                foreach ($queries as $query) {
                    $adapter->query($query)->execute();
                }
                
                $this->setMigrationInserted($idRow);
                
                if ($class->getIsInitial()) {
                    $this->setInitialized();
                }
                
                $logger->log(Logger::INFO, sprintf('Migration %s succesfully executed', $migration['class_name']));
                
                $adapter->getDriver()
                ->getConnection()
                ->commit();
            } catch (Exception $e) {
                $adapter->getDriver()
                    ->getConnection()
                    ->rollback();
                
                throw $e;
            }
        }
    }
    
    public function create($name, $author)
    {
        $logger = $this->getLogger();
        $dir = $this->getMigrationsDir();
        $index = $this->getLastMigrationNumber() + 1;
        $prefix = $this->getPrefix();
        $suffix = $this->getSuffix();
        
        $fileName = $prefix . $index . $suffix;
        
        while (true === is_file($dir . $fileName)) {
            $index++;
            $fileName = $prefix . $index . $suffix;
        }
        
        $content = <<<'FILE'
<?php
class Migration{index} extends \Base\Db\Migrations\Migration
{
    protected $isInitial = false;
    
    protected $index = {index};
    
    public function getName()
    {
        return '{name}';
    }
    
    public function getAuthor()
    {
        return '{author}';
    }
    
    public function getQueries()
    {
        return [
            
        ];
    }
}

FILE;
        
        if (strpos($content, '{index}') !== false) {
            $content = str_replace('{index}', $index, $content);
        }
        
        if (strpos($content, '{name}') !== false) {
            $content = str_replace('{name}', $name, $content);
        }
        
        if (strpos($content, '{author}') !== false) {
            $content = str_replace('{author}', $author, $content);
        }
        
        if (!file_put_contents($dir . $fileName, $content)) {
            throw new \Exception(sprintf('Unable to create new migration file in directory %s', $dir));
        }
        
        $this->createMigrationRow([
            'name' => $name,
            'index' => $index,
            'file_name' => $dir . $fileName,
            'author' => $author,
        ]);
        
        $logger->log(Logger::INFO, sprintf('Migration %s succesfully created', $index));
    }
    
    /**
     * Pobierz listę plików w katalogu migracji, posegregowaną wg daty utworzenia
     * @return array
     */
    protected function getMigrations()
    {
        $return = [];
        $files = [];
        $dir = $this->getMigrationsDir();
        $prefix = $this->getPrefix();
        $iterator = new \DirectoryIterator($dir);
        
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            
            $name = $file->getFilename();
            
            if (strpos($name, $prefix) !== 0) {
                continue;
            }
            
            $files[$file->getMTime()][] = [
                'name' => $file->getBasename(),
                'path' => $file->getPathname(),
                'extension' => $file->getExtension(),
                'type' => $file->getType(),
                'class_name' => $file->getBasename('.' . $file->getExtension()),
            ];
        }
        
        ksort($files);
        
        foreach ($files as $file) {
            $return = array_merge($return, $file);
        }
        
        return $return;
    }
    
    /**
     * @return \Base\Db\Table\AbstractModel
     */
    protected function getModel()
    {
        $serviceManager = $this->getServiceManager();
        $modelName = $this->getMigrationsModel();
        
        $model = $serviceManager->get($modelName);
        
        return $model;
    }
    
    /**
     * Pobierz obiekt migracji
     * @param array $data
     * @return \Base\Db\Migrations\Migration
     * @throws \Exception
     */
    protected function getMigrationClass($data)
    {
        $className = $data['class_name'];
        $path = $data['path'];

        include_once $path;

        if (!class_exists($className)) {
            throw new \Exception(sprintf('Migration file %s doesnt contain migration class %s', $path, $className));
        }

        $class = new $className();

        if (!$class instanceof Migrations\Migration) {
            throw new \Exception(sprintf('Class %s have to extend %s', $className, Migrations\Migration::class));
        }
        
        return $class;
    }
    
    protected function createMigrationRow($data)
    {
        $model = $this->getModel();
        $entity = $model->getEntity();
        
        $entity->exchangeArray($data);
        
        $model->createRow($entity);
    }
    
    protected function getMigrationRow($fileName, $data)
    {
        $model = $this->getModel();
        
        $row = $model->fetchRow([
            'file_name' => $fileName, 
            'NOT ghost',
        ]);
        
        if (empty($row)) {
            $this->createMigrationRow($data);
            return $this->getMigrationRow($fileName, $data);
        }
        
        return $row;
    }
    
    protected function setMigrationInserted($id)
    {
        if (!empty($id)) {
            $model = $this->getModel();
            
            $model->update(['is_inserted' => '1'], ['id' => $id]);
        }
    }
    
    protected function isInitialized()
    {
        $serviceManager = $this->getServiceManager();
        
        $config = $serviceManager->get('config');
        
        return $config['migrations']['initialized'];
    }
    
    protected function setInitialized()
    {
        $fileContent = <<<'FILE'
<?php
return [
    'migrations' => [
        'initialized' => true,
    ],
];
FILE;
        
        $dir = ROOT_PATH . '/../config/autoload/';
        $fileName = 'migrations.local.php';
        
        if (!is_writable($dir)) {
            throw new \Exception(sprintf('Directory %s has to be writable', $dir));
        }
        
        if (!file_put_contents($dir . $fileName, $fileContent)) {
            throw new \Exception(sprintf('Unable to write file %s%s', $dir, $fileName));
        }
    }
    
    /**
     * Zainicjalizuj domyślny logger, na wypadek gdyby żaden nie został przekazany przy tworzeniu klasy
     */
    protected function initLogger()
    {
        $logger = new Logger();
        $writer = new Stream('php://output');
        
        $logger->addWriter($writer);
        
        $this->setLogger($logger);
    }
    
    protected function getLastMigrationNumber()
    {
        $index = 0;
        $model = $this->getModel();
        
        $select = $model->select()
            ->columns(['index' => new \Laminas\Db\Sql\Expression("MAX(index)")])
            ->where('NOT ghost');
        
        $row = $model->fetchRow($select);
        
        if (!empty($row->index)) {
            $index = $row->index;
        }
        
        return $index;
    }
}
