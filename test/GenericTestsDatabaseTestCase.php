<?php
namespace test;
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 2016/08/11
 * Time: 12:53
 */
abstract class GenericTestsDatabaseTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    // PDO のインスタンス生成は、クリーンアップおよびフィクスチャ読み込みのときに一度だけ
    static private $pdo = null;

    // PHPUnit_Extensions_Database_DB_IDatabaseConnection のインスタンス生成は、テストごとに一度だけ
    private $conn = null;

    /**
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    final public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new \PDO( $GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'] );
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, $GLOBALS['DB_DBNAME']);
        }

        return $this->conn;
    }
}