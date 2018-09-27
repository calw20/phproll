<?php include "./../DBStuff.php"; ?>
<?php 
	$numMeetings = $conn->query("SELECT count('DATE') AS numMeeting FROM meeting_date")->fetch_assoc()["numMeeting"];
	if(isset($_GET['cutoff']) && ($_GET['cutoff']!==null)){ $cutOff = (int)$_GET['cutoff'];} else { $cutOff = 100;}
	
	/*function random_str($length = 10, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'){
		$pieces = [];
		$max = mb_strlen($keyspace, '8bit') - 1;
		for ($i = 0; $i < $length; ++$i) {
			$pieces []= $keyspace[random_int(0, $max)];
		}
		return implode('', $pieces);
	}*/
	?>
<html>
	<head>
		<title>Missing People!</title>
		<link rel="stylesheet" href="./../pure-min.css">
		<style>
			@media print {
				.printbtn {
					display :  none;
				}
				.large-print{
					zoom: 2;
					-moz-transform: scale(2);
				}
				@page {size: portrait}
			}
		</style>
	</head>
	<body>
	<div align="center">
		<div style="height:1px"></div>
		<h2>Missing People Counter!</h2>
		<form action = "" method = "get" class="printbtn">
			Ignore people with attendence higher than:
			<input type="number" name="cutoff" min="0" max="100" step="1" value="<?php echo $cutOff; ?>">
			percent.
			<input type="submit">
			<input type="button" value="Export CSV" onclick=document.getElementById("makeCSV").submit();>
			<input type="button" value="Email All" onclick=document.getElementById("emailAll").submit();>
			<input type="button" value="Print" onclick=window.print();>
		</form>
		<table class="pure-table pure-table-bordered large-print" style="text-align:center; vertical-align:middle;">
			<thead style="text-align:center; vertical-align:middle;">
				<tr>
					<td>Name</td>
					<td>Atednence</td>
					<td>Days Missed</td>
					<td class="printbtn">Options</td>
				</tr>
			</thead>
			
			<tbody>
			<?php
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
						
						if ($lDMissedP >= 70){ $colour = "rgb(202, 60, 60);";}
						elseif ($lDMissedP >= 50){ $colour = "rgb(223, 117, 20);";}
						elseif ($lDMissedP >= 25){ $colour = "rgb(0, 120, 231);";}
						else { $colour = "rgb(66, 184, 221);";}
												
						echo '<tr style="color: white; background: '.$colour.'">';
							echo '<td>'.$row["FNAME"].' '.$row["SNAME"].'</td>';
							echo '<td>'.(string)($lDMissedPF).'% ('.(string)($numMeetings-$lDMissed).'/'.$numMeetings.')</td>';
							echo '<td>';
								$sql2 = "SELECT meeting_date.DATE AS Date from members, meeting_date, meeting_present Where meeting_present.MBRID = members.MBRID";
								$sql2 = $sql2." AND meeting_present.MEETINGID = meeting_date.MEETINGID AND (meeting_present.Present = 0 AND meeting_present.EMAIL";
								$sql2 = $sql2." = 0) AND NOT (members.TYPE = 5 OR members.TYPE = 6) AND members.MBRID = ".$row["MBRID"].";";
								$result2 = $conn->query($sql2);
								if ($result2->num_rows > 0) {									
									while($row2 = $result2->fetch_assoc()) {
										echo date('l', strtotime($row2["Date"]))." the ".date('jS', strtotime($row2["Date"])).' of '.date('F Y', strtotime($row2["Date"])).' at '.date('g:i a',strtotime($row2["Date"]));
										echo '<br>';
									}
							}
							echo '</td>';
							if (strpos($row["UNAME"], '@') !== false){
								$email = $row["UNAME"];
							} else {
								$email = $row["UNAME"] . '@emmaus.qld.edu.au';
							}
							echo '<td class="printbtn"><button style="color: black;" onclick=location.href=\'mailto:'.$email.'\';>Email</button></td>';
						echo '</tr>';
					}
				} else {
					echo "Either your DB is empty or not one person has missed a meeting!";
				}
			?>
			</tbody>
		</table>
		<div style="height:18px"></div>
		<button onclick=location.href='./../index.php'>Go Home</button>
		<button onclick=window.close()>Close</button>
	</div>
	<form id="makeCSV" target="_blank" action="./rollMissCSV.php">
		<input type="hidden" name="filename" value="Interact.csv">
		<input type="hidden" name="cutoff" value="<?php echo $cutOff; ?>">
	</form>
		<form id="emailAll" target="_blank" action="./rollMissCSV.php">
		<input type="hidden" name="emailall" value="1">
		<input type="hidden" name="cutoff" value="<?php echo $cutOff; ?>">
	</form>
	</body>
</html>
<?php $conn->close(); ?>