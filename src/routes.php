<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {
    $container = $app->getContainer();

 	// obtiene todas los usuarios 
    $app->get('/usuarios', function ($request, $response, $args) {
        $sth = $this->db->prepare("SELECT * FROM users ORDER BY firstname");
        $sth->execute();
        $todos = $sth->fetchAll();
        return $this->response->withJson($todos);
    });


	// obtiene el task con un determinado id
    $app->get('/usuario/[{id}]', function ($request, $response, $args) {
        $sth = $this->db->prepare("SELECT * FROM users WHERE id=:id");
        $sth->bindParam("id", $args['id']);
        $sth->execute();
        $todos = $sth->fetchObject();
        return $this->response->withJson($todos);
    });

    // Add a new todo
    $app->post('/usuario', function ($request, $response) {
        $input = $request->getParsedBody();
        $sql = "INSERT INTO users (email, firstname, lastname, password) VALUES (:email, :firstname, :lastname, :password)";
        $sth = $this->db->prepare($sql);
        $sth->bindParam("email", $input['email']);
        $sth->bindParam("firstname", $input['firstname']);
        $sth->bindParam("lastname", $input['lastname']);
        $sth->bindParam("password", $input['password']);
        $sth->execute();
        $input['id'] = $this->db->lastInsertId();
        return $this->response->withJson($input);
    });

     // DELETE a todo with given id
    $app->delete('/usuario/[{id}]', function ($request, $response, $args) {
        $sth = $this->db->prepare("DELETE FROM users WHERE id=:id");
        $sth->bindParam("id", $args['id']);
        $sth->execute();
        $todos = $sth->fetchAll();        
        return $this->response->withJson($todos);
    });

	// Update todo with given id
    $app->put('/usuario/[{id}]', function ($request, $response, $args) {
        $input = $request->getParsedBody();
        $sql = "UPDATE users SET firstname=:firstname WHERE id=:id";
        $sth = $this->db->prepare($sql);
        $sth->bindParam("id", $args['id']);
        $sth->bindParam("firstname", $input['firstname']);
        $sth->execute();
        $input['id'] = $args['id'];
        return $this->response->withJson($input);
    });

    // login
    $app->post('/login', function ($request, $response) {        
        $input = $request->getParsedBody();
        $sql = "SELECT * FROM users WHERE email=:email AND password=:password";
        $sth = $this->db->prepare($sql);
        $sth->bindParam("email", $input['email']);     
        $sth->bindParam("password", $input['password']);    
        $sth->execute();
        $todos = $sth->fetchAll();
        return $this->response->withJson($todos);
    });


	$app->options('/{routes:.+}', function ($request, $response, $args) {
	    return $response;
	});

	$app->add(function ($req, $res, $next) {
	    $response = $next($req, $res);
	    return $response
	            ->withHeader('Access-Control-Allow-Origin', '*')
	            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
	            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
	});



    // LAST ROUTE:
    // Catch-all route to serve a 404 Not Found page if none of the routes match
	// NOTE: make sure this route is defined last
	$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
	    $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
	    return $handler($req, $res);
	});

/*
    $app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container) {
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");

        // Render index view
        return $container->get('renderer')->render($response, 'index.phtml', $args);
    });

*/



};


