<?php

namespace models;

use \MongoDB\BSON\ObjectID as ObjectID;

class Model
{

    protected $connection;
    protected $collection;
    protected $delete;
    public $model;

    public function __construct($newModel = [], $create = true)
    {
        $mongo = new \MongoDB\Client("mongodb://localhost:27017");

        $this->connection = $mongo->HandS;

        $this->initialize($newModel,$create);

        $model = array_merge($this->template,$newModel);

        foreach($model as $k => $v) $this->model->$k = $v;

        $this->model->created_at = new \DateTime();
        $this->model->updated_at = new \DateTime();

        $this->collection = $this->connection->{$this->collectionName};
        $this->delete = $this->connection->{$this->collectionName . "_delete"};

        if ($create)
        {
            $id = $this->collection->insertOne($this->model);

            $this->model->_id = $id->getInsertedId();
        }

        return $this;
    }

    public function __get($propertyName){
        if(array_key_exists($propertyName, $this->model)){
            return $this->model->$propertyName;
        }
        return false;
    }

    public function __isset($propertyName){
        if(!is_null($this->model))
        {
            if(array_key_exists($propertyName, $this->model))
            {
                return true;
            }
            return false;
        }
        return false;
    }

    public function __toString()
    {
        $stage = json_decode(json_encode($this->model));
        unset($stage->html);
        unset($stage->svg);
        unset($stage->data);
        unset($stage->previous);
        return json_encode($stage);
    }

    public function isEmpty(){
        if(is_null($this->model)) return true;
        return false;
    }

    public function __set($propertyName, $propertyValue){
        $this->model->$propertyName = $propertyValue;
    }

    public function __unset($propertyName){
        $this->model->updated_at = new \DateTime();
        $this->collection->updateOne(["_id"=>$this->model->_id],['$unset' => [$propertyName=>""]]);
        unset($this->model->$propertyName);
    }

    public function save()
    {
        $this->model->updated_at = new \DateTime();
        $this->collection->updateOne(["_id"=>$this->model->_id],['$set' => $this->model]);
    }

    public function initialize(&$newModel = [],$create)
    {
        return;
    }

    public function connection()
    {
        return $this->connection;
    }

    public static function convert_id($search)
    {
        foreach($search as $index=>$term)
        {
            if(is_array($term))
            {
                $search[$index] = self::convert_id($term);
            } else {
                if($index === "_id"){

                    $search[$index] = new ObjectID($term);
                }

            }
        }
        return $search;
    }

    public static function find($search = [],$convert = true, $multi = false)
    {
        if ($convert) $search = self::convert_id($search);

        $class = get_called_class();
        $newModel = new $class([],false);

        if (!$multi)
        {
            $newModel->model = $newModel->collection->findOne($search);

            return $newModel;
        }

        $newModels = [];

        $results =  $newModel->collection->find($search);

        foreach($results as $model)
        {
            $newModel = new $class([],false);
            $newModel->model = $model;
            $newModels[] = $newModel;
        }

        return $newModels;

    }

    public static function findOrdered($search = [],$sort = [],$convert = true)
    {
        if ($convert) $search = self::convert_id($search);

        $class = get_called_class();
        $newModel = new $class([],false);

        $newModels = [];

        $results =  $newModel->collection->find($search,["sort"=>$sort]);

        foreach($results as $model)
        {
            $newModel = new $class([],false);
            $newModel->model = $model;
            $newModels[] = $newModel;
        }

        return $newModels;

    }

    public function delete()
    {
        $this->collection->deleteOne(["_id"=>new ObjectID($this->_id)]);

        $this->model = null;

        return $this;

    }

    public static function deleteSoft($search = [])
    {
        $class = get_called_class();
        $newModel = new $class([],false);

        $results =  $newModel->collection->find($search);

        foreach($results as $result)
        {
            // $newModel->delete->insertOne($result->model);
            $newModel->delete->insertOne($result);
            $newModel->collection->deleteOne(["_id"=>$result->_id]);
            // $result->model = null; Can't be right
        }

    }

    public static function valid_id($id)
    {
        if (preg_match("/^[0-9a-fA-F]{24}$/",$id)) return true;
        return false;
    }

}