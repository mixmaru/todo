<?php
/**
 * Created by IntelliJ IDEA.
 * User: mix
 * Date: 2016/05/21
 * Time: 16:46
 *
 * httpレスポンスデータを表すクラス
 */

namespace classes;


class Response
{
    const VIEW_ROOT_PATH = ROOT_PATH."templates/";

    public function __construct()
    {
    }

    /**
     * @param $template_path テンプレートファイルのパス
     * @param array $args テンプレート変数に渡す値の配列
     * html文字列を出力する
     */
    public function render($template, array $args){
        //拡張子確認
        $file_info = new \SplFileInfo($template);
        $extension = $file_info->getExtension();
        if($extension === ""){
            $template = $template.".php";
        }
        $template_file_path = self::VIEW_ROOT_PATH.$template;
        extract($args);
        require($template_file_path);
    }
}