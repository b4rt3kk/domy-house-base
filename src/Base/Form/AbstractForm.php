<?php
namespace Base\Form;

abstract class AbstractForm extends \Laminas\Form\Form
{
    protected $serviceManager;
    
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
    
    protected function submit($value = 'Submit', $options = [])
    {
        $name = array_key_exists('name', $options) ? $options['name'] : 'submit_form';
        $attributes = [
            'class' => 'btn btn-primary',
            'value' => $value,
        ];
        
        if (isset($options['attributes'])) {
            $attributes = array_merge($options['attributes'], $attributes);
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
        
        $this->submit($value, $options);
    }
}
