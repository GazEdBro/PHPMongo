<?php

namespace models;

use \Knp\Snappy\Pdf;
use \MongoDB\BSON\ObjectID as ObjectID;

class Document extends Model
{

    protected $collectionName;
    protected $template = [
        "title" => "",
        "issue" => 1,
        "current" => true,
        "type" => "",
        "number" => "",
        "status" => "Pending",
        "previous" => [],
        "reviews" => [],
        "editDocument" => [
            "location" => "",
            "name" => "",
        ],
        "viewDocument" => [
            "location" => "",
            "name" => "",
        ],
        "editable" => false
    ];


    function __construct($newModel = [], $create = true)
    {
        $this->collectionName = "documents";

        parent::__construct($newModel,$create);

        if ($this->type == "html")
        {
            $this->pdf = $this->makePdf();

            parent::save();

        }
    }

    function initialize(&$newModel = [], $create)
    {
        if($create) {
            global $user;

            $this->template["reviewDate"] =  new \DateTime('+1 Week');
            $this->template["publishDate"] =  new \DateTime();
            $this->template["owner"] = $user->keyData();
            $this->template["author"] = $user->keyData();

            unset($newModel['csrf_value']);
            unset($newModel['csrf_name']);
        }

    }

    public function save()
    {
        $this->collection->updateOne(["_id" => $this->_id],['$set'=>['current'=>false]]);

        $this->updated_at = new \DateTime();

        $previous = (array) $this->previous;

        array_push($previous,(string) $this->_id);

        $this->previous = $previous;

        unset($this->model->_id);

        $this->issue++;

        $id = $this->collection->insertOne($this->model);

        $this->_id = $id->getInsertedId();

    }

    function makePdf()
    {
        $snappy = new Pdf('/usr/local/bin/wkhtmltopdf');

        $directory = "../../storage/documents/";

        $fileName = uniqid();

        $render = new \Slim\Views\Blade('../views','../cache');

        $snappy->generateFromHtml($render->fetch('review/' . $this->type ,['router'=>null,'mongo'=>$this->mongo,'document'=>$this]),"$directory$fileName");

        return $fileName;

    }
    public function gentleSave()
    {
        parent::save();
    }

    public function keyData($type='viewDocument')
    {
        return [
            "_id" => $this->_id,
            "title" => $this->title,
            "type" => $this->type,
            "location" => isset($this->{$type}->location) ? $this->{$type}->location : $this->{$type}['location']
        ];
    }


}