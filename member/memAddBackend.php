<?php
include "./../DBStuff.php"; 

$enableQuery = true;
$falseError = false;
$infoFlag = false;
$redirect = 1;
if(isset($_GET['fname']) && ($_GET['fname']!==null || $_GET['fname']!=="")){ $fname = (string)$_GET['fname'];} else { $infoFlag = true; $fname = (int)5; }
if(isset($_GET['lname']) && ($_GET['lname']!==null || $_GET['lname']!=="")){ $lname = (string)$_GET['lname'];} else { $infoFlag = true; $lname = (int)5; }
if(isset($_GET['uname']) && ($_GET['uname']!==null || $_GET['uname']!=="")){ $uname = (string)$_GET['uname'];} else { $infoFlag = true; $uname = (int)5; }
if(isset($_GET['bday']) && ($_GET['bday']!==null || $_GET['bday']!=="")){ $bday = strtotime($_GET['bday']);} else { $infoFlag = true; $bday = strtotime("0000-00-00"); }
if(isset($_GET['roll']) && ($_GET['roll']!==null || $_GET['roll']!=="")){ $roll = (int)$_GET['roll'];} else { $infoFlag = true; $roll = "Not set!"; }
if(isset($_GET['year']) && ($_GET['year']!==null  || $_GET['year']!=="")){ $year = (int)$_GET['year'];} else { $infoFlag = true; $year = "Not set!"; }
if(isset($_GET['grade']) && ($_GET['grade']!==null  || $_GET['grade']!=="")){ $grade = (int)$_GET['grade'];} else { $infoFlag = true; $grade = "Not set!"; }
if(isset($_GET['update']) && ($_GET['update']!==null || $_GET['update']!=="")){ $update = (int)$_GET['update'];} else { $infoFlag = true; $update = "Not set!"; }
if(isset($_GET['newid']) && ($_GET['newid']!==null || $_GET['newid']!=="")){ $newid = (int)$_GET['newid'];} else { $infoFlag = true; $newid = "Not set!"; }
if(isset($_GET['alred']) && ($_GET['alred']!==null || $_GET['alred']!=="")){ $alred = (int)$_GET['alred'];} else { $alred = 0; }
 if(isset($_GET['memDisabled']) && ($_GET['memDisabled']!==null || $_GET['memDisabled']!=="")){ $memDisabled = (int)$_GET['memDisabled'];} else { $memDisabled = 0; }
if(isset($_GET['herr']) && ($_GET['herr']!==null || $_GET['herr']!=="")){ $humanError = (int)$_GET['herr'];} else { $humanError = 1; }

list($y, $m, $d) = explode("-", date('Y-m-d',$bday));
if(!checkdate($m, $d, $y)){ $infoFlag = true; }
unset($y, $m, $D);

if (!true || !is_string($fname) || !is_string($lname) || !is_string($uname) || !is_int($roll) || !is_int($year) || !is_int($update) || !is_int($newid) || !is_int($grade)){ $infoFlag = true; }

if ($infoFlag){
	echo '<h1>Error!</h1>';
	echo '<h3>Not my fault I swear!</h3>';
	echo 'Something has gone very wrong...';
	echo "It may be that something was (somehow) passed to ".basename($_SERVER['PHP_SELF'])." (This script.) that should not have been"."<br>";
	echo "eg a string was passed to newid, or something else broke! Good Luck! :p<br><br>";
	echo "The varibles are as follow:"."<br>";
	echo "fname: ".$fname.". The type is: ".gettype($fname).".<br>";
	echo "lname: ".$lname.". The type is: ".gettype($lname).".<br>";
	echo "uname: ".$uname.". The type is: ".gettype($uname).".<br>";
	echo "bday: ".date('Y-m-d',$bday).". The type is: ".gettype($bday).".<br>";
	echo "roll: ".$roll.". The type is: ".gettype($roll).".<br>";
	echo "year: ".$year.". The type is: ".gettype($year).".<br>";
	echo "grade: ".$grade.". The type is: ".gettype($grade).".<br>";
	echo "update: ".$update.". The type is: ".gettype($update).".<br>";
	echo "newid: ".$newid.". The type is: ".gettype($newid).".<br>";
	echo "alred: ".$alred.". The type is: ".gettype($alred).".<br>";
	echo "memDisabled: ".$memDisabled.". The type is: ".gettype($memDisabled).".<br>";
	echo "herr: ".$herr.". The type is: ".gettype($herr).".<br>";
	echo 'Click the close button to quit and try again later.</p>';
	echo '<button onclick=window.close()>Close</button><br>';
	$conn->close();
	exit("Invalild Input!");
}

//Lets do some grade checks
if ($roll == 5 || $roll == 6){$grade = 0;}

if ($bday !== strtotime(0000-00-00)){$bday = date('Y-m-d',$bday);} else {$bday = "0000-00-00";}

//We have all the things now...
if ($update == 0){
	$sql = "INSERT INTO `members` (FNAME, SNAME, UNAME, TYPE, YEAR, BDAY, GRADE, DISABLED) VALUES (";
	$sql = $sql."'".$fname."','".$lname."','".$uname."','".$roll."','".$year."','".$bday."','".$grade."','".$memDisabled."');";
} else {
	$sql = "UPDATE `members` SET `FNAME` = '".$fname."', `SNAME` = '".$lname."', `UNAME` = '".$uname."'";
	$sql = $sql.", `TYPE` = '".$roll."', `YEAR` = '".$year."', `BDAY` = '".$bday."', `GRADE` = '".$grade;
	$sql = $sql."', `DISABLED` = '".$memDisabled."' WHERE `members`.`MBRID` = ".$newid;
}

if ($enableQuery){
	if (mysqli_query($conn, $sql)) {
	   $mysqlError = 0;
	} else {
		$mysqlError = 1;
	}
} else {
	$mysqlError = 0;
}

if($falseError){$mysqlError = 1;}

if ($redirect == 1 && $mysqlError == 0) {
	if ($alred == 1 || $update == 0){
		header('Location: ./memAdd.php' , true, "302");
	} else {
		echo '<script>window.close()</script>';
	}
}else {
	if ($humanError == 1 && $mysqlError == 1){
			echo '<h1>SQL ERROR!</h1>';
			echo '<p>It was the cake, not me!</p>';
			echo "<hr>";
		    echo "Error: "; echo mysqli_error($conn);
			echo "<br>SQL: ".$sql;
			echo "<br>Member ID: ".$newid;
			echo "<br>Update: ".$update;
			echo "<hr>";
			echo '<p>Yeh, looks there was a SQL Error, please alert the devs/server owner if this continues.<br>';
			echo 'Click the close button to quit and try again later.</p>';
			echo '<button onclick=window.close()>Close</button><br>';
	} elseif ($humanError == 0 && $mysqlError == 1) {
		echo 'false';
	} else {
		echo 'true';
	}
}
 
$conn->close();
exit();
?>
