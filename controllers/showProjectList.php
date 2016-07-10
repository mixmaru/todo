<?php
/**
 * Created by IntelliJ IDEA.
 * User: mix
 * Date: 2016/07/10
 * Time: 17:24
 *
 * プロジェクト一覧の取得と表示
 */

function showProjectList(User $user){
    /* todo:

    //全てのプロジェクトを取得。
    $allProjects = project群::selectByUser($user);

    //プロジェクトと所属Todoのデータを取得
    $retArray=[];
    foreach($allProjects as $project){
        $todos = [];//プロジェクト所属todo
        foreach($project->getTodo() as $todo){
            $todos[] = ['id' => $todo->getId(), 'name' => $todo->getName(),,,,];
        }
        $retArray[] = [
            'project' => [
                'name' => $project->getName(),
                ,,,
            ],
            'todo' => $todos,
        ];
    }

    //を表示する
    $this->renderer->render("list", [
        'pageTitle' => "projectリスト",
        'projectsData' => $retArray,
    ]);
    */
}