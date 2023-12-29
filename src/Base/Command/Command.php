<?php

namespace Base\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends \Symfony\Component\Console\Command\Command implements CommandInterface
{
    const STATUS_WAITING = 1;
    const STATUS_EXECUTING = 2;
    const STATUS_ERROR = 3;
    
    const MESSAGE_SUCCESS = 'Operacja przebiegła pomyślnie';
    
    protected $serviceManager;
    
    protected $actionsTableClassName;
    
    protected $actionsTableMapping = [
        'id' => 'id',
        'command' => 'command',
        'id_status' => 'id_status',
        'is_executed' => 'is_executed',
        'execution_start_date' => 'execution_start_date',
        'execution_end_date' => 'execution_end_date',
        'message' => 'message',
        'created_at' => 'created_at',
        'created_by' => 'created_by',
        'changed_at' => 'changed_at',
        'changed_by' => 'changed_by',
        'removed_at' => 'removed_at',
        'removed_by' => 'removed_by',
        'ghost' => 'ghost',
    ];
    
    protected $isDebug = false;
    
    protected $isTestMode = false;
    
    /**
     * @var \Base\Logger\Logger
     */
    protected $logger;
    
    protected $memoryLimit = '5G';

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
    
    public function getLogger(): \Base\Logger\Logger
    {
        return $this->logger;
    }

    public function setLogger(\Base\Logger\Logger $logger)
    {
        $this->logger = $logger;
    }
    
    public function getActionsTableClassName()
    {
        return $this->actionsTableClassName;
    }

    /**
     * Nazwa klasy dla tabeli przechowującej wykonywane akcje cli
     * @param string $actionsTableClassName
     */
    public function setActionsTableClassName($actionsTableClassName)
    {
        $this->actionsTableClassName = $actionsTableClassName;
    }
    
    public function getActionsTableMapping()
    {
        return $this->actionsTableMapping;
    }

    /**
     * Mapowanie dla tabeli przechowującej wykonywane akcje cli
     * @param array $actionsTableMapping
     */
    public function setActionsTableMapping($actionsTableMapping)
    {
        $this->actionsTableMapping = $actionsTableMapping;
    }
    
    public function getIsDebug()
    {
        return $this->isDebug;
    }

    /**
     * W trybie debugowania wyświetlane są wszystkie błędy
     * @param boolean $isDebug
     */
    public function setIsDebug($isDebug)
    {
        $this->isDebug = $isDebug;
    }
    
    public function getIsTestMode()
    {
        return $this->isTestMode;
    }
    
    public function getMemoryLimit()
    {
        return $this->memoryLimit;
    }

    public function setMemoryLimit($memoryLimit)
    {
        $this->memoryLimit = $memoryLimit;
    }

    /**
     * W trybie testowym ignorowany jest status wykonywanej akcji (uruchomienie następuje dla każdego statusu)
     * @param boolean $isTestMode
     */
    public function setIsTestMode($isTestMode)
    {
        $this->isTestMode = $isTestMode;
    }
    
