<?php

declare(strict_types=1);
require_once dirname(__DIR__) . '/src/Base/Form/AbstractForm.php';
require_once dirname(__DIR__) . '/src/Base/Paginator.php';

final class PaginatorFilterTest extends \PHPUnit\Framework\TestCase
{
    private function form(bool $search): \Base\Form\AbstractForm
    {
        $form = new class extends \Base\Form\AbstractForm {};
        $form->setCsrfValidation(false);
        $form->add(['name'=>'id','type'=>'number','attributes'=>['required'=>true,'min'=>1]]);
        $form->add(['name'=>'date','type'=>'date','attributes'=>['required'=>true]]);
        $form->add(['name'=>'status','type'=>'select','options'=>['value_options'=>['a'=>'Active']], 'attributes'=>['required'=>true,'multiple'=>true]]);
        $form->add(['name'=>'token','type'=>'hidden','attributes'=>['required'=>true]]);
        $form->initInputFilter();
        $form->getInputFilter()->get('id')->setContinueIfEmpty(true);
        if ($search) {
            $services = new \Laminas\ServiceManager\ServiceManager(['services'=>['filter'=>$form]]);
            $paginator = new class($services) extends \Base\Paginator {
                public function __construct(private $services) { parent::__construct(); }
                public function getServiceManager(): \Laminas\ServiceManager\ServiceManager { return $this->services; }
            };
            $paginator->setFilterFormName('filter');
            self::assertSame($form, $paginator->getFilterForm());
        }
        return $form;
    }

    public function testMissingAndExplicitlyEmptyCriteriaAreOptional(): void
    {
        foreach ([[], ['id'=>'','date'=>'','status'=>[]], ['id'=>null,'date'=>null,'status'=>null]] as $data) {
            $form=$this->form(true); $form->setData($data+['token'=>'valid']);
            self::assertTrue($form->isValid(),json_encode($form->getMessages()));
            self::assertNull($form->get('id')->getAttribute('required'));
            self::assertFalse($form->getInputFilter()->get('id')->continueIfEmpty());
        }
    }

    public function testFilledCriteriaStillValidateAndZeroIsNotEmpty(): void
    {
        foreach ([['id'=>'0'],['id'=>'abc'],['id'=>['1']],['date'=>'invalid'],['status'=>['unknown']]] as $data) {
            $form=$this->form(true); $form->setData($data+['token'=>'valid']);
            self::assertFalse($form->isValid(),json_encode($data));
        }
        $form=$this->form(true);$form->setData(['id'=>'1','date'=>'2026-10-23','status'=>['a'],'token'=>'valid']);
        self::assertTrue($form->isValid(),json_encode($form->getMessages()));
    }

    public function testOrdinaryFormsAndSecurityFieldsKeepRequirements(): void
    {
        $form=$this->form(false);$form->setData(['token'=>'valid']);
        self::assertFalse($form->isValid());
        self::assertTrue($form->getInputFilter()->get('id')->isRequired());
        $form=$this->form(true);$form->setData([]);
        self::assertFalse($form->isValid());
        self::assertSame(['token'],array_keys($form->getMessages()));
    }
}
