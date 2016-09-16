<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 2016/08/19
 * Time: 21:32
 */

namespace services;

use models\Project;
use models\Todo;

class ProjectService
{
    public static function getAllProjectByUserId($user_id){
        $ret_array = [];
        foreach(Project::getAll($user_id) as $project){
            $ret_array[] = $project->getArray();
        }
        return $ret_array;
    }

    public static function validate(Project $project){
        $error_msg['id'] = $project->validateId();
        $error_msg['name'] = $project->validateName();
        $error_msg['user_id'] = $project->validateUserId();
        foreach($error_msg as $key => $value){
            if(empty($value)){
                unset($error_msg[$key]);
            }
        }
        return $error_msg;
    }

    public static function createNewProject($name, $user_id){
        Project::begin();
        try{
            //プロジェクト新規登録
            $project = new Project();
            $project->name = $name;
            $project->user_id = $user_id;
            $error_messages = $project->save();
            if(!empty($error_messages)){
                throw new \Exception("projectの新規登録に失敗");
            }

            //root todo新規登録
            $todo = new Todo();
            $error_messages = $todo->makeRootTodo($project->id, $user_id);
            if(!empty($error_messages)){
                throw new \Exception("todoの新規登録に失敗");
            }
            $project->root_todo_id = $todo->id;
            $error_messages = $project->save();
            if(!empty($error_messages)){
                throw new \Exception("project->root_todo_idの登録に失敗");
            }
        }catch(\Exception $e){
            Project::rollback();
            throw $e;
        }
        Project::commit();
        return ['project' => $project, 'todo' => $todo];
    }
}