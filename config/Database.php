<?php
//http://localhost/bwatson-midterm/api/quotes/
//https://bwatson-midterm.herokuapp.com/api/quotes/

class Database {
    //database properties
    private $host = 'acw2033ndw0at1t7.cbetxkdyhwsb.us-east-1.rds.amazonaws.com';
    private $db_name = 'hebbvymz8qd8v5sq';
    private $username = 'e49uxd1dsch86tr1';
    private $password;
    private $conn;

    //database constructor
    function __construct(){
        $this->password = getenv('DATA_PASS');
    }
    

    /* Local testing database properties
    private $host = 'localhost';
    private $db_name = 'quotesdb';
    private $username = 'root';
    private $password;
    private $conn;
    */

    //Connection function
    public function connect() {
        $this->conn = null;

        try 
        {
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $e)
        {
            echo 'Connection Error: '.$e->getMessage();
        }

        return $this->conn;
    }
}

?>