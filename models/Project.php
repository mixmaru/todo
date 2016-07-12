<?php
/**
 * Created by IntelliJ IDEA.
 * User: mix
 * Date: 2016/07/12
 * Time: 21:47
 *
 * projectクラス
 */
namespace models;

class Project extends BaseModel{

    public function __construct($id)
    {
        parent::__construct();
        /*todo:
        $idでプロジェクトテーブルからデータを取得してプロパティにセットする
        */
    }

    //このプロジェクトに紐づくTodo群を返す
    public function getTodos(){
        $sql = " SELECT * FROM todo WHERE path like '/9/%' ORDER BY path ";
        $stm = $this->db->prepare($sql);
        $result = $stm->execute();
        foreach($stm->fetchAll(\PDO::FETCH_ASSOC) as $key => $value){
            var_dump($key, $value);
        }
    }

}