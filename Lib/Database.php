<?php

Class Database extends mysqli
{

    private static $_instance;

    protected $db;

//    protected $dbHost       = "localhost";
//    protected $dbUsername   = "root";
//    protected $dbPassword   = "";
//    protected $dbDatabase   = "crawler";

    protected $dbHost       ="db518842993.db.1and1.com";
    protected $dbUsername   = "dbo518842993";
    protected $dbPassword   = "riverline2013";
    protected $dbDatabase   = "db518842993";
    protected $charset      = "UTF8";

    /**
     * Empêche la création externe d'instances.
     */
    public function __construct () {
        parent::__construct($this->dbHost, $this->dbUsername, $this->dbPassword, $this->dbDatabase);

        if (mysqli_connect_error()) {
            die('Erreur de connexion (' . mysqli_connect_errno() . ') '
                    . mysqli_connect_error());
        }

        $this->query("SET NAMES ". $this->charset);
    }

    /**
     * Empêche la copie externe de l'instance.
     */
    private function __clone () {}

    /**
     * Renvoi de l'instance et initialisation si nécessaire.
     */
    public static function getInstance () {
        if (!(self::$_instance instanceof self))
            self::$_instance = new self();

        return self::$_instance;
    }

    /**
     * @return \mysqli
     */
    public function getDb()
    {
        return $this->db;
    }

    // Performs a 'mysql_real_escape_string' on the entire array/string
	private function SecureData($data){
		if(is_array($data)){
			foreach($data as $key=>$val){
				if(!is_array($data[$key])){
					$data[$key] = $this->escape_string($data[$key]);
				}
			}
		}else{
			$data = $this->escape_string($data);
		}
		return $data;
	}

    public function Insert($vars, $table, $exclude = '', $secureData = true){

		// Catch Exclusions
		if($exclude == ''){
			$exclude = array();
		}

		array_push($exclude, 'MAX_FILE_SIZE'); // Automatically exclude this one

		// Prepare Variables
		$vars = $secureData ? $this->SecureData($vars) : $vars;

		$query = "INSERT INTO `{$table}` SET ";
		foreach($vars as $key=>$value){
			if(in_array($key, $exclude)){
				continue;
			}
			//$query .= '`' . $key . '` = "' . $value . '", ';
			$query .= "`{$key}` = '{$value}', ";
		}

		$query = substr($query, 0, -2);

		return $this->query($query);
	}

    // Updates a record in the database based on WHERE
	public function Update($table, $set, $where, $exclude = '',$secureData = true){
		// Catch Exceptions
		if(trim($table) == '' || !is_array($set) || !is_array($where)){
			return false;
		}
		if($exclude == ''){
			$exclude = array();
		}

		array_push($exclude, 'MAX_FILE_SIZE'); // Automatically exclude this one

		$set 		= $secureData ? $this->SecureData($set) : $set;
		$where 	    = $secureData ? $this->SecureData($where) : $where;

		// SET

		$query = "UPDATE `{$table}` SET ";

		foreach($set as $key=>$value){
			if(in_array($key, $exclude)){
				continue;
			}
			$query .= "`{$key}` = '{$value}', ";
		}

		$query = substr($query, 0, -2);

		// WHERE

		$query .= ' WHERE ';

		foreach($where as $key=>$value){
			$query .= "`{$key}` = '{$value}' AND ";
		}

		$query = substr($query, 0, -5);

		return $this->query($query);
	}
}