<?php

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    $method = $_SERVER['REQUEST_METHOD'];

    //CORS
    if ($method === 'OPTIONS'){
        header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE');
        header('Access-Control-Allow-Headers: Origin, Accept, Content-type, X-Requested-With');
    }
    
    include_once '../../config/Database.php';
    include_once '../../models/Category.php';

    //database initialization
    $database = new Database();
    $db = $database->connect();

    //new category object
    $categories = new Category($db);



    //read all categories (id isn't present in the request)
    if ($method === 'GET' && isset($_GET['id']) === false){
    
        $result = $categories->read();

        $num = $result->rowCount();

        //if there's at least one category in the database
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

    //read single category (id present in the request)
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

    //create a category
    if ($method === 'POST'){
        $data = json_decode(file_get_contents("php://input"));

        $categories->category = $data->category;

        //checking to make sure a category was submitted with the request
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

    //update a category
    if ($method === 'PUT'){
        $data = json_decode(file_get_contents("php://input"));

        $categories->category = $data->category;
        $categories->id = $data->id;

        //making sure the required data was submitted with the request
        if ($categories->category === NULL || $categories->id === NULL)
        {
            echo json_encode (array('message' => 'Missing Required Parameters'));
            return;
        }

        //checking to make sure the category exists
        $categories->isValidCategory();

        //if there is no category to match the id
        if($categories->categoryAnswer === NULL) {
            echo json_encode(
                array('message' => 'categoryId Not Found')
            );
            return;
        }

        if($categories->update()){
            $categories_arr = array(
                'id' => $categories->id,
                'category' => $categories->category
            );
            echo json_encode ($categories_arr);
        }
        else {
            echo json_encode (
                array('message'=>'Category Not Updated Error')
            );
        }
    }

    //delete a category
    if ($method === 'DELETE'){
        $data = json_decode(file_get_contents("php://input"));

        $categories->id = $data->id;

        //if the category id is missing from the request
        if ($categories->id === NULL)
        {
            echo json_encode (array('message' => 'Missing Required Parameters'));
            return;
        }

        //checking to make sure the category exists and it's not being used by a quote
        $categories->isValidCategory();
        $categories->inQuote();

        //if the category doesn't exist
        if($categories->categoryAnswer === NULL) {
            echo json_encode(
                array('message' => 'categoryId Not Found')
            );
            return;
        }

        //if the category is being used by a quote 
        if($categories->quoteAnswer !== NULL) {
            echo json_encode(
                array('message' => 'Cannot Delete Category, id Currently In Use')
            );
            return;
        }

        if($categories->delete()){
            $categories_arr = array(
                'id' => $categories->id
            );
            echo json_encode ($categories_arr);
        }
        else {
            echo json_encode (
                array('message'=>'Category Not Deleted Error')
            );
        }
    }