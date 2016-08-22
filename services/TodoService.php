<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 2016/08/19
 * Time: 21:32
 */

namespace services;

use models\Todo;
use models\Project;

class TodoService
{

    /**
     * user_idと期間を渡して期間内のTodoデータを取得する
     * [
     *     [
     *         'date' => '日付',
     *         'project' => [プロジェクトデータ配列],
     *         'todo' => [Todoデータ配列],
     *     ],
     *     …
     * ]
     *
     * @param $user_id
     * @param $start_date
     * @param $limit_date
     * @return array
     */
    public static function getTodoListByDay($user_id, $start_date, $limit_date){
        //do_date, limit_dateが指定日内のuser_idの全てのTodoを取得する。(objで取得)（order 日付、 プロジェクトview_order）
        $todo_objs = Todo::getTodoByDate($user_id, $start_date, $limit_date);

        //含まれているproject_idを取得する。(重複をとりのぞく）
        $project_ids = array_unique(array_values(array_map(function($todo){
            return $todo->project_id;
        }, $todo_objs)));

        //$project_idsからそれぞれプロジェクトデータを取得する(objで取得)
        $project_objs = Project::getProjectsById($project_ids);

        //返却用データを作成する
        $ret_array = [];
        $start = strtotime($start_date);
        $limit = strtotime($limit_date);
        foreach($todo_objs as $todo_obj){
            $do_date_time = strtotime($todo_obj->do_date);
            $tmp_data = [
                'date' => ($start <= $do_date_time && $do_date_time <= $limit) ? $todo_obj->do_date : $todo_obj->limit_date,//$todo_objのdo_dateかlimit_dateを代入,
                'project' => $project_objs[$todo_obj->project_id]->getArray(),
                'todo' => $todo_obj->getArray(),
            ];
            $ret_array[] = $tmp_data;
        }

        return $ret_array;
    }
}