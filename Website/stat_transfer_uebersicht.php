<?php include 'zz1.php'; ?>
<title>Transfer-Übersicht | Ballmanager.de</title>
<?php include 'zz2.php'; ?>
<?php include 'zzsubnav_statistik.php'; ?>
<?php if ($loggedin == 1) { ?>
<h1>Liga wählen</h1>
<form action="" method="get" accept-charset="utf-8">
<p><select name="liga" size="1" style="width:200px">
	<option value="alle">Alle Ligen</option>
    <?php
    if (isset($_GET['liga'])) {
    	$temp_liga = mysql_real_escape_string(trim(strip_tags($_GET['liga'])));
    }
    else {
    	$temp_liga = 'alle';
    }
    if ($temp_liga == 'alle') {
        $temp_liga_query = "";
    }
    else {
        $temp_liga_query = " AND b.liga = '".$temp_liga."'";
    }
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
<h1>Transfer-Übersicht</h1>
<p>In dieser Tabelle sind die 20 teuersten Transfers dieser Saison aufgelistet. Es werden jedoch nur Spieler angezeigt, die nach dem Transfer weiterhin in Europa gespielt haben.</p>
<p>
<table>
<thead>
<tr class="odd">
<th scope="col">Spieler</th>
<th scope="col">Käufer</th>
<th scope="col">Ablöse</th>
<th scope="col">Datum</th>
</tr>
</thead>
<tbody>
<?php
$sql1 = "SELECT a.spieler, a.gebot, a.datum, b.vorname, b.nachname, c.ids, c.name FROM ".$prefix."transfers AS a JOIN ".$prefix."spieler AS b ON a.spieler = b.ids JOIN ".$prefix."teams AS c ON a.bieter = c.ids WHERE a.bieter != 'AUSSERHALB_EU'".$temp_liga_query." ORDER BY a.gebot DESC LIMIT 0, 20";
$sql2 = mysql_query($sql1);
$counter = 0;
while ($sql3 = mysql_fetch_assoc($sql2)) {
	if ($sql3['gebot'] == 1) { continue; } // Leihgaben
	if ($counter % 2 == 0) { echo '<tr>'; } else { echo '<tr class="odd">'; }
	echo '<td class="link"><a href="/spieler.php?id='.$sql3['spieler'].'">'.$sql3['vorname'].' '.$sql3['nachname'].'</a></td>';
	echo '<td class="link"><a href="/team.php?id='.$sql3['ids'].'">'.$sql3['name'].'</a></td>';
	echo '<td>'.number_format($sql3['gebot'], 0, ',', '.').'€</td>';
	echo '<td>'.date('d.m.Y', $sql3['datum']).'</td>';
	echo '</tr>';
	$counter++;
}
?>
</tbody>
</table>
</p>
<?php } else { ?>
<h1>Transfer-Übersicht</h1>
<p>Du musst angemeldet sein, um diese Seite aufrufen zu können!</p>
<?php } ?>
<?php include 'zz3.php'; ?>