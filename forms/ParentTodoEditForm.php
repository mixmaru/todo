<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 2016/08/27
 * Time: 14:58
 */

namespace forms;

class ParentTodoEditForm extends BaseForm
{

    private $parent_todo_id;


    public function validate(){
        return empty($this->error_messages) ? true : false;
    }
}