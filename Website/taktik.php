<?php include 'zz1.php'; ?>
<title>Taktik | Ballmanager.de</title>
<style type="text/css">
<!--
select { width: 200px; }
-->
</style>
<?php include 'zz2.php'; ?>
<h1>Taktik</h1>
<?php
// SPIELTYP ANFANG
$spieltypTaktik = 'Liga';
$spieltypenMoeglich = array('Liga', 'Pokal', 'Cup', 'Test');
if (isset($_GET['spieltypTaktik'])) {
	if (in_array($_GET['spieltypTaktik'], $spieltypenMoeglich)) {
		$spieltypTaktik = $_GET['spieltypTaktik'];
	}
}
echo '<p style="text-align:right">';
foreach ($spieltypenMoeglich as $spieltypSingle) {
	echo '<a href="/taktik.php?spieltypTaktik='.$spieltypSingle.'" class="pagenava'; if ($spieltypTaktik == $spieltypSingle) { echo ' aktiv'; } echo '">'.$spieltypSingle.'</a>&nbsp;';
}
echo '</p>';
// SPIELTYP ENDE
?>
<?php if ($loggedin == 1) { ?>
<?php
$eigene_spiele1 = "SELECT COUNT(*) FROM ".$prefix."spiele WHERE typ = '".$live_scoring_spieltyp_laeuft."' AND (team1 = '".$cookie_teamname."' OR team2 = '".$cookie_teamname."') AND ABS(datum-".time().") < 7200";
$eigene_spiele2 = mysql_query($eigene_spiele1);
$eigene_spiele3 = mysql_result($eigene_spiele2, 0);
if ($live_scoring_spieltyp_laeuft == $spieltypTaktik && $eigene_spiele3 != 0) {
	echo '<p>Zurzeit läuft ein '.$live_scoring_spieltyp_laeuft.'spiel. Deshalb kannst Du diese Taktik gerade nicht ändern.</p>';
}
else { ?>
<?php
if (count($_POST) > 5 && $cookie_id != DEMO_USER_ID) {
	if (isset($_POST['ausrichtung'])) { $ausrichtung = intval(trim($_POST['ausrichtung'])); } else { $ausrichtung = 2; }
	if (isset($_POST['geschw_auf'])) { $geschw_auf = intval(trim($_POST['geschw_auf'])); } else { $geschw_auf = 2; }
	if (isset($_POST['pass_auf'])) { $pass_auf = intval(trim($_POST['pass_auf'])); } else { $pass_auf = 2; }
	if (isset($_POST['risk_pass'])) { $risk_pass = intval(trim($_POST['risk_pass'])); } else { $risk_pass = 2; }
	if (isset($_POST['druck'])) { $druck = intval(trim($_POST['druck'])); } else { $druck = 2; }
	if (isset($_POST['aggress'])) { $aggress = intval(trim($_POST['aggress'])); } else { $aggress = 2; }
	$up1 = "UPDATE ".$prefix."taktiken SET ausrichtung = ".$ausrichtung.", geschw_auf = ".$geschw_auf.", pass_auf = ".$pass_auf.", risk_pass = ".$risk_pass.", druck = ".$druck.", aggress = ".$aggress." WHERE team = '".$cookie_team."' AND spieltyp = '".$spieltypTaktik."'";
	$up2 = mysql_query($up1);
	if ($spieltypTaktik == 'Test') {
		setTaskDone('tactics_friendlies');
	}
}
?>
<?php
if (isset($_POST['vorlageLaden']) && $cookie_id != DEMO_USER_ID) {
	$vorlageLaden = mysql_real_escape_string(strip_tags(trim(base64_decode($_POST['vorlageLaden']))));
	$vorlageLaden1 = "SELECT ausrichtung, geschw_auf, pass_auf, risk_pass, druck, aggress FROM ".$prefix."taktiken_vorlagen WHERE team = '".$cookie_team."' AND name = '".$vorlageLaden."'";
	$vorlageLaden2 = mysql_query($vorlageLaden1);
	$vL3 = mysql_fetch_assoc($vorlageLaden2);
	$vorlageLaden4 = "UPDATE ".$prefix."taktiken SET ausrichtung = ".$vL3['ausrichtung'].", geschw_auf = ".$vL3['geschw_auf'].", pass_auf = ".$vL3['pass_auf'].", risk_pass = ".$vL3['risk_pass'].", druck = ".$vL3['druck'].", aggress = ".$vL3['aggress']." WHERE team = '".$cookie_team."' AND spieltyp = '".$spieltypTaktik."'";
	$vorlageLaden5 = mysql_query($vorlageLaden4);
	if ($vorlageLaden5 == FALSE) {
		echo addInfoBox('Die Vorlage konnte nicht geladen werden. Bitte versuche es noch einmal.');
	}
	else {
		echo addInfoBox('Die Vorlage &quot;'.$vorlageLaden.'&quot; wurde geladen und als neue Taktik übernommen.');
	}
}
if (isset($_POST['vorlageLoeschen']) && $cookie_id != DEMO_USER_ID) {
	$vorlageLoeschen = mysql_real_escape_string(strip_tags(trim(base64_decode($_POST['vorlageLoeschen']))));
	$vorlageLoeschen1 = "DELETE FROM ".$prefix."taktiken_vorlagen WHERE team = '".$cookie_team."' AND name = '".$vorlageLoeschen."'";
	$vorlageLoeschen2 = mysql_query($vorlageLoeschen1);
	if (mysql_affected_rows() == 0) {
		echo addInfoBox('Die Vorlage konnte nicht gelöscht werden. Bitte versuche es noch einmal.');
	}
	else {
		echo addInfoBox('Die Vorlage &quot;'.$vorlageLoeschen.'&quot; wurde gelöscht.');
	}
}
$tue1 = "SELECT ausrichtung, geschw_auf, pass_auf, risk_pass, druck, aggress FROM ".$prefix."taktiken WHERE team = '".$cookie_team."' AND spieltyp = '".$spieltypTaktik."'";
$tue2 = mysql_query($tue1);
$tue3 = mysql_fetch_assoc($tue2);
if (isset($_POST['vorlageSpeichern']) && $cookie_id != DEMO_USER_ID) {
	$vorlageSpeichern = mysql_real_escape_string(trim(strip_tags($_POST['vorlageSpeichern'])));
	$vorlageSpeichern1 = "INSERT INTO ".$prefix."taktiken_vorlagen (team, name, zeit, ausrichtung, geschw_auf, pass_auf, risk_pass, druck, aggress) VALUES ('".$cookie_team."', '".$vorlageSpeichern."', ".time().", ".$tue3['ausrichtung'].", ".$tue3['geschw_auf'].", ".$tue3['pass_auf'].", ".$tue3['risk_pass'].", ".$tue3['druck'].", ".$tue3['aggress'].")";
	$vorlageSpeichern2 = mysql_query($vorlageSpeichern1);
	if ($vorlageSpeichern2 == FALSE) {
		echo addInfoBox('Deine Taktik konnte nicht als Vorlage gespeichert werden. Bitte versuche es noch einmal. Du darfst jeden Namen nur ein Mal benutzen.');
	}
	else {
		echo addInfoBox('Deine Taktik wurde als Vorlage mit dem Namen &quot;'.$vorlageSpeichern.'&quot; gespeichert.');
	}
}
?>
<form action="/taktik.php?spieltypTaktik=<?php echo $spieltypTaktik; ?>" method="POST" accept-charset="utf-8">
<p>
<label for="ausrichtung">Ausrichtung:</label>
<select name="ausrichtung" size="1">
	<option value="1"<?php if ($tue3['ausrichtung'] == '1') { echo ' selected="selected"'; } ?>>Defensiv</option>
	<option value="2"<?php if ($tue3['ausrichtung'] == '2') { echo ' selected="selected"'; } ?>>Neutral</option>
	<option value="3"<?php if ($tue3['ausrichtung'] == '3') { echo ' selected="selected"'; } ?>>Offensiv</option>
</select>
</p>
<p>
<label for="geschw_auf">Geschwindigkeit des Spielaufbaus:</label>
<select name="geschw_auf" size="1">
	<option value="1"<?php if ($tue3['geschw_auf'] == 1) { echo ' selected="selected"'; } ?>>Langsam</option>
	<option value="2"<?php if ($tue3['geschw_auf'] == 2) { echo ' selected="selected"'; } ?>>Gemäßigt</option>
	<option value="3"<?php if ($tue3['geschw_auf'] == 3) { echo ' selected="selected"'; } ?>>Schnell</option>
</select>
</p>
<p>
<label for="pass_auf">Passdistanz:</label>
<select name="pass_auf" size="1">
	<option value="1"<?php if ($tue3['pass_auf'] == 1) { echo ' selected="selected"'; } ?>>Kurz</option>
	<option value="2"<?php if ($tue3['pass_auf'] == 2) { echo ' selected="selected"'; } ?>>Gemischt</option>
	<option value="3"<?php if ($tue3['pass_auf'] == 3) { echo ' selected="selected"'; } ?>>Lang</option>
</select>
</p>
<p>
<label for="risk_pass">Chancen-Erarbeitung:</label>
<select name="risk_pass" size="1">
	<option value="1"<?php if ($tue3['risk_pass'] == 1) { echo ' selected="selected"'; } ?>>Sicher</option>
	<option value="2"<?php if ($tue3['risk_pass'] == 2) { echo ' selected="selected"'; } ?>>Ausgeglichen</option>
	<option value="3"<?php if ($tue3['risk_pass'] == 3) { echo ' selected="selected"'; } ?>>Riskant</option>
</select>
</p>
<p>
<label for="druck">Druck:</label>
<select name="druck" size="1">
	<option value="1"<?php if ($tue3['druck'] == 1) { echo ' selected="selected"'; } ?>>Niedrig</option>
	<option value="2"<?php if ($tue3['druck'] == 2) { echo ' selected="selected"'; } ?>>Normal</option>
	<option value="3"<?php if ($tue3['druck'] == 3) { echo ' selected="selected"'; } ?>>Hoch</option>
</select>
</p>
<p>
<label for="aggress">Aggressivität:</label>
<select name="aggress" size="1">
	<option value="1"<?php if ($tue3['aggress'] == 1) { echo ' selected="selected"'; } ?>>Niedrig</option>
	<option value="2"<?php if ($tue3['aggress'] == 2) { echo ' selected="selected"'; } ?>>Normal</option>
	<option value="3"<?php if ($tue3['aggress'] == 3) { echo ' selected="selected"'; } ?>>Hoch</option>
</select>
</p>
<p><input type="submit" value="Speichern"<?php echo noDemoClick($cookie_id); ?> /></p>
</form>
<h1>Vorlagen nutzen</h1>
<?php
$tmp1 = "SELECT name FROM ".$prefix."taktiken_vorlagen WHERE team = '".$cookie_team."' ORDER BY name ASC";
$tmp2 = mysql_query($tmp1);
$vorhandeneVorlagen = array();
while ($tmp3 = mysql_fetch_assoc($tmp2)) {
	$vorhandeneVorlagen[] = $tmp3['name'];
}
?>
<p>Hier kannst Du die aktuell ausgewählte Taktik als Vorlage speichern. Du kannst auch eine Vorlage laden, die dann die aktuelle Taktik ersetzt.</p>
<form action="/taktik.php?spieltypTaktik=<?php echo $spieltypTaktik; ?>" method="POST" accept-charset="utf-8">
<p><select name="vorlageLaden" size="1" style="width:200px">
	<?php
	foreach ($vorhandeneVorlagen as $vorhandeneVorlage) {
		echo '<option value="'.base64_encode($vorhandeneVorlage).'">'.$vorhandeneVorlage.'</option>';
	}
	?>
</select> <input type="submit" value="Laden" onclick="return<?php echo noDemoClick($cookie_id, TRUE); ?> confirm('Bist Du sicher?')" /></p>
</form>
<form action="/taktik.php?spieltypTaktik=<?php echo $spieltypTaktik; ?>" method="POST" accept-charset="utf-8">
<p><input type="text" name="vorlageSpeichern" value="Name für neue Vorlage ..." style="width:200px" onfocus="if(this.value == 'Name für neue Vorlage ...') this.value = ''" /> <input type="submit" value="Speichern" onclick="return<?php echo noDemoClick($cookie_id, TRUE); ?> confirm('Bist Du sicher?')" /></p>
</form>
<form action="/taktik.php?spieltypTaktik=<?php echo $spieltypTaktik; ?>" method="POST" accept-charset="utf-8">
<p><select name="vorlageLoeschen" size="1" style="width:200px">
	<?php
	foreach ($vorhandeneVorlagen as $vorhandeneVorlage) {
		echo '<option value="'.base64_encode($vorhandeneVorlage).'">'.$vorhandeneVorlage.'</option>';
	}
	?>
</select> <input type="submit" value="Löschen" onclick="return<?php echo noDemoClick($cookie_id, TRUE); ?> confirm('Bist Du sicher?')" /></p>
</form>
<h1>Erklärungen zu den einzelnen Einstellungen</h1>
<p>Wähle oben einfach aus jeder der sechs Auswahllisten deine bevorzugte Einstellung aus. Du solltest die Taktiken - je nach Gegner - anpassen, um das Beste aus deiner Mannschaft rauszuholen. Die Taktik, die gespeichert ist, während ein Spiel simuliert wird, wird für dieses Spiel auch angewendet. Du hast für jeden Teil drei Auswahlmöglichkeiten. Im Folgenden werden davon die äußeren Möglichkeiten erklärt, die mittleren stellen immer nur einen Mittelweg dar.</p>
<p><strong>Ausrichtung:</strong> Die Einstellung "Offensiv" wird den Angriff Deines Teams stärken. Dein Team wird aber nicht mehr so viel Wert auf die Verteidigung legen, weshalb diese schwächer wird. Mit "Defensiv" bewirkst Du genau das Gegenteil: Die Verteidigung wird stärker, der Angriff schwächer.</p>
<p><strong>Geschwindigkeit des Spielaufbaus:</strong> Mit der Einstellung "Schnell" werden mehr Angriffe Deines Teams erfolgreich verlaufen und durch die Abwehr kommen, dafür steigt aber auch das Risiko, vom Gegner ausgekontert zu werden. Wenn Dein Team "Langsam" spielt, wird es schwerer, Torchancen herauszuspielen. Der Vorteil ist jedoch, dass Dein Gegner Dich nicht so leicht auskontern kann.</p>
<p><strong>Passdistanz:</strong> "Kurz" als bevorzugte Passdistanz hat zur Folge, dass mehr Angriffe durch die gegnerische Abwehr kommen und zu Torchancen werden. Allerdings sind die Torchancen dann nicht so gut. Die Auswahl "Lang" bewirkt, dass weniger Torchancen enstehen, aber wenn eine Chance entsteht, dann ist sie auch sehr gut. Ein weiterer Nachteil: Du wirst häufiger im Abseits stehen.</p>
<p><strong>Chancen-Erarbeitung:</strong> Wenn du "Riskant" spielst, wirst Du nicht mehr Torschuss-Möglichkeiten bekommen als sonst, aber die Chancen werden besser. Dafür steigt aber auch die Kontergefahr stark an. Bei der Spielweise "Sicher" wirst Du wenige gute Chancen bekommen. Allerdings ist dann auch die Kontergefahr sehr niedrig.</p>
<p><strong>Druck:</strong> Wenn Deine Mannschaft "Viel" Druck macht, wirst Du Dir viele Konterchancen erarbeiten können. Leider brauchen Deine Spieler auch viel Kondition, um diese Spielweise durchhalten zu können. Die Frische Deiner Spieler wird so also stärker sinken. Falls der Druck, den Dein Team erzeugt, "Niedrig" ist, wird das Spiel nicht so anstrengend. Dein Gegner wird aber auch weniger Fehler machen, sodass Du weniger Konterchancen erhältst.</p>
<p><strong>Aggressivität:</strong> Bei der Auswahl "Hoch" wirst Du viele gegnerische Angriffe durch Fouls unterbrechen, wodurch auch gute Chancen des Gegners zunichte gemacht werden. Allerdings erhält der Gegner so auch mehr Freistoß-Möglichkeiten, aus denen Tore enstehen können. Falls die Aggressivität Deines Teams "Niedrig" ist, werden Deine Spieler weniger Fouls begehen (fairer spielen), gute Chancen des Gegners werden sie aber auch seltener mit unfairen Mitteln stoppen.</p>
<?php } ?>
<?php } else { ?>
<p>Du musst angemeldet sein, um diese Seite aufrufen zu können!</p>
<?php } ?>
<?php include 'zz3.php'; ?>