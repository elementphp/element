<?php

namespace element\mvc;

class DB{
    
    private static $modelInfo = [];
    
    /**
     * 	@return PDO object
     */
    private static function dba(){
        try{
		    $db = new DAL();
		    $dbo = $db->getDb();
		    return $dbo;
        } catch(Exception $e){
            echo $e->getMessage();
        }
	}

    /**
     * Returns a single model using primary key as reference
     * 	@return array results from database
     * 
     * @param id {string/integer}
     * @param class {string}
     */
    public static function searchModelById($id, $class){

        $db = new DB();
        $key = $db->getModelInfo($class);
        $keyPlace = ":" . $key;
        $query = $key . " = " . $keyPlace;

        $params = [$keyPlace=>$id];
        $limit = 1;
        
        $db = null;

        return self::searchModel( $query, $params, $class, $limit);
    }

    /**
     * Returns all models with given class name
     * 	@return array results from database
     * 
     * NOTE: SQL can be overridden for other purposes as required
     *
     * @param sql 	{string}
     * @param class {string}
     */
    public static function allModel($sql, $class){
		$db 		= 	self::dba();
		$query  	= 	$db->query(str_replace("element\mvc\\", "", $sql));
		$results	= 	$query->fetchAll(\PDO::FETCH_CLASS, $class);

        $query = null;
        $db = null;
		return $results;
	}

    /**
     * Returns models using parameterized query
     * 	@return array results from database
     * 
     * @param query  {string} 
     * @param params {array} 
     * @param class  {string} 
     * @param limit  {integer} 
     */
    public static function searchModel($query, $params, $class, $limit){

        $db = self::dba();
        $sql = "SELECT * FROM " . str_replace("element\mvc\\", "", $class) . " WHERE " . $query;
        if($limit) $sql .= " LIMIT " . $limit;

        $sth = $db->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        $sth->execute($params);
        $results = $sth->fetchAll(\PDO::FETCH_CLASS, $class);

        $sth = null;
        $db = null;

		return $results;
	}

    /**
     * STUFF
     */
	public static function searchRaw($sql){
		$db 		=   self::dba();
		$query  	= 	$db->query($sql);
		$results	= 	$query->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);

        $query = null;
        $db = null;
		return $results;
	}


    public function saveModel($className, $returnId=false){

        $className = str_replace('element\mvc\\', "", $className);
        $db = self::dba();
        $info = $this->prepareData($className, $db, $returnId);

        $statement = sprintf( "INSERT INTO %s ( %s ) VALUES %s ON DUPLICATE KEY UPDATE %s ", $className,$info['cols'],$info['vals'],$info['dupes']);

        $db->query( $statement );

        $lastId = null;

        if($returnId){
            $lastId = $db->lastInsertId();
        } 
        $db = null;
        return $lastId;
    }

    public static function deleteModel($id, $class){

        try{
            $db = new DB();
            $key = $db->getModelInfo($class);
            $keyPlace = ":" . $key;
            $query = $key . " = " . $keyPlace;

            $params = [$keyPlace=>$id];

            $db = self::dba();
            $sql = "DELETE FROM " . $class . " WHERE " . $query;
        
            $sth = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->execute($params);
            
            $sth = null;
            $db = null;
            return true;
        } catch(Exception $ex){
            $e = $ex->getMessage();
            return false;
        }
    }
    
    protected static function returnModelInfo(){
        return self::$modelInfo;
    }

    private function getModelInfo($className){
        $className = str_replace("element\mvc\\", "", $className);
        if(!isset(self::$modelInfo[$className])){
            $modelInfo = self::searchRaw("SHOW KEYS FROM " . $className . " WHERE Key_name = 'PRIMARY'");
            self::$modelInfo[$className] =  $modelInfo[$className]["Column_name"];
        } 
        return self::$modelInfo[$className];
    }


    private function prepareData($className, $db, $returnId=false) {
        
        $info = $this->getModelInfo($className);
        $primaryKey = $info;

        $me = (array)$this;
        
        $dupes = "";

        if( empty($me[ $primaryKey ]) ) {
            unset($me[ $primaryKey ]);
        } else if($returnId) {
            $dupes .= $primaryKey."=LAST_INSERT_ID(" . $primaryKey . "),";
        }

        $vals = [];
       
        foreach( $me as $key => $value ){

            if($value === null){
                unset($me[$key]);
            } else {
                $vals[] = (gettype($value)==="string") ? $db->quote($value) : $value;
                $dupes .= $key . "=VALUES(" . $key . "),";
            }
        }

        $keys = array_keys($me);
        $cols = join(",",$keys);

        $values   = "(" . join($vals,",") . ")";
        $dupes   = rtrim($dupes,",");

        return [
            "id" => $primaryKey,
            "cols" => $cols,
            "vals" => $values,
            "dupes" => $dupes
        ];  
    }
}
