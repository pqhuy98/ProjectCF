<?php
	function get_ip() {
		$ipaddress = '';
		if (getenv('HTTP_CLIENT_IP'))
			$ipaddress = getenv('HTTP_CLIENT_IP');
		else if(getenv('HTTP_X_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
		else if(getenv('HTTP_X_FORWARDED'))
			$ipaddress = getenv('HTTP_X_FORWARDED');
		else if(getenv('HTTP_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_FORWARDED_FOR');
		else if(getenv('HTTP_FORWARDED'))
		   $ipaddress = getenv('HTTP_FORWARDED');
		else if(getenv('REMOTE_ADDR'))
			$ipaddress = getenv('REMOTE_ADDR');
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;
	}
	function get_result($dir,$path) {
		$real_path=$dir.$path;
 		if (($sock = socket_create(AF_INET,SOCK_STREAM,SOL_TCP))===False) {
			echo "</table><h1>Server error 001</h1>";
			exit;
		}
		if (socket_connect($sock,"192.168.0.169",2222)===False) {
			echo "</table><h1>Service is under maintenance.</h1>";
			exit;
		}
		if (socket_write($sock,$real_path,strlen($real_path))===False) {
			echo "</table><h1>Server error 002</h1>";
			exit;
		}
		$result = socket_read($sock,1000000);
		socket_close($sock);
		if ($result===False) {
			echo "</table><h1>Server doesn't respond.</h1>";
			exit;
		}
		return $result;
	}
	function get_sorted_prob($path,$dir) {
		if(!file_exists("history/"))
			mkdir("history", 0711);
		file_put_contents("history/history.txt",date("H:i:s d/m/Y")." ".get_ip()." ".$path."\n",FILE_APPEND);
		if(!file_exists($dir))
			mkdir($dir, 0777);
		$prob = explode(" ",get_result($dir,$path));
		if ($prob[0]=="-1") {
			return False;
		}
		$res = Array();
		$label = array("Airplane","Car","Bird","Cat","Deer","Dog","Frog","Horse","Ship","Truck");
		for($i=0;$i<10;$i++)
			$res[] = Array("label"=>$label[$i],"prob"=>round(floatval($prob[$i])*150));
		usort($res,function($i,$j) {return $i["prob"]>$j["prob"];});
		$res = array_reverse($res);
		$res[0]["label"] = strtoupper($res[0]["label"]);
		return Array($res[0],$res[1],$res[2],$res[3],$res[4]);
		// $res = Array();
		// $res[] = Array("label"=>"Car","prob"=>75.0);
		// $res[] = Array("label"=>"Dog","prob"=>35.0);
		// $res[] = Array("label"=>"Cat","prob"=>15.0);
		// $res[] = Array("label"=>"Frog","prob"=>5.0);
		// $res[] = Array("label"=>"Ship","prob"=>1.0);
		// return $res;
	}
	function alert($s) {
		echo"<script>window.onload=function(){alert('$s');window.location = 'index.html';}</script>";
	}
	function print_result($res) {
			echo "<table align=center>";
			$b = "<b>";$_b="</b>";
			foreach($res as $p) {
				echo "<tr><td>$b".$p["label"]."$_b</td><td style='text-align:left'>";
				echo "<img src='bar.png' width=".$p["prob"]."px height=10px>";
				echo "</td></tr>";
				$b="";$_b="";
			}
			echo "</table>";
	}
	function upload($file) {
		$upload_dir="raw_images/";
		if(!file_exists($upload_dir))
			mkdir($upload_dir, 0777);
		$name = $file["name"];
		if($file["error"]<>0) {
			switch($file["error"]) {
				case 4:
					return false;
				case 1: 
					alert("<b>$name</b> exceeds the max upload size");
					break;
				case 2:
					alert("<b>$name</b> exceeds the max upload size");
					break;
				case 3:
					alert("<b>$name</b> wasn't uploaded completely.");
					break; 
				case 6: 
					alert("Missing a temporary folder.");
					break;
				case 7:
					alert("System error : failed to write <b>$name</b> to disk.");
					break;
				case 8:
					alert("System error : failed to write <b>$name</b> to disk.");
					break;
			}
		} else if (!exif_imagetype($file["tmp_name"]))
			alert("This image is not valid or corrupted.");
		else {
			$idx = max(0,count(scandir($upload_dir))-2);
			move_uploaded_file($file["tmp_name"],$upload_dir.$idx);
			file_put_contents($upload_dir.$idx.".png","");
			chmod($upload_dir.$idx, 0777);
			chmod($upload_dir.$idx.".png", 0777);
			$res = get_sorted_prob($idx,$upload_dir);
			if ($res!==False) {
				echo "<img src=$upload_dir$idx height=200px class='result_img' onmouseenter='' onmouseleave=''><br><br>";
				print_result($res);
			} else {	
				echo "</table><h1>The image is corrupted.</h1>";
			}
		}
		return true;
	}
	function upload_url($url) {
		$upload_dir="raw_images/";
		if (empty($url)) return false;
		if (getimagesize($url)) {
			if (exif_imagetype($url)==IMAGETYPE_GIF) {
				echo "</table><h1>GIF images are not supported !</h1>";
				return true;
			}
			if(!file_exists($upload_dir))
				mkdir($upload_dir, 0777);
			$idx = max(0,count(scandir($upload_dir))-2);
			copy($url,$upload_dir.$idx);
			file_put_contents($upload_dir.$idx.".png","");
            chmod($upload_dir.$idx,0777);
            chmod($upload_dir.$idx.".png",0777);
			$res = get_sorted_prob($idx,$upload_dir);
			if ($res!==False) {
				echo "<img src=$upload_dir$idx height=200px class='result_img' onmouseenter='' onmouseleave=''><br><br>";
				print_result($res);
			} else {
				echo "</table><h1>The image is corrupted.</h1>";
			}
			return true;
		} else return false;
	}
	function solve() {		
		$msg1 = false;
		echo "<table><tr><td width=500px>";
		if (isset($_FILES["file"]))
			$msg1 = upload($_FILES["file"]);
		$msg2 = false;
		echo "</td><td width=50%>";
		if (isset($_POST["url"]))
			$msg2 = upload_url($_POST["url"]);
		echo "</td></tr></table>";
		if (!$msg1 && !$msg2)
			echo "<img src='oops.jpg' class='result_img'";
		$_POST = Array();
	}
?>


<!DOCTYPE HTML>
<html>
<head>
	<title>VERSION 4.00</title>
	<link rel="icon" href="../cifar10.png">
	<meta charset="utf-8">
	<link rel="stylesheet" href="../css/style.css">
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="../css/projects.css">
	<link href="https://fonts.googleapis.com/css?family=Montserrat|Open+Sans|Pangolin|Roboto|Bahiana|Caveat+Brush|Orbitron" rel="stylesheet">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script src="js/rotate.js"></script>
</head> 
<body>
<div class="go_to_home">
	<a href="../" title="Home"><img src="../home.png" width=25></a>
</div>
<div class="middle">
<div class="welcome_cifar">
<!-- ######################################################################################################### -->
	<span class="header_cifar"><b>"VERSION 4.00"</b></span><br>
	<span class="credit_cifar"><i>I'm a convolutional neural net.<br>Trust me, I can think.</i></span>
	<br><br><br>
	<table align=center width=675px><tr>
	<td width=40%>
s		<!--<div class="helper_off" id="hp1">
			You give me an <b>image</b>.<br>
			I tell you what it is.<br>
			It must be 1 in <b>10</b> classes.<br>
			Accuracy 87% in lastest test.<br>
			<b>If I don't know the answer,<br>
			I'll throw a random result.<br></b>
		</div>-->
	</td><td width=10% style="vertical-align: top">
		<!--<img src="symbol.png" width=100px class="helper_symbol" id="helper" onmouseenter="start_spin()" onmouseleave="stop_spin()">-->
		<?php solve();?>
	</td><td width=40%>
		<!--<div class="helper_off" id="hp2">
			<b><i>Available classes :</i></b><br>
			airplane, bird,<br>
			car, cat,<br>
			deer, dog,<br>
			frog, horse<br>
			ship, truck.
		</div>-->
	</td></tr></table>

	<script src="js/javascript.js"></script>
	<br><br>
	<a href="./" class="back_cifar"><b>Want to test me more ?</b></a>
<!-- ######################################################################################################### -->
</div></div>
<div class="network">
	<div id="large-header" class="large-header"></div>
	<canvas  id="canvas"></canvas>
	<script src="../background/js/TweenLite.min.js"></script>
	<script src="../background/js/EasePack.min.js"></script>
	<script src="../background/js/background.js"></script>
</div>
</body>
</html>

