<?php
/**
 * Created by IntelliJ IDEA.
 * User: mix
 * Date: 2016/06/23
 * Time: 23:11
 */

namespace classes;


class View
{
    const DEFAULT_EXTENSION = "html";
    private $renderer;

    public function __construct()
    {
        $loader = new \Twig_Loader_Filesystem(TEMPLATE_DIR_PATH);
        $this->renderer = new \Twig_Environment($loader, [
            'debug'         => true,//todo:本番ではfalseにする
            'cache'         => VIEW_CACHE_DIR_PATH,//todo:キャッシュが書き込めない
            'charset'       => 'utf-8',
//            'auto_reload'   => true,
//        	'autoescape'    => false,
        ]);
        $this->renderer->addExtension(new \Twig_Extension_Debug());
    }

    public function render($template, $args = []){
        //拡張子がなければデフォルト拡張子をつける
        $info = new \SplFileInfo($template);
        if($info->getExtension() == ""){
            $template .= ".".self::DEFAULT_EXTENSION;
        }
        echo $this->renderer->render($template, $args);
    }

    /**
     * エラーコードで表示テンプレートを決定
     * エラーメッセージが空ならその際、適したデフォルトエラーメッセージを設定。
     * @param int $error_code
     * @param null $error_message
     */
    public function renderError($error_code = 404, $error_message = null){
        $args = [];
        switch($error_code){
            case 400:
                $template = "error/400";
                $args['message'] = (!is_null($error_message)) ? $error_message : "不正なアクセスです";
                header("HTTP/1.0 400 Bad Request");
                break;
            case 404:
                $template = "error/404";
                $args['message'] = (!is_null($error_message)) ? $error_message : "このページは存在しません";
                header("HTTP/1.0 404 Not Found");
                break;
            default:
                $template = "error/501";
                $args['message'] = "不明なエラーです";
                header("HTTP/1.0 501 Not Implemented");
        }
        $this->render($template, $args);
        exit();
    }

}