<?php include "./../DBStuff.php";?>
<?php
	if(isset($_GET['roll']) && ($_GET['roll']!==null)){ $meetid = (int)$_GET['roll'];} else { $meetid = null;}
	if ($meetid == null){
		
	}
	
	$meetInfoRes = $conn->query('SELECT * FROM `meeting_date` WHERE MEETINGID = '.$meetid)->fetch_assoc();
	$meetingRAW = array( "id" => $meetInfoRes["MEETINGID"],
					  "fdt" => strtotime($meetInfoRes["DATE"]) );

	$meeting = array( "id" => $meetingRAW["id"],
					  "fdt" => $meetingRAW["fdt"],
					  "date" => date('d-m-Y', $meetingRAW["fdt"]),
					  "time" => date('g:i a',$meetingRAW["fdt"]),
					  "date-time-format" => date('l', $meetingRAW["fdt"])." the ".date('jS', $meetingRAW["fdt"]).' of '.date('F Y', $meetingRAW["fdt"]).' at '.date('g:i a',$meetingRAW["fdt"]));
?>
<html>
	<head>
		<title>Meeting Info</title>
		<link rel="stylesheet" href="./../pure-min.css">
		<link rel="stylesheet" href="./buttons.css">
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
	<body> <!--style="max-width:550px"-->
		<div align="center" class="large-print">
			<div style="height:1px"></div>
			<h2 style="margin-bottom:5px">Meeting Info</h2>
			<p style="margin-top:0px"> For <?php echo $meeting["date-time-format"];?><br>Meeting ID: <?php echo $meetid; ?></p>
			<button class="pure-button button-secondary printbtn" onclick=window.close()>Close</button>
			<button class="pure-button pure-button-primary printbtn" onclick=window.print()>Print</button>
			<div id="tables">
				<table class="pure-table pure-table-bordered" style="text-align:center; vertical-align:middle;">
					<h4 style="margin-bottom:5px">Head Members</h4>
					<?php $npR = $conn->query('SELECT MAX(EMAIL) AS MEMAIL FROM `meeting_present`, `members` WHERE `meeting_present`.`MBRID` = `members`.`MBRID` AND TYPE IN (0,1,2,3) AND MEETINGID = '.$meetid)->fetch_assoc()["MEMAIL"]?>
					<?php if ($npR == 1) {echo '<p style="margin-bottom:5px; margin-top:0px">NOTE: N.P Means Not Present.</p>';} ?>
					<hr style="max-width: 500px">
					<thead style="text-align:center; vertical-align:middle;">
						<tr>
							<td>Name</td>
							<td>Grade</td>
							<td>Type</td>
							<td>Present</td>
						</tr>
					</thead>
					<tbody>
						<?php
							$sql = "SELECT * FROM members WHERE TYPE IN (0,1,2,3) AND MBRID IN (SELECT MBRID FROM `meeting_present` WHERE MEETINGID = ".$meetid." )";
							$result = $conn->query($sql);
							if ($result->num_rows > 0) {
								while($row = $result->fetch_assoc()) {
									if ($conn->query('SELECT PRESENT AS PRES FROM `meeting_present` WHERE MEETINGID = '.$meetid.' AND MBRID = '.$row["MBRID"])->fetch_assoc()["PRES"] == 1){
										$style = 'background: rgb(28, 184, 65);';
										$not = '';
									}elseif($conn->query('SELECT EMAIL AS EMAIL FROM `meeting_present` WHERE MEETINGID = '.$meetid.' AND MBRID = '.$row["MBRID"])->fetch_assoc()["EMAIL"] == 1){
										$style = 'background: rgb(223, 117, 20);';
										$not = 'reason';
									}else{
										$style = 'background: rgb(202, 60, 60);';
										$not = 'not ';
									}
									echo '<tr style="color: white; '.$style.'">';
									echo "<td>" . $row["FNAME"]. " " . $row["SNAME"]. "</td>";
									echo "<td>".$row["GRADE"]."</td>";
									echo "<td>".$conn->query('SELECT TYPENAME FROM member_types WHERE TYPEID = '.$row["TYPE"])->fetch_assoc()["TYPENAME"].'</td>';
									if ($not !== 'reason') {echo '<td>Was '.$not.'present.</td>';} else {echo '<td>N.P, Had reason.</td>';}
									echo '</tr>';
								}
							} else {
								echo "No Head members of your group? This can be an issue... Add people or check your database.";
							}
						?>
					</tbody>
				</table>
				<table class="pure-table pure-table-bordered" style="text-align:center; vertical-align:middle;">
					<h4 style="margin-bottom:5px">Staff/Other Members</h4>
					<hr style="max-width: 500px">
					<thead style="text-align:center; vertical-align:middle;">
						<tr>
							<td>Name</td>
							<td>Grade</td>
							<td>Type</td>
							<td>Present</td>
						</tr>
					</thead>
					<tbody>
						<?php
							$sql = "SELECT * FROM members WHERE TYPE IN (5,6) AND MBRID IN (SELECT MBRID FROM `meeting_present` WHERE MEETINGID = ".$meetid." )";
							$result = $conn->query($sql);
							if ($result->num_rows > 0) {
								while($row = $result->fetch_assoc()) {
									if ($conn->query('SELECT PRESENT AS PRES FROM `meeting_present` WHERE MEETINGID = '.$meetid.' AND MBRID = '.$row["MBRID"])->fetch_assoc()["PRES"] == 1){
										$style = 'background: rgb(28, 184, 65);';
										$not = '';
									}elseif($conn->query('SELECT EMAIL AS EMAIL FROM `meeting_present` WHERE MEETINGID = '.$meetid.' AND MBRID = '.$row["MBRID"])->fetch_assoc()["EMAIL"] == 1){
										$style = 'background: rgb(223, 117, 20);';
										$not = 'email';
									}else{
										$style = 'background: rgb(202, 60, 60);';
										$not = 'not ';
									}
									echo '<tr style="color: white; '.$style.'">';
									echo "<td>" . $row["FNAME"]. " " . $row["SNAME"]. "</td>";
									echo "<td>N.A.</td>";
									echo "<td>".$conn->query('SELECT TYPENAME FROM member_types WHERE TYPEID = '.$row["TYPE"])->fetch_assoc()["TYPENAME"].'</td>';
									if ($not !== 'email') {echo '<td>Was '.$not.'present.</td>';} else {echo '<td>Only Emailed</td>';}
									echo '</tr>';
								}
							} else {
								echo "No Staff/Other types of people in your group? This can be an issue... Add people or check your database.";
							}
						?>
					</tbody>
				</table>
				<table class="pure-table pure-table-bordered" style="text-align:center; vertical-align:middle;">
					<h4 style="margin-bottom:5px">Members</h4>
					<?php $npR = $conn->query('SELECT MAX(EMAIL) AS MEMAIL FROM `meeting_present`, `members` WHERE `meeting_present`.`MBRID` = `members`.`MBRID` AND TYPE = 4 AND MEETINGID = '.$meetid)->fetch_assoc()["MEMAIL"]?>
					<?php if ($npR == 1) {echo '<p style="margin-bottom:5px; margin-top:0px">NOTE: N.P Means Not Present.</p>';} ?>
					<hr style="max-width: 500px">
					<thead style="text-align:center; vertical-align:middle;">
						<tr>
							<td>Name</td>
							<td>Grade</td>
							<td>Type</td>
							<td>Present</td>
						</tr>
					</thead>
					<tbody>
						<?php
							$sql = "SELECT * FROM members WHERE TYPE IN (4) AND MBRID IN (SELECT MBRID FROM `meeting_present` WHERE MEETINGID = ".$meetid." )";
							$result = $conn->query($sql." ORDER BY GRADE DESC");

							if ($result->num_rows > 0) {
								while($row = $result->fetch_assoc()) {
									if ($conn->query('SELECT PRESENT AS PRES FROM `meeting_present` WHERE MEETINGID = '.$meetid.' AND MBRID = '.$row["MBRID"])->fetch_assoc()["PRES"] == 1){
										$style = 'background: rgb(28, 184, 65);';
										$not = '';
									}elseif($conn->query('SELECT EMAIL AS EMAIL FROM `meeting_present` WHERE MEETINGID = '.$meetid.' AND MBRID = '.$row["MBRID"])->fetch_assoc()["EMAIL"] == 1){
										$style = 'background: rgb(223, 117, 20);';
										$not = 'reason';
									}else{
										$style = 'background: rgb(202, 60, 60);';
										$not = 'not ';
									}
									echo '<tr style="color: white; '.$style.'">';
									echo "<td>" . $row["FNAME"]. " " . $row["SNAME"]. "</td>";
									echo "<td>".$row["GRADE"]."</td>";
									echo "<td>".$conn->query('SELECT TYPENAME FROM member_types WHERE TYPEID = '.$row["TYPE"])->fetch_assoc()["TYPENAME"].'</td>';
									if ($not !== 'reason') {echo '<td>Was '.$not.'present.</td>';} else {echo '<td>N.P, Had reason.</td>';}
									echo '</tr>';
								}
							} else {
								echo "No members in your group? This can be an issue... Add people or check your database.";
							}
						?>
					</tbody>
				</table>
			</div>
			<!--div id="controls">
				<div style="height:10px"></div>
				<a class="button-large button-secondary pure-button" style="color: white;" href="./rollNew.php">Reset</a>
				<button class="button-large button-warning pure-button" onclick=window.close()>Close</button>
				<button class="pure-button button-large pure-button-primary" onclick="sendForm()">Submit</button>
			</div-->
			<div style="height:30px"></div>
		</div>
	</body>
</html>
<?php $conn->close(); ?>