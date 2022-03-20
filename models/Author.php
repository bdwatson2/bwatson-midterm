<?php
    class Author {
        //database things
        private $conn;
        private $table = 'authors';

        //Author properties
        public $id;
        public $author;

        //constructor with the database
        public function __construct($db){
            $this->conn = $db;
        }

        //get authors
        public function read() {
            $query = 'SELECT a.id, a.author 
                    FROM ' . $this->table . ' a 
                    ORDER BY a.id';

            $stmt = $this->conn->prepare($query);

            $stmt->execute();

            return $stmt;
        }


        //get single author 
        public function read_single() {
            $query = 'SELECT a.id, a.author 
                    FROM ' . $this->table . ' a
                    WHERE a.id = ? 
                    LIMIT 0,1';

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(1, $this->id);

            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->id = $row['id'];
            $this->author = $row['author'];


        }

        //POST function for author
        public function create(){
            $query = 'INSERT INTO ' . $this->table . ' 
             SET author = ?';

            $stmt = $this->conn->prepare($query);

            $this->author = htmlspecialchars(strip_tags($this->author));

            $stmt->bindParam(1, $this->author);

            if($stmt->execute()){
                $query2 = 'SELECT * FROM ' . $this->table . ' WHERE author = ? ORDER BY id DESC';
                $stmt2 = $this->conn->prepare($query2);
                $stmt2->bindParam(1, $this->author);
                $stmt2->execute();
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);

                $this->id = $row['id'];
                $this->author = $row['author'];
                return true;
            }

            printf("Error: %s.\n", $stmt->error);
            return false;
        }





    }