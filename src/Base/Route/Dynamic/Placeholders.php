<?php
namespace Base\Route\Dynamic;

class Placeholders
{
    /**
     * @var \Base\Route\Dynamic\Placeholder[]
     */
    protected $data = [];
    
    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }
    
    public function addPlaceholder(Placeholder $placeholder)
    {
        $this->data[] = $placeholder;
    }
    
    public function hasPlaceholder(Placeholder $placeholder)
    {
        $return = false;
        $data = $this->getData();
        
        foreach ($data as $row) {
            /* @var $row Placeholder */
            if ($row->getName() === $placeholder->getName()) {
                $return = true;
                
                break;
            }
        }
        
        return $return;
    }
    
    /**
     * Pobierz obiekt placeholdera na podstawie jego nazwy
     * @param string $name
     * @return \Base\Route\Dynamic\Placeholder
     */
    public function getPlaceholderByName($name)
    {
        $return = null;
        $placeholders = $this->getData();
        
        foreach ($placeholders as $placeholder) {
            /* @var $placeholder \Base\Route\Dynamic\Placeholder */
            if ($placeholder->getName() === $name) {
                $return = $placeholder;
                
                break;
            }
        }
        
        return $return;
    }
    
    /**
     * Pobierz obiekt placeholdera na podstawie jego nazwy kodowej
     * @param string $name
     * @return \Base\Route\Dynamic\Placeholder
     */
    public function getPlaceholderByRawName($name)
    {
        $return = null;
        $placeholders = $this->getData();
        
        foreach ($placeholders as $placeholder) {
            /* @var $placeholder \Base\Route\Dynamic\Placeholder */
            if ($placeholder->getRawName() === $name) {
                $return = $placeholder;
                
                break;
            }
        }
        
        return $return;
    }
}
