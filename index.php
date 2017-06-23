<?php
	require 'vendor/autoload.php';
	$app = new Slim\App([
		'settings' => require_once("config.php")
	]);
	
	$container = $app->getContainer();

	$container['db'] = function ($c) {
		$db = $c['settings']['db'];
		$pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
			$db['user'], $db['pass']);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		return $pdo;
	};
	
	$app->get("/login", function($request, $response){
		$username = $request->getQueryParams()["username"];
		$password = $request->getQueryParams()["password"];
		$query = $this->db->prepare(
			"SELECT `username`, `password`
			FROM `users` 
			WHERE `username` >= :username AND
			`password` <= :password 
			LIMIT 1"
		);
		$query->bindParam(":username", $username);
		$query->bindParam(":password", $password);
		$query->execute();
		$readings = $query->fetchAll();
		if(count($readings) == 1){		
			$body["success"] = true;
			$body["description"] = "Successfully logged in";
		} else {
			$body["success"] = false;
			$body["description"] = "Could not log in";
		}
		$response->write(json_encode($body));
		return $response
			->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
			->withStatus(200);
	});
	
	$app->get("/getReadings", function($request, $response){
		$fromdate = $request->getQueryParams()["from"];
		$todate = $request->getQueryParams()["to"];
		$query = $this->db->prepare(
			"SELECT `interval_readings`.`value`, `interval_blocks`.`published`,`interval_blocks`.`updated` ,`interval_blocks`.`meter_reading_id`
			FROM `interval_readings` 
			INNER JOIN `interval_blocks` 
			ON `interval_readings`.`interval_block_id` = `interval_blocks`.`id` 
			WHERE `interval_blocks`.`published` >= :from AND
			`interval_blocks`.`published` <= :to 
			ORDER BY `interval_blocks`.`updated` DESC LIMIT 1000000"
		);
		$query->bindParam(":from", $fromdate);
		$query->bindParam(":to", $todate);
		$query->execute();
		$readings = $query->fetchAll();
		$type1 = [];
		$type2 = [];
		$type3 = [];
		$type4 = [];
		$type5 = [];
		for	($i = 0; $i<count($readings); $i++){
		
			if ($readings[$i]["meter_reading_id"] == 1){
			
				array_push($type1, $readings[$i]);
			} else if ($readings[$i]["meter_reading_id"] == 2){
				array_push($type2, $readings[$i]);
			} else if ($readings[$i]["meter_reading_id"] == 3){
				array_push($type3, $readings[$i]);
			} else if ($readings[$i]["meter_reading_id"] == 4){
				array_push($type4, $readings[$i]);
			} else if ($readings[$i]["meter_reading_id"] == 5){
				array_push($type5, $readings[$i]);
			}
		}
		$body["success"] = true;
		$body["description"] = "Whatever";
		$body["results"]["type1"] = $type1;
		$body["results"]["type2"] = $type2;
		$body["results"]["type3"] = $type3;
		$body["results"]["type4"] = $type4;
		$body["results"]["type5"] = $type5;
		$response->write(json_encode($body));
		return $response
			->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
			->withStatus(200);
	});
	
	
	$connection = null;
	$app->run();

?>