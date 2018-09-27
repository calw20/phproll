<?php include "./../DBStuff.php"; ?>
<html>
	<head>
		<title>Meeting Manager</title>
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
			
			function toggleDel(){
				document.getElementById("toggle-del").innerHTML = document.getElementById("toggle-del").innerHTML == "Show Delete Button" ? 'Hide Delete Button' : 'Show Delete Button';
				var buttons = document.getElementsByClassName("btn-del");
				for (var i = 0, len = buttons.length; i < len; i++) {
				buttons[i].hidden = !buttons[i].hidden;
				}
			}
			
			function email(id){
				window.open("./rollEmail.php?roll="+id+"&bcc=" + document.getElementById("bccBox").value);
			}
		</script>
		<div align="center">
			<div style="height:1px"></div>
			<h2>Meeting Manager</h2>
			<div>
				<form action = "" method = "get">
					<label for="year">Year</label>
					<select id="year" name="year">
						<option value="all">All</option>
						<?php
							$years = $conn->query("SELECT DISTINCT YEAR(DATE) AS YEAR FROM `meeting_date` ORDER BY YEAR(DATE) DESC");
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
					<button type="submit">Go</button>
					<button onclick=location.reload()>Refresh</button>
					<button type=button id="toggle-del" onclick=toggleDel()>Show Delete Button</button>
					<button type=button onclick=window.open('./rollMissed.php')>Absentee Stats</button>
					<input type="checkbox" id="bccBox" value="1" onclick="this.value = this.value == 0 ? 1 : 0;" checked="true">BCC All.</input>
				</form>
			</div>
			<table class="pure-table pure-table-bordered" style="text-align:center; vertical-align:middle;">
				<thead style="text-align:center; vertical-align:middle;">
					<tr>
						<td>ID Number</td>
						<td>Date</td>
						<td>Options</td>
					</tr>
				</thead>
				
				<tbody>
				<?php
					$sql = "SELECT * FROM `meeting_date`";
					if(isset($_GET['year']) && ($_GET['year']!==null)){ $year = $_GET['year'];} else { $year = date("Y") ;}
					if ($year != "all"){
						$sql = $sql." WHERE YEAR(DATE) = ".$year.";";
					} else {
						$sql = $sql.";";
					}
					$result = $conn->query($sql);
					if ($result->num_rows > 0) {
						while($row = $result->fetch_assoc()) {
							echo '<tr>';
								echo '<td>'.$row["MEETINGID"].'</td>';
								echo '<td>'.date('d-M-y h:i a', strtotime($row["DATE"])).'</td>';
								echo '<td><button onclick=window.open("./rollPrint.php?roll='.$row["MEETINGID"].'")>Info/Print</button> ';
								echo '<button onclick=window.open("./rollNew.php?roll='.$row["MEETINGID"].'")>Edit</button> ';
								echo '<button onclick=email('.$row["MEETINGID"].')>Email</button> ';
								echo '<button hidden class="btn-del" onclick=window.open(\'./rollDel.php?meetingid='.$row["MEETINGID"].'\')>Delete Meeting</button></td>';
							echo '</tr>';
						}
					} else {
						echo "No Meetings found! Add new meetings or check your database entries.";
					}
				?>
				</tbody>
			</table>
			<div style="height:18px"></div>
			<button onclick=location.href='./../index.php'>Go Home</button>
			<button onclick=window.open('./rollNew.php')>New Meeting</button>
			<div style="height:20px"></div>
		</div>
	</body>
</html>
<?php $conn->close(); ?>