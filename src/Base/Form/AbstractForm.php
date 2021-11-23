<?php
namespace Base\Form;

abstract class AbstractForm extends \Laminas\Form\Form
{
    const UPLOAD_ERR_OK = 0;
    const UPLOAD_ERR_INI_SIZE = 1;
    const UPLOAD_ERR_FORM_SIZE = 2;
    const UPLOAD_ERR_PARTIAL = 3;
    const UPLOAD_ERR_NO_FILE = 4;
    const UPLOAD_ERR_NO_TMP_DIR = 6;
    const UPLOAD_ERR_CANT_WRITE = 7;
    const UPLOAD_ERR_EXTENSION = 8;
    
    protected $phpFileUploadErrors = [
        self::UPLOAD_ERR_OK => 'There is no error, the file uploaded with success',
        self::UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        self::UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        self::UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
        self::UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        self::UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        self::UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        self::UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
    ];
    
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
    
    public function getPhpFileUploadErrors()
    {
        return $this->phpFileUploadErrors;
    }

    public function setPhpFileUploadErrors($phpFileUploadErrors)
    {
        $this->phpFileUploadErrors = $phpFileUploadErrors;
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
    
    /**
     * Metoda nadpisuje domyślną metodę ustawiającą dane dla formularza.
     * Rozszerza sprawdzanie dla elementu typu File, gdzie w przypadku nie przesłania go w formularzu i tak były sprawdzane validatory.
     * Dlatego w przypadku nie przesłania pliku dane pliku są w ogóle usuwane z tablicy wejściowej.
     * @param iterable $data
     * @return \Laminas\Form\Form
     */
    public function setData(iterable $data)
    {
        $elements = $this->getElements();

        foreach ($elements as $element) {
            /* @var $element \Laminas\Form\Element */
            $name = $element->getName();
            $isMultiple = false;
            
            if (strpos($name, '[]') === strlen($name) - 2) {
                // jest to element tablicowy
                $isMultiple = true;
                // wycięcie z nazwy elementu nazwy tablicowej
                $name = rtrim($name, '[]');
            }

            if ($element instanceof \Laminas\Form\Element\File) {
                $filesData = $data[$name];
                
                if (!$isMultiple) {
                    // jeśli nie jest to tablica plików to przerobienie na tablicę
                    $filesData = [$data[$name]];
                }
                
                foreach ($filesData as $key => $values) {
                    if ($values['error'] === self::UPLOAD_ERR_NO_FILE) {
                        // w przypadku gdy w ogóle nie przesłano pliku usunięcie jego danych z tablicy przesłanych plików
                        unset($filesData[$key]);
                    }
                }
                
                // żaden plik nie został przesłany
                if (empty($filesData)) {
                    // usunięcie z tablicy z przesłanymi danymi
                    unset($data[$name]);
                }
            }
        }
        
        return parent::setData($data);
    }

    protected function getUploadFileErrorExplained($errorNo)
    {
        $phpErrors = $this->getPhpFileUploadErrors();
        
        return $phpErrors[$errorNo];
    }
    
    protected function button($name, $value, $options = [])
    {
        $options['name'] = $name;
        $options['attributes'] = [
            'class' => 'btn btn-secondary',
        ];

        $this->submit($value, $options);
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
