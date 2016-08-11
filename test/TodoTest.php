<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 2016/08/11
 * Time: 0:01
 */

namespace test;

use PHPUnit\Framework\TestCase;
use models\Todo;
use test\GenericTestsDatabaseTestCase;

//初期化ファイル読み込み
define("ENV", "unit_test");
include dirname(__FILE__)."/../init/init.php";

//class TodoTest extends \PHPUnit_Extensions_Database_TestCase{
class TodoTest extends GenericTestsDatabaseTestCase{

//    public function getConnection()
//    {
//        var_dump("move");
//        $pdo = new \PDO( $GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD']);
//        return $this->createDefaultDBConnection($pdo, ':memory:');
//    }

//    public function getDataSet()
//    {
//        var_dump("データセットされた");
//        return $this->createMySQLXMLDataSet('test/db_test_data.xml');
//    }
    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createMySQLXMLDataSet('test/db_test_data.xml');
//        return new \PHPUnit_Extensions_Database_DataSet_YamlDataSet(
//            dirname(__FILE__)."/_files/guestbook.yml"
//        );
    }


    public function setUp(){
        //初期化
        var_dump("初期化された");
    }

    public function tearDown(){
    }

    public function testGetRowCount()
    {
        $this->assertEquals(2, $this->getConnection()->getRowCount('db_test_data'));
    }

//    public function testDataCheck(){
//        var_dump("test実行された");
//        $this->getConnection()->createDataSet();
//        $this->getDataSet();
////        var_dump(Todo::getTodoListByUser(1));
////        $this->assertEquals("", Todo::getTodoListByUser(1));
//    }
//
//    public function testPushAndPop(){
////        $stack = [];
////        $this->assertEquals(0, count($stack));
////
////        array_push($stack, 'foo');
////        $this->assertEquals('foo', $stack[count($stack)-1]);
////        $this->assertEquals(1, count($stack));
////
////        $this->assertEquals('foo', array_pop($stack));
////        $this->assertEquals(0, count($stack));
//    }
}
