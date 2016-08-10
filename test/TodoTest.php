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

//初期化ファイル読み込み
define("ENV", "unit_test");
include dirname(__FILE__)."/../init/init.php";

class TodoTest extends TestCase{

    public function setUp(){
        //初期化
    }

    public function tearDown(){
    }

    public function testDataCheck(){
        var_dump(Todo::getTodoListByUser(1));
//        $this->assertEquals("", Todo::getTodoListByUser(1));
    }

    public function testPushAndPop(){
//        $stack = [];
//        $this->assertEquals(0, count($stack));
//
//        array_push($stack, 'foo');
//        $this->assertEquals('foo', $stack[count($stack)-1]);
//        $this->assertEquals(1, count($stack));
//
//        $this->assertEquals('foo', array_pop($stack));
//        $this->assertEquals(0, count($stack));
    }
}
