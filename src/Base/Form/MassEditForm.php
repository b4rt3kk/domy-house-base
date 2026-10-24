<?php

namespace Base\Form;

/** Field and input specifications must come from trusted model configuration. */
class MassEditForm extends AbstractForm
{
    protected array $fields = [];
    private array $invalidTypes = [];

    public function setFields(array $fields): void { $this->fields = $fields; }

    public function setData(iterable $data)
    {
        $data = is_array($data) ? $data : iterator_to_array($data);
        $this->invalidTypes = [];
        foreach ($this->getElements() as $name => $element) {
            if (isset($data[$name]) && !is_scalar($data[$name])) {
                $data[$name] = '';
                $this->invalidTypes[] = $name;
            }
        }
        return parent::setData($data);
    }

    public function init()
    {
        parent::init();
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-mass-edit', 'true');
        $this->add(['name' => 'ids', 'type' => \Laminas\Form\Element\Hidden::class]);
        foreach ($this->fields as $name => $field) {
            $label = $field['element']['options']['label'] ?? $name;
            $this->add([
                'name' => 'change_' . $name, 'type' => \Laminas\Form\Element\Select::class,
                'options' => ['label' => $label . ' — działanie', 'value_options' => [
                    'keep' => 'Pozostaw bez zmian', 'set' => 'Ustaw wartość',
                ] + (!empty($field['nullable']) ? ['clear' => 'Wyczyść wartość'] : [])],
                'attributes' => ['class' => 'form-select selectpicker-select-filter', 'data-mass-field' => $name],
            ]);
            $this->get('change_' . $name)->setValue('keep');
            $this->add(['name' => $name] + $field['element']);
        }
        $this->submit('Zapisz zmiany');
        $this->cancel('Anuluj');
    }

    public function initInputFilter()
    {
        // Do not call AbstractForm's generic filter: it replaces CSRF specifications.
        $filter = $this->getInputFilter();
        $filter->add(['name' => 'ids', 'required' => true, 'validators' => [[
            'name' => \Laminas\Validator\Callback::class,
            'options' => ['callback' => static function ($value): bool {
                try { \Base\MassAction\Selection::ids($value); return true; }
                catch (\InvalidArgumentException $e) { return false; }
            }],
        ]]]);
        foreach ($this->fields as $name => $field) {
            // Replace explicit rules: merging retains DateTimeLocal's seconds-only validator.
            if (isset($field['input']) && $filter->has($name)) $filter->remove($name);
            $filter->add(['name' => $name, 'required' => false, 'allow_empty' => true] + ($field['input'] ?? []));
        }
        $this->setPreferFormInputFilter(true);
    }

    public function isValid(): bool
    {
        $values = $this->getValues();
        $validation = ['ids'];
        if ($this->getCsrfValidation()) $validation[] = 'csrf';
        $changed = false;
        foreach ($this->fields as $name => $field) {
            $validation[] = 'change_' . $name;
            $mode = $values['change_' . $name] ?? 'keep';
            if ($mode === 'set') {
                $validation[] = $name;
                $this->getInputFilter()->get($name)->setRequired(true)->setAllowEmpty(false);
            }
            if ($mode !== 'keep') $changed = true;
        }
        $this->setValidationGroup($validation);
        $valid = parent::isValid();
        foreach (array_intersect($this->invalidTypes, $validation) as $name) {
            $this->get($name)->setMessages(['Podaj pojedynczą poprawną wartość.']);
            $valid = false;
        }
        if (!$changed) {
            $this->get('ids')->setMessages(['Wybierz co najmniej jedno pole do zmiany.']);
            $valid = false;
        }
        return $valid;
    }

    /** Call only after successful validation. Unselected fields are never included. */
    public function getChanges(): array
    {
        $data = $this->getData();
        $changes = [];
        foreach ($this->fields as $name => $field) {
            if (($data['change_' . $name] ?? 'keep') === 'set') $changes[$name] = $data[$name];
            if (($data['change_' . $name] ?? 'keep') === 'clear' && !empty($field['nullable'])) $changes[$name] = null;
        }
        return $changes;
    }
}
