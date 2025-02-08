<?php
namespace Base\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class Dictionary extends AbstractHelper
{
    use \Base\Traits\ServiceManagerTrait;
    
    public function __invoke(\Base\Db\Table\AbstractEntity $entity, $columnName, $prototype = null)
    {
        $value = $entity->{$columnName};
        $dictionaries = $entity->getDictionaries();
        
        if (empty($dictionaries) && $prototype instanceof \Base\Db\Table\AbstractEntity) {
            $dictionaries = $prototype->getDictionaries();
        }
        
        $dictionaryValues = [];
        
        if (array_key_exists($columnName, $dictionaries)) {
            // skonfigurowano słownik dla tej kolumny
            $columnDictionary = $dictionaries[$columnName];
            
            $dictionary = $this->getServiceManager()->get(\Base\Dictionary::class);
            /* @var $dictionary \Base\Dictionary */
            $dictionary->init();
            
            if (!empty($columnDictionary['name'])) {
                $dictionary->setDictionaryName($columnDictionary['name']);
            }

            if (!empty($columnDictionary['id'])) {
                $dictionary->setIdKey($columnDictionary['id']);
            }

            if (!empty($columnDictionary['modelName'])) {
                $dictionary->setModelName($columnDictionary['modelName']);
            }

            if (!empty($columnDictionary['nameFields'])) {
                $dictionary->setNameFields($columnDictionary['nameFields']);
            }

            if (!empty($columnDictionary['where'])) {
                $dictionary->setWhere($columnDictionary['where']);
            }

            if (!empty($columnDictionary['separator'])) {
                $dictionary->setSeparator($columnDictionary['separator']);
            }
            
            $dictionaryValues = $dictionary->getDictionary();
        }

        return $dictionaryValues[$value] ?? $value;
    }
}
