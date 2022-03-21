<?php

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    $method = $_SERVER['REQUEST_METHOD'];

    //CORS preflight
    if ($method === 'OPTIONS'){
        header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE');
        header('Access-Control-Allow-Headers: Origin, Accept, Content-type, X-Requested-With');
    }
    
    include_once '../../config/Database.php';
    include_once '../../models/Quote.php';

    //init database
    $database = new Database();
    $db = $database->connect();

    //new quote object
    $quotes = new Quote($db);



    //read all quotes (no ids are set)
    if ($method === 'GET' && isset($_GET['id']) === false && isset($_GET['authorId']) === false && isset($_GET['categoryId']) === false){
    
        $result = $quotes->read();

        $num = $result->rowCount();

        //if there's more than one quote
        if($num > 0) {
            $quotes_arr = array();

            while ($row = $result->fetch(PDO::FETCH_ASSOC)){
                extract($row);

                $quote_item = array(
                    'id' => $id,
                    'quote' => $quote,
                    'author' => $author,
                    'category' => $category
                );

                array_push($quotes_arr, $quote_item);
            }

            echo json_encode($quotes_arr);
        }
        else 
        {
            echo json_encode(
                array('message' => 'No Quotes Found')
            );
        }
    }

    //read single by quote id (there's an id, but no category or author id)
    if ($method === 'GET' && isset($_GET['id']) === true && isset($_GET['authorId']) === false && isset($_GET['categoryId']) === false){

        $quotes->id = $_GET['id'];

        $quotes->read_single();

        $quotes_arr = array(
            'id' => $quotes->id,
            'quote' => $quotes->quote,
            'author' => $quotes->author,
            'category' => $quotes->category
        );

        //if there is no quote for the quote id
        if ($quotes->quote === NULL){
            echo json_encode (
                array('message' => 'No Quotes Found')
            );

        }else{
            print_r(json_encode($quotes_arr));
        }
    }

    //read single/multiple by author id only
    if ($method === 'GET' && isset($_GET['id']) === false && isset($_GET['authorId']) === true && isset($_GET['categoryId']) === false){
        $quotes->authorId = $_GET['authorId'];

        $result = $quotes->read_authorId();

        $num = $result->rowCount();

        //if any quotes exist
        if($num > 0) {
            $quotes_arr = array();

            while ($row = $result->fetch(PDO::FETCH_ASSOC)){
                extract($row);

                $quote_item = array(
                    'id' => $id,
                    'quote' => $quote,
                    'author' => $author,
                    'category' => $category
                );

                array_push($quotes_arr, $quote_item);
            }

            echo json_encode($quotes_arr);
        }
        else 
        {
            echo json_encode(
                array('message' => 'No Quotes Found')
            );
        }
    }


    //read single/multiple by category id
    if ($method === 'GET' && isset($_GET['id']) === false && isset($_GET['authorId']) === false && isset($_GET['categoryId']) === true){
        $quotes->categoryId = $_GET['categoryId'];

        $result = $quotes->read_categoryId();

        $num = $result->rowCount();

        //if any quotes exist
        if($num > 0) {
            $quotes_arr = array();

            while ($row = $result->fetch(PDO::FETCH_ASSOC)){
                extract($row);

                $quote_item = array(
                    'id' => $id,
                    'quote' => $quote,
                    'author' => $author,
                    'category' => $category
                );

                array_push($quotes_arr, $quote_item);
            }

            echo json_encode($quotes_arr);
        }
        else 
        {
            echo json_encode(
                array('message' => 'No Quotes Found')
            );
        }
    }

    //read single/multiple by author id and category id
    if ($method === 'GET' && isset($_GET['id']) === false && isset($_GET['authorId']) === true && isset($_GET['categoryId']) === true){
        $quotes->categoryId = $_GET['categoryId'];
        $quotes->authorId = $_GET['authorId'];

        $result = $quotes->read_aId_and_cId();

        $num = $result->rowCount();

        //if any quotes exist
        if($num > 0) {
            $quotes_arr = array();

            while ($row = $result->fetch(PDO::FETCH_ASSOC)){
                extract($row);

                $quote_item = array(
                    'id' => $id,
                    'quote' => $quote,
                    'author' => $author,
                    'category' => $category
                );

                array_push($quotes_arr, $quote_item);
            }

            echo json_encode($quotes_arr);
        }
        else 
        {
            echo json_encode(
                array('message' => 'No Quotes Found')
            );
        }
    }

    //create a quote
    if ($method === 'POST'){
        $data = json_decode(file_get_contents("php://input"));

        $quotes->quote = $data->quote;
        $quotes->authorId = $data->authorId;
        $quotes->categoryId = $data->categoryId;

        //if any required data is missing from the request
        if ($quotes->quote === NULL || $quotes->authorId === NULL || $quotes->categoryId === NULL)
        {
            echo json_encode (array('message' => 'Missing Required Parameters'));
            return;
        }


        //checking to make sure the author id and category id are valid
        $quotes->isValidAuthor();
        $quotes->isValidCategory();
        
        //if the authorId doesn't exist
        if($quotes->authorAnswer === NULL) {
            echo json_encode(
                array('message' => 'authorId Not Found')
            );
            return;
        }

        //if the categoryId doesn't exist
        if($quotes->categoryAnswer === NULL) {
            echo json_encode(
                array('message' => 'categoryId Not Found')
            );
            return;
        }

        //create the quote
        if($quotes->create()){
            $quotes_arr = array(
                'id' => $quotes->id,
                'quote' => $quotes->quote,
                'authorId' => $quotes->authorId,
                'categoryId' => $quotes->categoryId
            );
            echo json_encode ($quotes_arr);
        }
        else {
            echo json_encode (
                array('message'=>'Quote Not Created Error')
            );
        }
    }

    //update an existing quote
    if ($method === 'PUT'){
        $data = json_decode(file_get_contents("php://input"));

        $quotes->id = $data->id;
        $quotes->quote = $data->quote;
        $quotes->authorId = $data->authorId;
        $quotes->categoryId = $data->categoryId;

        //if any of the required data is missing from the request
        if ($quotes->id === NULL || $quotes->quote === NULL || $quotes->authorId === NULL || $quotes->categoryId === NULL)
        {
            echo json_encode (array('message' => 'Missing Required Parameters'));
            return;
        }

        //checking to make sure the quote, author, and category submitted actually exist
        $quotes->isValidAuthor();
        $quotes->isValidCategory();
        $quotes->isValidQuote();
        
        //if the author doesn't exist
        if($quotes->authorAnswer === NULL) {
            echo json_encode(
                array('message' => 'authorId Not Found')
            );
            return;
        }

        //if the category doesn't exist
        if($quotes->categoryAnswer === NULL) {
            echo json_encode(
                array('message' => 'categoryId Not Found')
            );
            return;
        }

        //if the quote doesn't exist
        if($quotes->quoteAnswer === NULL) {
            echo json_encode(
                array('message' => 'No Quotes Found')
            );
            return;
        }


        if($quotes->update()){
            $quotes_arr = array(
                'id' => $quotes->id,
                'quote' => $quotes->quote,
                'authorId' => $quotes->authorId,
                'categoryId' => $quotes->categoryId
            );
            echo json_encode ($quotes_arr);
        }
        else {
            echo json_encode (
                array('message'=>'Quote Not Updated Error')
            );
        }
    }


    //delete a quote
    if ($method === 'DELETE'){
        $data = json_decode(file_get_contents("php://input"));

        $quotes->id = $data->id;

        //if the id isn't submitted with the request
        if ($quotes->id === NULL)
        {
            echo json_encode (array('message' => 'Missing Required Parameters'));
            return;
        }

        //does the quote exist
        $quotes->isValidQuote();

        //if the quote doesn't exist
        if($quotes->quoteAnswer === NULL) {
            echo json_encode(
                array('message' => 'No Quotes Found')
            );
            return;
        }

        //delete and return the id of the deleted quote
        if($quotes->delete()){
            $quotes_arr = array(
                'id' => $quotes->id
            );
            echo json_encode ($quotes_arr);
        }
        else {
            echo json_encode (
                array('message'=>'Quote Not Deleted Error')
            );
        }
    }