    protected function configure()
    {
        $this->addOption('no-output', null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, "Turn off all output data", false);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int 0 if everything went fine, or an exit code
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isDebug = $this->getIsDebug();
        $isTestMode = $this->getIsTestMode();
        $memoryLimit = $this->getMemoryLimit();
        
        // ustawienie limitu pamięci
        ini_set("memory_limit", $memoryLimit);
        
        // zalogowanie jako domyślny użytkownik wirtualny
        $this->loginAsVirtualUser();
        
        if ($isDebug) {
            // włącz raportowanie błędów
            $this->setDebugMode();
        }
        
        $noOutput = !empty($input->getOption('no-output'));
        
        $command = $input->getArgument('command');
        
        if ($this->isExecuting($command) && !$isTestMode) {
            throw new \Exception(sprintf("Komenda %s jest obecnie w trakcie wykonywania", $command));
        }
        
        $this->setCommandExecuting($command);
        
        $this->logMessage(sprintf("Rozpoczęto przetwarzanie komendy: %s", $command), \Base\Logger\Logger::MESSAGE_INFO);
        
        try {
            $startTime = microtime(true);
            
            $output->writeln(sprintf("Czas rozpoczęcia %s", date('Y-m-d H:i:s')));
            
            $this->executeAction($input, $output);
            
            $output->writeln(sprintf("Czas zakończenia %s", date('Y-m-d H:i:s')));
            
            $this->setCommandMessage($command);
            
            $endTime = microtime(true);
            
            if (!$noOutput) {
                $output->writeln("");
                $output->writeln(sprintf("Czas wykonywania: %s sec", number_format($endTime - $startTime, 6, '.', '')));
                $output->writeln("");
            }
            
            $this->logMessage(sprintf("Czas wykonywania: %s sec", number_format($endTime - $startTime, 6, '.', '')), \Base\Logger\Logger::MESSAGE_INFO);
        } catch (\Exception $e) {
            $this->setCommandError($command, $e->getMessage());
            $this->logMessage($e, \Base\Logger\Logger::MESSAGE_ERROR);
        }

        $this->setCommandExecuted($command);
        
        $this->logMessage(sprintf("Zakończono przetwarzanie komendy: %s", $command), \Base\Logger\Logger::MESSAGE_INFO);

        return 0;
    }
    
    /**
     * Pobierz obiekt modelu, w którym przechowywane są wykonywane akcje cron
     * @return \Base\Db\Table\AbstractModel
     * @throws \Exception
     */
    protected function getActionsTableModel()
    {
        $modelName = $this->getActionsTableClassName();
        
        if (empty($modelName)) {
            throw new \Exception(sprintf("Nazwa modelu z wykonywanymi akcjami cli nie może być pusta. Utwórz fabrykę abstrakcyjną dziedziczącą po %s i określ nazwę modelu dla tabeli wykonywanych akcji", AbstractCommandFactory::class));
        }
        
        $model = $this->getServiceManager()->get($modelName);
        
        if (!$model instanceof \Base\Db\Table\AbstractModel) {
            throw new \Exception(sprintf("Klasa modelu musi dziedziczyć po %s", \Base\Db\Table\AbstractModel::class));
        }
        
        return $model;
    }
    
    /**
     * Pobierz zmapowaną nazwę kolumny dla tabeli z wykonywanymi akcjami cron
     * @param string $columnName
     * @return string
     * @throws \Exception
     */
    protected function getMappedColumnName($columnName)
    {
        $mapping = $this->getActionsTableMapping();
        $name = $mapping[$columnName];
        
        if (empty($name)) {
            throw new \Exception(sprintf("Brak zmapowania nazwy kolumny dla kolumny %s", $columnName));
        }
        
        return $name;
    }
    
    /**
     * Pobierz wiersz dla wykonywanej komendy na podstawie jej nazwy
     * @param string $command
     * @return \Base\Db\Table\AbstractEntity
     */
    protected function getCommandRow($command)
    {
        $model = $this->getActionsTableModel();
        
        $select = $model->select()
                ->where(["NOT {$this->getMappedColumnName('ghost')}", $this->getMappedColumnName('command') => $command]);
                
        $row = $model->fetchRow($select);
        
        if (empty($row)) {
            $this->createCommandRow($command);
            $row = $this->getCommandRow($command);
        }
        
        return $row;
    }
    
    protected function createCommandRow($command)
    {
        $model = $this->getActionsTableModel();
        
        $entity = $model->getEntity();
        $entity->exchangeArray([
            $this->getMappedColumnName('command') => $command,
            $this->getMappedColumnName('id_status') => self::STATUS_WAITING,
        ]);
        
        $id = $model->createRow($entity);
        
        return $id;
    }
    
    /**
     * Sprawdź czy komenda jest obecnie w trakcie wykonywania
     * @param string $command
     * @return boolean
     */
    protected function isExecuting($command)
    {
        $row = $this->getCommandRow($command);
        $column = $this->getMappedColumnName('id_status');
        
        return $row->{$column} === self::STATUS_EXECUTING;
    }
    
