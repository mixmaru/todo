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

class Todo
{
    private $id;
    private $is_done;
    private $title;
    private $limit_date;
    private $view_order;
    private $created;
    private $modified;

    /**
     * todo constructor.
     * @param null $id
     * $idのデータがあれば、そのデータを読み込んでインスタンス化する
     * なければ、すべてnullのデータをインスタンス化する
     */
    public function __construct($id = null){
        /*todo:
        //いちどだけDB接続を実行する
        if($id !== null){
            //データをロードする
        }
        */
    }

    /**
     * プロパティのデータを永続化する。
     * $this->idがnullもしくは、存在しないidなら新規登録する。
     * 存在するidならそのデータをプロパティ値で上書きする
     */
    public function save(){
        /*todo:
        $now_date = 現在時刻を取得
        if($this->idのデータがある){
            //レコードのlast_update_dateを$now_dateで、その他のデータをプロパティでうわがく。
        }else{
            //プロパティデータでレコードを追加する。createレコード追加時のidを$this->idに入れる。
        }
        */
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