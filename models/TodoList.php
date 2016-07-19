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

use models\Todo;

class TodoList extends BaseModel
{
    private $todoList;

    static public function getTodoListByProject($project_id){
        $ret_obj = new TodoList();
        $ret_obj->todoList = [];

        $sql = "SELECT * FROM ".Todo::TABLE_NAME." WHERE project_id = :project_id ORDER BY depth ASC";
        $params = [':project_id' => $project_id];

        $stmt = self::$pdo->prepare($sql);
        if($stmt->execute($params)){
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if($result !== false){
                $tmp_list = [];
                $tmp_tree = [];
                foreach($result as $data){
                    $todo_obj = new Todo();
                    $todo_obj->setId((int) $data['id']);
                    $todo_obj->setTitle($data['title']);
                    $todo_obj->setDoDate($data['do_date']);
                    $todo_obj->setLimitData($data['limit_date']);
                    $todo_obj->setIsDone($data['is_done']);
                    $todo_obj->setPath($data['path']);
                    $todo_obj->setProjectId($data['project_id']);
                    $todo_obj->setUserId($data['user_id']);
//                    $todo_obj->created      = $data['created'];
//                    $todo_obj->modified     = $data['modified'];

                    $path_array = explode("/", $todo_obj->getPath());
                    array_pop($path_array);//から要素の除去
                    array_shift($path_array);//から要素の除去
                    $current_id = array_pop($path_array);
                    $parent_id = array_pop($path_array);//なければnull
                    $tmp_list[$current_id] = [
                        'obj' => $todo_obj,
                        'child' => [],
                    ];
                    if(!isset($parent_id)){
                        $tmp_tree[$current_id] = &$tmp_list[$current_id];
                    }else{
                        $tmp_list[$parent_id]['child'][$current_id] = &$tmp_list[$current_id];
                    }
                }
                $ret_obj->todoList = $tmp_tree;
            }
        }
        return $ret_obj;
    }

    public function getDataArray(){
        foreach($this->todoList as $todo){
            var_dump($todo['obj']->getId());
            foreach($todo['child'] as $todo2){
                var_dump($todo2['obj']->getId());
                foreach($todo2['child'] as $todo3){
                    var_dump($todo3['obj']->getId());
                }
            }
        }
    }
}