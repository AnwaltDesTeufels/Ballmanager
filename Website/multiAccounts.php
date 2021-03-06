<?php include 'zz1.php'; ?>
<title>Multi-Accounts | Ballmanager.de</title>
<?php include 'zz2.php'; ?>
<?php
if ($loggedin == 1) {
if ($_SESSION['status'] != 'Helfer' && $_SESSION['status'] != 'Admin') { exit; }
?>
<?php
echo '<h1>User vergleichen</h1>';
echo '<form action="/multiAccounts.php" method="get" accept-charset="utf-8">';
echo '<p>User 1: <input type="text" name="user1" /></p>';
echo '<p>User 2: <input type="text" name="user2" /></p>';
echo '<p><input type="submit" value="Vergleichen" /></p>';
echo '</form>';
if (isset($_POST['connectAction']) && isset($_POST['user1ID']) && isset($_POST['user2ID'])) {
	$tempUser1ID = mysql_real_escape_string(trim(strip_tags($_POST['user1ID'])));
	$tempUser2ID = mysql_real_escape_string(trim(strip_tags($_POST['user2ID'])));
	$markNotice = 'Das Team-Mitglied '.$cookie_username.' hat ';
	if ($_POST['connectAction'] == 'create') {
		$connectAction1 = "INSERT INTO ".$prefix."users_multis (user1, user2, found_time) VALUES ('".$tempUser1ID."', '".$tempUser2ID."', ".time()."),('".$tempUser2ID."', '".$tempUser1ID."', ".time().")";
		$connectAction2 = mysql_query($connectAction1);
		$multiChangesType = 'connect';
		$markNotice .= 'die folgenden User als Multi-Accounts verknüpft:<br />';
		echo addInfoBox('Die beiden User wurden im System als Multi-Accounts verknüpft.');
	}
	elseif ($_POST['connectAction'] == 'delete') {
		$connectAction1 = "DELETE FROM ".$prefix."users_multis WHERE (user1 = '".$tempUser1ID."' AND user2 = '".$tempUser2ID."') OR (user1 = '".$tempUser2ID."' AND user2 = '".$tempUser1ID."')";
		$connectAction2 = mysql_query($connectAction1);
		$multiChangesType = 'unconnect';
		$markNotice .= 'die Verknüpfung der folgenden User als Multi-Accounts gelöst:<br />';
		echo addInfoBox('Die Verknüpfung der beiden User im System als Multi-Accounts wurde gelöst.');
	}
	$multiChanges1 = "INSERT INTO ".$prefix."multiChanges (helfer, zeit, user1, user2, type) VALUES ('".$cookie_id."', ".time().", '".$tempUser1ID."', '".$tempUser2ID."', '".$multiChangesType."')";
	$multiChanges2 = mysql_query($multiChanges1);
	// ANDERE HELFER INFORMIEREN ANFANG
	$markNotice .= '<a href="/manager.php?id='.$tempUser1ID.'">User 1</a> + <a href="/manager.php?id='.$tempUser2ID.'">User 2</a><br /><br />[Nachricht vom System]';
	$sql1 = "INSERT INTO ".$prefix."pn (von, an, titel, inhalt, zeit, in_reply_to) VALUES ('18a393b5e23e2b9b4da106b06d8235f3', '18a393b5e23e2b9b4da106b06d8235f3', 'Multi-Markierung', '".mysql_real_escape_string($markNotice)."', ".time().", '')";
	$sql2 = mysql_query($sql1);
	// ANDERE HELFER INFORMIEREN ENDE
}
if (isset($_GET['user1']) && isset($_GET['user2'])) {
	$user1Name = mysql_real_escape_string(trim(strip_tags($_GET['user1'])));
	$user2Name = mysql_real_escape_string(trim(strip_tags($_GET['user2'])));
	// USER IN DB SUCHEN ANFANG
	$sql1 = "SELECT ids FROM ".$prefix."users WHERE username = '".$user1Name."'";
	$sql2 = mysql_query($sql1);
	if (mysql_num_rows($sql2) != 1) {
		echo addInfoBox('Der User '.$user1Name.' konnte nicht gefunden werden.');
		include 'zz3.php';
		exit;
	}
	else {
		$sql3 = mysql_fetch_assoc($sql2);
		$user1ID = $sql3['ids'];
	}
	$sql1 = "SELECT ids FROM ".$prefix."users WHERE username = '".$user2Name."'";
	$sql2 = mysql_query($sql1);
	if (mysql_num_rows($sql2) != 1) {
		echo addInfoBox('Der User '.$user2Name.' konnte nicht gefunden werden.');
		include 'zz3.php';
		exit;
	}
	else {
		$sql3 = mysql_fetch_assoc($sql2);
		$user2ID = $sql3['ids'];
	}
	// USER IN DB SUCHEN ENDE
	$user1 = "SELECT user, ip, zeit, userAgent FROM ".$prefix."loginLog WHERE user = '".$user1ID."' OR user = '".$user2ID."' ORDER BY zeit DESC LIMIT 0, 100";
	$user1 = mysql_query($user1);
	$bothIPs = array();
	$user1IPs = array();
	$user2IPs = array();
	$counter = 0;
	while ($user1a = mysql_fetch_assoc($user1)) {
		$indexStr = $user1a['ip'].'_'.$counter;
		$bothIPs[$indexStr] = array();
		if ($user1a['user'] == $user1ID) {
			$bothIPs[$indexStr][1] = array($user1a['zeit'], $user1a['userAgent']);
			$bothIPs[$indexStr][2] = 0;
			$user1IPs[] = $user1a['ip'];
		}
		else {
			$bothIPs[$indexStr][1] = 0;
			$bothIPs[$indexStr][2] = array($user1a['zeit'], $user1a['userAgent']);
			$user2IPs[] = $user1a['ip'];
		}
		$counter++;
	}
	ksort($bothIPs);
	$multiIPs = array_intersect($user1IPs, $user2IPs);
?>
<h1>Verknüpfung</h1>
<?php
$sql1 = "SELECT found_time FROM ".$prefix."users_multis WHERE user1 = '".$user1ID."' AND user2 = '".$user2ID."'";
$sql2 = mysql_query($sql1);
if (mysql_num_rows($sql2) == 0) {
	echo '<p>Die beiden User '.$user1Name.' und '.$user2Name.' sind im System noch nicht verknüpft.</p>';
	echo '<p>Wenn Du durch den IP-Vergleich der Meinung bist, dass es sich um Multi-Accounts handelt, kannst Du die beiden User jetzt im System verknüpfen.</p>';
	echo '<form action="/multiAccounts.php?user1='.urlencode($user1Name).'&user2='.urlencode($user2Name).'" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="user1ID" value="'.$user1ID.'" />';
	echo '<input type="hidden" name="user2ID" value="'.$user2ID.'" />';
	echo '<input type="hidden" name="connectAction" value="create" />';
	echo '<p><input type="submit" value="Verknüpfung erstellen" onclick="return confirm(\'Bist Du sicher?\')" /></p>';
	echo '</form>';
}
else {
	$sql3 = mysql_fetch_assoc($sql2);
	echo '<p>Die beiden User '.$user1Name.' und '.$user2Name.' sind im System seit dem '.date('d.m.Y H:i', $sql3['found_time']).' Uhr verknüpft.</p>';
	echo '<p>Wenn Du durch den IP-Vergleich der Meinung bist, dass es sich nicht (mehr) um Multi-Accounts handelt, kannst Du die Verknüpfung im System lösen.</p>';
	echo '<form action="/multiAccounts.php?user1='.urlencode($user1Name).'&user2='.urlencode($user2Name).'" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="user1ID" value="'.$user1ID.'" />';
	echo '<input type="hidden" name="user2ID" value="'.$user2ID.'" />';
	echo '<input type="hidden" name="connectAction" value="delete" />';
	echo '<p><input type="submit" value="Verknüpfung lösen" onclick="return confirm(\'Bist Du sicher?\')" /></p>';
	echo '</form>';
}
?>
<p></p>
<h1>IP-Adressen (<?php echo count($multiIPs); ?> Treffer)</h1>
<p>Die folgende Liste zeigt, wann die beiden User <?php echo $user1Name.' und '.$user2Name; ?> mit welcher IP-Adresse eingeloggt waren. Wenn Du mit der Maus über ein Datum mit Uhrzeit fährst, siehst Du die Browser-Informationen dieses Nutzers.</p>
<p>
<table>
<thead>
<tr class="odd">
<th scope="col">IP-Hash</th>
<th scope="col"><?php echo $user1Name; ?></th>
<th scope="col"><?php echo $user2Name; ?></th>
</tr>
</thead>
<tbody>
<?php
$counter = 0;
$letzteZeile = '';
foreach ($bothIPs as $bothIP=>$userData) {
	if ($counter % 2 == 0) { echo '<tr>'; } else { echo '<tr class="odd">'; }
	$bothIP = substr($bothIP, 0, 32);
	if ($bothIP == $letzteZeile) {
		echo '<td>&nbsp;</td>';
	}
	else {
		echo '<td class="link"><a';
		if (in_array($bothIP, $multiIPs)) { echo ' style="color:red"'; }
		echo ' href="/ipInfo.php?ip='.urlencode($bothIP).'">'.$bothIP.'</a></td>';
	}
	echo '<td>';
	if (is_array($userData[1])) {
		echo '<span title="'.$userData[1][1].'">'.date('d.m.Y H:i', $userData[1][0]).'</span>';
	}
	else {
		echo '&nbsp;';
	}
	echo '</td>';
	echo '<td>';
	if (is_array($userData[2])) {
		echo '<span title="'.$userData[2][1].'">'.date('d.m.Y H:i', $userData[2][0]).'</span>';
	}
	else {
		echo '&nbsp;';
	}
	echo '</td>';
	echo '</tr>';
	$letzteZeile = $bothIP;
	$counter++;
}
?>
</tbody>
</table>
</p>
<?php
} // if isset user1 AND isset user2
else {
	$sql1 = "SELECT a.user1, a.found_ip, a.found_time, b.username FROM ".$prefix."users_multis AS a JOIN ".$prefix."users AS b ON a.user1 = b.ids ORDER BY a.found_time DESC LIMIT ".$start.", ".$eintraege_pro_seite;
	$sql2 = mysql_query($sql1);
	$blaetter3 = anzahl_datensaetze_gesamt($sql1);
	echo '<h1>Letzte Multis</h1>';
	echo '<p>Die folgenden Accounts wurden zuletzt als Multi-Accounts erkannt.</p>';
	echo '<table><thead><tr class="odd"><th scope="col">Manager</th><th scope="col">Grund</th><th scope="col">Gefunden</th></tr></thead><tbody>';
	$counter = 0;
	while ($sql3 = mysql_fetch_assoc($sql2)) {
		if ($counter % 2 == 0) { echo '<tr>'; } else { echo '<tr class="odd">'; }
		echo '<td class="link">'.displayUsername($sql3['username'], $sql3['user1']).'</td><td>';
		if (strlen($sql3['found_ip']) == 39) { // UNIQUE_[...]
			echo 'Cookie: '.substr($sql3['found_ip'], 7, 8).'...';
		}
		else {
			echo 'IP-Adresse: '.substr($sql3['found_ip'], 0, 8).'...';
		}
		echo '</td><td>'.date('d.m.Y H:i', $sql3['found_time']).'</td>';
		echo '</tr>';
		$counter++;
	}
	echo '</tbody></table>';
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
}
?>
<?php } else { ?>
<h1>Multi-Accounts</h1>
<p>Du musst angemeldet sein, um diese Seite aufrufen zu können!</p>
<?php } ?>
<?php include 'zz3.php'; ?>