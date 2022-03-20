<?php

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'OPTIONS'){
        header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE');
        header('Access-Control-Allow-Headers: Origin, Accept, Content-type, X-Requested-With');
    }
    
    include_once '../../config/Database.php';
    include_once '../../models/Category.php';

    $database = new Database();
    $db = $database->connect();

    $categories = new Category($db);



    //read all
    if ($method === 'GET' && isset($_GET['id']) === false){
    
        $result = $categories->read();

        $num = $result->rowCount();

        if($num > 0) {
            $categories_arr = array();

            while ($row = $result->fetch(PDO::FETCH_ASSOC)){
                extract($row);

                $category_item = array(
                    'id' => $id,
                    'category' => $category
                );

                array_push($categories_arr, $category_item);
            }

            echo json_encode($categories_arr);
        }
        else 
        {
            echo json_encode(
                array('message' => 'No Categories Found')
            );
        }
    }

    //read single
    if ($method === 'GET' && isset($_GET['id']) === true){

        $categories->id = $_GET['id'];

        $categories->read_single();

        $categories_arr = array(
            'id' => $categories->id,
            'category' => $categories->category
        );

        if ($categories->category === NULL){
            echo json_encode (
                array('message' => 'categoryId Not Found')
            );

        }else{
            print_r(json_encode($categories_arr));
        }
    }

    //create
    if ($method === 'POST'){
        $data = json_decode(file_get_contents("php://input"));

        $categories->category = $data->category;

        if ($categories->category === NULL)
        {
            echo json_encode (array('message' => 'Missing Required Parameters'));
            return;
        }

        if($categories->create()){
            $categories_arr = array(
                'id' => $categories->id,
                'category' => $categories->category
            );
            echo json_encode ($categories_arr);
        }
        else {
            echo json_encode (
                array('message'=>'Category Not Created Error')
            );
        }
    }