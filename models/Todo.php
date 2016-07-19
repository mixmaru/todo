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



class Todo extends BaseModel
{
    private $title;
    private $do_date;
    private $limit_date;
    private $is_done;
    private $path;
    private $depth;
    private $project_id;
    private $user_id;



    const TABLE_NAME = "todo";


    /**
     * todo constructor.
     * @param null $id
     * $idのデータがあれば、そのデータを読み込んでインスタンス化する
     * なければ、すべてnullのデータをインスタンス化する
     */
    public function __construct($id = null){
        parent::__construct();
        if($id !== null){
            $sql = "SELECT id, title, do_date, limit_date, is_done, path, depth, project_id, user_id, created, modified ";
            $sql .= "FROM ".self::TABLE_NAME." ";
            $sql .= "WHERE id = :id ";
            $stmt = $this->db->prepare($sql);
            if($stmt->execute(['id' => $id])){
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                if($result !== false){
                    $this->id           = (int) $result['id'];
                    $this->title        = $result['title'];
                    $this->do_date        = $result['do_date'];
                    $this->limit_date   = $result['limit_date'];
                    $this->is_done      = $result['is_done'];
                    $this->path      = $result['path'];
                    $this->depth      = (int) $result['depth'];
                    $this->project_id   = (int) $result['project_id'];
                    $this->user_id   = (int) $result['user_id'];
                    $this->created      = $result['created'];
                    $this->modified     = $result['modified'];
                }
            }
        }
    }

    /**
     * 全てのTodoを表示する順に取得する
     * @param bool $byObject true:オブジェクトで取得 false:配列で取得
     * @return array
     * @throws \Exception
     */
    static public function getAllTodo($byObject = true){
        $obj = new Todo;//捨てオブジェクト

        $ret_array = [];

        $sql = "SELECT id, is_done, title, limit_date, view_order, created, modified ";
        $sql .= "FROM ".self::TABLE_NAME." ";
        $sql .= "ORDER BY limit_date ASC, view_order ASC";
        $stmt = self::$pdo->prepare($sql);
        if(!$stmt->execute()){
            throw new \Exception("データ取得に失敗しました");
        }
        $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if($records === false){
            throw new \Exception("データ取得に失敗しました");
        }
        foreach($records as $record){
            if($byObject){
                $todo_obj = new Todo();
                $todo_obj->id           = (int) $record['id'];
                $todo_obj->is_done      = (int) $record['is_done'];
                $todo_obj->title        = $record['title'];
                $todo_obj->limit_date   = $record['limit_date'];
                $todo_obj->view_order   = (int) $record['view_order'];
                $todo_obj->created      = $record['created'];
                $todo_obj->modified     = $record['modified'];
                $ret_array[] = $todo_obj;
            }else{
                $ret_array[] = [
                    'id'            => (int) $record['id'],
                    'is_done'       => (int) $record['is_done'],
                    'title'         => $record['title'],
                    'limit_date'    => $record['limit_date'],
                    'view_order'    => (int) $record['view_order'],
                    'created'       => $record['created'],
                    'modified'      => $record['modified'],
                ];
            }
        }
        return $ret_array;
    }

