<?php

namespace BaseTest;

use Base\Form\MassEditForm;
use Base\MassAction\Selection;
use PHPUnit\Framework\TestCase;

final class MassActionsTest extends TestCase
{
    private $sessions;

    protected function setUp(): void
    {
        $this->sessions = \Laminas\Session\Container::getDefaultManager();
        $manager = $this->getMockBuilder(\Laminas\Session\SessionManager::class)
            ->setConstructorArgs([null, new \Laminas\Session\Storage\ArrayStorage()])->onlyMethods(['start'])->getMock();
        \Laminas\Session\Container::setDefaultManager($manager);
    }

    protected function tearDown(): void { \Laminas\Session\Container::setDefaultManager($this->sessions); }

    public function testSelectionIsBoundedStrictAndDeduplicated(): void
    {
        self::assertSame(['2', '10'], Selection::ids('10,2,10'));
        foreach (['', '0', '-1', '01', '1,', '1, 2', '1e2', ['1'], str_repeat('9', 20), implode(',', range(1,101))] as $bad) {
            try { Selection::ids($bad); self::fail('Accepted invalid IDs'); }
            catch (\InvalidArgumentException $e) { self::assertNotEmpty($e->getMessage()); }
        }
    }

    private function form(bool $csrf = false): MassEditForm
    {
        $form = new MassEditForm('mass-test');
        $form->setCsrfValidation($csrf);
        $form->setFields([
            'status' => ['element' => ['type' => \Laminas\Form\Element\Select::class,
                'options' => ['label' => 'Status', 'value_options' => [1 => 'Active', 2 => 'Inactive']]],
                'input' => ['validators' => [['name' => \Laminas\Validator\InArray::class, 'options' => ['haystack' => [1,2]]]]]],
            'date' => ['nullable' => true, 'element' => ['type' => \Laminas\Form\Element\Text::class]],
        ]);
        $form->init(); $form->initInputFilter();
        return $form;
    }

    public function testOnlyExplicitChangesAreReturnedAndUnselectedInvalidValuesIgnored(): void
    {
        $form = $this->form();
        $form->setData(['ids'=>'1,2','change_status'=>'set','status'=>'2','change_date'=>'keep','date'=>['bad'], 'ghost'=>1]);
        self::assertTrue($form->isValid(), json_encode($form->getMessages()));
        self::assertSame(['status'=>'2'], $form->getChanges());
        $form = $this->form();
        $form->setData(['ids'=>'1','change_status'=>'keep','status'=>'invalid','change_date'=>'clear','date'=>'ignored']);
        self::assertTrue($form->isValid(), json_encode($form->getMessages()));
        self::assertSame(['date'=>null], $form->getChanges());
    }

    public function testInvalidModesNoChangesAndMissingValuesFail(): void
    {
        foreach ([['change_status'=>'keep'], ['change_status'=>'set','status'=>''],
            ['change_status'=>'set','status'=>'999'], ['change_status'=>'clear'], ['change_status'=>['set']]] as $data) {
            $form=$this->form(); $form->setData($data+['ids'=>'1','change_date'=>'keep']);
            self::assertFalse($form->isValid(), json_encode($data));
        }
    }

    public function testCsrfRemainsRequiredAfterInitialization(): void
    {
        foreach ([null, 'forged'] as $token) {
            $form=$this->form(true); $form->setData(['ids'=>'1','change_status'=>'set','status'=>'1','change_date'=>'keep','csrf'=>$token]);
            self::assertFalse($form->isValid());
            self::assertNotEmpty($form->get('csrf')->getMessages());
        }
        $form=$this->form(true); $token=$form->get('csrf')->getValue();
        $form->setData(['ids'=>'1','change_status'=>'set','status'=>'1','change_date'=>'keep','csrf'=>$token]);
        self::assertTrue($form->isValid(),json_encode($form->getMessages()));
    }

    private function model(array $rows, bool $failWrite = false): array
    {
        $connection=$this->createMock(\Laminas\Db\Adapter\Driver\ConnectionInterface::class);
        $driver=$this->createMock(\Laminas\Db\Adapter\Driver\DriverInterface::class);
        $driver->method('getConnection')->willReturn($connection);
        $adapter=$this->createMock(\Laminas\Db\Adapter\Adapter::class);
        $adapter->method('getDriver')->willReturn($driver);
        $gateway=$this->createMock(\Laminas\Db\TableGateway\TableGateway::class);
        $gateway->method('getAdapter')->willReturn($adapter);
        $model=new class($gateway) extends \Base\Db\Table\AbstractModel {
            public function __construct($gateway) { $this->setTableGateway($gateway); }
            public array $rows=[];
            public array $writes=[];
            public array $events=[];
            public bool $failWrite=false;
            public function getEntity() { return new class extends \Base\Db\Table\AbstractEntity {
                protected $allowMassActions=true;
                protected $massEditableFields=['status'=>[]];
            }; }
            public function fetchMassRows(array $ids,array $contextColumns=[],bool $lock=false): array {
                if (!$lock) throw new \LogicException('Missing lock');
                $this->events[]='lock';return $this->rows;
            }
            public function update($data,$where) {
                $this->writes[]=$data;
                if ($this->failWrite) throw new \RuntimeException('Database failed');
                return count($this->rows);
            }
            public function clearCache() { $this->events[]='cache'; }
        };
        $model->rows=$rows;$model->failWrite=$failWrite;
        return [$model,$connection];
    }

