<?php include "./../DBStuff.php";?>
<?php 
	if(isset($_GET['roll']) && ($_GET['roll']!==null)){ $roll = (int)$_GET['roll'];} else { $roll = null;}
	if ($roll != null){$meetid = $roll;} else {$meetid = $conn->query("SELECT MAX(MEETINGID) AS MID FROM `meeting_date`;")->fetch_assoc()["MID"] + 1;}
?>
<html>
	<head>
		<title><?php if( $roll == null){echo 'New';}else{echo 'Edit';} ?> Meeting</title>
		<link rel="stylesheet" href="./../pure-min.css">
		<link rel="stylesheet" href="./buttons.css">
	</head>	
	<body> <!--style="max-width:550px"-->
		<script>
			function flipHere(me){
				if (me.classList.contains('button-success')){
					if(me.classList.contains('button-warning')) {me.classList.remove('button-warning');}
					/*if (!me.classList.contains('staff')) {me.classList.add('button-error');} else {*/ me.classList.add('button-warning'); //}
					me.classList.remove('button-success'); 
					if (me.classList.contains('staff')) {me.innerHTML = 'Email Only';} else {me.innerHTML = 'Has Reason';}
				} else if(me.classList.contains('button-warning')){
					me.classList.add('button-error');
					me.classList.remove('button-warning'); 
					me.innerHTML = 'Not Present';
				} else {
					if(me.classList.contains('button-warning')) {me.classList.remove('button-warning');}
					me.classList.remove('button-error');
					me.classList.add('button-success'); 
					me.innerHTML = 'Present';}
			}
			
			function sendForm(){
				var buttons = document.getElementsByClassName("presentBTN");
				var pplPresent = "";
				for (var i = 0, len = buttons.length; i < len; i++) {
					let pres = buttons[i].classList.contains('button-success') ? 1 : 0;
					let email = buttons[i].classList.contains('button-warning') ? 1 : 0;
					pplPresent += "(" + buttons[i].value + ", ROLLNAME-MATCHTHIS, " + pres + ", " + email +"),";
				}
				pplPresent = pplPresent.slice(0, -1);
				 document.getElementById("pplpresent").value = pplPresent;
				 document.getElementById("newRoll").submit();
			}
			window.onunload = function(){
				window.opener.location.reload();
			}
		</script>
		<form id="newRoll" action = "./rollBackend.php" method = "get">
			<input type="hidden" value="" name="pplpresent" id="pplpresent"></input>
			<input type="hidden" value="<?php echo $meetid; ?>" name="meetid"></input>
			<input type="hidden" value="<?php if($roll != null){echo 1;}else{0;}?>" name="update"></input>
		</form>
		<div align="center">
			<div style="height:1px"></div>
			<h2 style="margin-bottom:5px"><?php if( $roll == null){echo 'New';}else{echo 'Edit';} ?> Meeting</h2>
			<p style="margin-top:0px"> For <?php echo date("l").' the '.date("jS").' of '.date("F")." ".date("Y").' at '.date('g:i a') ?><br>Meeting ID: <?php echo $meetid; ?></p>
			<div id="tables">
				<table class="pure-table pure-table-bordered" style="text-align:center; vertical-align:middle;">
					<h4 style="margin-bottom:5px">Head Members</h4>
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
							$sql = "SELECT * FROM members WHERE TYPE IN (0,1,2,3)";
							if ($roll == null){$sql = $sql." AND DISABLED = 0;";} else {$sql = $sql." AND MBRID IN (SELECT MBRID FROM `meeting_present` WHERE MEETINGID = ".$meetid." )";}
							$result = $conn->query($sql);
							if ($result->num_rows > 0) {
								while($row = $result->fetch_assoc()) {
									if (date("0000-m-d",strtotime($row["BDAY"])) == date("0000-m-d")){ $style = 'style="color: white; background: rgb(223, 117, 20);"';} else {$style = '';}
									echo '<tr '.$style.' >';
									echo "<td>" . $row["FNAME"]. " " . $row["SNAME"]. "</td>";
									echo "<td>".$row["GRADE"]."</td>";
									echo "<td>".$conn->query('SELECT TYPENAME FROM member_types WHERE TYPEID = '.$row["TYPE"])->fetch_assoc()["TYPENAME"].'</td>';
									echo '<td><button id=btn-'.$row["MBRID"].'" value="'.$row["MBRID"].'" onclick="flipHere(this)" class="presentBTN heads button-xlarge pure-button button-';
									if($roll != null){
										if ($conn->query('SELECT PRESENT AS PRES FROM `meeting_present` WHERE MEETINGID = '.$meetid.' AND MBRID = '.$row["MBRID"])->fetch_assoc()["PRES"] == 1){
											echo 'success">Present';
										}else if($conn->query('SELECT EMAIL AS EMAIL FROM `meeting_present` WHERE MEETINGID = '.$meetid.' AND MBRID = '.$row["MBRID"])->fetch_assoc()["EMAIL"] == 1){
											echo 'warning">Has Reason';
										}else{
											echo 'error">Not Present';
										}
									} else {
										echo 'success">Present';
									}
									echo '</button></td>';
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
							$sql = "SELECT * FROM members WHERE TYPE IN (5,6)";
							if ($roll == null){$sql = $sql." AND DISABLED = 0;";} else {$sql = $sql." AND MBRID IN (SELECT MBRID FROM `meeting_present` WHERE MEETINGID = ".$meetid." )";}
							$result = $conn->query($sql);
							if ($result->num_rows > 0) {
								while($row = $result->fetch_assoc()) {
									if (date("0000-m-d",strtotime($row["BDAY"])) == date("0000-m-d")){ $style = 'style="color: white; background: rgb(223, 117, 20);"';} else {$style = '';}
									echo '<tr '.$style.' >';
									echo "<td>" . $row["FNAME"]. " " . $row["SNAME"]. "</td>";
									echo "<td>N.A.</td>";
									echo "<td>".$conn->query('SELECT TYPENAME FROM member_types WHERE TYPEID = '.$row["TYPE"])->fetch_assoc()["TYPENAME"].'</td>';
									echo '<td><button id=btn-'.$row["MBRID"].'" value="'.$row["MBRID"].'" onclick="flipHere(this)" class="presentBTN staff button-xlarge pure-button button-';
									if($roll != null){
										if ($conn->query('SELECT PRESENT AS PRES FROM `meeting_present` WHERE MEETINGID = '.$meetid.' AND MBRID = '.$row["MBRID"])->fetch_assoc()["PRES"] == 1){
											echo 'success">Present';
										}else if($conn->query('SELECT EMAIL AS EMAIL FROM `meeting_present` WHERE MEETINGID = '.$meetid.' AND MBRID = '.$row["MBRID"])->fetch_assoc()["EMAIL"] == 1){
											echo 'warning">Email Only';
										}else{
											echo 'error">Not Present';
										}
									} else {
										echo 'success">Present'; //'warning">Email Only';
									}
									echo '</button></td>';
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
							$sql = "SELECT * FROM members WHERE TYPE IN (4)";
							if ($roll == null){$sql = $sql." AND DISABLED = 0";} else {$sql = $sql." AND MBRID IN (SELECT MBRID FROM `meeting_present` WHERE MEETINGID = ".$meetid." )";}
							$result = $conn->query($sql." ORDER BY GRADE DESC;");
							//echo $sql.'<br>';
							//echo mysqli_error($conn);
							if ($result->num_rows > 0) {
								while($row = $result->fetch_assoc()) {
									if (date("0000-m-d",strtotime($row["BDAY"])) == date("0000-m-d")){ $style = 'style="color: white; background: rgb(223, 117, 20);"';} else {$style = '';}
									echo '<tr '.$style.' >';
									echo "<td>" . $row["FNAME"]. " " . $row["SNAME"]. "</td>";
									echo "<td>".$row["GRADE"]."</td>";
									echo "<td>".$conn->query('SELECT TYPENAME FROM member_types WHERE TYPEID = '.$row["TYPE"])->fetch_assoc()["TYPENAME"].'</td>';
									echo '<td><button id=btn-'.$row["MBRID"].'" value="'.$row["MBRID"].'" onclick="flipHere(this)" class="presentBTN members button-xlarge pure-button button-';
									if($roll != null){
																				if ($conn->query('SELECT PRESENT AS PRES FROM `meeting_present` WHERE MEETINGID = '.$meetid.' AND MBRID = '.$row["MBRID"])->fetch_assoc()["PRES"] == 1){
											echo 'success">Present';
										}else if($conn->query('SELECT EMAIL AS EMAIL FROM `meeting_present` WHERE MEETINGID = '.$meetid.' AND MBRID = '.$row["MBRID"])->fetch_assoc()["EMAIL"] == 1){
											echo 'warning">Has Reason';
										}else{
											echo 'error">Not Present';
										}
									} else {
										echo 'error">Not Present';
									}
									echo '</button></td>';
									echo '</tr>';
								}
							} else {
								echo "No members in your group? This can be an issue... Add people or check your database.";
							}
						?>
					</tbody>
				</table>
			</div>
			<div id="controls">
				<div style="height:10px"></div>
				<a class="button-large button-secondary pure-button" href="./../index.php">Home</a>
				<a class="button-large button-secondary pure-button" style="color: white;" href="./rollNew.php<?php if($roll != null){echo '?roll='.$meetid;}?>">Reset</a>
				<button class="button-large button-warning pure-button" onclick=window.close()>Close</button>
				<button class="pure-button button-large pure-button-primary" onclick="sendForm()">Submit</button> 
			</div>
			<div style="height:30px"></div>
		</div>
	</body>
</html>
<?php $conn->close(); ?>