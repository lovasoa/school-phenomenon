<?php
function hue ($r,$g,$b) {
	$m = ($r+$g+$b)/3;
	//Give a hue of 0 to all greys
	if (pow($r-$m,2) + pow($g-$m,2) + pow($b-$m,2) < 100) {return -$m;}
	return atan2(sqrt(3)*($g-$b), $r-$g-$b) + pi();
}

$im = imagecreatefrompng("images/nuancier.png");
$w = imagesx($im);
$h = imagesy($im);

$motif_w = 90;
$motif_h = 90;

$colors = array();

$cw = $w/9;
$ch = $h/14;

for ($c=1; $c<6; $c++) {
	$x0 = ($c%3)*($w/3);
	$y0 = floor($c/3)*($h/2);

	for ($i=0; $i<21; $i++) {
		$x = $x0 + ($i%3)*$cw + $cw/2;
		$y = $y0 + floor($i/3)*$ch + $ch/2;

		$num = $c*21+$i+1;
		$rgb = imagecolorat($im, $x, $y);
		$hex = dechex($rgb & 0xFFFFFF);
		while (strlen($hex)<6) {$hex = '0'.$hex;}
		$r = ($rgb>>16)&0xFF; $g = ($rgb>>8)&0xFF; $b = $rgb&0xFF;

		//On détermine si la couleur est unie
		$uni = TRUE;
		for ($xt=$x; $xt<$x+10&&$uni==TRUE; $xt++) {
			if ($rgb != imagecolorat($im, $xt, $y)) {
				$uni=FALSE;
			}
		}

		if ($uni === FALSE) {
			$motif = imagecreatetruecolor($motif_w, $motif_h);
			imagecopy($motif, $im, 0, 0, $x-$motif_w, $y-$motif_h, $motif_w, $motif_h);
			imagejpeg($motif, "images/motifs/$num.jpg");
			imagedestroy($motif);
		}

		$infos = array(
			"couleur" => '#'.$hex,
			"num"=>$num,
			"hue"=>hue($r,$g,$b)
		);
		if ($uni === FALSE) {
			$infos['motif'] = TRUE;
		}
		$colors[] = $infos;
	}
}


usort($colors, function ($a,$b) {
	if (isset($a['motif'])) return 1;
	if (isset($b['motif'])) return -1;
	return $a['hue']-$b['hue'];
});

for($i=0;$i<count($colors);$i++) {
	unset($colors[$i]["hue"]);
}


$flags = isset($_GET["pretty_print"]) ?  JSON_PRETTY_PRINT : 0;
header("Content-Type: application/json");
echo json_encode($colors, $flags);
?>
