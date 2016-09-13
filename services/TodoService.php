<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 2016/08/19
 * Time: 21:32
 */

namespace services;

use forms\TodoEditForm;
use models\BaseModel;
use models\Todo;
use models\Project;

class TodoService
{
    public static function validate(Todo $todo){
        $error_msg['id'] = $todo->validateId();
        $error_msg['title'] = $todo->validateTitle();
        $error_msg['do_date'] = $todo->validateDoDate();
        $error_msg['limit_date'] = $todo->validateLimitDate();
        $error_msg['user_id'] = $todo->validateUserId();
        $error_msg['project_id'] = $todo->validateProjectId();
        foreach($error_msg as $key => $value){
            if(empty($value)){
                unset($error_msg[$key]);
            }
        }
        return $error_msg;
    }

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

    public static function getTodoListByProjectId($project_id){
        $ret_array = [];
        $todo_objs = Todo::getTodosByProjectIds([$project_id]);
        foreach($todo_objs as $todo){
            $ret_array[$todo->id] = $todo->getArray();
        }
        return $ret_array;
    }

    /**
     * user_idを渡して、関連する全てのTodoデータをプロジェクトデータとともに返す
     *
     * @param $user_id
     * @return array
     *   [
     *      [
     *          'project' => [],
     *          'todo' => [],
     *      ],
     *      …
     *   ]
     *
     */
    public static function getTodoListByUser($user_id){
        $ret_array = [];
        //$user_idに関連する全てのprojectデータを取得する
        $project_objs = Project::getProjectsByUserId($user_id);
        $project_ids = array_keys($project_objs);
        $todo_objs = Todo::getTodosByProjectIds($project_ids);//todo:並び順を指定できるようにしたほうがいいかも。ここでは (project_view_order, 深さ順、着手日順)で取り出す
        foreach($todo_objs as $todo){
            $tmp_array['project'] = $project_objs[$todo->project_id]->getArray();
            $tmp_array['todo'] = $todo->getArray();
            $ret_array[] = $tmp_array;
        }
        return $ret_array;
    }

    public static function getTodoById($todo_id, $user_id){
        $ret_array = [];
        $todo_obj = Todo::getTodo($todo_id, $user_id);
        return $todo_obj->getArray();
    }

    public static function getParentTodoIdById($id){
        $todo = new Todo($id);
        return $todo->getParentId();
    }

    /**
     * Todo編集画面の入力値でDBへ保存する
     * @param TodoEditForm $form
     */
    public static function saveTodo(TodoEditForm $form){
        //トランザクション開始
        BaseModel::begin();
        try{
            if($form->project_id == -1){
                var_dump("プロジェクト新規登録");
                //プロジェクト新規登録
                $project = new Project();
                $project->name = $form->new_project_name;
                $project->user_id = 1;
                $project->root_todo_id = -1;//既存のtodoであっても、todoのproject_idを変えてからでないと登録できない。バリデーションで弾かれる
                $error_messages = $project->save();
                if(!empty($error_messages)){
                    throw new \Exception("projectの新規登録に失敗");
                }
                $form->project_id = $project->id;
            }
            //todoを更新
            if(!is_null($form->todo_id)){
                $todo = new Todo($form->todo_id);
            }else{
                $todo = new Todo();
            }
            $todo->title = $form->todo_title;
            $todo->do_date = $form->todo_do_date;
            $todo->limit_date = $form->todo_limit_date;
            if(is_null($form->parent_todo_id)){
                $todo->setPathRoot();
            }else{
                $todo->setPathByParentTodoId($form->parent_todo_id);
            }
            $todo->project_id = $form->project_id;
            $todo->user_id = 1;//todo: ログイン機能ができるまで1で決め打ち
            $error_messages = $todo->save();
            if(!empty($error_messages)){
                throw new \Exception("Todo保存時にバリデーションエラーが発生しました");
            }
            //projectが新規登録の場合はここでroot_todo_idを登録する
//            if($project->root_todo_id == -1){
//                $project->root_todo_id = $todo->id;
//                $error_messages = $project->save();
//                if(!empty($error_messages)){
//                    throw new \Exception("project保存時にエラーが発生しました");
//                }
//            }
        }catch(\Exception $e){
            BaseModel::rollback();
            throw $e;
        }
        BaseModel::commit();
    }
}