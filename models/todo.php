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

class todo
{
    private $id;
    private $checked;
    private $tilte;
    private $limit_date;
    private $view_order;
    private $create_date;
    private $last_update_date;

    /**
     * todo constructor.
     * @param null $id
     * $idのデータがあれば、そのデータを読み込んでインスタンス化する
     * なければ、すべてnullのデータをインスタンス化する
     */
    public function __construct($id = null){

    }

    /**
     * プロパティのデータを永続化する。
     * $this->idがnullもしくは、存在しないidなら新規登録する。
     * 存在するidならそのデータをプロパティ値で上書きする
     */
    public function save(){

        return true;
    }

    /**
     * $this->idのデータを削除する。
     */
    public function delete(){

        return true;
    }
}