    /**
     * Ustaw komendę jako w trakcie wykonywania
     * @param string $command
     * @throws \Exception
     */
    protected function setCommandExecuting($command)
    {
        $row = $this->getCommandRow($command);
        
        if (empty($row)) {
            throw new \Exception(sprintf("Komenda %s nie istnieje", $command));
        }
        
        $this->updateCommandRow($row->id, [
            $this->getMappedColumnName('id_status') => self::STATUS_EXECUTING,
            $this->getMappedColumnName('is_executed') => '0',
            $this->getMappedColumnName('execution_start_date') => new \Laminas\Db\Sql\Expression("NOW()"),
        ]);
    }
    
    /**
     * Ustaw komendę jako przetworzoną
     * @param string $command
     * @throws \Exception
     */
    protected function setCommandExecuted($command)
    {
        $row = $this->getCommandRow($command);
        
        if (empty($row)) {
            throw new \Exception(sprintf("Komenda %s nie istnieje", $command));
        }
        
        $this->updateCommandRow($row->id, [
            $this->getMappedColumnName('id_status') => self::STATUS_WAITING,
            $this->getMappedColumnName('is_executed') => '1',
            $this->getMappedColumnName('execution_end_date') => new \Laminas\Db\Sql\Expression("NOW()"),
        ]);
    }
    
    /**
     * Ustaw błąd dla komendy
     * @param string $command
     * @throws \Exception
     */
    protected function setCommandError($command, $message)
    {
        $row = $this->getCommandRow($command);
        
        if (empty($row)) {
            throw new \Exception(sprintf("Komenda %s nie istnieje", $command));
        }
        
        $this->updateCommandRow($row->id, [
            $this->getMappedColumnName('id_status') => self::STATUS_ERROR,
            $this->getMappedColumnName('message') => $message,
        ]);
    }
    
    /**
     * Ustaw wiadomość dla komendy
     * @param string $command
     * @throws \Exception
     */
    protected function setCommandMessage($command, $message = self::MESSAGE_SUCCESS)
    {
        $row = $this->getCommandRow($command);
        
        if (empty($row)) {
            throw new \Exception(sprintf("Komenda %s nie istnieje", $command));
        }
        
        $this->updateCommandRow($row->id, [
            $this->getMappedColumnName('message') => $message,
        ]);
    }
    
    /**
     * Zaktualizuj wiersz dla komendy o podanym id
     * @param integer $id
     * @param array $data
     */
    protected function updateCommandRow($id, $data)
    {
        $mappedData = [];
        
        foreach ($data as $columnName => $value) {
            $column = $this->getMappedColumnName($columnName);
            $mappedData[$column] = $value;
        }
        
        $model = $this->getActionsTableModel();
        
        $model->update($mappedData, [$this->getMappedColumnName('id') => $id]);
    }
    
    /**
     * Włącz raportowanie błędów
     */
    protected function setDebugMode()
    {
        error_reporting(E_ALL);
        ini_set("display_errors", '1');
    }
    
    /**
     * Zaloguj się jako użytkownik wirtualny
     * @param string $login Nazwa użytkownika wirtualnego
     */
    protected function loginAsVirtualUser($login = \Base\Services\Auth\AuthManager::DEFAULT_VIRTUAL_USER)
    {
        $authManager = $this->getServiceManager()->get(\Base\Services\Auth\AuthManager::class);
        /* @var $authManager \Base\Services\Auth\AuthManager */
        $authenticationService = $this->getServiceManager()->get(\Laminas\Authentication\AuthenticationService::class);
        /* @var $authenticationService \Laminas\Authentication\AuthenticationService */
        $adapter = $authenticationService->getAdapter();
        /* @var $adapter \Base\Services\Auth\AuthAdapter */
        
        $authManager->login([
            $adapter->getLoginColumnName() => $login,
        ]);
    }
    
    protected function logMessage($message, $messageType = \Base\Logger\Logger::MESSAGE_SUCCESS, $additionalData = [])
    {
        $logger = $this->getLogger();
        
        if ($logger instanceof \Base\Logger\Logger) {
            $logger->logMessage($message, $messageType, $additionalData);
        }
    }
    
    /**
     * Funkcja do wykonania
     * @param $input
     * @param $output
     */
    abstract protected function executeAction(InputInterface $input, OutputInterface $output);
}
