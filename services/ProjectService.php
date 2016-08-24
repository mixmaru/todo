<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 2016/08/19
 * Time: 21:32
 */

namespace services;

use models\Project;

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
       return $project->validate();
    }
}