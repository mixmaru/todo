<?php
/**
 * Created by IntelliJ IDEA.
 * User: mix
 * Date: 2016/07/17
 * Time: 12:52
 */

namespace models;


class TodoTree extends BaseModel
{
    const TABLE_NAME = "todo_tree";

    private $id;
    private $ancestor_id;
    private $descendant_id;
    private $path_length;

    public function __construct($id = null)
    {
        parent::__construct();
        if($id !== null){
            $sql = "SELECT id, ancestor_id, descendant_id, path_length, created, modified ";
            $sql .= "FROM ".self::TABLE_NAME." ";
            $sql .= "WHERE id = :id ";
            $stmt = $this->db->prepare($sql);
            if($stmt->execute(['id' => $id])){
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                if($result !== false){
                    $this->id               = (int) $result['id'];
                    $this->ancestor_id      = (int) $result['ancestor_id'];
                    $this->descendant_id    = (int) $result['descendant_id'];
                    $this->path_length      = (int) $result['path_length'];
                    $this->created          = $result['created'];
                    $this->modified         = $result['modified'];
                }
            }
        }
    }

    public function getAncestorId(){
        return $this->ancestor_id;
    }

    public function getDescendant_id(){
        return $this->descendant_id;
    }

    public function getPathLength(){
        return $this->path_length;
    }

    public function setAncestorId($ancestor_id){
        $this->ancestor_id = $ancestor_id;
    }

    public function setDescendant_id($descendant_id){
        $this->descendant_id = $descendant_id;
    }
}