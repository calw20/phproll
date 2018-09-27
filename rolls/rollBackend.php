<?php
include "./../DBStuff.php";

$enableQuery = true;
$falseError = false;
$infoFlag = false;
$redirect = 1;
if(isset($_GET['pplpresent']) && ($_GET['pplpresent']!==null || $_GET['pplpresent']!=="")){ $pplpresent = (string)$_GET['pplpresent'];} else { $infoFlag = true; $pplpresent = (int)5; }
if(isset($_GET['meetid']) && ($_GET['meetid']!==null || $_GET['meetid']!=="")){ $meetid = (int)$_GET['meetid'];} else { $meetid = null; }
if(isset($_GET['update']) && ($_GET['update']!==null || $_GET['update']!=="")){ $update = (int)$_GET['update'];} else { $update = 0; }
if(isset($_GET['alred']) && ($_GET['alred']!==null || $_GET['alred']!=="")){ $alred = (int)$_GET['alred'];} else { $alred = 1; }
if(isset($_GET['herr']) && ($_GET['herr']!==null || $_GET['herr']!=="")){ $humanError = (int)$_GET['herr'];} else { $humanError = 1; }

if (!true || !is_string($pplpresent) || !is_int($alred) || !is_int($humanError) || !is_int($update)){ $infoFlag = true; }

function sqlErrorMeeting($local_sql, $local_conn, $local_meetid, $local_update, $localMysqlError = 1, $localHumanError = 1, $forceLocalError = 0){
	if (!isset($local_sql) || !isset($local_conn) || !isset($local_meetid) || !isset($local_update)){
		echo '<h1>FUNCTION ERROR!</h1>';
		$tmpWords = isset($local_sql) ? '': '$local_sql';
		$tmpWords = isset($local_conn) ? $tmpWords.'': $tmpWords.'$local_conn';
		$tmpWords = isset($local_meetid) ? $tmpWords.'': $tmpWords.' $local_meetid';
		$tmpWords = isset($local_update) ? $tmpWords.'': $tmpWords.' $local_update';
		$tmpWasWhere = sizeof(explode('$', $tmpWords)) > 2 ? ' where' :' was';
		echo '<p>'.$tmpWords.$tmpWasWhere.' not set. This function cannot work without them, check your code!</p>';
		$local_conn->close();
		die();
	}
	if ($localHumanError == 1 && ($localMysqlError == 1 || $forceLocalError == 1)){
			echo '<h1>SQL ERROR!</h1>';
			if($forceLocalError != 1){ echo '<p>It was the cake, not me!</p>'; } else {echo '<p>It was you not me!</p>';}
			echo "<hr>";
		    echo "Error: "; echo mysqli_error($local_conn);
			echo "<br>SQL: ".$local_sql;
			echo "<br>Meeting ID: ".$local_meetid;
			echo "<br>Update: ".$local_update;
			echo "<hr>";
			echo '<p>Yeh, looks there was a SQL Error, please alert the devs/server owner if this continues.<br>';
			echo 'Click the close button to quit and try again later.</p>';
			echo '<button onclick=window.close()>Close</button><br>';
			$local_conn->close();
			die();
	} elseif ($localHumanError == 0 && ($localMysqlError == 1 || $forceLocalError == 1)) {
		echo 'false';
		$local_conn->close();
		die();
	}
}

function sqlQueryNumError($local_sql, $local_conn, $local_enableQuery = true){
	if ($local_enableQuery){
		$res = mysqli_multi_query($local_conn, $local_sql);
		//echo mysqli_error($local_conn);
		//echo '<br>'.$local_sql;
		if ($res) { $x = 0; } else { $x = 1; }
		do {
		  if ($result = mysqli_store_result($local_conn)) {
			mysqli_free_result($result);
		  }
		} while (mysqli_next_result($local_conn));
		//echo $x;
		return $x;
	} else {
		//echo 0;
		return 0;
	}
}

