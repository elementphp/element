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
     * Returns models using raw sql query
     * 	@return array results from database
     * 
     * @param sql  {string} 
     *
     */
	public static function searchRaw($sql){
		$db 		=   self::dba();
		$query  	= 	$db->query($sql);
		$results	= 	$query->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);

        $query = null;
        $db = null;
		return $results;
	}

    /**
     * insert or update
     * 	@return integer last insert id or null
     * 
     * @param className  {string}      
     * @param returnId  {boolean} 
     *
     */
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


    /**
     * delete model
     * 	@return boolean dependant on operation success/failure
     * 
     * @param id  {integer}      
     * @param class  {string} 
     *
     */
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

    /**
     * @return object abstraction of getModelInfo
     */
    protected static function returnModelInfo(){
        return self::$modelInfo;
    }

    /**
     * @return string gets table primary key after running SHOW KEYS
     * 
     * Either grabs the info or returns cached version
     */
    private function getModelInfo($className){
        $className = str_replace("element\mvc\\", "", $className);
        if(!isset(self::$modelInfo[$className])){
            $modelInfo = self::searchRaw("SHOW KEYS FROM " . $className . " WHERE Key_name = 'PRIMARY'");
            self::$modelInfo[$className] =  $modelInfo[$className]["Column_name"];
        } 
        return self::$modelInfo[$className];
    }

    
    /**
     * TODO
     */
    public static function addModel($className, $addData) {
        
        $className = str_replace('element\mvc\\', "", $className);
        
        $db = new DB();
        $pdo = self::dba();

        $info = $db->prepareAddData($addData);
        
        $pdo->beginTransaction();

        $sql = "INSERT INTO $className (" . implode(",", $info["datafields"] ) . ") VALUES " .
		implode(',', $info["question_marks"]);
		$stmt = $pdo->prepare ($sql);

        try {
			$stmt->execute($info["insert_values"]);
		}
		catch (PDOException $e){
			echo $e->getMessage();
		}
		$pdo->commit();
        
    }

    private function prepareAddData($addData) {
        
        if(gettype($addData)!=="array" || ( gettype($addData)==="array" && !isset($addData[0]))) {
            $addData = [ $addData ];
        }

        $responseObj = [
            "datafields" => [],
            "insert_values" => [],
            "question_marks" => []
        ];
        
        $isObjects = gettype($addData[0]) !== "array";

        if($isObjects) {
            
            $responseObj["datafields"] = array_keys( (array)$addData[0] );

            foreach($addData as $d){
                $d = (array)$d;
                array_push($responseObj["question_marks"], '('  . $this->placeholders('?', sizeof($d)) . ')' );
                $responseObj["insert_values"] = array_merge($responseObj["insert_values"], array_values($d));
            }

        } else {

            $responseObj["datafields"] = array_keys( $addData[0] ); 

            foreach($addData as $d){
                array_push($responseObj["question_marks"], '('  . $this->placeholders('?', sizeof($d)) . ')' );                
                $responseObj["insert_values"] = array_merge($responseObj["insert_values"], array_values($d));
            }

        }
        return $responseObj;
    }
    
    private function placeholders($text, $count=0, $separator=","){
		
		$result = array();
		
		if($count > 0){
			for ($x=0; $x<$count; $x++){
				$result[] = $text;
			}
		}
		return implode($separator, $result);
	}


    /**
     * @return array 
     * 
     * used in saveModel function
     */
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

        /**
         * TODO - Don't think the "id" section of array is ever used
         * Not removing immediately, need to test
         */
        return [
            "id" => $primaryKey,
            "cols" => $cols,
            "vals" => $values,
            "dupes" => $dupes
        ];  
    }
}
