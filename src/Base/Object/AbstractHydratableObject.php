<?php
namespace Base\Object;

abstract class AbstractHydratableObject
{
    protected \Base\Enums\Config\SerializationType $serializationType = \Base\Enums\Config\SerializationType::Camelcase;
    
    private array $objectRawData = [];
    
    /**
     * Pobierz typ serializacji danych tego obiektu na dane tablicowe w przypadku zastosowania metody toArray() 
     * lub właściwości obiektu w przypadku zastosowania metody toDataObject()
     * @return \Base\Enums\Config\SerializationType
     */
    public function getSerializationType(): \Base\Enums\Config\SerializationType
    {
        return $this->serializationType;
    }

    /**
     * Ustaw typ serializacji danych tego obiektu na dane tablicowe w przypadku zastosowania metody toArray() 
     * lub właściwości obiektu w przypadku zastosowania metody toDataObject()
     * @param \Base\Enums\Config\SerializationType $serializationType
     * @return void
     */
    public function setSerializationType(\Base\Enums\Config\SerializationType $serializationType): void
    {
        $this->serializationType = $serializationType;
    }
    
    /**
     * Pobierz surowe dane tego obiektu
     * @return array
     */
    public function getObjectRawData(): array
    {
        return $this->objectRawData;
    }

    /**
     * Ustaw surowe dane tego obiektu
     * @param array $objectRawData
     * @return void
     */
    public function setObjectRawData(array $objectRawData): void
    {
        $this->objectRawData = $objectRawData;
    }
    
    public function hydrate(array $data)
    {
        // na początku przypisanie wartości raw
        $this->setObjectRawData($data);
        
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $methodName = 'set' . ucfirst($this->getSerializedName($key));
                
                if (method_exists($this, $methodName)) {
                    $this->{$methodName}($value);
                }
            }
        }
    }
    
    public function toArray()
    {
        
    }
    
    public function toDataObject()
    {
        
    }
    
    protected function getSerializedName($unserializedName)
    {
        $serializationType = $this->getSerializationType();
        $name = null;
        
        switch ($serializationType) {
            case \Base\Enums\Config\SerializationType::Camelcase:
                // wejściowa nazwa rozdzielona jest wielką literą
                // nic nie robimy z nazwą ponieważ klasa obsługuje domyślnie taki właśnie format
                $name = $unserializedName;
                break;
            case \Base\Enums\Config\SerializationType::Underscore:
                // wejściowa nazwa rozdzielona jest podkreśleniami
                $chunks = explode('_', $unserializedName);
                $name = implode('', array_filter($chunks, function($chunk) {
                    return ucfirst($chunk);
                }));
                break;
        }
        
        // rozdzielenie nazwy na podstawie podkreśleń
        
        return $unserializedName;
    }
    
    protected function getUnserializedName($serializedName)
    {
        $serializationType = $this->getSerializationType();
        
        return $serializedName;
    }
}