    public function testAtomicWriteChecksAllRowsAuditsAndInvalidatesAfterCommit(): void
    {
        [$model,$connection]=$this->model([['id'=>1,'ghost'=>false],['id'=>2,'ghost'=>false]]);
        $connection->expects(self::once())->method('beginTransaction');
        $connection->expects(self::once())->method('commit')->willReturnCallback(function () use ($model) { self::assertSame(['lock'],$model->events); });
        $connection->expects(self::never())->method('rollback');
        $checked=[];
        self::assertSame(2,$model->updateMass(['1','2'],['status'=>2],7,static fn()=>true,
            static function($row) use (&$checked,$model) { self::assertSame([],$model->writes);$checked[]=$row['id']; }));
        self::assertSame([1,2],$checked);
        self::assertSame(7,$model->writes[0]['changed_by']);
        self::assertSame(['lock','cache'],$model->events);
        self::assertTrue($model->getUseCache());
    }

    public function testMissingDeletedForbiddenOrInvalidRowsRollbackBeforeWrite(): void
    {
        foreach (['missing','deleted','forbidden','invalid'] as $case) {
            $rows=$case==='missing' ? [['id'=>1]] : [['id'=>1],['id'=>2,'ghost'=>$case==='deleted']];
            [$model,$connection]=$this->model($rows);
            $connection->expects(self::once())->method('rollback');
            $connection->expects(self::never())->method('commit');
            try {
                $model->updateMass(['1','2'],['status'=>2],7,
                    static fn($row)=>!($case==='forbidden' && $row['id']===2),
                    static function($row) use ($case) { if ($case==='invalid' && $row['id']===2) throw new \DomainException('Invalid'); });
                self::fail('Expected rejection');
            } catch (\DomainException $e) { self::assertSame([],$model->writes); }
        }
    }

    public function testWriteFailureRollsBackAndRestoresCacheFlag(): void
    {
        [$model,$connection]=$this->model([['id'=>1]],true);
        $connection->expects(self::once())->method('rollback');
        $connection->expects(self::never())->method('commit');
        try { $model->updateMass(['1'],['status'=>2],7,static fn()=>true,static fn()=>null); self::fail(); }
        catch (\RuntimeException $e) { self::assertTrue($model->getUseCache());self::assertSame(['lock'],$model->events); }
    }

    public function testLockedReadUsesBoundParametersExplicitColumnsAndStableOrder(): void
    {
        $statement=new class extends \Laminas\Db\Adapter\Driver\Pdo\Statement {
            public function execute($parameters=null) { return new \ArrayIterator([['id'=>2],['id'=>10]]); }
        };
        $driver=$this->createMock(\Laminas\Db\Adapter\Driver\DriverInterface::class);
        $driver->method('createStatement')->willReturn($statement);
        $driver->method('getPrepareType')->willReturn('named');
        $driver->method('formatParameterName')->willReturnCallback(static fn($name)=>':'.$name);
        $adapter=new \Laminas\Db\Adapter\Adapter($driver,new \Laminas\Db\Adapter\Platform\Postgresql());
        $gateway=new \Laminas\Db\TableGateway\TableGateway(new \Laminas\Db\Sql\TableIdentifier('items','public'),$adapter);
        $model=new class($gateway) extends \Base\Db\Table\AbstractModel {
            public function __construct($gateway) { $this->setTableGateway($gateway); }
        };
        self::assertSame([['id'=>2],['id'=>10]],$model->fetchMassRows(['10','2'],['valid_from'],true));
        self::assertStringContainsString('"public"."items"',$statement->getSql());
        self::assertStringContainsString('"valid_from"',$statement->getSql());
        self::assertStringContainsString('ORDER BY "id" ASC FOR UPDATE',$statement->getSql());
        self::assertStringNotContainsString('*',$statement->getSql());
        self::assertSame(['2','10'],array_values($statement->getParameterContainer()->getNamedArray()));
    }

    public function testNonNullableFieldCannotBeCleared(): void
    {
        [$model,$connection]=$this->model([]);
        $connection->expects(self::never())->method('beginTransaction');
        $this->expectException(\InvalidArgumentException::class);
        $model->updateMass(['1'],['status'=>null],7,static fn()=>true,static fn()=>null);
    }

    public function testForbiddenColumnsAreRejectedBeforeTransaction(): void
    {
        [$model,$connection]=$this->model([]);
        $connection->expects(self::never())->method('beginTransaction');
        $this->expectException(\InvalidArgumentException::class);
        $model->updateMass(['1'],['created_by'=>8],7,static fn()=>true,static fn()=>null);
    }
}
