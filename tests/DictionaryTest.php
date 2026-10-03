<?php

declare(strict_types=1);

use Base\Db\Table\AbstractModel;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Platform\Postgresql;
use Laminas\Db\Sql\Select;
use Laminas\Db\TableGateway\TableGateway;
use PHPUnit\Framework\TestCase;

// Use this checkout's implementation with the consuming application's autoloader.
require_once dirname(__DIR__) . '/src/Base/Dictionary.php';

final class DictionaryTest extends TestCase
{
    private function dictionary(): Base\Dictionary
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->method('getAttribute')->with(PDO::ATTR_DRIVER_NAME)->willReturn('pgsql');
        $pdo->method('quote')->willReturnCallback(static fn ($value) => "'" . str_replace("'", "''", (string) $value) . "'");
        $adapter = $this->createMock(Adapter::class);
        $adapter->method('getPlatform')->willReturn(new Postgresql($pdo));
        $gateway = $this->createMock(TableGateway::class);
        $gateway->method('getAdapter')->willReturn($adapter);
        $model = $this->createMock(AbstractModel::class);
        $model->method('getTableGateway')->willReturn($gateway);
        $model->method('select')->willReturnCallback(static fn () => new Select('cities'));
        $model->method('fetchAll')->willReturnCallback(static function (Select $select) {
            $parameters = $select->getRawState('where')->getExpressionData()[0][1];
            $id = (int) $parameters[1];
            return new ArrayIterator([(object) ['id' => $id, 'name' => 'Miasto ' . $id]]);
        });
        return new class($model) extends Base\Dictionary {
            private array $cache = [];
            public function __construct(private AbstractModel $model) { $this->init(); $this->setNameFields(['name']); }
            protected function getDictionaryModel() { return $this->model; }
            protected function getStorage() {
                return new class($this->cache) {
                    public function __construct(private array &$cache) {}
                    public function hasItem($key) { return array_key_exists($key, $this->cache); }
                    public function getItem($key) { return $this->cache[$key]; }
                    public function addItem($key, $value) { $this->cache[$key] = $value; }
                };
            }
        };
    }

    public function testFilteredLabelsWorkWithApiWarningHandlerAndSeparateCacheEntries(): void
    {
        $dictionary = $this->dictionary();
        set_error_handler(static function ($severity, $message, $file, $line) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        });
        try {
            $dictionary->setWhere(['id' => [1]]);
            self::assertSame([1 => 'Miasto 1'], $dictionary->getDictionary());
            $dictionary->setWhere(['id' => [2]]);
            self::assertSame([2 => 'Miasto 2'], $dictionary->getDictionary());
            $dictionary->setWhere(['id' => [1]]);
            self::assertSame([1 => 'Miasto 1'], $dictionary->getDictionary());
        } finally {
            restore_error_handler();
        }
    }

    public function testNamedDictionaryDoesNotRequireDatabaseModel(): void
    {
        $dictionary = $this->dictionary();
        $dictionary->setDictionaryName('yes_no');
        $dictionary->setNamedDictionaryCallable(static fn () => [0 => 'Nie', 1 => 'Tak']);
        self::assertSame([0 => 'Nie', 1 => 'Tak'], $dictionary->getDictionary());
    }
}
