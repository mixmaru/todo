<?php
/**
 * Created by IntelliJ IDEA.
 * User: mix
 * Date: 2016/07/12
 * Time: 21:49
 */
namespace models;

use classes\UserPdo;

class BaseModel{
    static protected $pdo;

    protected $db;

    protected $id;
    protected $created;
    protected $modified;

    protected static $transaction_nest_count = 0;

    public function __construct()
    {
        $this->connect();
        $this->db = self::$pdo;
    }

    protected static function connect(){
        //pdo接続はインスタンス間で使い回す
        if(is_null(self::$pdo)){
            self::$pdo = new UserPdo("mysql:host=".DB_HOST.";dbname=".DB_DB, DB_USER, DB_PASS);
        }
    }

    public function getId(){
        return $this->id;
    }
    public function setId($id){
        $this->id = $id;
    }

    public function getCreated(){
        return $this->created;
    }

    public function getModified(){
        return $this->modified;
    }

    public static function begin(){
        self::connect();
        if(self::$transaction_nest_count == 0){
            self::$pdo->beginTransaction();
        }
        self::$transaction_nest_count++;
    }

    public static function commit(){
        self::connect();
        self::$transaction_nest_count--;
        if(self::$transaction_nest_count <= 0){
            self::$pdo->commit();
        }
    }

    public static function rollback(){
        self::connect();
        self::$pdo->rollBack();
    }

}