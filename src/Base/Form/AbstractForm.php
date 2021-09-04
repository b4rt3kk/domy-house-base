<?php
namespace Base\Form;

abstract class AbstractForm extends \Laminas\Form\Form
{
    protected $serviceManager;
    
    protected $isInitialized = false;
    
    public function getIsInitialized()
    {
        return $this->isInitialized;
    }

    public function setIsInitialized($isInitialized)
    {
        $this->isInitialized = $isInitialized;
    }
    
    public function init()
    {
        parent::init();
        
        $this->setIsInitialized(true);
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
    
    public function getValues()
    {
        return $this->data;
    }
    
    public function isCancelled($name = 'cancel_form')
    {
        $values = $this->getValues();
        
        return isset($values[$name]);
    }
    
    protected function submit($value = 'Submit', $options = [])
    {
        $name = array_key_exists('name', $options) ? $options['name'] : 'submit_form';
        $attributes = [
            'class' => 'btn btn-primary',
            'value' => $value,
        ];
        
        if (isset($options['attributes'])) {
            $attributes = array_merge($attributes, $options['attributes']);
        }
        
        $config = array_merge($options, [
            'type' => \Laminas\Form\Element\Submit::class,
            'name' => $name,
            'attributes' => $attributes,
        ]);
        
        $this->add($config);
    }
    
    protected function cancel($value = 'Cancel', $options = [])
    {
        $options['name'] = 'cancel_form';
        $options['attributes'] = [
            'class' => 'btn btn-secondary',
        ];
        
        $this->submit($value, $options);
    }
    
    protected function addClearStart($values, $label = '-- wybierz --')
    {
        return ['' => $label] + $values;
    }
}
