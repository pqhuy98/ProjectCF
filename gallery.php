<?php
	$p_string=scandir("raw_images/");
	$path = [];
	foreach ($p_string as $s)
		if ('0'<=$s[0]&&$s[0]<='9')
			$path[] = intval($s);
	sort($path);
	foreach ($path as $p) {
		if ($p[0]!='.')
			echo "$p<br><img src='raw_images/$p' alt='$p' height=500px><br><br>";
	}
?>