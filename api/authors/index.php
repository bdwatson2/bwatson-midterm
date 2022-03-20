<?php

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'OPTIONS'){
        header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE');
        header('Access-Control-Allow-Headers: Origin, Accept, Content-type, X-Requested-With');
    }
    
    include_once '../../config/Database.php';
    include_once '../../models/Author.php';

    $database = new Database();
    $db = $database->connect();

    $authors = new Author($db);



    //read all
    if ($method === 'GET' && isset($_GET['id']) === false){
    
        $result = $authors->read();

        $num = $result->rowCount();

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

    //read single
    if ($method === 'GET' && isset($_GET['id']) === true){

        $authors->id = $_GET['id'];

        $authors->read_single();

        $authors_arr = array(
            'id' => $authors->id,
            'author' => $authors->author
        );

        if ($authors->author === NULL){
            echo json_encode (
                array('message' => 'authorId Not Found')
            );

        }else{
            print_r(json_encode($authors_arr));
        }
    }

    //create
    if ($method === 'POST'){
        $data = json_decode(file_get_contents("php://input"));

        $authors->author = $data->author;

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