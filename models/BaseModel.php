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

    public function __construct()
    {
        //pdo接続はインスタンス間で使い回す
        if(is_null(self::$pdo)){
            self::$pdo = new UserPdo("mysql:host=".DB_HOST.";dbname=".DB_DB, DB_USER, DB_PASS);
        }
        $this->db = self::$pdo;
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

}