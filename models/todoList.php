<?php
/**
 * Created by IntelliJ IDEA.
 * User: mix
 * Date: 2016/05/21
 * Time: 16:43
 *
 * todoモデルの集合モデルクラス
 */

namespace models;


class TodoList
{
    private $models;

    /**
     * todoList constructor.
     */
    public function __construct()
    {
        $this->models = [new todo()];
    }

    public function getDataArray(){
        $todo_data_array = [];
        for($i=0; $i<10; $i++){
            $todo_data_array[] = [
                'id' => $i,
                'done' => false,
                'title' => "タイトル",
                'limit_date' => "2016-05-30",
                'view_order' => 30,
                'create_date' => "2016-05-0$i 21:11:11",
                'last_update_date' => "2016-05-0$i 21:11:11"
            ];
        }
        return $todo_data_array;
    }
}