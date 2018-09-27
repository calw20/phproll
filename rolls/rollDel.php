<?php include "./../DBStuff.php"; ?>
<?php
	$enableQuery = true;
	$falseError = false;
	$redirect = 1;
	if(isset($_GET['meetingid']) && ($_GET['meetingid']!==null || $_GET['meetingid']!="")){ $meetingid = (int)$_GET['meetingid'];} else { $meetingid = "Not set!"; }
	if(isset($_GET['confirm']) && ($_GET['confirm']!==null || $_GET['confirm']!="")){ $confirm = (int)$_GET['confirm'];} else { $confirm = 0; }
	if(isset($_GET['hout']) && ($_GET['hout']!==null || $_GET['hout']!="")){ $humanOutput = (int)$_GET['hout'];} else { $humanOutput = 1; }
	
	if (!isset($meetingid) || !is_int($meetingid)){
		echo '<h1>Error</h1><hr>';
		echo 'You didn\'t supply a meeting id, nothing can be deleted.';
		echo '<br>Click the close button to quit and try again later.</p>';
		echo '<button onclick=window.close()>Close</button><br>';
		$conn->close();
		die();
	}
	
	if ($confirm == 1){
		$sql='DELETE FROM `meeting_present` WHERE `meeting_present`.MEETINGID = '.$meetingid.'; DELETE FROM `meeting_date` WHERE `meeting_date`.MEETINGID = '.$meetingid.';';
		
		if ($enableQuery){
			$res = mysqli_multi_query($conn, $sql);
			echo mysqli_error($conn);
			if ($res) { $mysqlError = 0; } else { $mysqlError = 1; }
			do {
			  if ($result = mysqli_store_result($conn)) {
				mysqli_free_result($result);
			  }
			} while (mysqli_next_result($conn));
		} else {
			$mysqlError = 0;
		}
		
		if ($falseError) {$mysqlError = 2;}
			
		if($mysqlError == 0 && $humanOutput == 1 && $redirect == 1){
			echo "<script>window.close()</script>";
		} elseif ($mysqlError == 0 && $humanOutput == 0) {
			echo 'true';
		} elseif ($mysqlError > 0 && $humanOutput == 1) {
			echo '<h1>SQL ERROR!</h1>';
			if($mysqlError == 2){ $tmpWord = 'you';} else { $tmpWord = 'cake';}
			echo '<p>It was the '.$tmpWord.', not me!</p>';
			echo "<hr>";
		    echo "Error: "; echo mysqli_error($conn);
			echo "<br>SQL: ".$sql;
			echo "<br>Meeting ID: ".$meetingid;
			echo "<hr>";
			echo '<p>Yeh, looks there was a SQL Error, please alert the devs/server owner if this continues.<br>';
			echo 'Click the close button to quit and try again later.</p>';
			echo '<button onclick=window.close()>Close</button><br>';
		}  else {
			echo 'false';
		}
		
		$conn->close();
		exit();
	}

	$meetInfoRes = $conn->query('SELECT * FROM `meeting_date` WHERE MEETINGID = '.$meetingid)->fetch_assoc();
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
		<title>Delete Meeting</title>
		<link rel="stylesheet" href="./../pure-min.css">
		<style>
		.button-xlarge {
            font-size: 150%;
        }
		.button-large {
            font-size: 110%;
        }
		
		button-secondary,
		.button-warning,
		.button-success,
        .button-error {
            color: white;
            border-radius: 4px;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
        }

        .button-success {
            background: rgb(28, 184, 65); /* this is a green */
        }

        .button-error {
            background: rgb(202, 60, 60); /* this is a maroon */
        }
		
		.button-warning {
            background: rgb(223, 117, 20); /* this is an orange */
        }

        .button-secondary {
            background: rgb(66, 184, 221); /* this is a light blue */
        }
		</style>
	</head>
	<body>
		<script>
			function flip(me){
				if (me.classList.contains('button-success')){
					me.classList.add('button-error');
					me.classList.remove('button-success'); 
					me.innerHTML = 'Yes delete meeting.';
					document.getElementById('confirm').value = 1;
				} else {
					me.classList.remove('button-error');
					me.classList.add('button-success'); 
					me.innerHTML = 'No close this window on go.'
					document.getElementById('confirm').value = 0;
				}
			}
			
			function runForm(){
				if (document.getElementById('confirm').value == 1){
					document.getElementById('form1').submit();
				} else{
					window.close()
				}
				
			}
			window.onunload = function(){
				window.opener.location.reload();
			}
		</script>
		<div align="center">
			<div style="height:1px"></div>
			<h2>Delete Meeting</h2>
			<p>Do you really want to delete the meeting that happend<br>on <?php echo $meeting["date-time-format"];?>?</p>
			<form method="get" id="form1">
			<input type="hidden" value="<?php echo $_GET['meetingid'];?>" name="meetingid" id="meetingid"></input>
			<input type="hidden" value="0" name="confirm" id="confirm"></input>
			<button type="button" id="btn-conf" class="pure-button button-xlarge button-success" onclick=flip(this)>No close this window on go.</button>
			<button type="button" onclick=runForm() class="pure-button button-xlarge button-warning">Go</button>
			</form>
		</div>
	</body>
</html>
<?php $conn->close(); ?>