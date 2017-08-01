<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Db\Adapter\Platform;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Platform\Mysql;
use Zend\Db\Adapter\Driver\Mysqli;
use Zend\Db\Adapter\Driver\Pdo;

/**
 * @group integration
 * @group integration-mysql
 */
class MysqlIntegrationTest extends \PHPUnit_Framework_TestCase
{
    public $adapters = [];

    public function testQuoteValueWithMysqli()
    {
        if (!$this->adapters['mysqli'] instanceof \Mysqli) {
            $this->markTestSkipped('MySQL (Mysqli) not configured in unit test configuration file');
        }
        $mysql = new Mysql($this->adapters['mysqli']);
        $value = $mysql->quoteValue('value');
        $this->assertEquals('\'value\'', $value);

        $mysql = new Mysql(new Mysqli\Mysqli(new Mysqli\Connection($this->adapters['mysqli'])));
        $value = $mysql->quoteValue('value');
        $this->assertEquals('\'value\'', $value);
    }

    public function testQuoteValueWithPdoMysql()
    {
        if (!$this->adapters['pdo_mysql'] instanceof \PDO) {
            $this->markTestSkipped('MySQL (PDO_Mysql) not configured in unit test configuration file');
        }
        $mysql = new Mysql($this->adapters['pdo_mysql']);
        $value = $mysql->quoteValue('value');
        $this->assertEquals('\'value\'', $value);

        $mysql = new Mysql(new Pdo\Pdo(new Pdo\Connection($this->adapters['pdo_mysql'])));
        $value = $mysql->quoteValue('value');
        $this->assertEquals('\'value\'', $value);
    }

    public function testFewLimitValueNotMerged()
    {
        if (!$this->adapters['pdo_mysql'] instanceof \PDO) {
            $this->markTestSkipped('MySQL (PDO_Mysql) not configured in unit test configuration file');
        }
        $driver = new Pdo\Pdo(new Pdo\Connection($this->adapters['pdo_mysql']));
        $mysql = new Mysql($driver);
        $adapter = new Adapter($driver, $mysql);
        $sql = new Sql($adapter, 'table');

        $subSelect = new Select('foo');
        $subSelect->columns(['column'])->limit(1);

        $select = new Select('bar');
        $select->columns(['column' => $subSelect])->limit(99);

        $statement = $sql->prepareStatementForSqlObject($select);

        $parametersCount = $statement->getParameterContainer()->count();
        $placeholdersCount = substr_count($statement->getSql(), ':');

        $this->assertEquals($parametersCount, $placeholdersCount, 'Count of parameters and placeholder not the same');
    }
}
