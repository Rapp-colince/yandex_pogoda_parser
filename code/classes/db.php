<?php

class DB{
    private static $instance;
    private static $isConnect = false;
    
    private function __construct(){}

    public static function getInstance(){
        if(self::$instance === NULL){
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private static function connect(){
        $link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
        mysql_select_db(DB_NAME, $link);
        self::$isConnect = true;
    }
    
    public static function query($sql, $param = ''){
        if(self::$isConnect !== true){
            self::connect();
        }
        $result = mysql_query($sql) or die($sql.'<br/>'.(IS_DEBUG===true ? mysql_error() : 'mysql_error'));
        switch ($param){
            case 'lastInsertId':
                return mysql_insert_id();
            default: 
                return $result;
        }
    }
    
    public static function select($sql, $count=0){
        if(!$result = self::query($sql)){
            return array();
        }
        if(mysql_num_rows($result)===0){
            return array();
        }
        $data = array();
        while($row = mysql_fetch_assoc($result)){
            if($count===1){
                return $row;
            }
            $data[] = $row;
        }
        return $data;
    }
    
    public function __clone(){}
    public function __wakeup(){}

}
