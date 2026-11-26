<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Base\Services\Auth\AuthAdapter;
use Base\Services\Rbac\RbacRolesManager;
use Base\Services\Csv\Generator;
use Laminas\Db\ResultSet\ResultSet;

foreach (['Auth/AuthAdapter', 'Auth/OAuth/Google/AuthAdapter', 'Rbac/RbacRolesManager', 'Csv/Generator'] as $class) {
    require_once dirname(__DIR__) . '/src/Base/Services/' . $class . '.php';
}

final class P1SecurityTest extends TestCase
{
    public function testVirtualAccountsNeverAuthenticateAndRegularPasswordsAreVerified(): void
    {
        foreach ([[true, 'good', false], [true, 'bad', false], [false, 'bad', false], [false, 'good', true]] as [$virtual, $password, $expected]) {
            $adapter = new class($virtual) extends AuthAdapter {
                public array $events = [];
                public function __construct(private bool $virtual) {}
                protected function getUserByLoginRow($login) { return (object) ['is_virtual' => $this->virtual, 'password' => password_hash('good', PASSWORD_BCRYPT)]; }
                public function setUserRow($row) { $this->userRow = $row; }
                public function callEvent($event) { $this->events[] = $event; }
            };
            $adapter->setLogin('synthetic');
            $adapter->setPassword($password);
            $result = $adapter->authenticate();
            self::assertSame($expected, $result->isValid());
            self::assertSame($expected, in_array(AuthAdapter::EVENT_LOGIN_SUCCESS, $adapter->events, true));
            if (!$expected) self::assertNull($result->getIdentity());
        }
    }

    public function testGoogleRejectsTechnicalLockedAndInactiveAccountsBeforeUpdatingProvider(): void
    {
        foreach ([[true,false,true,false],[false,true,true,false],[false,false,false,false],[false,false,true,true]] as [$virtual,$locked,$active,$allowed]) {
            $model=$this->createMock(Base\Db\Table\AbstractModel::class);
            $model->method('getEntity')->willReturn($this->getMockForAbstractClass(Base\Db\Table\AbstractEntity::class));
            $model->expects($allowed?self::once():self::never())->method('update');
            $adapter=new class($virtual,$locked,$active,$model) extends Base\Services\Auth\OAuth\Google\AuthAdapter {
                public array $events=[];
                public function __construct(private bool $virtual,private bool $locked,private bool $active,private $model) {}
                protected function getClient() { return new class { public function getAccessToken(){return ['access_token'=>'synthetic'];} }; }
                protected function getStorageContainer() { return new stdClass(); }
                protected function getService($client=null) { return (object)['userinfo'=>new class { public function get(){return (object)['id'=>'google-fixture','email'=>'fixture@example.test'];} }]; }
                protected function getUserByLoginRow($login) { return (object)['id'=>7,'is_virtual'=>$this->virtual,'is_locked'=>$this->locked,'is_active'=>$this->active]; }
                public function getModel() { return $this->model; }
                public function setUserRow($row) { $this->userRow=$row; }
                public function callEvent($event) { $this->events[]=$event; }
            };
            $adapter->setRowPostConditions([['condition'=>['is_virtual'=>false]],['condition'=>['is_locked'=>false]],['condition'=>['is_active'=>true]]]);
            $result=$adapter->authenticate();
            self::assertSame($allowed,$result->isValid());
            self::assertSame($allowed,in_array(AuthAdapter::EVENT_LOGIN_SUCCESS,$adapter->events,true));
            if (!$allowed) self::assertNull($result->getIdentity());
        }
    }

    public function testWildcardMatchesTheWholePermissionAndQuotesRegexCharacters(): void
    {
        foreach ([new ArrayObject([], ArrayObject::ARRAY_AS_PROPS), new class extends Base\Db\Table\AbstractEntity {}] as $prototype) {
            $manager = $this->getMockForAbstractClass(RbacRolesManager::class);
            $rows = new ResultSet();
            $rows->setArrayObjectPrototype($prototype);
            $rows->initialize([['name' => 'orders.*'], ['name' => 'a#b[0].*'], ['name' => null]]);
            $manager->method('getRolePermissionsData')->willReturn($rows);
            foreach (['orders.show', 'orders.show.own', 'a#b[0].read'] as $permission) self::assertTrue($manager->isAnyPermissionAllowed(1, $permission), $permission);
            foreach (['unrelated1.action', 'prefix.orders.show', 'ordersXshow', 'a#b0.read', '', "orders.show\n"] as $permission) self::assertFalse($manager->isAnyPermissionAllowed(1, $permission));
        }
    }

    public function testCsvLoadsWithRealParentAndExportsSuppliedAndModelRows(): void
    {
        $generator = new Generator();
        $generator->setHeaders(['name' => 'Nazwa', 'price' => 'Cena']);
        $generator->setData([['name' => 'Test; "A"', 'price' => '12.34']]);
        self::assertSame("Nazwa;Cena\n\"Test; \"\"A\"\"\";12.34\n", $generator->getBody());
        $model = $this->createMock(Base\Db\Table\AbstractModel::class);
        $entity = $this->createMock(Base\Db\Table\AbstractEntity::class);
        $entity->method('getHeadersMapping')->willReturn(['name' => ['title' => 'Nazwa']]);
        $rows = $this->createMock(ResultSet::class);
        $rows->method('getArrayObjectPrototype')->willReturn($entity);
        $rows->method('toArray')->willReturn([['name' => 'Model']]);
        $model->method('fetchAll')->willReturn($rows);
        $services = new Laminas\ServiceManager\ServiceManager(['services' => ['test-model' => $model]]);
        $generator->setServiceManager($services);
        $generator->setData([]);
        $generator->setModelName('test-model');
        self::assertSame("Nazwa\nModel\n", $generator->getBody());
    }
}
