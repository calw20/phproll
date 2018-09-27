<?php include "./../DBStuff.php"; ?>
<?php
	if(isset($_GET['member']) && ($_GET['member']!==null)){ $memberID = (int)$_GET['member'];} else { $memberID = null;}
	
	if ($memberID !== null){
		$memUpdate = 1;
		$sql = "SELECT * from `members` WHERE MBRID = ".$memberID.";";
		$memberRow = mysqli_fetch_assoc(mysqli_query($conn, $sql));
		
		$mInfo = array("id"    => $memberRow["MBRID"],
					   "fname" => $memberRow["FNAME"],
					   "lname" => $memberRow["SNAME"],
					   "uname" => $memberRow["UNAME"],
					   "type"  => $memberRow["TYPE"],
					   "year"  => $memberRow["YEAR"],
					   "bday"  => $memberRow["BDAY"],
					   "grade" => $memberRow["GRADE"],
					   "disabled" => $memberRow["DISABLED"]) ;
	} else {
		$memUpdate = 0;
		$mInfo = array("id"    => "",
					   "fname" => "",
					   "lname" => "",
					   "uname" => "",
					   "type"  => "",
					   "year"  => "",
					   "bday"  => "",
					   "grade" => "",
					   "disabled" => 0);
	}
?>
<html>
	<head>
		<title><?php if ($memberID === null) { echo 'Add New Member';} else {echo 'Edit Member Info';}?></title>
		<link rel="stylesheet" href="./pure-min-mem.css">
		<link rel="stylesheet" href="./../grids-responsive-min.css">
		<link rel="stylesheet" href="./misc.css">
	</head>
	<body>
	<script>
		function runThis(){
			updateEMAIL();
		}
		function updateEMAIL(){
			if (document.getElementById('roll').value == 6) {
				document.getElementById('uname').placeholder = "Email";
			} else if (document.getElementById('roll').value != 6 && document.getElementById('uname').placeholder != "Username"){
				document.getElementById('uname').placeholder = "Username";
			}
		}
		
		window.onunload = function(){
			window.opener.location.reload();
		}
	</script>
	<div> <!--class="content" style="max-width:500px; margin-left:auto; margin-right:auto;"-->
		<form action="./memAddBackend.php" method="get" class="pure-form pure-form-stacked" autocomplete="off">
			<fieldset class="pure-group">
				<legend>
					<h2 style="text-align: center;"><?php if ($memberID === null) { echo 'Add New Member';} else {echo 'Edit Member Info';}?></h2>
				</legend>
				<div class="pure-g">
					<div class="pure-u-1 pure-u-md-1-4 f5pad">
						<input type="text" id="fname" name="fname" placeholder="First Name" class="mywitdth" required value="<?php echo $mInfo["fname"]; ?>">
						<input type="text" id="lname" name="lname" placeholder="Last Name" class="mywitdth" required value="<?php echo $mInfo["lname"]; ?>">
						<input type="text" id="uname" name="uname" placeholder="Username" class="mywitdth" required value="<?php echo $mInfo["uname"]; ?>"> <!--style="text-transform:uppercase"-->
						<input type="date" id="bday" name="bday" placeholder="Birthday" class="mywitdth" value="<?php echo $mInfo["bday"]; ?>">
					</div>
					<div class="pure-u-1 pure-u-md-1-4 fp"></div><!---->
					<div class="pure-u-1 pure-u-md-1-2">
						<div class="pure-controls">
							<label for="roll" style="display:inline-block;">Roll: </label>
							<select id="roll" name="roll" style="display:inline-block;" onchange=runThis()>
								<?php
									$sql = "SELECT TYPENAME, TYPEID FROM `member_types`";
									if ($memberID === null){
										$sql = $sql."WHERE TYPEID NOT IN (SELECT DISTINCT TYPE FROM `members` WHERE YEAR = ".date("Y")." AND TYPE NOT IN (4,5,6)) ORDER BY TYPEID;";
									} else {$sql = $sql.";";}									
									$types = $conn->query($sql);
									if ($types->num_rows > 0) {
										while($row = mysqli_fetch_assoc($types)) {
											echo '<option value="'.$row["TYPEID"].'"'; 
											if ($memberID === null && $row["TYPEID"] == 4) {echo "selected";}
											if ($memberID !== null && $row["TYPEID"] == $mInfo["type"]) {echo "selected";}
											echo '>'.$row["TYPENAME"].'</option>';
										}
									}						
									unset($types);
									unset($sql);
								?>
							</select>
							<div style="height:5px"></div>
							<label for="grade" style="display:inline-block;">Grade: </label>
							<select id="grade" name="grade" style="display:inline-block;" >
								<option id="op12" value="12">12</option>
								<option id="op11" value="11">11</option>
								<option id="op10" value="10">10</option>
								<option id="op0" value="0">N.A.</option>
							</select>
							<?php
								$sql = "SELECT MAX(MBRID) AS LID FROM `members`;";
								//$result = mysqli_query($conn, $sql);
								$row = mysqli_fetch_assoc(mysqli_query($conn, $sql)); //was ($result);
								$newid = (int)$row["LID"] + 1;
								unset($sql); unset($row); /*unset($result);*/
							?>
							<div style="height:5px"></div>
							<label for="mbridShow" style="display:inline-block;">Member ID: </label>
							<input type="text" value="<?php if($memberID === null){ echo $newid; } else { echo $mInfo["id"]; } ?>" readonly style="max-width:100px; display:inline-block;" name="newid">
							
							<div style="height:5px"></div>
							<label for="mbrYear" style="display:inline-block;">Year: </label>
								<input type="number" id="year" name="year" min="2000" max="3000" step="1" value="<?php if($memberID === null){ echo date("Y").'"'; echo "readonly"; } else { echo $mInfo["year"].'"'; } ?> style="max-width:100px; display:inline-block;" name="year">
						</div>
					</div>	
					<div class="pure-u-1 pure-u-md-1-2 f5pad">
						<div class="pure-controls">
							<button type="submit" class="pure-button pure-button-primary">Submit</button>
							<button onclick=window.close() class="pure-button">Close</button>
							<?php 
								if($memberID === null){ 
									echo '<button type="reset" class="pure-button">Reset</button>';
								} else {
									echo '<a href="./memAdd.php" class="pure-button">Reset</a>';
								}
							?>
						</div>
					</div>
					<div class="pure-u-1 pure-u-md-1-3 f5pad">
						<input id="memDisabled" type="hidden" value="<?php echo $mInfo["disabled"]; ?>" name="memDisabled"></input>
						
						<input type="checkbox" style="display:inline-block;" onchange="this.checked ? document.getElementById('memDisabled').value = 1 : document.getElementById('memDisabled').value = 0;" <?php if ($mInfo["disabled"] == 1){echo 'checked';}?>></input>
						<label for="memDisabled" style="display:inline-block;">Disable Member</label>
					</div>
				</div>
			</fieldset>
			<input type="hidden" id="mUpdate" name="update" value="<?php echo $memUpdate; ?>">
		</form>
	</div>
	<script>
		function genX(count){
			let x = "";
			for (let i = 4 - count; i > 0 ; i--){
			 x += "X";
			}
			return x;
		}
		
		var unameBox = document.getElementById('uname');
		var fnameBox = document.getElementById('fname');
		var lnameBox = document.getElementById('lname');
		
		lnameBox.onkeyup = function(){
			if (document.getElementById('roll').value == 6) return;
			fLength = (fname.value.length < 4 ? fname.value.length : 4);
			lLength = (lname.value.length < 4 ? lname.value.length : 4);
			
			uname = lnameBox.value.substring(0,lLength) + genX(lLength) + fname.value.substring(0,fLength) + genX(fLength);
			unameBox.value = (lLength > 0 && fLength > 0 ? uname.toUpperCase() : "");
		}
		
	</script>
	</body>
</html>
<?php $conn->close(); ?>