    static public function getTodoListByProject($project_id, $object = true){
        new Todo;
        $sql = "SELECT id, title, do_date, limit_date, is_done, path, project_id, user_id, created, modified "
              ."FROM `todo` "
              ."WHERE project_id = :project_id "
              ."ORDER BY (LENGTH(path) - LENGTH(REPLACE(path, '/', '')) -1)";
        $stmt = self::$pdo->prepare($sql);
        if(!$stmt->execute([':project_id' => $project_id])){
            throw new \Exception("データ取得に失敗しました");
        }
        $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $ret_tree_data = [];//返却用Tree構造データ
        $tmp_list_data = [];//Treeデータ作成に必要なlistデータ
        foreach($records as $todo_data){
            if($object){
                $node = new Todo();
                $node->setId($todo_data['id']);
                $node->setTitle($todo_data['title']);
                $node->setDoDate($todo_data['do_date']);
                $node->setLimitData($todo_data['limit_date']);
                $node->is_done = $todo_data['is_done'];
                $node->setPath($todo_data['path']);
                $node->setProjectId($todo_data['project_id']);
                $node->setUserId($todo_data['user_id']);
                $node->created = $todo_data['created'];
                $node->modified = $todo_data['modified'];
            }else{
                $node = $todo_data;
            }
            $path_array = array_filter(explode('/', $todo_data['path']), 'strlen');
            $current_id = array_pop($path_array);
            $parent_id = array_pop($path_array);
            $tmp_list_data[$current_id] = [
                'data' => $node,
                'child' => [],
//                'parent' => null,
            ];
            if(is_null($parent_id)){
                $ret_tree_data[$current_id] = &$tmp_list_data[$current_id];
            }else{
//                $tmp_list_data[$current_id]['parent'] = &$tmp_list_data[$parent_id];
                $tmp_list_data[$parent_id]['child'][$current_id] = &$tmp_list_data[$current_id];
            }
        }
        return $ret_tree_data;
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
     * @return bool
     * @throws \Exception
     */
    public function delete(){
        if(!is_null($this->id) && $this->isExist($this->id)){
            //$this->idのレコードを削除する
            $sql = "DELETE FROM ".self::TABLE_NAME." WHERE id = :id";
            $params = ['id' => $this->id];
            $stmt = self::$pdo->prepare($sql);
            if(!$stmt->execute($params)){
                throw new \Exception("データ取得に失敗しました");
            }
            $this->clear();
            return true;
        }
        return false;
    }

    /**
     * プロパティをnullにする(self::$pdoを除く)
     */
    public function clear(){
        $reflect_obj = new \ReflectionClass($this);
        $props = $reflect_obj->getProperties();
        foreach($props as $prop){
            if($prop->name != "pdo"){
                $prop_name = $prop->name;
                $this->$prop_name = null;
            }
        }
    }

    /**
     * @param $id
     * @return bool $idのレコードがあるかどうかを確認
     * @throws \Exception
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

    /**
     * プロパティデータを配列として取得
     * @param bool $assciate :関連モデルのデータも一緒にとってくるかどうか
     * @return array
     */
    public function getDataArray(){
        $ret_array = [
            'id' => $this->getId(),
            'do_date' => $this->getDoData(),
            'limit_date' => $this->getLimitData(),
            'is_done' => $this->isDone(),
            'path' => $this->getPath(),
            'depth' => $this->getDepth(),
            'project_id' => $this->getProjectId(),
            'user_id' => $this->getId(),
            'created' => $this->getCreated(),
            'modified' => $this->getModified(),
        ];
        return $ret_array;
    }

    /*****getter setter****/
    public function isDone(){
        return $this->is_done;
    }
    public function getTitle(){
        return $this->title;
    }
    public function getDoData(){
        return $this->do_date;
    }
    public function getLimitData(){
        return $this->limit_date;
    }
    public function getPath(){
        return $this->path;
    }
    public function getDepth(){
        return $this->depth;
    }
    public function getProjectId(){
        return $this->project_id;
    }
    public function getUserId(){
        return $this->user_id;
    }
    public function getViewOrder(){
        return $this->view_order;
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
    public function setDoDate($do_date){
        $this->do_date = $do_date;
    }
    public function setLimitData($limit_date){
        $this->limit_date = $limit_date;
    }
    public function setIsDone($is_done){
        $this->is_done = $is_done;
    }
    public function setPath($path){
        $this->path = $path;
    }
    public function setProjectId($project_id){
        $this->project_id = $project_id;
    }
    public function setUserId($user_id){
        $this->user_id = $user_id;
    }
    public function setViewOrder($view_order){
        $this->view_order = $view_order;
    }
}