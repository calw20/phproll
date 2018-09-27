<?php include "./../DBStuff.php"; ?>
<?php
	$disSpace = false;

	$numMeetings = $conn->query("SELECT count('DATE') AS numMeeting FROM meeting_date")->fetch_assoc()["numMeeting"];
	if(isset($_GET['filename']) && ($_GET['filename']!==null)){ $filename = (string)$_GET['filename'];} else { $filename = "export.csv";}
	if(isset($_GET['emailall']) && ($_GET['emailall']!==null)){ $emailOnly = (int)$_GET['emailall'];} else { $emailOnly = 0;}
	if(isset($_GET['cutoff']) && ($_GET['cutoff']!==null)){ $cutOff = (int)$_GET['cutoff'];} else { $cutOff = 100;}
	$csvArray = array();
	$emailList = '';
	
	$sql = "Select MIDS As MBRID, MissedDays, members.FNAME, members.SNAME, members.UNAME From";
	$sql = $sql."(select MIDS, count(*) AS MissedDays From (SELECT members.MBRID As MIDS from members, meeting_date, ";
	$sql = $sql."meeting_present Where meeting_present.MBRID = members.MBRID AND meeting_present.MEETINGID = meeting_date.MEETINGID ";
	$sql = $sql."AND (meeting_present.Present = 0 AND meeting_present.EMAIL = 0) AND NOT (members.TYPE = 5 OR members.TYPE = 6)) AS DatesMissed ";
	$sql = $sql."group by MIDS) AS tblMissedDays, members Where members.MBRID = MIDS ORDER BY `MissedDays` DESC;";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$lDMissed = (int)$row["MissedDays"];
			$lDMissedP = round(($lDMissed/$numMeetings)*100, 2);
			$lDMissedPF = 100-$lDMissedP;
		
			if ($cutOff <= $lDMissedPF){continue;};
			
			if (strpos($row["UNAME"], '@') !== false){
				$email = $row["UNAME"];
			} else {
				$email = $row["UNAME"] . '@emmaus.qld.edu.au';
			}
			
			
			if ($emailOnly == 1){
				$emailList = $emailList.$email.';';
				continue;
			}
			
			$sql2 = "SELECT meeting_date.DATE AS Date from members, meeting_date, meeting_present Where meeting_present.MBRID = members.MBRID";
			$sql2 = $sql2." AND meeting_present.MEETINGID = meeting_date.MEETINGID AND (meeting_present.Present = 0 AND meeting_present.EMAIL";
			$sql2 = $sql2." = 0) AND NOT (members.TYPE = 5 OR members.TYPE = 6) AND members.MBRID = ".$row["MBRID"].";";
			$result2 = $conn->query($sql2);
			if ($result2->num_rows > 0) {
				$eCount = 5;
				if ($result2->num_rows > 1){ $emailMissedDates = '<br>Those meetings you missed where; ';}
				else { $emailMissedDates = '<br>That meeting was; '; }
							while($row2 = $result2->fetch_assoc()) {
								if ($eCount == 0){ $eCount = 6; $emailMissedDates = $emailMissedDates.'<br>'; }
								$emailMissedDates = $emailMissedDates.date('l', strtotime($row2["Date"]))." the ".date('jS', strtotime($row2["Date"])).' of '.date('F Y', strtotime($row2["Date"])).', ';
								$eCount -= 1;
							}
							$emailMissedDates = substr($emailMissedDates, 0, -2).'.';
							if ($result2->num_rows > 1){ $emailMissedDates = substr_replace($emailMissedDates,' and ',strrpos($emailMissedDates, ','),1);}
			}
			//$email = "wingcall@emmaus.qld.edu.au";
			if ($lDMissedP >= 70){ 
				$emailScold = 'an extremely high';
			} elseif ($lDMissedP >= 50){ 
				$emailScold = "rather high";
			} elseif ($lDMissedP >= 25){
				$emailScold = "a low";}
			else {
				$emailScold = "a rather low";
			}
			
			$meetName = "Interact Committee";
			
			$textSig = "<br>Secretary of the Emmaus – Rotary Interact Committee.";
			
			$subject = "Missed ".$meetName." Meetings!";
			
			$emailText = 'Hi '.$row["FNAME"].',';
			$emailText = $emailText.'<br>It seems you have '.$emailScold.' number of unexplained absences at the '.$meetName.'!';
			if ($disSpace) {$emailText = str_replace('<br>', '$$BR$$', $emailText); }
			$emailText = $emailText.'<br>You have only been present at '.(string)($numMeetings-$lDMissed).' out of '.$numMeetings.' meetings! (That’s an attendance rate of '.$lDMissedPF.'%, meaning you have, unexplainably, missed '.$lDMissed.' meetings!)';
			$emailText = $emailText.'<br>Please remember that as a valued member of the '.$meetName.' you are expected to be present at all meetings unless you have given notice, in writing, ahead of time of your absence.';
			$emailText = $emailText.$emailMissedDates;
			$emailText = $emailText.'<br>If you do not wish to be a part of the '.$meetName.' anymore please notify the appropriate personal as soon as possible.';
			if ($disSpace) {$emailText = str_replace('<br>', ' ', $emailText); }
			$emailText = $emailText.'<br>';
			$emailText = $emailText.'<br>Many thanks and we hope to see you at the next meeting!';
			$emailText = $emailText.$textSig;
			$emailText = $emailText.'<br>##SIG_HERE##';
			
			if ($disSpace) {$emailText = str_replace('$$BR$$', '<br>', $emailText); }
			
			$emailText =  htmlentities($emailText);
			$emailText = str_replace("&lt;", "<", str_replace("&gt;", ">", $emailText));
			
			$localArray = array($email, $subject, $emailText);
			
			array_push($csvArray, $localArray);
		}
	}
	
	if ($emailOnly == 1){
		echo '<title>Emailing Members</title>';
		echo '<p>Emailing selected members, this window should close automagicly if not, hit the button.</p>';
		echo '<button onclick=window.close()>Close</button>';
		echo '<script>window.onload = setTimeout(\'self.close()\',1000); </script>';
		echo '<script>location.href="mailto:'.$emailList.'";</script>';
		exit;		
	}
	
	header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="'.$filename.'";');
	
    $f = fopen('php://output', 'w');

    foreach ($csvArray as $line) {
        fputcsv($f, $line, "|");
    }
?>
<?php $conn->close(); ?>