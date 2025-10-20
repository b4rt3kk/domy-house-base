<?php
namespace Base\Logic;

abstract class AbstractLogic implements LogicInterface
{
    use \Base\Traits\ServiceManagerTrait;
    
    public function unserializeFormArray($data)
    {
        $return = [];
        
        foreach ($data as $row) {
            $name = $row['name'];
            $value = $row['value'];
            $isArray = false;
            
            if (strpos($name, '[]')) {
                $name = str_replace(['[]'], '', $name);
                
                if (!is_array($return[$name])) {
                    $return[$name] = [];
                }
                
                $isArray = true;
            }
            
            if ($isArray === true) {
                $return[$name][] = $value; 
            } else {
                $return[$name] = $value;
            }
        }
        
        return $return;
    }
    
    public function unserializeJqueryArray($data)
    {
        $return = [];
        
        foreach ($data as $row) {
            $return[$row['name']] = $row['value'];
        }
        
        return $return;
    }
    
    public function generateRandomString($length = 32)
    {
        $return = null;
        $chars = 'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890';
        
        for ($i = 0; $i < $length; $i++) {
            $return .= $chars[mt_rand(0, strlen($chars) -1)];
        }
        
        return $return;
    }
    
    public function getAsUrlName($name, $separator = '-')
    {
        $charsToReplace = 'ęóąśłżźćń';
        $charsReplacements = 'eoaslzzcn';
        
        // zamiania wszystkich białych znaków na separator
        $return = mb_strtolower(preg_replace("#\s+#", $separator, trim($name)), 'UTF-8');
        
        // zamiana polskich znaków
        $return = str_replace(mb_str_split($charsToReplace, 1, 'UTF-8'), str_split($charsReplacements), $return);
        
        // wyrzucenie wszystkich niedozwolonych znaków
        $return = preg_replace(sprintf("#[^A-Za-z0-9\%s]#", $separator), '', $return);
        
        return $return;
    }

    /**
     * Calculates the total size of all files within the specified directory.
     *
     * @param string $path The path to the directory whose total size needs to be calculated.
     *                      The path should point to a valid directory.
     * @return int The size of all files in the directory, in bytes. Returns 0 if the path is not a directory.
     */
    public function getDirectorySize($path)
    {
        $size = 0;

        if (!is_dir($path)) {
            return 0; // lub możesz rzucić wyjątek
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size; // <-- wynik w bajtach
    }

    /**
     * Pobierz wiersz z modelu o wskazanym id
     *
     * @param string $modelName
     * @param int $id
     * @return \Base\Db\Table\AbstractEntity
     */
    protected function getRow(string $modelName, $id)
    {
        if (empty($id)) {
            throw new \Exception("Id nie może być puste");
        }

        $model = $this->getModel($modelName);
        $primaryKey = $model->getPrimaryKey();

        $select = $model->select()
            ->where([$primaryKey => $id]);

        $row = $model->fetchRow($select);

        return $row;
    }

    /**
     * Utwórz wiersz i zwróć jego id
     *
     * @param string $modelName
     * @param array|\Base\Form\AbstractForm $data
     * @return int
     */
    protected function createRow(string $modelName, $data) : int
    {
        if ($data instanceof \Base\Form\AbstractForm) {
            $data = $data->getData();
        }

        $model = $this->getModel($modelName);

        $entity = $model->getEntity();
        $entity->exchangeArray($data);

        $id = $model->createRow($entity);

        return $id;
    }

    /**
     * Pobierz obiekt modelu
     *
     * @param string $modelName
     * @return \Base\Db\Table\AbstractModel
     * @throws \Exception
     */
    protected function getModel($modelName)
    {
        $model = $this->getServiceManager()->get($modelName);

        if (!$model instanceof \Base\Db\Table\AbstractModel) {
            throw new \Exception(sprintf($this->translate("Obiekt modelu musi dziedziczyć po %s"), \Base\Db\Table\AbstractModel::class));
        }

        return $model;
    }
}
