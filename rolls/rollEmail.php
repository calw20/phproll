<?php 
	include "./../DBStuff.php";
	$fail1 = false;
	$fail2 = false;
	if(isset($_GET['roll']) && ($_GET['roll']!==null || $_GET['roll']!=="")){ $roll = (int)$_GET['roll'];} else { $infoFlag = true; $roll = "Not set!"; }
	if(isset($_GET['bcc']) && ($_GET['bcc']!==null || $_GET['bcc']!=="")){ $bcc = (int)$_GET['bcc'];} else { $bcc = 0; }
	
	if (!is_int($roll)){
		echo '<h1>Error!</h1>';
		echo '<h3>Not my fault I swear!</h3>';
		echo 'Something has gone very wrong...';
		echo "It may be that something was (somehow) passed to ".basename($_SERVER['PHP_SELF'])." (This script.) that should not have been"."<br>";
		echo "eg a string was passed to roll, or something else broke! Good Luck! :p<br><br>";
		echo "The varibles are as follow:"."<br>";
		echo "roll: ".$roll.". The type is: ".gettype($roll).". (Should be int)<br>";
		exit("Invalild Input!");	
	}
	
	$sql = "SELECT UNAME FROM `members` WHERE MBRID IN (SELECT MBRID FROM `meeting_present` WHERE MEETINGID = ".$roll." AND (PRESENT = 1 OR EMAIL = 1))";
	
	$result = $conn->query($sql." AND TYPE NOT IN (5,6);");
	
	$email="";
	
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			if (strpos($row["UNAME"], '@') !== false){
				$email = $email.$row["UNAME"].";";
			} else {
				$email = $email.$row["UNAME"] . '@emmaus.qld.edu.au' . ';';
			}
		}
	} else {
		$fail1 = true;
	}

	$email = rtrim($email,';');
	
	$result = $conn->query($sql." AND TYPE IN (5,6);");

	if (!$fail1){$email = $email.'&cc=';}

	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			if (strpos($row["UNAME"], '@') !== false){
				$email = $email.$row["UNAME"].";";
			} else {
				$email = $email.$row["UNAME"] . '@emmaus.qld.edu.au' . ';';
			}
		}
	} else {
		$fail2 = true;
	}
	
	$email = rtrim($email,';');
	
	if ($bcc == 1){
		$sql = "SELECT UNAME FROM `members` WHERE MBRID IN (SELECT MBRID FROM `meeting_present` WHERE MEETINGID = ".$roll." AND PRESENT = 0 AND EMAIL = 0);";
		$result = $conn->query($sql);
		$email = $email.'&bcc=';
		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				if (strpos($row["UNAME"], '@') !== false){
					$email = $email.$row["UNAME"].";";
				} else {
					$email = $email.$row["UNAME"] . '@emmaus.qld.edu.au' . ';';
				}
			}
		}
		$email = rtrim($email,';');
	}
	echo '<title>Emailing Members</title>';
	echo '<p>Emailing members in selected meeting, this window should close automagicly if not hit the button.</p>';
	echo '<button onclick=window.close()>Close</button>';
	echo '<script>window.onload = setTimeout(\'self.close()\',1000); </script>';
	if (!$fail2 || !$fail1) {echo '<script>location.href="mailto:'.$email.'";</script>';}
	
	
	$conn->close(); 
?>