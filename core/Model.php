<?php

namespace element\mvc;

class Model extends DB{

    public static function getOne($query, $params){
        $resp = self::getMany($query, $params, 1);
        
        if($resp && gettype($resp)==="array"){
            return $resp[0];
        } else {
            return false;
        }
        
    }

    public static function getAll(){
        $tableName = self::_get_class_name();
        $results = DB::allModel("select * from " . $tableName, $tableName);
        return $results;
    }

    
    public static function getById($id){
        $class = self::_get_class_name();
        $results = DB::searchModelById($id, $class);
        if( count($results) > 0 ){
            return $results[0];
        } else {
            return [];
        }
        
    }

    public static function deleteById($id){
        $class = self::_get_class_name();
        $deleted = DB::deleteModel($id, $class);
        return $deleted;
    }

    public static function getMany($query, $params, $limit = null){
        $class = self::_get_class_name();
        $results = DB::searchModel($query, $params, $class, $limit);
        return $results;
    }

    public static function add($addData) {
        $class = self::_get_class_name();
        $result = DB::addModel($class, $addData);
        return $result;
    }

    public static function upsert($updateData) {
        $class = self::_get_class_name();
        $result = DB::upsertModel($class, $updateData);
        return $result;
    }


    public function save($returnId=false){
        try{
            $className = str_replace('element\mvc\\', "", self::_get_class_name());
            $m = $this->saveModel($className, $returnId);
            
            if($returnId) {
                $ids = DB::returnModelInfo();
                if(!$this->{$ids[$className]}){
                    $this->{$ids[$className]} = $m;
                }
            }
            return true;
        } catch(Exception $ex) {
            return false;
        }
    }



    static function _get_class_name() {
        $classname =  get_called_class(); //str_replace("element\mvc\\", "", get_called_class());
        return strtolower($classname);
    }
    
}