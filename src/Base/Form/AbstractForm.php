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
    
    protected $csrfValidation = true;
    
    protected $csrfTimeout = 600;
    
    protected $specialElementsTypes = [
        \Laminas\Form\Element\Csrf::class,
    ];
    
    public function getIsInitialized()
    {
        return $this->isInitialized;
    }

    public function setIsInitialized($isInitialized)
    {
        $this->isInitialized = $isInitialized;
    }
    
    public function getCsrfValidation()
    {
        return $this->csrfValidation;
    }

    /**
     * Ustaw flagę walidacji CSRF.
     * Spowoduje to automatyczne dodanie elementu formularza typu csrf.
     * W przypadku używania innego sposobu renderowania formularza niż base/form należy samemu dodać renderowanie tego elementu wewnątrz form.
     * 
     * Info: https://olegkrivtsov.github.io/using-zend-framework-3-book/html/en/Advanced_Usage_of_Forms/Form_Security_Elements.html
     * @param boolean $csrfValidation
     */
    public function setCsrfValidation($csrfValidation)
    {
        $this->csrfValidation = $csrfValidation;
    }
    
    public function getCsrfTimeout()
    {
        return $this->csrfTimeout;
    }

    public function setCsrfTimeout($csrfTimeout)
    {
        $this->csrfTimeout = $csrfTimeout;
    }
    
    /**
     * Pobierz typy specjalnych elementów formularza
     * @return array
     */
    public function getSpecialElementsTypes()
    {
        return $this->specialElementsTypes;
    }

    public function setSpecialElementsTypes($specialElementsTypes)
    {
        $this->specialElementsTypes = $specialElementsTypes;
    }
    
    /**
     * Pobierz specjalne elementy formularza
     * @return \Laminas\Form\Element[]
     */
    public function getSpecialElements()
    {
        $return = [];
        $specialElementsTypes = $this->getSpecialElementsTypes();
        $elements = $this->getElements();
        
        foreach ($elements as $element) {
            if (in_array(get_class($element), $specialElementsTypes)) {
                $return[] = $element;
            }
        }
        
        return $return;
    }
    
    public function init()
    {
        parent::init();
        
        $isCsrfValidation = $this->getCsrfValidation();
        
        if ($isCsrfValidation) {
            $this->add([
                'type' => 'csrf',
                'name' => 'csrf',
                'options' => [
                    'csrf_options' => [
                        'timeout' => $this->getCsrfTimeout(),
                    ],
                ],
            ]);
        }
        
        $this->setIsInitialized(true);
    }
    
    public function initInputFilter()
    {
        $inputFilter = $this->getInputFilter();
        $elements = $this->getElements();
        
        foreach ($elements as $element) {
            /* @var $element \Laminas\Form\Element */
            $isRequired = !empty($element->getAttribute('required'));
            
            $inputFilter->add([
                'name' => $element->getName(),
                'required' => $isRequired,
            ]);
        }
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
