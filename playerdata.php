<?php
	/*Posting and Receiving player data for Forged Hero Mobile MMORPG.
	Test: forgedhero.com/playerdata.php?username=Jake
	*/


	// connect to MySQL database
	include_once 'php/functions.php';

	$port = '3306';
	$link = @mysql_connect('143.95.234.90', 'forgedhe_wp217', '*********');
	$dbName = "forgedhe_leaderboard";
	$tableName = "playerData";

	if (!$link) {
		exit('Error: Could not connect to MySQL server!');
	}

	// connect to the table
	mysql_select_db($dbName)or die("cannot select DB");

	// setup files
	$incomingJson = 'json.txt'; 
	$sqlErrorLog = "sqlErrors.txt";

	// initialize the string
	$string = "";

// start GET data
	if ($_SERVER['REQUEST_METHOD'] === 'GET') {

		// initialize the JSON body variable
		$jsonBody="";

		// get table contents and username
		$query = mysql_query("SELECT * FROM ".$tableName." WHERE username ='".$_GET["username"]."'");

		// construct an array
		$rows = array();

		// loop through the table
		while($row = mysql_fetch_assoc($query)) {
		    $rows[] = $row;	   
		}
			
			// fill in table
			$username = $rows[0]['username'];
			$playerID =$rows[0]['playerID'];
			$playerX = $rows[0]['playerX'];
			$playerY = $rows[0]['playerY'];
			$currentArea = $rows[0]['currentArea'];
			$HP = $rows[0]['HP'];
			$mana = $rows[0]['mana'];
			$coins = $rows[0]['coins'];
			$ancientCoins = $rows[0]['ancientCoins'];
			$doubleXPtime = $rows[0]['doubleXPtime'];

			// construct the JSON return from our data
			$jsonString = '{"Name":"'.$tableRow .'","Value":"|'.$username.'|'.$playerID.'|'.$playerX.'|'.$playerY.'|'.$currentArea.'|'.$HP.'|'.$mana.'|'.$coins.'|'.$ancientCoins.'|'.$doubleXPtime.'|"},';

			// append the JSON return with the new data
			$jsonBody=$jsonBody.$jsonString;

		// construct the JSON response

		// this is the header of the JSON return. It will have to be adjusted to match whatever your app is expecting. We have to define this here to get the row count above.
		$jsonHeadher='{"Properties":[],"Name":"","Children":[{"Properties":[{"Name":"rowCount","Value":1},{"Name":"columnCount","Value":10},{"Name":"0-1-name","Value":"Username"},{"Name":"0-1-type","Value":1},{"Name":"0-2-name","Value":"PlayerID"},{"Name":"0-2-type","Value":2},{"Name":"0-3-name","Value":"playerX"},{"Name":"0-3-type","Value":2},{"Name":"0-4-name","Value":"playerY"},{"Name":"0-4-type","Value":2},{"Name":"0-5-name","Value":"currentArea"},{"Name":"0-5-type","Value":2},{"Name":"0-6-name","Value":"HP"},{"Name":"0-6-type","Value":2},{"Name":"0-7-name","Value":"Mana"},{"Name":"0-7-type","Value":2},{"Name":"0-8-name","Value":"coins"},{"Name":"0-8-type","Value":2},{"Name":"0-9-name","Value":"ancientCoins"},{"Name":"0-9-type","Value":2},{"Name":"0-10-name","Value":"doubleXPtime"},{"Name":"0-10-type","Value":2}],"Name":"id861382_headers","Children":[]},{"Properties":[';
		
		$jsonFooter='],"Name":"id861382","Children":[]}]}';

		// remove comma
		$jsonBody=rtrim($jsonBody, ",");

		// construct full JSON file
		$returnedJson=$jsonHeadher.$jsonBody.$jsonFooter;
		
		// write JSON so app can read
		echo $returnedJson;	


	} // end of get

	// start SEND data
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		//capture incoming data
		error_reporting(1);
		$sig = $_POST["sig"];
		$jsondata = $_POST["params"];

		// capture sent data
		file_put_contents($incomingJson,$jsondata);

		// check for successful sent
		echo '{"Status":"Success"}';

		// convert JSON to an array
		$array = json_decode($jsondata, TRUE);

		//get the total number of objects in the array
		$arrlength = count($array['Children']['1']['Properties']);

		// set while loop index
		$i = 0;
		
		//loop through array node and get row values
		while ($i < $arrlength ) {

			// get row value
			$value = $array['Children']['1']['Properties'][$i]['Value']."\n";

			// convert delimited string to an array
			$arrayPieces = explode("|", $value);

			// get array values.
			$rowName = $arrayPieces[0];  
			$username = $arrayPieces[1];
			$playerID =$arrayPieces[2];
			$playerX = $arrayPieces[3];
			$playerY = $arrayPieces[4];
			$currentArea = $arrayPieces[5];
			$HP = $arrayPieces[6];
			$mana = $arrayPieces[7];
			$coins = $arrayPieces[8];
			$ancientCoins = $arrayPieces[9];
			$doubleXPtime = $arrayPieces[10];

			// construct SQL statement
			$sql="INSERT INTO ".$tableName."
                        (username, playerID, playerX, playerY, currentArea, HP, mana, coins, ancientCoins, doubleXPtime)
                        VALUES
                        ('$username', '$playerID', '$playerX', '$playerY', '$currentArea', '$HP', '$mana','$coins', '$ancientCoins', '$doubleXPtime')
                        ON DUPLICATE KEY UPDATE
                        playerX='".$playerX."',
                        playerY='".$playerY."'...,
                        currentArea='".$currentArea."',
                        HP='".$HP."',
                        mana='".$mana."',
                        coins='".$coins."',
                        ancientCoins='".$ancientCoins."',
                        doubleXPtime='".$doubleXPtime."'
";

                        
			// insert SQL statement
            $result=mysql_query($sql);

            // catch errors
            if($result){
                // if successful do nothing for now.
            }

            else {

                $message  = 'Invalid query: ' . mysql_error() . "\n";
                $message .= 'Whole query: ' . $query;
                file_put_contents($sqlErrorLog, $message, FILE_APPEND);
                die($message);
            }

			$i++;
		}
	} // end of POST

	

	// close the SQL connection
	mysql_close($link);

?>
