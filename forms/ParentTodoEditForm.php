<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 2016/08/27
 * Time: 14:58
 */

namespace forms;

use models\Project;

class ParentTodoEditForm extends BaseForm
{

    protected $parent_todo_id;


    public function validate(){
        //指定parent_todo_idが指定projectに属するtodoであるか確認


        return empty($this->error_messages) ? true : false;
    }
}