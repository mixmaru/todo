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
    public function __construct()
    {
    }

    /**
     * @return string
     * リクエストメソッドタイプを返す
     * get or post or delete or put
     * 取得できなければfalseを返す
     */
    public function getMethod(){
        $ret_method = false;
        if(isset($_SERVER["REQUEST_METHOD"])){
            $ret_method = mb_strtolower($_SERVER["REQUEST_METHOD"]);
        }

        return $ret_method;
    }

    /**
     * @param null $key
     * @return string
     * get値から指定のkeyのデータを取得する。
     * nullが渡された場合は、全ての値を配列にもたせて返す
     * 値がなければfalseを返す
     */
    public function get($key = null){
        return $this->getValue("get", $key);
    }

    /**
     * @param null $key
     * @return string
     * post値から指定のkeyのデータを取得する。
     * nullが渡された場合は、全ての値を配列にもたせて返す
     * 値がなければfalseを返す
     */
    public function post($key = null){
        return $this->getValue("post", $key);
    }

    /**
     * getかpostから指定keyの値を取得する。keyがなければfalseを返す
     * keyが指定されていなければ全ての値を返す
     * @param $method "get" or "post"
     * @param null $key
     * @return mixed
     * @throws \Exception
     */
    private function getValue($method, $key = null){
        if($method != "get" && $method != "post"){
            throw new \Exception("getかpostを指定してください");
        }

        //$_GETや$_POSTは可変変数で扱うことができず、get,postの文字列をつかって動的に分けることが出来なかった。
        //とりあえずしょうがなくcaseで分岐させる
        switch($method){
            case "get":
                $values = $_GET;
                break;
            case "post":
                $values = $_POST;
                break;
            default:
                throw new \Exception("不明なエラーが発生しました");
        }

        $ret_value = false;
        if(is_null($key)){
            //全ての値を返す
            $ret_value = $values;
        }else{
            //$keyの値が存在するならそれを返す。
            $ret_value = (isset($values[$key])) ? $values[$key] : false;
        }
        return $ret_value;
    }
}