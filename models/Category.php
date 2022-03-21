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

            //preparing, binding, executing the query
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->id = $row['id'];
            $this->category = $row['category'];


        }

        //create a category
        public function create(){
            $query = 'INSERT INTO ' . $this->table . ' 
             SET category = ?';

            //preparing the query
            $stmt = $this->conn->prepare($query);

            //cleaning the author
            $this->author = htmlspecialchars(strip_tags($this->category));

            //binding a parameter
            $stmt->bindParam(1, $this->category);

            //by ordering by DESC, we can ensure that the most recently created
            //category is returned in the event two categories are the same
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

            //preparing the query
            $stmt = $this->conn->prepare($query);

            //cleaning the data
            $this->category = htmlspecialchars(strip_tags($this->category));
            $this->id = htmlspecialchars(strip_tags($this->id));

            //binding parameters
            $stmt->bindParam(1, $this->category);
            $stmt->bindParam(2, $this->id);

            //getting updated category info
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

        /*this function checks to make sure the category
        exists, if it doesn't, the categoryAnswer returns NULL*/
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

            //preparing, cleaning, binding
            $stmt = $this->conn->prepare($query);
            $this->id = htmlspecialchars(strip_tags($this->id));
            $stmt->bindParam(1, $this->id);

            if($stmt->execute()){
                return true;
            }

            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        //a function to see if the category exists in a quote
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