<?php
/**
 * Created by IntelliJ IDEA.
 * User: mix
 * Date: 2016/07/10
 * Time: 17:41
 *
 * project削除プロセスの擬似コード
 */

function deleteProject(Project $project){
    //削除するプロジェクトに紐付いているtodoは全てotherProjectへ紐付ける
    $todos = $project->getTodo();
    $otherProject = Project::getOtherProject();
    if(!$todos->setProject($otherProject)){
        //エラー
    }

    $project->delete();
}