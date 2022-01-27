<?php

namespace Base\Command\Migrations;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Migrate extends AbstractMigrations
{
    public function test()
    {
        $this->runMigrations();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Uruchomienie migracji...");
        
        try {
            $this->runMigrations($output);
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
        
        $output->writeln("Zakończono przetwarzanie migracji...");
    }
    
    /**
     * Uruchom nieuruchomione pliki migracji.
     * Jeśli nie istnieje tabela migracji to najpierw uruchom plik z podstawowymi zapytaniami.
     */
    protected function runMigrations(OutputInterface $output)
    {
        if (!$this->isTableExists()) {
            // uruchomienie pliku, który utworzy podstawowe tabele konieczne do podstawowego działania aplikacji
            $output->writeln("Uruchomienie zapytań początkowych...");
            
            $this->runInitialQueries();
        }
        
        $model = $this->getModel();
        
        if (!$model->isTableExists()) {
            throw new \Exception("Tabela migracji nie istnieje w bazie danych. Być może jej utworzenie nie zostało skonfigurowane w pliku zapytań podstawowych.");
        }
        
        // lista plików migracji
        $files = $this->getMigrationsFiles();
        
        // adapter brany na podstawie modelu migracji
        $adapter = $model->getTableGateway()->getAdapter()->getDriver()->getConnection();
        
        foreach ($files as $file) {
            try {
                $migration = $this->getMigrationClass($file);

                if (!$this->hasMigrationRow($migration->getFileName())) {
                    // utworzenie wiersza dla migracji jeśli nie istnieje w bazie danych
                    $this->addMigrationRow([
                        $this->getMappedColumnName(self::COL_FILE_NAME) => $migration->getFileName(),
                        $this->getMappedColumnName(self::COL_INDEX) => $migration->getIndex(),
                        $this->getMappedColumnName(self::COL_NAME) => $migration->getName(),
                    ]);
                }

                // pobranie wiersza migracji na podstawie nazwy pliku
                $row = $this->getMigrationByFileNameRow($migration->getFileName());

                if (empty($row)) {
                    throw new \Exception(sprintf("Nie udało się utworzyć lub odnaleźć wpisu w bazie danych dla migracji dla pliku %s", $file));
                }

                $isExecutedCol = $this->getMappedColumnName(self::COL_IS_EXECUTED);

                if ($row->{$isExecutedCol}) {
                    // ta migracja została już wykonana
                    // przejście do kolejnej
                    continue;
                }
                
                $output->writeln(sprintf("Przetwarzanie pliku %s", $file));

                // pobranie listy zapytań z migracji
                $queries = $migration->getQueries();

                // uruchomienie transakcji (jeśli któreś zapytanie nie pójdzie to wszystkie zostaną wycofane)
                $adapter->beginTransaction();

                try {
                    // uruchomienie zapytań po kolei z tablicy
                    foreach ($queries as $query) {
                        $adapter->execute($query);
                    }

                    // oznacz migrację jako wykonaną
                    $this->setMigrationRowExecuted($row->{$this->getMappedColumnName(self::COL_ID)});

                    $adapter->commit();
                } catch (\Exception $e) {
                    $adapter->rollback();
                    throw $e;
                }
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
            }
        }
    }
    
    /**
     * Uruchom podstawowe zapytania konieczne do podstawowego działania aplikacji
     * @throws \Exception
     */
    protected function runInitialQueries()
    {
        $model = $this->getModel();
        $initFile = $this->getInitSqlFileDir();
        $queries = file_get_contents($initFile);
        
        $adapter = $model->getTableGateway()->getAdapter()->getDriver()->getConnection();
        
        $adapter->beginTransaction();
        
        try {
            $adapter->execute($queries);
            
            $adapter->commit();
        } catch (\Exception $e) {
            $adapter->rollback();
            throw $e;
        }
    }
}
