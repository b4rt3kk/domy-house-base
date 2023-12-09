<?php

declare(strict_types=1);

namespace Base\Form\Element;

use Laminas\Filter\StringTrim;
use Laminas\Form\Element;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\ValidatorInterface;

class AccountNumber extends Element implements InputProviderInterface
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = [
        'type' => 'text',
    ];

    /** @var null|ValidatorInterface */
    protected $validator;

    /** @var null|ValidatorInterface */
    protected $accountNumberValidator;

    /**
     * Get primary validator
     */
    public function getValidator(): ValidatorInterface
    {
        if (null === $this->validator) {
            $accountNumberValidator = $this->getAccountNumberValidator();
            
            $this->validator = $accountNumberValidator;
        }

        return $this->validator;
    }

    /**
     * Sets the primary validator to use for this element
     *
     * @return $this
     */
    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
        
        return $this;
    }

    public function getAccountNumberValidator(): ValidatorInterface
    {
        if (null === $this->accountNumberValidator) {
            $this->accountNumberValidator = new \Base\Validator\AccountNumber();
        }
        
        return $this->accountNumberValidator;
    }

    /**
     * Sets the email validator to use for multiple or single
     * email addresses.
     *
     * @return $this
     */
    public function setAccountNumberValidator(ValidatorInterface $validator)
    {
        $this->accountNumberValidator = $validator;
        return $this;
    }

    /**
     * Provide default input rules for this element
     *
     * Attaches an email validator.
     *
     * @return array
     */
    public function getInputSpecification(): array
    {
        return [
            'name'       => $this->getName(),
            'required'   => true,
            'filters'    => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                $this->getValidator(),
            ],
        ];
    }
}

