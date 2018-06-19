<?php

namespace models;

class User extends Model
{

    protected $collectionName = "users";
    protected $template =
    [
        "username" => "",
        "password" => "",
        "surname" => "",
        "firstname" => "",
        "dob" => "",
        "email" => "",
        "telephone" => "",
        "mobile" => "",
        "company" => "",
        "name" => "",
        "title" => "",
        "messages" => [],
        "tasks" => [],
        "notifications" => [],
        "currentTasks" => [
            "red" => [],
            "amber" => [],
            "green" => [],
        ],
        "image" => "",
        "permissions" => [
            "organisation" => [
                "edit" => false,
                "view" => false
            ],
            "management" => [
                "edit" => false,
                "view" => false
            ],
            "test" => [
                "edit" => false,
                "view" => false
            ],
            "dashboard" => [
                "edit" => false,
                "view" => false
            ],
            "incident" => [
                "edit" => false,
                "view" => false
            ],
            "risk" => [
                "edit" => false,
                "view" => false
            ],
            "training" => [
                "edit" => false,
                "view" => false
            ],
            "operations" => [
                "edit" => false,
                "view" => false
            ],
            "construction" => [
                "edit" => false,
                "view" => false
            ],
            "executive" => [
                "edit" => false,
                "view" => false
            ],
            "audit" => [
                "edit" => false,
                "view" => false
            ]
        ]
    ];

    function keyData()
    {
        return [
            "_id"=>$this->_id,
            "name"=>$this->name,
            "telephone"=>$this->telephone,
            "mobile"=>$this->mobile,
            "email"=>$this->email
        ];
    }

    function training($type="")
    {

        $training = "";

        foreach($this->skillData as $skill)
        {
            if($skill->type == $type) $training .= $skill->description . "<br />";
        }

        return $training;

    }

}