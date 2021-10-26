<?php
namespace Base;

class Dictionary extends Logic\AbstractLogic
{
    protected $modelName;
    
    protected $dictionaryName;
    
    protected $idKey = 'id';
    
    protected $nameFields = [];
    
    protected $where = [];
    
    protected $separator = ' ';
    
    protected $namedDictionaryCallable;
    
    public function getNamedDictionaryCallable()
    {
        return $this->namedDictionaryCallable;
    }

    public function setNamedDictionaryCallable($namedDictionaryCallable)
    {
        $this->namedDictionaryCallable = $namedDictionaryCallable;
    }
    
    public function getModelName()
    {
        return $this->modelName;
    }

    public function getDictionaryName()
    {
        return $this->dictionaryName;
    }

    public function getIdKey()
    {
        return $this->idKey;
    }

    public function getNameFields()
    {
        return $this->nameFields;
    }

    public function getSeparator()
    {
        return $this->separator;
    }

    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
    }

    public function setDictionaryName($dictionaryName)
    {
        $this->dictionaryName = $dictionaryName;
    }

    public function setIdKey($idKey)
    {
        $this->idKey = $idKey;
    }

    public function setNameFields($nameFields)
    {
        $this->nameFields = $nameFields;
    }

    public function setSeparator($separator)
    {
        $this->separator = $separator;
    }
    
    public function getWhere()
    {
        return $this->where;
    }

    public function setWhere($where)
    {
        $this->where = $where;
    }
        
    public function getDictionary()
    {
        $name = $this->getDictionaryName();
        $return = [];
        
        if (!empty($name)) {
            // jeśli określono parametr name to pobieranie wartości słownikowych na podstawie wstrzykniętego callable
            $return = $this->getNamedDictionary();
        } else {
            $model = $this->getModel();
            $idKey = $this->getIdKey();
            $nameFields = $this->getNameFields();
            $where = $this->getWhere();
            $separator = $this->getSeparator();

            $select = $model->select();

            if (!empty($where)) {
                $select->where($where);
            }

            $data = $model->fetchAll($select);

            foreach ($data as $row) {
                $name = null;

                foreach ($nameFields as $nameField) {
                    $name .= $row->{$nameField} . $separator;
                }

                $return[$row->{$idKey}] = trim($name, $separator);
            }
        }
        
        return $return;
    }
    
    /**
     * @return \Base\Db\Table\AbstractModel
     */
    protected function getModel()
    {
        $model = $this->getServiceManager()->get($this->getModelName());
        
        return $model;
    }
    
    /**
     * Pobierz customowy słownik dla określonego name
     * @return array
     */
    protected function getNamedDictionary()
    {
        $return = [];
        $callable = $this->getNamedDictionaryCallable();
        
        if (empty($callable)) {
            throw new \Exception("Nie koreślono callable dla customowego słownika z określonym name");
        }
        
        if (is_array($callable)) {
            $return = call_user_func($callable, $this);
        } else {
            $return = $callable($this);
        }
        
        return $return;
    }
}
