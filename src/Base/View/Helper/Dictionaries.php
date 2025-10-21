<?php
namespace Base\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Metoda pobiera wszystkie słowniki dostępne dla definicji wiersza \Base\Db\Table\AbstractEntity i zwraca je w postaci tablicy
 */
class Dictionaries extends AbstractHelper
{
    use \Base\Traits\ServiceManagerTrait;
    
    public function __invoke(\Base\Db\Table\AbstractEntity $entity)
    {
        $rowDictionaries = $entity->getDictionaries();
        
        $dictionaries = [];

        foreach ($rowDictionaries as $key => $rowDictionary) {
            $dictionary = $this->getServiceManager()->get(\Base\Dictionary::class);
            $dictionary->init();

            /* @var $dictionary \Base\Dictionary */
            if (!empty($rowDictionary['name'])) {
                $dictionary->setDictionaryName($rowDictionary['name']);
            }

            if (!empty($rowDictionary['id'])) {
                $dictionary->setIdKey($rowDictionary['id']);
            }

            if (!empty($rowDictionary['modelName'])) {
                $dictionary->setModelName($rowDictionary['modelName']);
            }

            if (!empty($rowDictionary['nameFields'])) {
                $dictionary->setNameFields($rowDictionary['nameFields']);
            }

            if (!empty($rowDictionary['where'])) {
                $dictionary->setWhere($rowDictionary['where']);
            }

            if (!empty($rowDictionary['separator'])) {
                $dictionary->setSeparator($rowDictionary['separator']);
            }

            $dictionaries[$key] = $dictionary->getDictionary();
        }
        
        return $dictionaries;
    }
}
