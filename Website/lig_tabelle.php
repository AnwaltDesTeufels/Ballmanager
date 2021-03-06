<?php include 'zz1.php'; ?>
<title>Tabelle | Liga | Ballmanager.de</title>
<style type="text/css">
<!--
.team_<?php echo md5($cookie_teamname); ?> {
	font-weight: bold;
}
.tabelle_meister td, .tabelle_meister a {
	background: #79ca39;
	color: #000;
}
.tabelle_pokal_sicher td, .tabelle_pokal_sicher a {
	background: #79df39;
	color: #000;
}
.tabelle_abstieg td, .tabelle_abstieg a {
	background: #ff6a00;
	color: #fff;
}
.tabelle_aufstieg td, .tabelle_aufstieg a {
	background: #79ca39;
	color: #000;
}
-->
</style>
<?php
if (isset($_GET['liga'])) {
    $temp_liga = mysql_real_escape_string(trim(strip_tags($_GET['liga'])));
}
elseif (isset($liga)) {
    $temp_liga = $liga;
}
elseif ($cookie_liga != '') {
    $temp_liga = $cookie_liga;
}
else {
	$temp_liga = '9bf31c7ff062936a96d3c8bd1f8f2ff3';
}
// ERGEBNISSE FUER TAG DAVOR ODER DANACH ANFANG
$slideResults = $cookie_spieltag;
$slide = 0;
if (isset($_GET['slide'])) {
	$slide = intval($_GET['slide']);
	$slideResults = $cookie_spieltag+$slide;
	if ($slideResults < 1 OR $slideResults > 22) {
		$slideResults = $cookie_spieltag;
		$slide = 0;
	}
}
// ERGEBNISSE FUER TAG DAVOR ODER DANACH ENDE
?>
<?php include 'zz2.php'; ?>
<?php if ($loggedin == 0) { ?>
<h1>Liga</h1><p>Du musst angemeldet sein, um diese Seite aufrufen zu können!</p><?php include 'zz3.php'; exit; ?>
<?php } else { ?>
<?php
setTaskDone('league_standings');
if (isset($_POST['nachricht']) && isset($_POST['liga']) && $cookie_id != DEMO_USER_ID) {
	// CHAT-SPERREN ANFANG
	$sql1 = "SELECT MAX(chatSperre) FROM ".$prefix."helferLog WHERE managerBestrafen = '".$cookie_id."'";
	$sql2 = mysql_query($sql1);
	if (mysql_num_rows($sql2) > 0) {
		$sql3 = mysql_fetch_assoc($sql2);
		$chatSperreBis = $sql3['MAX(chatSperre)'];
		if ($chatSperreBis > 0 && $chatSperreBis > time()) {
			echo addInfoBox('Du bist noch bis zum '.date('d.m.Y H:i', $chatSperreBis).' Uhr für die Kommunikation im Spiel gesperrt. Wenn Dir unklar ist warum, frage bitte das <a class="inText" href="/wio.php">Ballmanager-Team.</a>');
			include 'zz3.php';
			exit;
		}
	}
	// CHAT-SPERREN ENDE
	$nachricht = mysql_real_escape_string(trim(strip_tags($_POST['nachricht'])));
	$liga = mysql_real_escape_string(trim(strip_tags($_POST['liga'])));
	$sql1 = "INSERT INTO ".$prefix."chats (user, zeit, nachricht, liga) VALUES ('".$cookie_id."', '".time()."', '".$nachricht."', '".$liga."')";
	$sql2 = mysql_query($sql1);
}
?>
<h1>Liga auswählen</h1>
<form action="" method="get" accept-charset="utf-8">
<p><select name="liga" size="1" style="width:200px">
    <?php
    $shsj1 = "SELECT ids, name FROM ".$prefix."ligen ORDER BY name ASC";
    $shsj2 = mysql_query($shsj1);
    while ($shsj3 = mysql_fetch_assoc($shsj2)) {
        echo '<option value="'.$shsj3['ids'].'"';
        if ($shsj3['ids'] == $temp_liga) { echo ' selected="selected"'; }
        echo '>'.$shsj3['name'].'</option>';
    }
    ?>
</select>
<input type="submit" value="Auswählen" /></p>
</form>
<?php
$temp = FALSE;
$tmp_dateiname = 'cache/tabelle'.$temp_liga.'.html';
if (file_exists($tmp_dateiname)) {
	if (filemtime($tmp_dateiname) > getTimestamp('-1 hour') OR (date('H', time()) < 16 && date('H', time()) > 13)) {
		$tmp_liga_cache = file_get_contents($tmp_dateiname);
		if (strlen($tmp_liga_cache) > 0) {
			$ersatz_temp = '<strong>'.$cookie_teamname.'</strong>';
			echo str_replace($cookie_teamname, $ersatz_temp, $tmp_liga_cache);
            $temp = TRUE;
		}
	}
}
if ($temp == FALSE) {
?>
<?php
if ($cookie_spieltag > 11) { // Hinrunden-Tabelle der aktuellen Saison
	$linkText = 'Hinrunde';
	$linkURL = '/stat_geschichte.php?saison_spieltag='.$cookie_saison.'-11&liga='.$temp_liga;
}
else { // End-Tabelle der letzten Saison
	$linkText = 'Letzte Saison';
	$linkURL = '/stat_geschichte.php?saison_spieltag='.intval($cookie_saison-1).'-22&liga='.$temp_liga;
}
$tmp_liga_cache = '';
$tmp_liga_cache .= '
<h1>Tabelle</h1>
<p style="text-align:right"><a href="/stat_torjaegerliste.php?liga='.$temp_liga.'" class="pagenava">Torjäger</a> <a href="'.$linkURL.'" class="pagenava">'.$linkText.'</a></p>
<table>
<thead>
<tr class="odd">
<th scope="col">&nbsp;</th>
<th scope="col">Team</th>
<th scope="col">SP</th>';
if (!isMobile()) {
	$tmp_liga_cache .= '<th scope="col">S-U-N</th>';
}
$tmp_liga_cache .= '<th scope="col">TO</th>';
if (!isMobile()) {
	$tmp_liga_cache .= '<th scope="col">DI</th>';
}
$tmp_liga_cache .= '<th scope="col">PT</th>
</tr>
</thead>
<tbody>';
$liga1 = "SELECT ids, name, gespielt, pkt_gesamt FROM ".$prefix."ligen WHERE ids = '".$temp_liga."'";
$liga2 = mysql_query($liga1);
$liga3 = mysql_fetch_assoc($liga2);
// 2 ODER 3 POKALPLAETZE ANFANG
$jahresWertung1 = "SELECT COUNT(*) FROM ".$prefix."ligen WHERE hoch = 'KEINE' AND pkt_gesamt >= ".$liga3['pkt_gesamt'];
$jahresWertung2 = mysql_query($jahresWertung1);
$jahresWertung3 = mysql_result($jahresWertung2, 0);
// 2 ODER 3 POKALPLAETZE ENDE
$sql1 = "SELECT ids, name, tore, gegentore, punkte, aufstellung, vorjahr_liga, vorjahr_platz, pokalrunde, sunS, sunU, sunN FROM ".$prefix."teams WHERE liga = '".$temp_liga."' ORDER BY rank ASC";
$sql2 = mysql_query($sql1);
if (mysql_num_rows($sql2) == 0) { exit; }
$counter = 1;
$vorgaenger_punkte_tore = '';
while ($sql3 = mysql_fetch_assoc($sql2)) {
	$aktuell_punkte_tore = $sql3['tore'].':'.$sql3['gegentore'].'-'.$sql3['punkte'];
	$differenz = $sql3['tore']-$sql3['gegentore'];
	$tmp_liga_cache .= '<tr';
	if (strpos($liga3['name'], '1') !== FALSE) {
        if ($counter == 1) { $tmp_liga_cache .= ' class="tabelle_meister team_'.md5($sql3['name']).'"'; }
        elseif ($counter > 1 && $counter < 3) { $tmp_liga_cache .= ' class="tabelle_pokal_sicher team_'.md5($sql3['name']).'"'; }
        elseif ($counter == 3 && $jahresWertung3 <= 6) { $tmp_liga_cache .= ' class="tabelle_pokal_sicher team_'.md5($sql3['name']).'"'; }
        elseif ($counter > 9) { $tmp_liga_cache .= ' class="tabelle_abstieg team_'.md5($sql3['name']).'"'; }
        elseif ($counter % 2 == 0) { $tmp_liga_cache .= ' class="odd team_'.md5($sql3['name']).'"'; }
		else { $tmp_liga_cache .= ' class="team_'.md5($sql3['name']).'"'; }
	}
	elseif (strpos($liga3['name'], '2') !== FALSE OR strpos($liga3['name'], '3') !== FALSE) {
        if ($counter >= 1 && $counter <= 3) { $tmp_liga_cache .= ' class="tabelle_aufstieg team_'.md5($sql3['name']).'"'; }
		elseif ($counter >= 10 && $counter <= 12) { $tmp_liga_cache .= ' class="tabelle_abstieg team_'.md5($sql3['name']).'"'; }
        elseif ($counter % 2 == 0) { $tmp_liga_cache .= ' class="odd team_'.md5($sql3['name']).'"'; }
		else { $tmp_liga_cache .= ' class="team_'.md5($sql3['name']).'"'; }
	}
	else {
		if ($counter >= 1 && $counter <= 3) { $tmp_liga_cache .= ' class="tabelle_aufstieg team_'.md5($sql3['name']).'"'; }
		elseif ($counter % 2 == 0) { $tmp_liga_cache .= ' class="odd team_'.md5($sql3['name']).'"'; }
		else { $tmp_liga_cache .= ' class="team_'.md5($sql3['name']).'"'; }
	}
	$tmp_liga_cache .= '><td>';
	if ($aktuell_punkte_tore == $vorgaenger_punkte_tore) {
		$tmp_liga_cache .= '&nbsp;';
	}
	else {
		$tmp_liga_cache .= $counter;
	}
	$tmp_liga_cache .= '</td><td class="link"><a href="/team.php?id='.$sql3['ids'].'">'.$sql3['name'].' ('.number_format($sql3['aufstellung'], 1, ',', '.').')';
	if ($sql3['vorjahr_liga'] == $liga3['ids']) {
		if ($sql3['vorjahr_platz'] == 1) { $tmp_liga_cache .= ' [M]'; }
        if ($sql3['pokalrunde'] > 0) { $tmp_liga_cache .= ' [P]'; }
	}
	else {
		if ($sql3['vorjahr_platz'] < 4) { $tmp_liga_cache .= ' [AU]'; }
		if ($sql3['vorjahr_platz'] > 9) { $tmp_liga_cache .= ' [AB]'; }
	}
	$tmp_liga_cache .= '</a></td><td>'.$liga3['gespielt'].'</td>';
	if (!isMobile()) {
		$tmp_liga_cache .= '<td>'.$sql3['sunS'].'-'.$sql3['sunU'].'-'.$sql3['sunN'].'</td>';
	}
	$tmp_liga_cache .= '<td>'.$sql3['tore'].':'.$sql3['gegentore'].'</td>';
	if (!isMobile()) {
		$tmp_liga_cache .= '<td>'.$differenz.'</td>';
	}
	$tmp_liga_cache .= '<td>'.$sql3['punkte'].'</td>';
	$tmp_liga_cache .= '</tr>';
	$counter++;
	$vorgaenger_punkte_tore = $aktuell_punkte_tore;
}
$tmp_liga_cache .= '
</tbody>
</table>
<p><strong>Erklärung:</strong> ';
if (substr($liga3['name'], -1) == 1) {
	$tmp_liga_cache .= 'Platz 1: Meister, Platz 1-';
	if ($jahresWertung3 <= 6) { $tmp_liga_cache .= '3'; } else { $tmp_liga_cache .= '2'; }
	$tmp_liga_cache .= ': Pokalplätze, Platz 10-12: Abstiegsplätze';
	$tmp_liga_cache .= '</p><p><strong>In eckigen Klammern:</strong> M=Meister, AU=Aufsteiger, P=Pokalteilnehmer</p>';
}
elseif (substr($liga3['name'], -1) == 2) {
	$tmp_liga_cache .= 'Platz 1-3: Aufstiegsplätze, Platz 10-12: Abstiegsplätze';
	$tmp_liga_cache .= '</p><p><strong>In eckigen Klammern:</strong> AU=Aufsteiger, AB=Absteiger</p>';
}
else {
	$tmp_liga_cache .= 'Platz 1-3: Aufstiegsplätze';
	$tmp_liga_cache .= '</p><p><strong>In eckigen Klammern:</strong> AB=Absteiger</p>';
}
$tmp_liga_cache .= '<p><strong>Überschriften:</strong> SP: Spiele, S-U-N: Siege/Unentschieden/Niederlagen, TO: Tore, DI: Differenz, PT: Punkte</p>';
$datei = fopen($tmp_dateiname, 'w+');
fwrite($datei, $tmp_liga_cache);
fclose($datei);
echo $tmp_liga_cache;
}
?>
<h1>Ergebnisse (<?php echo $slideResults; ?>. Spieltag)</h1>
<form action="/lig_tabelle.php" method="get" accept-charset="utf-8">
<p style="text-align:right">
	<input type="hidden" name="liga" value="<?php echo $temp_liga; ?>" /><select name="slide" size="1" style="width:120px">
		<?php
		for ($i = 1; $i <= 22; $i++) {
			$slideSteps = intval($i-$cookie_spieltag);
			echo '<option value="'.$slideSteps.'"';
			if ($i == $slideResults) { echo ' selected="selected"'; }
			echo '>Spieltag '.$i.'</option>';
		}
		?>
	</select> <input type="submit" value="Anzeigen" />
</p>
</form>
<table>
<thead>
<tr class="odd">
<th scope="col">Datum</th>
<th scope="col">Heim</th>
<th scope="col">Auswärts</th>
<th scope="col">Ergebnis</th>
</tr>
</thead>
<tbody>
<?php
// BIS 6 UHR MORGENS DIE ERGEBISSE VON GESTERN ZEIGEN ANFANG
$anzuzeigender_tag = getTimestamp('+'.$slide.' days');
$heute_tag = date('d', $anzuzeigender_tag);
$heute_monat = date('m', $anzuzeigender_tag);
$heute_jahr = date('Y', $anzuzeigender_tag);
// BIS 6 UHR MORGENS DIE ERGEBISSE VON GESTERN ZEIGEN ENDE
$datum_min = mktime(00, 00, 01, $heute_monat, $heute_tag, $heute_jahr);
$datum_max = mktime(23, 59, 59, $heute_monat, $heute_tag, $heute_jahr);
$erg1 = "SELECT id, datum, team1, team2, ergebnis, typ FROM ".$prefix."spiele WHERE liga = '".$temp_liga."' AND datum > ".$datum_min." AND datum < ".$datum_max;
$erg2 = mysql_query($erg1);
$counter = 0;
while ($erg3 = mysql_fetch_assoc($erg2)) {
	if ($counter % 2 == 0) { echo '<tr class="team_'.md5($erg3['team1']).' team_'.md5($erg3['team2']).'">'; } else { echo '<tr class="odd team_'.md5($erg3['team1']).' team_'.md5($erg3['team2']).'">'; }
    // LIVE ODER ERGEBNIS ANFANG
    if ($erg3['typ'] == $live_scoring_spieltyp_laeuft && date('d', time()) == date('d', $erg3['datum'])) {
        $ergebnis_live = 'LIVE';
    }
    else {
        $ergebnis_live = $erg3['ergebnis'];
    }
    // LIVE ODER ERGEBNIS ENDE
	echo '<td>'.date('d.m.Y', $erg3['datum']).'</td><td>'.$erg3['team1'].'</td><td>'.$erg3['team2'].'</td><td class="link"><a href="/spielbericht.php?id='.$erg3['id'].'">'.$ergebnis_live.'</a></td>';
	echo '</tr>';
	$counter++;
}
echo '</tbody>';
echo '</table>';
?>
<h1>Deine Nachricht</h1>
<form action="/lig_tabelle.php" method="post" accept-charset="utf-8">
<p><input type="text" name="nachricht" style="width:80%" /> <input type="hidden" name="liga" value="<?php echo $temp_liga; ?>" /><input type="submit" value="Eintragen"<?php echo noDemoClick($cookie_id); ?> /></p>
</form>
<h1>Manager-Talk</h1>
<?php
if (isset($_GET['delEntry']) && $cookie_id != DEMO_USER_ID) {
	$delEntry = mysql_real_escape_string(trim(strip_tags($_GET['delEntry'])));
	$addSql = " AND user = '".$cookie_id."'";
	if ($_SESSION['status'] == 'Helfer' OR $_SESSION['status'] == 'Admin') { $addSql = ""; }
	$gb_in1 = "DELETE FROM ".$prefix."chats WHERE id = ".$delEntry.$addSql;
	$gb_in2 = mysql_query($gb_in1);
}
$sql1 = "SELECT ".$prefix."chats.id, ".$prefix."chats.user, ".$prefix."chats.zeit, ".$prefix."chats.nachricht, ".$prefix."users.username FROM ".$prefix."chats JOIN ".$prefix."users ON ".$prefix."chats.user = ".$prefix."users.ids WHERE ".$prefix."chats.liga = '".$temp_liga."' ORDER BY ".$prefix."chats.zeit DESC LIMIT 0, 20";
$sql2 = mysql_query($sql1);
while ($sql3 = mysql_fetch_assoc($sql2)) {
	echo '<p><b>'.displayUsername($sql3['username'], $sql3['user']).' schrieb am '.date('d.m.Y, H:i', $sql3['zeit']).':';
	if ($sql3['user'] == $cookie_id OR $_SESSION['status'] == 'Helfer' OR $_SESSION['status'] == 'Admin') {
		echo ' <a href="/lig_tabelle.php?liga='.mysql_real_escape_string($temp_liga).'&amp;delEntry='.$sql3['id'].'">[Löschen]</a>';
	}
	echo '</b><br />'.$sql3['nachricht'].'</p>';
}
?>
<?php } ?>
<?php include 'zz3.php'; ?>