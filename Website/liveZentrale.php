<?php include 'zz1.php'; ?>
<title>LIVE-Zentrale | Ballmanager.de</title>
<?php include 'zz2.php'; ?>
<?php if ($loggedin == 1) { ?>
<?php if ($live_scoring_spieltyp_laeuft == 'Liga' OR ($live_scoring_spieltyp_laeuft == 'Cup' && $cookie_spieltag < 15)) { ?>
<h1>Land wählen</h1>
<form action="" method="get" accept-charset="utf-8">
<p><select name="land" size="1" style="width:200px">
    <?php
	$sql1 = "SELECT land FROM ".$prefix."ligen WHERE ids = '".$cookie_liga."'";
	$sql2 = mysql_query($sql1);
	if (mysql_num_rows($sql2) == 0) { exit; }
	$sql3 = mysql_fetch_assoc($sql2);
	$meinLand = mysql_real_escape_string($sql3['land']);
    if (isset($_GET['land'])) {
    	$temp_land = mysql_real_escape_string(trim(strip_tags($_GET['land'])));
    }
    else {
    	$temp_land = $meinLand;
    }
    $shsj1 = "SELECT land FROM ".$prefix."ligen GROUP BY land ORDER BY land ASC";
    $shsj2 = mysql_query($shsj1);
    while ($shsj3 = mysql_fetch_assoc($shsj2)) {
        echo '<option value="'.$shsj3['land'].'"';
        if ($shsj3['land'] == $temp_land) { echo ' selected="selected"'; }
        echo '>'.$shsj3['land'].'</option>';
    }
    ?>
</select>
<input type="submit" value="Auswählen" /></p>
</form>
<?php } ?>
<?php
if ($live_scoring_spieltyp_laeuft == '') {
	echo '<h1>LIVE-Zentrale</h1>';
	echo '<p>Zurzeit läuft leider kein Spiel, von dem live berichtet werden kann.</p>';
	echo '<h1>Spielzeiten</h1><p>Cup: 10-12 Uhr</p><p>Liga: 14-16 Uhr</p><p>Pokal: 18-20 Uhr</p><p>Test: 22-24 Uhr</p>';
}
else {
	// FREUNDESLISTE LADEN ANFANG
	$kontaktListe1 = "SELECT a.f2, c.name FROM ".$prefix."freunde AS a JOIN ".$prefix."users AS b ON a.f2 = b.ids JOIN ".$prefix."teams AS c ON b.team = c.ids WHERE a.f1 = '".$cookie_id."' AND a.typ = 'F'";
	$kontaktListe2 = mysql_query($kontaktListe1);
	$kontaktListe = array();
	while ($kontaktListe3 = mysql_fetch_assoc($kontaktListe2)) {
		$kontaktListe[$kontaktListe3['name']] = 1;
	}
	// FREUNDESLISTE LADEN ENDE
	if (!isset($temp_land)) { $temp_land = ''; }
	echo '<h1>LIVE-Zentrale: '.$live_scoring_spieltyp_laeuft.' ('.$live_scoring_min_gespielt.'. Minute)</h1>';
	echo '<p style="text-align:right"><a href="'.$_SERVER['REQUEST_URI'].'" onclick="window.location.reload(); return false" class="pagenava">Aktualisieren</a></p>';
	function extract_kommentar_ergebnis($kommentar) {
			$ergebnis_str = '';
			$ergebnisPattern = '/ \[([0-9]+:[0-9]+)\]/i';
			$found_ergebnis = preg_match($ergebnisPattern, $kommentar, $ergebnis_array);
			if (isset($ergebnis_array[1])) {
				$ergebnis_str = ' '.$ergebnis_array[1];
			}
			$kommentar_str = preg_replace($ergebnisPattern, '', $kommentar);
			return array($kommentar_str, $ergebnis_str);
	}
	$heute_tag = date('d', time());
	$heute_monat = date('m', time());
	$heute_jahr = date('Y', time());
	$datum_min = mktime(00, 00, 01, $heute_monat, $heute_tag, $heute_jahr);
	if ($live_scoring_spieltyp_laeuft == 'Pokal' OR $live_scoring_spieltyp_laeuft == 'Cup') {
		$minMinute = intval($live_scoring_min_gespielt-35);
	}
	else {
		$minMinute = intval($live_scoring_min_gespielt-15);
	}
	if ($minMinute < 0) { $minMinute = 0; }
	$sql1 = "SELECT a.spiel, a.minute, a.kommentar, b.team1, b.team2, b.liga FROM ".$prefix."spiele_kommentare AS a JOIN ".$prefix."spiele AS b ON a.spiel = b.id ";
	if ($live_scoring_spieltyp_laeuft == 'Liga' OR ($live_scoring_spieltyp_laeuft == 'Cup' && $cookie_spieltag < 15)) { // nur bei Ligaspielen und Cup bis Viertelfinale
		$sql1 .= "WHERE b.land = '".$temp_land."' AND ";
	}
	else {
		$sql1 .= "WHERE ";
	}
	$sql1 .= "b.datum > ".$datum_min." AND b.simuliert = 1 AND b.typ = '".$live_scoring_spieltyp_laeuft."' AND a.minute < ".$live_scoring_min_gespielt." AND a.minute > ".$minMinute." ORDER BY b.liga ASC, a.spiel ASC";
	$sql2 = mysql_query($sql1);
	$resultTable = array();
	$letzterLigaWert = '';
	while ($sql3 = mysql_fetch_assoc($sql2)) {
		if ($sql3['liga'] != $letzterLigaWert && $letzterLigaWert != '') { $resultTable['CHANGE'.$sql3['liga']][] = 'FREIZEILE'; }
		$letzterLigaWert = $sql3['liga'];
		$liveErgebnis = extract_kommentar_ergebnis($sql3['kommentar']);
		$liveErgebnis = trim($liveErgebnis[1]);
		if ($liveErgebnis == '') {
			if (strpos($sql3['kommentar'], 'Der Schiedsrichter pfeift das Spiel ab') !== FALSE) {
				if (isset($resultTable[$sql3['spiel']])) {
					$resultTable[$sql3['spiel']][] = 'Beendet';
				}
				continue;
			}
			else {
				continue;
			}
		}
		$resultTable[$sql3['spiel']] = array($sql3['minute'], $sql3['team1'], $sql3['team2'], $liveErgebnis);
	}
	if (count($resultTable) > 0) {
		//ksort($resultTable); // nach Spielnummer sortieren
		echo '<p>';
		echo '<table>';
		echo '<thead>';
		echo '<tr>';
		echo '<th scope="col">&nbsp;</th>';
		echo '<th scope="col">Team 1</th>';
		echo '<th scope="col">Team 2</th>';
		echo '<th scope="col">LIVE</th>';
		echo '<th scope="col">&nbsp;</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		$counter = 0;
		foreach ($resultTable as $spielID=>$resultEntry) {
			$counter++;
			if ($resultEntry[0] == 'FREIZEILE') {
				echo '<tr><td colspan="4">&nbsp;</td></tr>';
				continue;
			}
			echo '<tr';
			if ($counter % 2 == 0) {
				echo ' class="odd"';
			}
			if ($resultEntry[1] == $cookie_teamname OR $resultEntry[2] == $cookie_teamname) { // eigenes Spiel
				echo ' style="font-weight:bold"';
			}
			echo '>';
			echo '<td>';
			if (isset($kontaktListe[$resultEntry[1]]) OR isset($kontaktListe[$resultEntry[2]])) { // Spiel eines Freundes
				echo '<img src="http://s3.amazonaws.com/ballmanager.de/images/protokoll/Spieler.png" alt="!" title="Spiel eines Freundes" />';
			}
			else {
				echo '&nbsp;';
			}
			echo '</td>';
			echo '<td>'.$resultEntry[1].'</td>';
			echo '<td>'.$resultEntry[2].'</td>';
			echo '<td style="color:';
			if (isset($resultEntry[4])) { echo '#000'; } else { echo 'red'; }
			echo '">'.$resultEntry[3].'</td>';
			echo '<td class="link"><a href="/spielbericht.php?id='.$spielID.'#lastAction">Spielbericht</a></td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
		echo '</p>';
	}
}
?>
<p><strong>Farben:</strong> Rot: LIVE, Schwarz: Beendet</p>
<p><strong>Hinweis:</strong> Spiele Deiner <a href="/freunde.php">Freunde</a> sind mit einem Symbol vor den Teamnamen gekennzeichnet. Deine eigenen Spiele sind fett markiert.</p>
<?php } else { ?>
<h1>LIVE-Zentrale</h1>
<p>Du musst angemeldet sein, um diese Seite aufrufen zu können!</p>
<?php } ?>
<?php include 'zz3.php'; ?>