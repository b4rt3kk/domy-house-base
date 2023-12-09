<?php

declare(strict_types=1);

namespace Base\Form\Element;

use Laminas\Filter\StringTrim;
use Laminas\Form\Element;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\ValidatorInterface;

class Nip extends Element implements InputProviderInterface
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
    protected $nipValidator;

    /**
     * Get primary validator
     */
    public function getValidator(): ValidatorInterface
    {
        if (null === $this->validator) {
            $nipValidator = $this->getNipValidator();
            
            $this->validator = $nipValidator;
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

    public function getNipValidator(): ValidatorInterface
    {
        if (null === $this->nipValidator) {
            $this->nipValidator = new \Base\Validator\Nip();
        }
        
        return $this->nipValidator;
    }

    /**
     * Sets the email validator to use for multiple or single
     * email addresses.
     *
     * @return $this
     */
    public function setNipValidator(ValidatorInterface $validator)
    {
        $this->nipValidator = $validator;
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

