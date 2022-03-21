<?php
    class Author {
        //database things
        private $conn;
        private $table = 'authors';

        //Author properties
        public $id;
        public $author;
        public $authorAnswer;
        public $quoteAnswer;

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

            //preparing the query
            $stmt = $this->conn->prepare($query);

            //cleaning data
            $this->author = htmlspecialchars(strip_tags($this->author));

            //binding parameters
            $stmt->bindParam(1, $this->author);

            //i've put in a second statement here to make sure that I can grab
            //the new id of the author that's been created
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

        //PUT function for author
        public function update(){
            $query = 'UPDATE ' . $this->table . ' 
             SET author = ? WHERE id = ?';

            //preparing the query
            $stmt = $this->conn->prepare($query);

            //cleaning data
            $this->author = htmlspecialchars(strip_tags($this->author));
            $this->id = htmlspecialchars(strip_tags($this->id));

            //binding parameters
            $stmt->bindParam(1, $this->author);
            $stmt->bindParam(2, $this->id);

            //grabbing updated info
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

        //this function checks to see if any authors are found
        //for a specific author id, stores either the author or NULL
        //in authorAnswer
        public function isValidAuthor(){
            $query = 'SELECT *  
                    FROM ' . $this->table . ' a 
                    WHERE a.id = ?';

            //prepare query
            $stmt = $this->conn->prepare($query);

            //bind parameters
            $stmt->bindParam(1, $this->id);

            //execute the query statement
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            //store the author answer
            $this->authorAnswer = $row['author'];
        }

        //DELETE function for author
        public function delete(){
            $query = 'DELETE FROM ' . $this->table . ' 
              WHERE id = ?';

            $stmt = $this->conn->prepare($query);

            //clean id
            $this->id = htmlspecialchars(strip_tags($this->id));

            $stmt->bindParam(1, $this->id);

            if($stmt->execute()){
                return true;
            }

            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        /*this function checks to see if the author is being used by a quote,
        and stores the answer in quoteAnswer (if a quote is found, quoteAnswer
        holds that quote, otherwise, it's NULL)*/
        public function inQuote(){
            $query = 'SELECT *  
                    FROM quotes q 
                    WHERE q.authorId = ?';

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(1, $this->id);

            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->quoteAnswer = $row['quote'];
        }


    }