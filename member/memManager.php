<?php include "./../DBStuff.php"; ?>
<?php if(isset($_GET['memDisabled']) && ($_GET['memDisabled']!==null || $_GET['memDisabled']!=="")){ $memDisabled = (int)$_GET['memDisabled'];} else { $memDisabled = 0; } ?>
<html>
	<head>
		<title>Member Manager</title>
		<link rel="stylesheet" href="./../pure-min.css">
	</head>
	
	<body>
		<script>
			function PopupCenter(url, title, w, h) {
				// Fixes dual-screen position                         Most browsers      Firefox
				var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : window.screenX;
				var dualScreenTop = window.screenTop != undefined ? window.screenTop : window.screenY;

				var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
				var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

				var left = ((width / 2) - (w / 2)) + dualScreenLeft;
				var top = ((height / 2) - (h / 2)) + dualScreenTop;
				var newWindow = window.open(url, title, 'menuebar=no, resizable=no, scrollbars=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

				// Puts focus on the newWindow
				if (window.focus) {
					newWindow.focus();
				}
			}
		</script>
		<div align="center">
			<div style="height:1px"></div>
			<h2>Member Manager</h2>
			<div>
				<!--?php echo ''; ?-->
				<form action = "" method = "get">
					<label for="year">Year</label>
					<select id="year" name="year">
						<option value="all">All</option>
						<?php
							$years = $conn->query("SELECT DISTINCT YEAR FROM `members` WHERE DISABLED = ".$memDisabled." ORDER BY YEAR DESC");
							if ($years->num_rows > 0) {
								while($row = mysqli_fetch_assoc($years)) {
									echo '<option value="'.$row["YEAR"].'"'; 
									if (($row["YEAR"] == date("Y") && isset($_GET['year']) == 0) || (isset($_GET['year']) && $row["YEAR"] == $_GET['year'])){echo 'selected';}
									echo '>'.$row["YEAR"].'</option>';
								}
							}						
							unset($years);
						?>
					</select>
					<label for="typelbl">Type</label>
					<select id="typelbl" name="type">
						<option value="all" <?php if ((isset($_GET['type']) && $_GET['type'] == "all")){echo 'selected';}?>>All</option>
						<option value="heads" <?php if ((isset($_GET['type']) && $_GET['type'] == "heads")){echo 'selected';}?>>Heads</option>
						<?php
							$types = $conn->query("SELECT DISTINCT * FROM `member_types` ORDER BY TYPEID ASC");
							echo mysqli_error($conn);
							if ($types->num_rows > 0) {
								while($row = mysqli_fetch_assoc($types)) {
									echo '<option value="'.$row["TYPEID"].'"'; 
									if ((isset($_GET['type']) && $row["TYPEID"] == $_GET['type'])){echo 'selected';}
									echo '>'.$row["TYPENAME"].'</option>';
								}
							}						
							unset($types);
						?>
					</select>
					<input id="memDisabled" type="hidden" value="<?php echo $memDisabled?>" name="memDisabled"></input>
					<input type="checkbox" onchange="this.checked ? document.getElementById('memDisabled').value = 1 : document.getElementById('memDisabled').value = 0;" <?php if ($memDisabled == 1){echo 'checked';}?>>Disabled Members</input>
					<button type="submit">Go</button>
					<button onclick=location.reload()>Refresh</button>
					<button type="button" onclick=document.getElementById("emailClick").click()>Email These People!</button>
				</form>
			</div>
			<table class="pure-table pure-table-bordered" style="text-align:center; vertical-align:middle;">
				<thead style="text-align:center; vertical-align:middle;">
					<tr>
						<td>ID Number</td>
						<td>Name</td>
						<td>Type</td>
						<td>Grade</td>
						<td>Brithday</td>
						<td>Year</td>
						<td>Options</td>
					</tr>
				</thead>
				
				<tbody>
					<?php
						if(isset($_GET['year']) && ($_GET['year']!==null)){ $year = $_GET['year'];} else { $year = date("Y") ;}
						if ($year !== 'all'){
							$year = ' WHERE YEAR = '.(int)$year;
						} else { 
							$year = "";
						}
						//echo $year;
						if(isset($_GET['type']) && ($_GET['type']!==null)){ $type = $_GET['type'];} else { $type = "all" ;}
						if (strpos($year, 'WHERE') !== false){
							$andWhere = ' AND ';
						} else {
							$andWhere = ' WHERE ';
						}
						if ($type !== 'all' && $type !== "heads"){
							$type = $andWhere.'TYPE = '.$type.' AND DISABLED = '.$memDisabled.';';
						} elseif ($type == "heads") {
							$type = $andWhere.'TYPE IN (0,1,2,3) AND DISABLED = '.$memDisabled.';';
						} else{
							$type = $andWhere.'DISABLED = '.$memDisabled.';';
						}
						
						$sql = "SELECT * FROM members".$year.$type;
						
						//echo $sql;
						
						$result = $conn->query($sql);
						//echo $sql.'<br>';
						//echo mysqli_error($conn);
						
						if ($result->num_rows > 0) {
							// output data of each row
							while($row = $result->fetch_assoc()) {
								//echo "id: " . $row["MBRID"]. " - Name: " . $row["FNAME"]. " " . $row["SNAME"]. "<br>" . $conn->insert_id;
								echo '<tr>';
								echo 	'<td>'.$row["MBRID"].'</td>';
								echo 	'<td>'.$row["FNAME"].' '.$row["SNAME"].'</td>';
								echo 	'<td>'.$conn->query('SELECT TYPENAME FROM member_types WHERE TYPEID = '.$row["TYPE"])->fetch_assoc()["TYPENAME"].'</td>';
								echo 	'<td>';
								if ($row["TYPE"] == 5 || $row["TYPE"] == 6 || $row["GRADE"] == 0){echo "N.A.";} else {echo $row["GRADE"];}
								echo '</td>';
								$mBDay = strtotime($row["BDAY"]);
								if ($mBDay !== strtotime(0000-00-00)){$mBDay = date('d-m-Y',$mBDay);} else {$mBDay = "Unknown";}
								echo	'<td>'.$mBDay.'</td>'; unset($mBDay);
								if (strpos($row["UNAME"], '@') !== false){
									$email = $row["UNAME"];
								} else {
									$email = $row["UNAME"] . '@emmaus.qld.edu.au';
								}
								echo '<td>'.$row["YEAR"].'</td>';
								echo 	'<td><button onclick=PopupCenter(\'./memAdd.php?member='.$row["MBRID"].'\',\'\',\'550\',\'330\');>Edit</button>';
								echo 	' <button onclick="location.href=\'mailto:'.$email.'\';">Email</button></td>';
								echo '</tr>';
							}
						} else {
							echo "No Members found! Add new members or check your database entries.";
						}
					?>
				</tbody>
			</table>
			<div style="height:20px"></div>
			<button onclick=location.href='./../index.php'>Go Home</button>
			<button onclick=PopupCenter('./memAdd.php','','550','330')>Add Member</button>
			<?php
				$sql = rtrim("SELECT UNAME FROM `members`".$year.$type,';');
				
				if (strpos($sql, 'WHERE') !== false){
					$andWhere = ' AND ';
				} else {
					$andWhere = ' WHERE ';
				}
				
				$result = $conn->query($sql.$andWhere."TYPE NOT IN (5,6) AND DISABLED = ".$memDisabled.";");
				//echo $sql.$andWhere."TYPE NOT IN (5,6);";
				//echo mysqli_error($conn);
				
				$email="";
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
				
				$result = $conn->query($sql.$andWhere."TYPE IN (5,6) AND DISABLED = ".$memDisabled.";");
				//echo mysqli_error($conn);
				
				$email = $email.'?cc=';
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						if (strpos($row["UNAME"], '@') !== false){
							$email = $email.$row["UNAME"].";";
						} else {
							$email = $email.$row["UNAME"] . '@emmaus.qld.edu.au' . ';';
						}
					}
				}
				
				echo '<button id="emailClick" onclick="location.href=\'mailto:'.$email.'\'">Email These People</button>';
			?>
			<div style="height:10px"></div>
		</div>
	</body>
</html>


<?php $conn->close(); ?>