<?php
/**
 * Created by IntelliJ IDEA.
 * User: mix
 * Date: 2016/05/21
 * Time: 16:44
 *
 * httpリクエストデータを表すクラス
 */

namespace classes;


class Request
{
    /**
     * @return string
     * リクエストメソッドタイプを返す
     * get or post or delete or put
     * 取得できなければfalseを返す
     */
    public function getMethod(){

        return "get";
    }

    /**
     * @param null $key
     * @return string
     * get値またはpost値から指定のkeyのデータを取得する。
     * nullが渡された場合は、全ての値を配列にもたせて返す
     * 値がなければfalseを返す
     */
    public function get($key = null){

        return "値";
    }

    public function post($key = null){
        return "値";
    }
}