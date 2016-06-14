<?php
/**
 * Created by IntelliJ IDEA.
 * User: mix
 * Date: 2016/05/21
 * Time: 16:40
 *
 * todoデータを表すモデルクラス
 */

namespace models;

use classes\Config;

class Todo
{
    private $id;
    private $is_done;
    private $title;
    private $limit_date;
    private $view_order;
    private $created;
    private $modified;

    const TABLE_NAME = "todo";

    static private $pdo;

    /**
     * todo constructor.
     * @param null $id
     * $idのデータがあれば、そのデータを読み込んでインスタンス化する
     * なければ、すべてnullのデータをインスタンス化する
     */
    public function __construct($id = null){
        //pdo接続はインスタンス間で使い回す
        if(is_null(self::$pdo)){
            $config = new Config();
            $config_params = $config->getConfig();
            $db_params = $config_params['db'];
            self::$pdo = new \PDO("mysql:host=".$db_params['host'].";dbname=".$db_params['db'], $db_params['user'], $db_params['password']);
        }
        if($id !== null){
            $sql = "SELECT id, is_done, title, limit_date, view_order, created, modified ";
            $sql .= "FROM ".self::TABLE_NAME." ";
            $sql .= "WHERE id = :id ";
            $stmt = self::$pdo->prepare($sql);
            if($stmt->execute(['id' => $id])){
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                if($result !== false){
                    $this->id           = (int) $result['id'];
                    $this->is_done      = (int) $result['is_done'];
                    $this->title        = $result['title'];
                    $this->limit_date   = $result['limit_date'];
                    $this->view_order   = (int) $result['view_order'];
                    $this->created      = $result['created'];
                    $this->modified     = $result['modified'];
                }
            }
        }
    }

    /**
     * プロパティのデータを永続化する。
     * $this->idがnullもしくは、存在しないidなら新規登録する。
     * 存在するidならそのデータをプロパティ値で上書きする
     */
    public function save(){
        $now_data = date("Y-m-d H:i:s");
        $this->modified = $now_data;
        $record_exist = $this->isExist($this->id);
        if($record_exist){
            //更新
            $sql = "UPDATE ".self::TABLE_NAME." SET  is_done = :is_done, title = :title, limit_date = :limit_date, view_order = :view_order, modified = :modified ";
            $sql .= "WHERE id = :id";
            $params = [
                ':id' => $this->id,
                ':is_done' => $this->is_done,
                ':title' => $this->title,
                ':limit_date' => $this->limit_date,
                ':view_order'=> $this->view_order,
                ':modified' => $this->modified,
            ];

        }else{
            //新規登録
            $this->created = $now_data;
            $sql = "INSERT INTO ".self::TABLE_NAME." (id, is_done, title, limit_date, view_order, created, modified) ";
            $sql .= "VALUES (:id, :is_done, :title, :limit_date, :view_order, :created, :modified) ";
            $params = [
                ':id' => $this->id,
                ':is_done' => $this->is_done,
                ':title' => $this->title,
                ':limit_date' => $this->limit_date,
                ':view_order'=> $this->view_order,
                ':created' => $this->created,
                ':modified' => $this->modified,
            ];

        }
        self::$pdo->beginTransaction();
        $stmt = self::$pdo->prepare($sql);
        if(!$stmt->execute($params)){
            throw new \Exception("データ更新に失敗しました");
        }
        if(!$record_exist){
            //レコード追加時のidを$this->idに入れる。
            $this->id = self::$pdo->lastInsertId();
        }
        self::$pdo->commit();
        return true;
    }

    /**
     * $this->idのデータを削除する。
     */
    public function delete(){
        /*todo:
        if($this->idのデータがある){
            //$this->idのレコードを削除する
        }
        */
        return true;
    }

    /**
     * @param $id
     * @return bool
     * $idのレコードがあるかどうかを確認
     */
    static public function isExist($id){
        $obj = new Todo();//self::$pdoを初期化するための捨てオブジェクト

        $sql = "SELECT id FROM ".self::TABLE_NAME." WHERE id = :id LIMIT 1";
        $params = ['id' => $id];
        $stmt = self::$pdo->prepare($sql);
        if(!$stmt->execute($params)){
            throw new \Exception("データ取得に失敗しました");
        }
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($result !== false && count($result) > 0){
            return true;
        }else{
            return false;
        }
    }

    /*****getter setter****/
    public function getId(){
        return $this->id;
    }
    public function isDone(){
        return $this->is_done;
    }
    public function getTitle(){
        return $this->title;
    }
    public function getLimitData(){
        return $this->limit_date;
    }
    public function getViewOrder(){
        return $this->view_order;
    }

    public function setId($id){
        $this->id = $id;
    }
    public function setDone(){
        $this->is_done = true;
    }
    public function setUnDone(){
        $this->is_done = false;
    }
    public function setTitle($title){
        $this->title = $title;
    }
    public function setLimitData($limit_date){
        $this->limit_date = $limit_date;
    }
    public function setViewOrder($view_order){
        $this->view_order = $view_order;
    }
}