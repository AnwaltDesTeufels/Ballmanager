<?php include 'zz1.php'; ?>
<title>Lotto | Ballmanager.de</title>
<?php include 'zz2.php'; ?>
<?php
$specialOffer = getSpecialOffer();
switch (date('m', time())) {
	case '01': $monat_string = 'Januar'; break;
	case '02': $monat_string = 'Februar'; break;
	case '03': $monat_string = 'März'; break;
	case '04': $monat_string = 'April'; break;
	case '05': $monat_string = 'Mai'; break;
	case '06': $monat_string = 'Juni'; break;
	case '07': $monat_string = 'Juli'; break;
	case '08': $monat_string = 'August'; break;
	case '09': $monat_string = 'September'; break;
	case '10': $monat_string = 'Oktober'; break;
	case '11': $monat_string = 'November'; break;
	case '12': $monat_string = 'Dezember'; break;
}
$heute_tag = date('d', time());
$heute_monat = date('m', time());
$heute_jahr = date('Y', time());
$datum_min = mktime(00, 00, 01, $heute_monat, 01, $heute_jahr);
$datum_max = mktime(23, 59, 59, $heute_monat, 31, $heute_jahr);
$datum_heute = date('Y-m-d', time());
?>
<h1>Lotto vom <?php echo $heute_tag.'. '.$monat_string.' '.$heute_jahr; ?></h1>
<?php if ($loggedin == 1) { ?>
<?php
// TIPP EINLOESEN ANFANG
if (isset($_POST['tipp']) && $cookie_id != DEMO_USER_ID) {
	if (is_array($_POST['tipp'])) {
		if (count($_POST['tipp']) == 4) {
			$temp = $_POST['tipp'];
			$tipp_string = '';
			foreach ($temp as $temps) {
				$temps = intval($temps);
				if ($temps > 0 && $temps < 16) {
					$tipp_string .= $temps.'-';
				}
			}
			if (strlen($tipp_string) > 4) {
				$tipp_string = substr($tipp_string, 0, -1);
				$in1 = "INSERT INTO ".$prefix."lotto_tipps (team, datum, zahlen) VALUES ('".$cookie_team."', '".$datum_heute."', '".$tipp_string."')";
				$in2 = mysql_query($in1);
				if ($in2 !== FALSE) {
					if ($specialOffer === FALSE) {
						$az1 = "UPDATE ".$prefix."teams SET konto = konto-1000000 WHERE ids = '".$cookie_team."'";
						$az2 = mysql_query($az1);
						$az3 = "INSERT INTO ".$prefix."buchungen (team, verwendungszweck, betrag, zeit) VALUES ('".$cookie_team."', 'Lottoschein', -1000000, '".time()."')";
						$az4 = mysql_query($az3);
						$formulierung = 'Du hast für 1.000.000 € einen Lottoschein eingelöst.';
					}
					else {
						$formulierung = 'Du hast zur Feier des Tages einen kostenlosen Lottoschein eingelöst.';
					}
                    // PROTOKOLL ANFANG
                    $az5 = "INSERT INTO ".$prefix."protokoll (team, text, typ, zeit) VALUES ('".$cookie_team."', '".$formulierung."', 'Finanzen', ".time().")";
                    $az6 = mysql_query($az5);
                    // PROTOKOLL ENDE
                    $upper_jackpot1 = "UPDATE ".$prefix."lotto SET jackpot = jackpot+750000";
                    $upper_jackpot2 = mysql_query($upper_jackpot1);
                }
			}
		}
	}
}
// TIPP EINLOESEN ENDE
// DATEN HOLEN ANFANG
$u1 = "UPDATE ".$prefix."lotto SET jackpot = 5000000 WHERE jackpot < 5000000";
$u2 = mysql_query($u1);
$get_jackpot1 = "SELECT jackpot, zahlen_gestern FROM ".$prefix."lotto LIMIT 0, 1";
$get_jackpot2 = mysql_query($get_jackpot1);
$get_jackpot3 = mysql_fetch_assoc($get_jackpot2);
$get_zahlen1 = "SELECT zahlen FROM ".$prefix."lotto_tipps WHERE datum = '".$datum_heute."' AND team = '".$cookie_team."'";
$get_zahlen2 = mysql_query($get_zahlen1);
if (mysql_num_rows($get_zahlen2) != 0) {
	$get_zahlen3 = mysql_fetch_assoc($get_zahlen2);
	$meine_zahlen = $get_zahlen3['zahlen'];
}
else {
	$meine_zahlen = '';
}
// DATEN HOLEN ENDE
echo addInfoBox('<strong>Jackpot:</strong> '.number_format($get_jackpot3['jackpot'], 0, ',', '.').' €');
?>
<?php if ($meine_zahlen == '') { ?>
<form action="/ver_lotto.php" method="post" accept-charset="utf-8">
<div style="position:relative;width:372px;height:483px;background-image:url(http://s3.amazonaws.com/ballmanager.de/images/lottoschein.png)">
<?php
for ($i = 0; $i < 5; $i++) {
	for ($k = 0; $k < 3; $k++) {
		$pos_x = 95+$k*85;
		$pos_y = 115+$i*75;
		$zahl = 1+$i*3+$k;
		echo '<input style="position:absolute;top:'.$pos_y.'px;left:'.$pos_x.'px" type="checkbox" name="tipp[]" value="'.$zahl.'" />';
	}
}
echo '</div><p><input type="submit" value="';
if ($specialOffer === FALSE) {
	echo 'Für 1.000.000€ einlösen';
}
else {
	echo 'Kostenlos einlösen';
}
echo '"'.noDemoClick($cookie_id).' /></p></form>';
?>
<?php
}
else {
	echo '<p>Du hast heute schon getippt. Deine Zahlen: '.$meine_zahlen.'</p>';
}
?>
<h1>Heute schon getippt</h1>
<?php
$sql1 = "SELECT a.team, b.name FROM ".$prefix."lotto_tipps AS a JOIN ".$prefix."teams AS b ON a.team = b.ids ORDER BY b.name ASC";
$sql2 = mysql_query($sql1);
$tipper_liste = '';
while ($sql3 = mysql_fetch_assoc($sql2)) {
	$tipper_liste .= '<a href="/team.php?id='.$sql3['team'].'">'.$sql3['name'].'</a>, ';
}
echo '<p>'.substr($tipper_liste, 0, -2).'</p>';
?>
<h1>Zahlen von gestern</h1>
<p><?php echo $get_jackpot3['zahlen_gestern']; ?></p>
<h1>Letzte Gewinne</h1>
<ul>
<?php
$sql1 = "SELECT a.team, a.summe, a.zeit, a.richtige, b.name FROM ".$prefix."lotto_gewinner AS a JOIN ".$prefix."teams AS b ON a.team = b.ids ORDER BY zeit DESC LIMIT 0, 10";
$sql2 = mysql_query($sql1);
$letzteZeit = 0;
while ($sql3 = mysql_fetch_assoc($sql2)) {
	$aktuelleZeit = date('d.m.Y', $sql3['zeit']);
	if ($aktuelleZeit != $letzteZeit) { echo '<li>------- '.$aktuelleZeit.' -------</li>'; }
	echo '<li><a href="/team.php?id='.$sql3['team'].'">'.$sql3['name'].'</a> ('.$sql3['richtige'].' Richtige) &raquo; '.number_format($sql3['summe'], 0, ',', '.').' €</li>';
	$letzteZeit = $aktuelleZeit;
}
?>
</ul>
<h1>Höchste Gewinne</h1>
<ul>
<?php
$sql1 = "SELECT a.team, a.summe, a.richtige, b.name FROM ".$prefix."lotto_gewinner AS a JOIN ".$prefix."teams AS b ON a.team = b.ids ORDER BY summe DESC LIMIT 0, 10";
$sql2 = mysql_query($sql1);
while ($sql3 = mysql_fetch_assoc($sql2)) {
	echo '<li><a href="/team.php?id='.$sql3['team'].'">'.$sql3['name'].'</a> ('.$sql3['richtige'].' Richtige) &raquo; '.number_format($sql3['summe'], 0, ',', '.').' €</li>';
}
?>
</ul>
<?php } else { ?>
<p>Du musst angemeldet sein, um diese Seite aufrufen zu können!</p>
<?php } ?>
<?php include 'zz3.php'; ?>