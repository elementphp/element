<?php

namespace element\mvc;

class DAL {

	/**
	 * 
	 * @var array
	 */
	private $_config 	= [];
	
	/**
	 * 
	 * @var array
	 */
	private $_pdo		= [];
	
	/**
	 * <p>Takes config, returns PDO</p>
	 * 
	 */
	public function __construct(){
		$this->_config = \element\core\Config::getSection("db");
	}
	
	public function getDb()
	{
		if (empty($this->_pdo['db'])
				|| !is_a($this->_pdo['db'], 'PDO')
				) {
					$this->_pdo['db'] = new \PDO(
							'mysql:dbname=' . $this->_config['dsn'] . ';host=' . $this->_config['host'],
							$this->_config['user'],
							$this->_config['pw'],
							[\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
							);
				}
				return $this->_pdo['db'];
	}
}