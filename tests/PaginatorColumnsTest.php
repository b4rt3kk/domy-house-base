<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/Base/Db/Table/AbstractEntity.php';
require_once dirname(__DIR__) . '/src/Base/Paginator.php';

use PHPUnit\Framework\TestCase;
use Laminas\Session\Container;
use Laminas\Session\SessionManager;
use Laminas\Session\Storage\ArrayStorage;

final class PaginatorColumnsTest extends TestCase
{
    protected function setUp(): void
    {
        Container::setDefaultManager(new SessionManager(null, new ArrayStorage()));
    }

    private function paginator(string $id = 'columns_test'): \Base\Paginator
    {
        $entity = new class extends \Base\Db\Table\AbstractEntity {};
        $entity->setHeadersMapping([
            'name' => ['title' => 'Nazwa'],
            'optional' => ['title' => 'Dodatkowe', 'defaultVisible' => false],
            'secret' => ['title' => 'Sekret', 'selectable' => false],
            'outside' => ['title' => 'Poza zakresem'],
        ]);
        $paginator = new class($entity) extends \Base\Paginator {
            private $entity;
            public function __construct($entity) { parent::__construct(); $this->entity = $entity; }
            protected function getModel($modelName = null) {
                return new class($this->entity) {
                    private $entity;
                    public function __construct($entity) { $this->entity = $entity; }
                    public function getEntity() { return $this->entity; }
                };
            }
        };
        $paginator->setPaginatorId($id);
        $paginator->setHeaders(['name', 'optional', 'secret']);
        return $paginator;
    }

    public function testDefaultsAndAllowlist(): void
    {
        $paginator = $this->paginator();
        self::assertSame(['name', 'optional'], array_keys($paginator->getColumnOptions()));
        self::assertSame(['name'], $paginator->getVisibleHeaders());
        $container = new Container(\Base\Paginator::getColumnPreferencesStorageName('columns_test'));
        $container->selection = ['optional', 'secret', 'name', 'outside', 'optional'];
        self::assertSame(['name', 'optional'], $paginator->getVisibleHeaders());
        self::assertSame(['name'], $this->paginator('other_list')->getVisibleHeaders());
        self::assertSame($paginator->getColumnSelectionToken(), $paginator->getColumnSelectionToken());
        self::assertNotSame($paginator->getColumnSelectionToken(), $this->paginator('other_list')->getColumnSelectionToken());
    }

    public function testEmptySelectionResetAndFilterIsolation(): void
    {
        $paginator = $this->paginator();
        $container = new Container(\Base\Paginator::getColumnPreferencesStorageName('columns_test'));
        $container->selection = [];
        self::assertSame(['name'], $paginator->getVisibleHeaders());
        $paginator->setFilterData(['name' => 'Dom']);
        $paginator->clearFilterData();
        self::assertSame(['name'], $paginator->getVisibleHeaders());
        $container->selection = ['secret', 'outside'];
        self::assertSame(['name'], $paginator->getVisibleHeaders());
        $container->selection = null;
        self::assertSame(['name'], $paginator->getVisibleHeaders());
        $paginator->getViewConfig()->setListPartial('custom/cards');
        self::assertSame([], $paginator->getColumnOptions());
        self::assertSame(['name', 'optional', 'secret'], $paginator->getVisibleHeaders());
    }
    public function testTableDoesNotRenderUnselectedOrForbiddenFields(): void
    {
        $paginator = $this->paginator();
        $entity = new class extends \Base\Db\Table\AbstractEntity {};
        $entity->setHeadersMapping(['name' => ['title' => 'Nazwa']]);
        $rows = new \Laminas\Db\ResultSet\ResultSet();
        $rows->setArrayObjectPrototype($entity);
        $rows->initialize([['name' => 'Widoczny tekst', 'optional' => 'Ukryty tekst', 'secret' => 'Sekret']]);
        $view = new \Laminas\View\Renderer\PhpRenderer();
        $view->setResolver(new \Laminas\View\Resolver\TemplateMapResolver([
            'base/table' => dirname(__DIR__) . '/src/view/partials/table.phtml',
        ]));
        $html = $view->render('base/table', ['data' => $rows, 'headers' => $paginator->getVisibleHeaders(), 'responsive' => true]);
        self::assertStringContainsString('Widoczny tekst', $html);
        self::assertStringNotContainsString('Ukryty tekst', $html);
        self::assertStringNotContainsString('Sekret', $html);
    }

}
