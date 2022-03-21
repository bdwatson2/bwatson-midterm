<?php

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    $method = $_SERVER['REQUEST_METHOD'];

    //for CORS
    if ($method === 'OPTIONS'){
        header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE');
        header('Access-Control-Allow-Headers: Origin, Accept, Content-type, X-Requested-With');
    }
    
    include_once '../../config/Database.php';
    include_once '../../models/Author.php';


    //database initialization 
    $database = new Database();
    $db = $database->connect();

    //new author object
    $authors = new Author($db);



    //if the method is read all
    if ($method === 'GET' && isset($_GET['id']) === false){
    
        $result = $authors->read();

        $num = $result->rowCount();

        //if there is at least one author in the database
        if($num > 0) {
            $authors_arr = array();

            while ($row = $result->fetch(PDO::FETCH_ASSOC)){
                extract($row);

                $author_item = array(
                    'id' => $id,
                    'author' => $author
                );

                array_push($authors_arr, $author_item);
            }

            echo json_encode($authors_arr);
        }
        else 
        {
            echo json_encode(
                array('message' => 'No Authors Found')
            );
        }
    }

    //if there is an id alongside the get, we read single
    if ($method === 'GET' && isset($_GET['id']) === true){

        $authors->id = $_GET['id'];

        $authors->read_single();

        $authors_arr = array(
            'id' => $authors->id,
            'author' => $authors->author
        );

        //the id won't be NULL because it's been submitted with the request, 
        //but the author will be NULL if there's no entry
        if ($authors->author === NULL){
            echo json_encode (
                array('message' => 'authorId Not Found')
            );

        }else{
            print_r(json_encode($authors_arr));
        }
    }

    //create a new author
    if ($method === 'POST'){
        $data = json_decode(file_get_contents("php://input"));

        $authors->author = $data->author;

        //if there wasn't an author in the request body
        if ($authors->author === NULL)
        {
            echo json_encode (array('message' => 'Missing Required Parameters'));
            return;
        }

        if($authors->create()){
            $authors_arr = array(
                'id' => $authors->id,
                'author' => $authors->author
            );
            echo json_encode ($authors_arr);
        }
        else {
            echo json_encode (
                array('message'=>'Author Not Created Error')
            );
        }
    }

    //update an existing author
    if ($method === 'PUT'){
        $data = json_decode(file_get_contents("php://input"));

        $authors->author = $data->author;
        $authors->id = $data->id;

        //if there's no author or author id in the request body
        if ($authors->author === NULL || $authors->id === NULL)
        {
            echo json_encode (array('message' => 'Missing Required Parameters'));
            return;
        }

        //checking to make sure there's an author attached to the id
        $authors->isValidAuthor();

        //if no authors are attached to the id, then the id doesn't exist
        if($authors->authorAnswer === NULL) {
            echo json_encode(
                array('message' => 'authorId Not Found')
            );
            return;
        }

        if($authors->update()){
            $authors_arr = array(
                'id' => $authors->id,
                'author' => $authors->author
            );
            echo json_encode ($authors_arr);
        }
        else {
            echo json_encode (
                array('message'=>'Author Not Updated Error')
            );
        }
    }

    //delete an author
    if ($method === 'DELETE'){
        $data = json_decode(file_get_contents("php://input"));

        $authors->id = $data->id;

        //if there's no id in the request
        if ($authors->id === NULL)
        {
            echo json_encode (array('message' => 'Missing Required Parameters'));
            return;
        }

        //checking to see if the author exists and if it isn't being used
        $authors->isValidAuthor();
        $authors->inQuote();

        //if the author doesn't exist
        if($authors->authorAnswer === NULL) {
            echo json_encode(
                array('message' => 'authorId Not Found')
            );
            return;
        }

        //if the author is being used by a quote
        if($authors->quoteAnswer !== NULL) {
            echo json_encode(
                array('message' => 'Cannot Delete Author, id Currently In Use')
            );
            return;
        }

        if($authors->delete()){
            $authors_arr = array(
                'id' => $authors->id
            );
            echo json_encode ($authors_arr);
        }
        else {
            echo json_encode (
                array('message'=>'Author Not Deleted Error')
            );
        }
    }