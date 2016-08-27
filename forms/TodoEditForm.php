<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 2016/08/27
 * Time: 14:58
 */

namespace forms;

use models\Todo;
use models\Project;
use classes\Session;

class TodoEditForm
{
    const TEMP_SAVE_SESSION_KEY = "TodoEditFormTempSave";

    private $todo_id;
    private $todo_title;
    private $todo_limit_date;
    private $todo_do_date;
    private $project_id;
    private $new_project_name;

    private $error_messages = [];

    public function __construct(array $input_data = null){
        if(!empty($input_data)){
            $this->loadArray($input_data);
        }
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function loadArray(array $properties){
        foreach($properties as $key => $value){
            if(property_exists($this, $key)){
                $this->$key = $value;
            }
        }
    }

    public function getArray(){
        return [
            'todo_id' => $this->todo_id,
            'todo_title' => $this->todo_title,
            'todo_limit_date' => $this->todo_limit_date,
            'todo_do_date' => $this->todo_do_date,
            'project_id' => $this->project_id,
            'new_project_name' => $this->new_project_name,
        ];
    }

    public function validate(){
        //新規プロジェクト作成の場合、projectデータのバリデーション
        if($this->project_id == -1){
            $project = new Project();
            $project->id = $this->project_id;
            $project->name = $this->new_project_name;
            $this->error_messages['project_id'] = $project->validateId();
            $this->error_messages['new_project_name'] = $project->validateName();
        }

        //todoデータのバリデーション
        $todo = new Todo();
        $todo->id = $this->todo_id;
        $todo->title = $this->todo_title;
        $todo->limit_date = $this->todo_limit_date;
        $todo->do_date = $this->todo_do_date;
        $todo->project_id = $this->project_id;
        $todo->user_id = 1;//todo:ログインシステムをつくるまでは1で決め打ち
        $this->error_messages['todo_id'] = $todo->validateId();
        $this->error_messages['todo_title'] = $todo->validateTitle();
        $this->error_messages['todo_do_date'] = $todo->validateDoDate();
        $this->error_messages['todo_limit_date'] = $todo->validateLimitDate();
        $this->error_messages['user_id'] = $todo->validateUserId();
        $this->error_messages['project_id'] = $todo->validateProjectId();

        foreach($this->error_messages as $key => $value){
            if(empty($value)){
                unset($this->error_messages[$key]);
            }
        }
        return empty($this->error_messages) ? true : false;
    }

    public function temporarySave(){
        $session = new Session();
        $session->set(self::TEMP_SAVE_SESSION_KEY, $this->getArray());
    }

    public function temporaryLoad(){
        $session = new Session();
        $data_array = $session->get(self::TEMP_SAVE_SESSION_KEY);
        if($data_array){
            $this->loadArray($data_array);
            return true;
        }else{
            return false;
        }
    }
}