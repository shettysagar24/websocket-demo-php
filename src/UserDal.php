<?php
namespace Demo;

Class UserDal{

    //dummy token based authentication & authorization
    public static $acl = [
      "1565036611" => ["role"=>"accountant", "user_id" => 1],
      "2487003290" => ["role"=>"client", "user_id" => 2],
      "5334790842" => ["role"=>"client", "user_id" => 3]
    ];

    public static $authorizePublisherRole = ["accountant"];


    public function authorizedToPublish($token){
        if(in_array($token, $this::$acl)){
            return in_array($this::$acl[$token]['role'], $this::$authorizePublisherRole);
        }
        return false;
    }

    public function authenticateUser($token){
        return in_array($token, $this::$acl);
    }
}