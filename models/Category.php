<?php
    class Category {
        //database things
        private $conn;
        private $table = 'categories';

        //category properties
        public $id;
        public $category;
        public $categoryAnswer;
        public $quoteAnswer;

        //constructor with the database
        public function __construct($db){
            $this->conn = $db;
        }

        //get categories
        public function read() {
            $query = 'SELECT c.id, c.category 
                    FROM ' . $this->table . ' c 
                    ORDER BY c.id';

            $stmt = $this->conn->prepare($query);

            $stmt->execute();

            return $stmt;
        }


        //get single category 
        public function read_single() {
            $query = 'SELECT c.id, c.category 
                    FROM ' . $this->table . ' c
                    WHERE c.id = ? 
                    LIMIT 0,1';

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(1, $this->id);

            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->id = $row['id'];
            $this->category = $row['category'];


        }

        public function create(){
            $query = 'INSERT INTO ' . $this->table . ' 
             SET category = ?';

            $stmt = $this->conn->prepare($query);

            $this->author = htmlspecialchars(strip_tags($this->category));

            $stmt->bindParam(1, $this->category);

            if($stmt->execute()){
                $query2 = 'SELECT * FROM ' . $this->table . ' WHERE category = ? ORDER BY id DESC';
                $stmt2 = $this->conn->prepare($query2);
                $stmt2->bindParam(1, $this->category);
                $stmt2->execute();
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);

                $this->id = $row['id'];
                $this->category = $row['category'];
                return true;
            }

            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        //PUT function for category
        public function update(){
            $query = 'UPDATE ' . $this->table . ' 
             SET category = ? WHERE id = ?';

            $stmt = $this->conn->prepare($query);

            $this->category = htmlspecialchars(strip_tags($this->category));
            $this->id = htmlspecialchars(strip_tags($this->id));

            $stmt->bindParam(1, $this->category);
            $stmt->bindParam(2, $this->id);

            if($stmt->execute()){
                $query2 = 'SELECT * FROM ' . $this->table . ' WHERE category = ? ORDER BY id DESC';
                $stmt2 = $this->conn->prepare($query2);
                $stmt2->bindParam(1, $this->category);
                $stmt2->execute();
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);

                $this->id = $row['id'];
                $this->author = $row['category'];
                return true;
            }

            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        public function isValidCategory(){
            $query = 'SELECT *  
                    FROM ' . $this->table . ' c 
                    WHERE c.id = ?';

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(1, $this->id);

            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->categoryAnswer = $row['category'];
        }

        //DELETE function for category
        public function delete(){
            $query = 'DELETE FROM ' . $this->table . ' 
              WHERE id = ?';

            $stmt = $this->conn->prepare($query);

            $this->id = htmlspecialchars(strip_tags($this->id));

            $stmt->bindParam(1, $this->id);

            if($stmt->execute()){
                return true;
            }

            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        public function inQuote(){
            $query = 'SELECT *  
                    FROM quotes q 
                    WHERE q.categoryId = ?';

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(1, $this->id);

            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->quoteAnswer = $row['quote'];
        }


    }