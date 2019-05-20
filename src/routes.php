<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

use \Firebase\JWT\JWT;

return function (App $app) {
    $container = $app->getContainer();

 	// obtiene todas los usuarios 
    $app->get('/usuarios', function ($request, $response, $args) {
    	 print_r($request->getAttribute('decoded_token_data'));
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
        
        //$input['password'] = password_hash($input['password'], PASSWORD_BCRYPT, ['cost' => 12]);      
        $input['password'] = password_hash($input['password'], PASSWORD_BCRYPT);      
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
    $app->post('/logines', function ($request, $response) {        
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


	// LOGIN TOKEN
	$app->post('/login', function (Request $request, Response $response, array $args) {
 
    $input = $request->getParsedBody();    
    $sql = "SELECT * FROM users WHERE email= :email";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("email", $input['email']);          
    $sth->execute();
    $user = $sth->fetchObject();
 
    // verify email address.
    if(!$user) {
        return $this->response->withJson(['error' => true, 'message' => 'These credentials do not match our records. USE'.$input['password']]);  
    }
 
    // verify password.
    if (!password_verify($input['password'],$user->password)) {
        return $this->response->withJson(['error' => true, 'message' => 'These credentials do not match our records. PAS']);  
    }
 
    $settings = $this->get('settings'); // get settings array.
    
    $token = JWT::encode(['id' => $user->id, 'email' => $user->email], $settings['jwt']['secret'], "HS256");
 
    //return $this->response->withJson(['token' => $token]);
    return $this->response->withJson(['id' => $user->id, 'ok' => true, 'usuario'=>$user, 'token' => $token, 'email'=> $user->email]);
 
	});
	// FIN LOGIN TOKEN

	//RUTA CON CON MIDDLEWARE
	$app->group('/api2', function(\Slim\App $app) {
 
	    $app->get('/user',function(Request $request, Response $response, array $args) {
	        //print_r($request->getAttribute('decoded_token_data'));
	        print_r($request->getAttribute('decoded_token_data'));
	 
	        /*output 
	        stdClass Object
	            (
	                [id] => 2
	                [email] => arjunphp@gmail.com
	            )
	                    
	        */
	    });
   
	});
	// FIN RUTA MIDRLE



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


