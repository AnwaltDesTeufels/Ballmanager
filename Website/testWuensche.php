<?php include 'zz1.php'; ?>
<title>Testwünsche | Ballmanager.de</title>
<?php include 'zz2.php'; ?>
<h1>Testwünsche</h1>
<?php if ($loggedin == 1) { ?>
<p>Du suchst noch Gegner für Testspiele? Ein paar Termine sind einfach noch frei geblieben? Dann bist Du hier genau richtig!</p>
<p>Hier kannst Du Deine Wünsche nach Testspielen öffentlich machen - und hoffen, dass Dich jemand hört. Wenn Du Erfolg haben willst, solltest Du wiederkehren - denn alte Wünsche verschwinden schnell!</p>
<?php
function time_rel($zeitstempel) {
	$ago = time()-$zeitstempel;
    if ($ago < 60) { $agos = 'kurzem'; }
    elseif ($ago < 3600) { $ago1 = round($ago/60, 0); if ($ago1 == 1) { $agos = '1 Minute'; } else { $agos = $ago1.' Minuten'; } }
    elseif ($ago < 86400) { $ago1 = round($ago/3600, 0);  if ($ago1 == 1) { $agos = '1 Stunde'; } else { $agos = $ago1.' Stunden'; } }
    else { $ago1 = round($ago/86400, 0);  if ($ago1 == 1) { $agos = '1 Tag'; } else { $agos = $ago1.' Tagen'; } }
	return $agos;
}
if (isset($_POST['nachricht']) && $cookie_id != DEMO_USER_ID) {
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
	$sql1 = "INSERT INTO ".$prefix."chats_tests (user, zeit, nachricht) VALUES ('".$cookie_id."', ".time().", '".$nachricht."')";
	$sql2 = mysql_query($sql1);
}
?>
<h1>Deine Nachricht</h1>
<form action="/testWuensche.php" method="post" accept-charset="utf-8">
<p><input type="text" name="nachricht" style="width:80%" /> <input type="submit" value="Eintragen"<?php echo noDemoClick($cookie_id); ?> /></p>
</form>
<h1>Wünsche nach Testspielen</h1>
<?php
if (isset($_GET['delEntry']) && $cookie_id != DEMO_USER_ID) {
	$delEntry = mysql_real_escape_string(trim(strip_tags($_GET['delEntry'])));
	$addSql = " AND user = '".$cookie_id."'";
	if ($_SESSION['status'] == 'Helfer' OR $_SESSION['status'] == 'Admin') { $addSql = ""; }
	$gb_in1 = "DELETE FROM ".$prefix."chats_tests WHERE id = ".$delEntry.$addSql;
	$gb_in2 = mysql_query($gb_in1);
}
$sql1 = "SELECT a.id, a.user, a.zeit, a.nachricht, b.username FROM ".$prefix."chats_tests AS a JOIN ".$prefix."users AS b ON a.user = b.ids ORDER BY a.zeit DESC LIMIT 0, 50";
$sql2 = mysql_query($sql1);
while ($sql3 = mysql_fetch_assoc($sql2)) {
	echo '<p><b>'.displayUsername($sql3['username'], $sql3['user']).' schrieb vor '.time_rel($sql3['zeit']).':';
	if ($sql3['user'] == $cookie_id OR $_SESSION['status'] == 'Helfer' OR $_SESSION['status'] == 'Admin') {
		echo ' <a href="/testWuensche.php?delEntry='.$sql3['id'].'">[Löschen]</a>';
	}
	echo '</b><br />'.autoLink($sql3['nachricht']).'</p>';
}
?>
<?php } else { ?>
<p>Du musst angemeldet sein, um diese Seite aufrufen zu können!</p>
<?php } ?>
<?php include 'zz3.php'; ?>