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
	 * searchRaw
	 *
	 * @param  string $sql
	 *
     * Returns models using raw sql query
     * 
	 * @return array
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
     * saveModel
     *
     * @param  string $className
     * @param  boolean $returnId
     *
     * insert or update
     * 
     * @return mixed 
     * (Either last insert id or null)
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
     * deleteModel
     *
     * @param  string $id
     * @param  string $class
     *
     * @return boolean
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
     * returnModelInfo
     * 
     * abstraction of getModelInfo
     * 
     * @return string
     */
    protected static function returnModelInfo(){
        return self::$modelInfo;
    }


    /**
     * getModelInfo
     *
     * @param string $className
     * 
     * Gets table primary key after running SHOW KEYS
     * Either grabs the info or returns cached version
     *
     * @return void
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
     * upsertModel
     *
     * @param  string $className
     * @param  mixed $upsertData
     *
     * @return boolean $isOk 
     */
    public static function upsertModel($className, $upsertData) {
        
        $isOk = false;

        $className = str_replace('element\mvc\\', "", $className);
        
        $db = new DB();
        $pdo = self::dba();

        $primaryKey = $db->getModelInfo($className);
        
        $info = $db->prepareUpsertData($upsertData, $primaryKey);
        
        $pdo->beginTransaction();

        $sql = "INSERT INTO $className (" . implode(",", $info["datafields"] ) . ") VALUES " . implode(',', $info["question_marks"]) . " ON DUPLICATE KEY UPDATE ";
        
        foreach($info["datafields"] as $key) {
            $sql .= "$key = VALUES($key),"; 
        }
        $sql = rtrim($sql,",");

		$stmt = $pdo->prepare ($sql);

        try {
            $stmt->execute($info["upsert_values"]);
            $pdo->commit();
            $isOk = true;
		}
		catch (PDOException $e){
            $pdo->rollBack();
        }
        $stmt = null;
        $pdo = null;
        return $isOk;
    }

    /**
     * prepareUpsertData
     *
     * @param  mixed $upsertData
     * @param  string $primaryKey
     *
     * @return array $responseObj
     */
    private function prepareUpsertData($upsertData, $primaryKey) {
        
        if(gettype($upsertData)!=="array" || ( gettype($upsertData)==="array" && !isset($upsertData[0]))) {
            $upsertData = [ $upsertData ];
        }

        $responseObj = [
            "datafields" => [],
            "upsert_values" => [],
            "question_marks" => []
        ];

        $isObjects = gettype($upsertData[0]) !== "array";

        $primer = (array)$upsertData[0];
        $hasPrimary = array_key_exists($primaryKey, $primer);

        $responseObj["datafields"] = array_keys( $primer );

        if($hasPrimary) {

            if($isObjects) {
            
                foreach($upsertData as $d){
                    $d = (array)$d;
                    array_push($responseObj["question_marks"], '('  . $this->placeholders('?', sizeof($d)) . ')' );
                    $responseObj["upsert_values"] = array_merge($responseObj["upsert_values"], array_values($d));
                }
    
            } else {
    
                foreach($upsertData as $d){
                    array_push($responseObj["question_marks"], '('  . $this->placeholders('?', sizeof($d)) . ')' );                
                    $responseObj["upsert_values"] = array_merge($responseObj["upsert_values"], array_values($d));
                }
    
            }

        } else {
            if($isObjects) {
                
                foreach($upsertData as $d){
                    $d = (array)$d;
                    $d = array($primaryKey => null) + $d;
                    array_push($responseObj["question_marks"], '('  . $this->placeholders('?', sizeof($d)) . ')' );
                    $responseObj["upsert_values"] = array_merge($responseObj["upsert_values"], array_values($d));
                }

            } else {

                foreach($upsertData as $d){
                    $d = array($primaryKey => null) + $d;
                    array_push($responseObj["question_marks"], '('  . $this->placeholders('?', sizeof($d)) . ')' );                
                    $responseObj["upsert_values"] = array_merge($responseObj["upsert_values"], array_values($d));
                }

            }
        }
        return $responseObj;
    }
    

    /**
     * addModel
     *
     * @param  string $className
     * @param  mixed $addData
     *
     * @return boolean $isOk
     */
    public static function addModel($className, $addData) {
        
        $isOk = false;

        $className = str_replace('element\mvc\\', "", $className);
        
        $db = new DB();
        $pdo = self::dba();

        $primaryKey = $db->getModelInfo($className);

        $info = $db->prepareAddData($addData, $primaryKey);
        
        $pdo->beginTransaction();

        $sql = "INSERT INTO $className (" . implode(",", $info["datafields"] ) . ") VALUES " .
		implode(',', $info["question_marks"]);
		$stmt = $pdo->prepare ($sql);

        try {
            $stmt->execute($info["insert_values"]);
            $pdo->commit();
            $isOk = true;
		}
		catch (PDOException $e){
            $pdo->rollBack();
        }
        $stmt = null;
        $pdo = null;
        return $isOk;
    }



    /**
     * prepareAddData
     *
     * @param  mixed $addData
     * @param  string $primaryKey
     *
     * @return array
     */
    private function prepareAddData($addData, $primaryKey) {
        
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

            $keypos = 0;
            $keylen = count($responseObj["datafields"]);
            for($i = 0; $i < $keylen; $i++) {
                if( $responseObj["datafields"][$i]===$primaryKey ) {
                    $keypos = $i;
                    break;
                }
            }
            unset($responseObj["datafields"][$keypos]);

            foreach($addData as $d){
                unset($d->$primaryKey);
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
    
    /**
     * placeholders
     *
     * @param  string $text
     * @param  integer $count
     * @param  string $separator
     *
     * helper for prepareAddData and prepareUpsertData
     * 
     * @return void
     */
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
     * prepareData
     *
     * @param  string $className
     * @param  object $db
     * @param  boolean $returnId
     *
     * @return void
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
