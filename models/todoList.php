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


class todoList
{
    private $models;

    /**
     * todoList constructor.
     */
    public function __construct()
    {
        $this->models = [new todo()];
    }
}