function sqlWithError($sql, $conn, $local_update, $local_meetid = null, $enableQuery = true, $localHumanError = 1, $forceLocalError = 0){
	$local_mysqlError = sqlQueryNumError($sql, $conn, $enableQuery);
	//echo $local_mysqlError;
	
	if(!$local_meetid){$local_meetid = $conn->query('SELECT MAX(MEETINGID) AS MID FROM `meeting_date`'); echo mysqli_error($conn); $local_meetid = $local_meetid->fetch_assoc()["MID"]; }
		
	$local_meetid = $local_mysqlError == 1 ? (string)($local_meetid + 1).' (Potential)' : $local_meetid;
	//echo $local_mysqlError;
	sqlErrorMeeting($sql, $conn, $local_meetid, $local_update, $local_mysqlError, $localHumanError, $forceLocalError);
}

function redirect($loc, $local_redirect = 1, $local_alred = 0, $local_update = 0){
	if ($local_redirect == 1) {
		if ($local_alred == 1 || $local_update == 1){
			header('Location: '.$loc , true, "302");
		} else {
			echo '<script>window.onload = function(){window.close()}</script>';
		}
	}else {
		echo 'true';
	}
}

if ($infoFlag){
	echo '<h1>Error!</h1>';
	echo '<h3>Not my fault I swear!</h3>';
	echo 'Something has gone very wrong...';
	echo "It may be that something was (somehow) passed to ".basename($_SERVER['PHP_SELF'])." (This script.) that should not have been"."<br>";
	echo "eg an integer was passed to pplpresent, or something else broke! Good Luck! :p<br><br>";
	echo "The varibles are as follow:"."<br>";
	echo "pplpresent: ".$pplpresent.". The type is: ".gettype($pplpresent).".<br>";
	echo "meetid: ".$meetid.". The type is: ".gettype($meetid).".<br>";
	echo "update: ".$update.". The type is: ".gettype($update).".<br>";
	echo "alred: ".$alred.". The type is: ".gettype($alred).".<br>";
	echo "humanError: ".$humanError.". The type is: ".gettype($humanError).".<br>";
	echo 'Click the close button to quit and try again later.</p>';
	echo '<button onclick=window.close()>Close</button><br>';
	$conn->close();
	exit("Invalild Input!");
}

//We have all the things now...
if ($update == 0 && $enableQuery){
	
	$values = str_replace("ROLLNAME-MATCHTHIS", "@mID" , $pplpresent);
	//echo $values;
	$sql = "INSERT INTO `meeting_date` (`MEETINGID`, `DATE`) VALUES (NULL, CURRENT_TIMESTAMP); SET @mID = (SELECT MAX(MEETINGID) FROM `meeting_date`); INSERT INTO `meeting_present` (`MBRID`, `MEETINGID`, `Present`, `EMAIL`) VALUES ".$values.';';
	
	sqlWithError($sql, $conn, $update, null, $enableQuery, $humanError, $falseError);
	
	
} elseif ($update == 1 && $enableQuery) {
	$values = str_replace("ROLLNAME-MATCHTHIS", $meetid ,htmlspecialchars_decode($pplpresent));
	$values = str_replace(" ", "", str_replace(")", "", str_replace("),", "", $values)));
	$sqlArr = explode("(", $values);
	array_shift($sqlArr);
	$sql = '';
	
	foreach($sqlArr as $val){
		$x = explode(",", $val);
		$sql = $sql."UPDATE `meeting_present` SET `Present` = '".$x[2]."' WHERE `meeting_present`.`MBRID` = ".$x[0]." AND `meeting_present`.`MEETINGID` = ".$x[1].'; ';
		$sql = $sql."UPDATE `meeting_present` SET `EMAIL` = '".$x[3]."' WHERE `meeting_present`.`MBRID` = ".$x[0]." AND `meeting_present`.`MEETINGID` = ".$x[1].'; ';
	}
	sqlWithError($sql, $conn, $update, $meetid, $enableQuery, $humanError, $falseError);
} else {
	echo '<h1>Um Error?</h1>';
	echo '<p>Something went wrong... If this keeps happening contact the devs/server owner.</p>';
	$conn->close();
	die();
}
//From here we are golden
redirect('./rollManager.php', $redirect, $alred);

$conn->close();
?>