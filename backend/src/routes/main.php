<?php

header("Access-Control-Allow-Methods: GET,PUT,POST,DELETE,PATCH,OPTIONS");

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);

// Database connection
try {
    $db = new PDO("mysql:host=localhost;dbname=slimders", "root", "");
} catch (PDOException $e) {
    echo $e->getMessage();
}

// The main (Home) route
$app->get('/', function () {
    return "Routes: /todos, /todos/:id, /create, /delete/:id";
});

// Route for get all todos
$app->get('/todos', function(Request $request, Response $response) {
    global $db;
    $query = $db->query("SELECT * FROM todos ORDER BY todo_id ASC")->fetchAll(PDO::FETCH_OBJ);
    return
    $response
        ->withStatus(200)
        ->withJson($query);

});

// Route for getting todos by their spesific ID
$app->get('/todos/{id}', function(Request $request, Response $response, $args) {
    global $db;
    $id = $args['id'];
    $query = $db->query("SELECT * FROM todos WHERE todo_id = $id")->fetch(PDO::FETCH_OBJ);

    if ($query) {
        return
        $response
            ->withStatus(200)
            ->withJson($query);
    } else {
        return $response->withJson(array(
            "message" => "Todo not found.",
        ));
    }
});

// Route for deleting todos
$app->delete('/delete/{todo_id}', function (Request $request, Response $response, $args) use ($app){

    global $db;
    $todo_id = $args['todo_id'];

        $query = $db->exec("DELETE FROM todos WHERE todo_id = {$todo_id}");
        if ($query) {
            return $response->withJson(array(
                "message" => "Todo $todo_id deleted succesfully."
            ));
        } else {
            return $response->withJson(array(
                "error_message" => "An error ocurred while deleting the todo. Todo not found or deleted before."
            ));
        }

});

// Route for the adding todos
$app->post('/create', function (Request $request, Response $response) use ($app) {

   global $db;
   $data = $request->getParsedBody();
   $todo = $data['todo'];
   $isCompleted = $data['is_completed'];

   if (trim($todo) == "") {
       return $response->withJson(array(
           "error_message" => "Please dont leave blank."
       ));
   } else {
       $query = $db->prepare("INSERT INTO todos set todo = ?, is_completed = ?");
       $insert = $query->execute([
           $todo, $isCompleted
       ]);

       if ( $insert ) {
           return $response->withJson(array(
               "message" => "Todo succesfully added to database."
           ));
       } else {
           return $response->withJson(array(
               "error_message" => "An error occured."
           ));
       }
   }
});

// Route for updating todos is_completed variable to 1
$app->patch('/complete/{id}', function(Request $request, Response $response, $args) {

    global $db;
    $id = $args['id'];

    $query = $db->prepare("UPDATE todos SET
        is_completed = ?
        WHERE todo_id = {$id}
    ");

    $update = $query->execute(array(
        1
    ));

    if ($update) {
        return $response->withJson(array(
            "message" => "Todo $id was succesfully uncompleted."
        ));
    }
    else {
        return $response->withJson(array(
            "error_message" => "An error ocurred. Todo not found."
        ));
    }
});

// Route for updating todos is_completed variable to 1
$app->patch('/uncomplete/{id}', function(Request $request, Response $response, $args) {

    global $db;
    $id = $args['id'];

    $query = $db->prepare("UPDATE todos SET
        is_completed = ?
        WHERE todo_id = {$id}
    ");

    $update = $query->execute(array(
        0
    ));

    if ($update) {
            return $response->withJson(array(
                "message" => "Todo $id was succesfully uncompleted."
            ));
        }
      else {
          return $response->withJson(array(
              "error_message" => "An error ocurred. Todo not found."
          ));
      }

});

// Route for updating todos
$app->patch('/change/{id}/{todo}', function(Request $request, Response $response, $args) {

    global $db;
    $id = $args['id'];
    $newTodo = $args['todo'];

    $query = $db->prepare("UPDATE todos SET
        todo = ?
        WHERE todo_id = {$id}
    ");

    $update = $query->execute(array(
        $newTodo
    ));

    if ($update) {
        return $response->withJson(array(
            "message" => "Todo $id was succesfully changed to $newTodo."
        ));
    }
    else {
        return $response->withJson(array(
            "error_message" => "An error ocurred."
        ));
    }
});