<?php
    class Quote {
        //database things
        private $conn;
        private $table = 'quotes';

        //Quote properties
        public $id;
        public $quote;
        public $author;
        public $category;
        public $authorId;
        public $categoryId;
        public $authorAnswer;
        public $categoryAnswer;
        public $quoteAnswer;

        //constructor with the database
        public function __construct($db){
            $this->conn = $db;
        }

        //get quotes
        public function read() {
            $query = 'SELECT q.id, q.quote, a.author, c.category 
                    FROM ' . $this->table . ' q 
                    LEFT JOIN 
                        authors a ON q.authorId = a.id 
                    LEFT JOIN 
                        categories c ON q.categoryId = c.id
                    ORDER BY q.id';

            $stmt = $this->conn->prepare($query);

            $stmt->execute();

            return $stmt;
        }

        //get single quote by quote id
        public function read_single() {
            $query = 'SELECT q.id, q.quote, a.author, c.category 
                    FROM (' . $this->table . ' q 
                    LEFT JOIN 
                        authors a ON q.authorId = a.id 
                    LEFT JOIN 
                        categories c ON q.categoryId = c.id) 
                    WHERE q.id = ? 
                    LIMIT 0,1';


            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(1, $this->id);

            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->id = $row['id'];
            $this->quote = $row['quote'];
            $this->author = $row['author'];
            $this->category = $row['category'];


        }


        //get quote(s) by author id
        public function read_authorId() {
            $query = 'SELECT q.id, q.quote, a.author, c.category 
            FROM ' . $this->table . ' q 
            LEFT JOIN 
                authors a ON q.authorId = a.id 
            LEFT JOIN 
                categories c ON q.categoryId = c.id
            WHERE a.id = ?';

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(1, $this->authorId);

            $stmt->execute();

            return $stmt;
            
        }

        //get quote(s) by category id
        public function read_categoryId() {
            $query = 'SELECT q.id, q.quote, a.author, c.category 
            FROM ' . $this->table . ' q 
            LEFT JOIN 
                authors a ON q.authorId = a.id 
            LEFT JOIN 
                categories c ON q.categoryId = c.id
            WHERE c.id = ?';

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(1, $this->categoryId);

            $stmt->execute();

            return $stmt;
            
        }

        //get quote(s) by author id & category id 
        public function read_aId_and_cId() {
            $query = 'SELECT q.id, q.quote, a.author, c.category 
            FROM ' . $this->table . ' q 
            LEFT JOIN 
                authors a ON q.authorId = a.id 
            LEFT JOIN 
                categories c ON q.categoryId = c.id
            WHERE a.id = ? AND c.id = ?';

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(1, $this->authorId);
            $stmt->bindParam(2, $this->categoryId);

            $stmt->execute();

            return $stmt;
            
        }

        //POST function for quote
        public function create(){
            $query = 'INSERT INTO ' . $this->table . ' 
             SET quote = ?, authorId = ?, categoryId = ?';

            $stmt = $this->conn->prepare($query);

            $this->quote = htmlspecialchars(strip_tags($this->quote));
            $this->authorId = htmlspecialchars(strip_tags($this->authorId));
            $this->categoryId = htmlspecialchars(strip_tags($this->categoryId));

            $stmt->bindParam(1, $this->quote);
            $stmt->bindParam(2, $this->authorId);
            $stmt->bindParam(3, $this->categoryId);

            if($stmt->execute()){
                $query2 = 'SELECT * FROM ' . $this->table . ' WHERE quote = ? ORDER BY id DESC';
                $stmt2 = $this->conn->prepare($query2);
                $stmt2->bindParam(1, $this->quote);
                $stmt2->execute();
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);

                $this->id = $row['id'];
                $this->quote = $row['quote'];
                $this->authorId = $row['authorId'];
                $this->categoryId = $row['categoryId'];
                return true;
            }

            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        public function isValidAuthor(){
            $query = 'SELECT *  
                    FROM authors a 
                    WHERE a.id = ?';

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(1, $this->authorId);

            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->authorAnswer = $row['author'];
        }

        public function isValidCategory(){
            $query = 'SELECT *  
                    FROM categories c 
                    WHERE c.id = ?';

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(1, $this->categoryId);

            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->categoryAnswer = $row['category'];
        }

        //PUT function for quote
        public function update(){
            $query = 'UPDATE ' . $this->table . ' 
             SET quote = ?, authorId = ?, categoryId = ? WHERE id = ?';

            $stmt = $this->conn->prepare($query);

            $this->quote = htmlspecialchars(strip_tags($this->quote));
            $this->authorId = htmlspecialchars(strip_tags($this->authorId));
            $this->categoryId = htmlspecialchars(strip_tags($this->categoryId));
            $this->id = htmlspecialchars(strip_tags($this->id));

            $stmt->bindParam(1, $this->quote);
            $stmt->bindParam(2, $this->authorId);
            $stmt->bindParam(3, $this->categoryId);
            $stmt->bindParam(4, $this->id);

            if($stmt->execute()){
                $query2 = 'SELECT * FROM ' . $this->table . ' WHERE quote = ? ORDER BY id DESC';
                $stmt2 = $this->conn->prepare($query2);
                $stmt2->bindParam(1, $this->quote);
                $stmt2->execute();
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);

                $this->id = $row['id'];
                $this->quote = $row['quote'];
                $this->authorId = $row['authorId'];
                $this->categoryId = $row['categoryId'];
                return true;
            }

            printf("Error: %s.\n", $stmt->error);
            return false;
        }

        public function isValidQuote(){
            $query = 'SELECT *  
                    FROM ' . $this->table . ' q 
                    WHERE q.id = ?';

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(1, $this->id);

            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->quoteAnswer = $row['quote'];
        }

        //DELETE function for quote
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


    }