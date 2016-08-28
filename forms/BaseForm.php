<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 2016/08/28
 * Time: 15:42
 */

namespace forms;


class BaseForm
{
    protected $error_messages = [];


    public function __construct(array $input_data = null){
        if(!empty($input_data)){
            $this->loadArray($input_data);
        }
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function loadArray(array $properties){
        foreach($properties as $key => $value){
            if(property_exists($this, $key)){
                $this->$key = $value;
            }
        }
    }

    public function getArray(){
        $ret_array = [];
        $reflect = new \ReflectionClass($this);
        $properties = $reflect->getProperties();
        foreach($properties as $property){
            $prop_name = $property->getName();
            if($prop_name != "error_messages"){
                $ret_array[$prop_name] = $this->$prop_name;
            }
        }
        return $ret_array;
    }
}