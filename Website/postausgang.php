<?php include 'zz1.php'; ?>
<title>Postausgang | Ballmanager.de</title>
<script type="text/javascript">
function checkAll(quelle) {
	for (i = 0; i < document.forms[0].length; i++) {
		if (document.forms[0][i].type === 'checkbox') {
			if (document.forms[0][i].name !== quelle.name) {
				document.forms[0][i].checked = !document.forms[0][i].checked;
			}
		}
	}
}
</script>
<?php include 'zz2.php'; ?>
<h1>Postausgang</h1>
<?php if ($loggedin == 0) { echo '<p>Du musst angemeldet sein, um diese Seite aufrufen zu können!</p>'; } else { ?>
<?php
if (isset($_POST['auswahl']) && $cookie_id != DEMO_USER_ID) {
	if (is_array($_POST['auswahl'])) {
		foreach ($_POST['auswahl'] as $markedEntry) {
			$sql1 = "UPDATE ".$prefix."pn SET geloescht_von = 1 WHERE von = '".$cookie_id."' AND ids = '".mysql_real_escape_string(trim(strip_tags($markedEntry)))."'";
			mysql_query($sql1);
			$del1 = "DELETE FROM ".$prefix."pn WHERE geloescht_von = 1 AND geloescht_an = 1";
			mysql_query($del1);
		}
		echo addInfoBox('Es wurden '.count($_POST['auswahl']).' Nachrichten aus Deinem Postausgang gelöscht.');
	}
}
?>
<p style="text-align:right;"><a class="pagenava" href="/posteingang.php">Posteingang</a> <a class="pagenava aktiv" href="/postausgang.php">Postausgang</a></p>
<form action="/postausgang.php" name="checkBoxForm" method="post" accept-charset="utf-8">
<p>
<table>
<thead>
<tr class="odd">
<th scope="col"><input type="checkbox" name="checkUncheckAll" onclick="checkAll(this);" /></th>
<th scope="col">Betreff</th>
<th scope="col">Empfänger</th>
<th scope="col">Datum</th>
</tr>
</thead>
<tbody>
<?php
// IGNORIER-LISTE ANFANG
$igno1 = "SELECT f2 FROM ".$prefix."freunde WHERE f1 = '".$cookie_id."' AND typ = 'B'";
$igno2 = mysql_query($igno1);
$ignoList = array();
while ($igno3 = mysql_fetch_assoc($igno2)) {
	$ignoList[] = $igno3['f2'];
}
// IGNORIER-LISTE ENDE
$sql1 = "SELECT a.ids, b.ids AS userID, von, an, titel, zeit, gelesen, username FROM ".$prefix."pn AS a JOIN ".$prefix."users AS b ON a.an = b.ids WHERE von = '".$cookie_id."' AND geloescht_von = 0 ORDER BY zeit DESC LIMIT ".$start.", ".$eintraege_pro_seite;
$sql2 = mysql_query($sql1);
$blaetter3 = anzahl_datensaetze_gesamt($sql1);
while ($sql3 = mysql_fetch_assoc($sql2)) {
	if (in_array($sql3['userID'], $ignoList)) { continue; }
	if ($sql3['gelesen'] == 0) {
		echo '<tr style="font-weight:bold"';
	}
	else {
		echo '<tr';
	}
	echo '>';
	echo '<td><input type="checkbox" name="auswahl[]" value="'.$sql3['ids'].'" /></td>';
	echo '<td class="link"><a href="/post.php?id='.$sql3['ids'].'">'.$sql3['titel'].'</a></td>';
	echo (isset($sql3['username']) ? '<td class="link">'.displayUsername($sql3['username'], $sql3['an']) : '<td>Gelöschter User').'</td>';
	echo '<td>'.date('d.m.y H:i', $sql3['zeit']).'</td>';
	echo '</tr>';
}
?>
</tbody>
</table>
</p>
<p><select name="markedAction" size="1" style="width:200px">
	<option value="DEL">Markierte löschen</option>
</select></p>
<p><input type="submit" value="Ausführen" onclick="return<?php echo noDemoClick($cookie_id, TRUE); ?> confirm('Bist Du sicher?');" /></p>
</form>
<?php
echo '<div class="pagebar">';
$wieviel_seiten = $blaetter3/$eintraege_pro_seite; // ERMITTELN DER SEITENANZAHL FÜR DAS INHALTSVERZEICHNIS
$vorherige = $seite-1;
if ($wieviel_seiten > 0) { echo '<a href="'.$_SERVER['SCRIPT_NAME'].'?seite=1">Erste</a> '; } else { echo '<span class="this-page">Erste</span>'; }
if ($seite > 1) { echo '<a href="'.$_SERVER['SCRIPT_NAME'].'?seite='.$vorherige.'">Vorherige</a> '; } else { echo '<span class="this-page">Vorherige</span> '; }
$naechste = $seite+1;
$vor4 = $seite-4; if ($vor4 > 0) { echo '<a href="'.$_SERVER['SCRIPT_NAME'].'?seite='.$vor4.'">'.$vor4.'</a> '; }
$vor3 = $seite-3; if ($vor3 > 0) { echo '<a href="'.$_SERVER['SCRIPT_NAME'].'?seite='.$vor3.'">'.$vor3.'</a> '; }
$vor2 = $seite-2; if ($vor2 > 0) { echo '<a href="'.$_SERVER['SCRIPT_NAME'].'?seite='.$vor2.'">'.$vor2.'</a> '; }
$vor1 = $seite-1; if ($vor1 > 0) { echo '<a href="'.$_SERVER['SCRIPT_NAME'].'?seite='.$vor1.'">'.$vor1.'</a> '; }
echo '<span class="this-page">'.$seite.'</span> ';
$nach1 = $seite+1; if ($nach1 < $wieviel_seiten+1) { echo '<a href="'.$_SERVER['SCRIPT_NAME'].'?seite='.$nach1.'">'.$nach1.'</a> '; }
$nach2 = $seite+2; if ($nach2 < $wieviel_seiten+1) { echo '<a href="'.$_SERVER['SCRIPT_NAME'].'?seite='.$nach2.'">'.$nach2.'</a> '; }
$nach3 = $seite+3; if ($nach3 < $wieviel_seiten+1) { echo '<a href="'.$_SERVER['SCRIPT_NAME'].'?seite='.$nach3.'">'.$nach3.'</a> '; }
$nach4 = $seite+4; if ($nach4 < $wieviel_seiten+1) { echo '<a href="'.$_SERVER['SCRIPT_NAME'].'?seite='.$nach4.'">'.$nach4.'</a> '; }
if ($seite < $wieviel_seiten) { echo '<a href="'.$_SERVER['SCRIPT_NAME'].'?seite='.$naechste.'">Nächste</a> '; } else { echo '<span class="this-page">Nächste</span> '; }
if ($wieviel_seiten > 0) { echo '<a href="'.$_SERVER['SCRIPT_NAME'].'?seite='.ceil($wieviel_seiten).'">Letzte</a>'; } else { echo '<span clss="this-page">Letzte</span>'; }
echo '</div>';
?>
<?php } ?>

<?php include 'zz3.php'; ?>