<?php

namespace Base\Services\Payments;

abstract class AbstractPayment extends AbstractInput
{
    const STATUS_PAYMENT_NEW = 1;
    const STATUS_PAYMENT_SUCCESS = 2;
    const STATUS_PAYMENT_WAITING = 3;
    const STATUS_PAYMENT_REJECTED = 4;
    const STATUS_PAYMENT_ERROR = 5;
    const STATUS_PAYMENT_SECURITY_BREACH = 6;
    
    const EVENT_UPDATE_PAYMENT = 'update_payment';
    const EVENT_IS_PAYMENT_VALID = 'is_payment_valid';
    
    protected $serviceManager;
    
    protected $paymentModelName;
    /**
     * Dowolny kod identyfikujący metodę płatności
     * @var string
     */
    protected $code;
    /**
     * Wyświetlana klientowi nazwa metody płatności
     * @var string
     */
    protected $name;
    /**
     * Wyświetlany klientowi obrazek metody płatności
     * @var string
     */
    protected $image;
    /**
     * Wyświetlany klientowi opis metody płatności
     * @var string
     */
    protected $description;
    /**
     * Treść przycisku do zatwierdzenia przesyłania płatności do providera danym kanałem
     * @var string
     */
    protected $submitButtonText = 'Wybierz';
    /**
     * URL end-pointa kanału płatności (tam gdzie mają trafiać rządania obsługi danego zakupu)
     * @var string
     */
    protected $tagetUrl;
    /**
     * Jaką metodą należy przesłać dane do end-pointa providera płatności
     * @var string
     */
    protected $submitMethod = 'POST';
    /**
     * Id kanału płatności. Wartość pomocnicza, tak by wiedzieć z którego kanału następuje płatność
     * @var integer
     */
    protected $idChannel;
    
    protected $config = [];
    
    protected $params = [];
    
    protected $paymentConfirmationData = [];
    
    protected $events = [];
    
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
    
    public function addEvent($name, $event)
    {
        $this->events[$name] = $event;
    }
    
    public function getEvent($name)
    {
        return array_key_exists($name, $this->events) ? $this->events[$name] : null;
    }
    
    public function callEvent($name)
    {
        $event = $this->getEvent($name);
        
        if (empty($event)) {
            throw new \Exception(sprintf("Zdarzenie %s nie zostało skonfigurowane", $name));
        }
        
        if (is_array($event)) {
            call_user_func($event, $this);
        } else {
            $event($this);
        }
        
    }
    
    public function getIdChannel()
    {
        return $this->idChannel;
    }

    public function setIdChannel($idChannel)
    {
        $this->idChannel = $idChannel;
    }
    
    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }
    
    public function getConfig()
    {
        return $this->config;
    }

    public function setConfig($config)
    {
        foreach ($config as $name => $value) {
            $this->setConfigValue($name, $value);
        }
    }
    
    /**
     * Skonfiguruj wartości dla istniejących parametrów klasy
     * @param string $name
     * @param mixed $value
     */
    public function setConfigValue($name, $value)
    {
        $normalizedName = $this->getNormalizedName($name);
        $methodName = 'set' . $normalizedName;
        
        if (method_exists($this, $methodName)) {
            $this->{$methodName}($value);
        }
        
        $this->config[$name] = $value;
    }
    
    public function getConfigValue($name, $default = null)
    {
        $data = $this->getConfig();
        
        return array_key_exists($name, $data) ? $data[$name] : $default;
    }
    
    public function getParams()
    {
        return $this->params;
    }
    
    public function setParams($params)
    {
        $this->params = $params;
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }
    
    public function getSubmitButtonText()
    {
        return $this->submitButtonText;
    }

    public function setSubmitButtonText($submitButtonText)
    {
        $this->submitButtonText = $submitButtonText;
    }
    
    /**
     * Pobierz adres URL na który należy przesłać dane płatności do providera
     * @return string
     */
    public function getTagetUrl()
    {
        return $this->tagetUrl;
    }

    public function setTagetUrl($tagetUrl)
    {
        $this->tagetUrl = $tagetUrl;
    }
    
    public function getSubmitMethod()
    {
        return $this->submitMethod;
    }

    public function setSubmitMethod($submitMethod)
    {
        $this->submitMethod = $submitMethod;
    }
    
    public function getPaymentConfirmationData()
    {
        return $this->paymentConfirmationData;
    }

    public function setPaymentConfirmationData($paymentConfirmationData)
    {
        $this->paymentConfirmationData = $paymentConfirmationData;
    }
    
    public function getPaymentModelName()
    {
        return $this->paymentModelName;
    }

    public function setPaymentModelName($paymentModelName)
    {
        $this->paymentModelName = $paymentModelName;
    }
    
    public function getParamsValues()
    {
        $return = [];
        $params = $this->getParams();
        
        foreach ($params as $param) {
            $normalizedName = $this->getNormalizedName($param);
            $methodName = 'get' . $normalizedName;
            
            if (method_exists($this, $methodName)) {
                $return[$param] = $this->{$methodName}();
            }
        }
        
        return $return;
    }
    
    /**
     * Wygeneruj listę ukrytych [hidden] elementów formularza z przypisanymi nazwami i wartościami, tak by można je było przesłać POST do providera kanału płatności
     * @return string
     */
    public function generateFormInputsHtml() 
    {
        $html = null;
        $params = $this->getParamsValues();
        
        foreach ($params as $name => $value) {
            if ($value !== null) {
                $html .= '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
            }
        }
        
        return $html;
    }
    
    /**
     * Pobierz obiekt modelu, w którym zapisywane są płatności
     * @return \Base\Db\Table\AbstractModel
     * @throws \Exception
     */
    public function getPaymentModel()
    {
        $serviceManager = $this->getServiceManager();
        $modelName = $this->getPaymentModelName();
        
        $model = $serviceManager->get($modelName);
        
        if (!$model instanceof \Base\Db\Table\AbstractModel) {
            throw new \Exception(sprintf("Model musi dziedziczyć po %s", \Base\Db\Table\AbstractModel::class));
        }
        
        return $model;
    }
    
    abstract public function isLandingPagePaymentSuccess($params);
    
    /**
     * Zaktualizuj dane dotyczące płatności
     */
    abstract public function updatePaymentData();
    
    /**
     * Czynności do wykonania po aktualizacji odebranych danych płatności
     */
    abstract public function afterConfirmationDataRecieved();    
}
