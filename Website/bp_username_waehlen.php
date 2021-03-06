<?php include 'zz1.php'; ?>
<?php
if ($loggedin != 1) {
	header('Location: /index.php');
	exit;
}
if (isset($_POST['username']) && isset($_SESSION['bp_username'])) {
	if (substr($_SESSION['username'], 0, 3) == 'BP_') {
		$email = mysql_real_escape_string(trim(strip_tags($_SESSION['bp_username'])));
		$username = mysql_real_escape_string(trim(strip_tags($_POST['username'])));
		if (!validUsername($username)) {
			echo '<p>Dein Managername darf nur die folgenden Zeichen enthalten (Länge: 3-30).</p>';
			echo '<p><strong>Buchstaben:</strong> A-Z + Umlaute (groß und klein)<br /><strong>Zahlen:</strong> 0-9<br /><strong>Sonderzeichen:</strong> Bindestrich</p>';
			echo '<p>Nicht erlaubt sind also Leerzeichen, Punkt, Komma, Sternchen usw.</p>';
			echo '<p>Bitte versuche es noch einmal.</p>';
		}
		else {
			$schon_vergeben4 = "SELECT COUNT(*) FROM ".$prefix."users WHERE username = '".$username."'";
			$schon_vergeben5 = mysql_query($schon_vergeben4);
			$schon_vergeben6 = mysql_result($schon_vergeben5, 0);
			if (strlen($username) < 3) {
				$schon_vergeben_message = '<p style="color:red">Dein Benutzername muss aus mindestens drei Zeichen bestehen.</p>';
			}
			elseif ($schon_vergeben6 != 0) {
				$schon_vergeben_message = '<p style="color:red">Der gewünschte Benutzername ist leider schon vergeben. Bitte versuche es mit einem anderen.</p>';
			}
			else {
				$sql1 = "UPDATE ".$prefix."users SET username = '".$username."' WHERE email = '".$email."'";
				$sql2 = mysql_query($sql1);
				header("Location: /warteliste.php");
			}
		}
	}
	else {
		$schon_vergeben_message = '<p style="color:red">Du hast schon einen Benutzernamen und kannst deshalb hier keinen wählen.</p>';
	}
}
?>
<title>Benutzernamen wählen | Ballmanager.de</title>
<?php include 'zz2.php'; ?>
<h1>Benutzernamen wählen</h1>
<?php if (isset($schon_vergeben_message)) { echo $schon_vergeben_message; } ?>
<p>Willkommen beim Ballmanager! Du stehst jetzt erst einmal auf der Warteliste. Sobald ein Team für Dich frei wird - und das wird nicht lange dauern - erhältst Du eine
Nachricht von uns bei Bigpoint.</p>
<p>Damit es dann sofort losgehen kann, brauchst Du jetzt noch einen Benutzernamen. Dieser Name wird überall im Spiel angezeigt, Du bist daran zu erkennen.</p>
<p>Trage Deinen gewünschten Namen in das Textfeld unten ein und klicke anschließend auf &quot;Fertig&quot;. Bitte halte Dich bei der Wahl Deines Namens aber an 
<a href="/regeln.php" onclick="window.open('/regeln.php'); return false">diese Regeln</a>.</p>
<?php
if (substr($_SESSION['username'], 0, 3) != 'BP_') {
	echo '<p><strong>Du hast schon einen Benutzernamen und kannst deshalb hier keinen wählen.</strong></p>';
}
else {
?>
<form action="/bp_username_waehlen.php" method="post" accept-charset="utf-8">
<p><input type="text" name="username" /></p>
<p><input type="submit" value="Fertig" /></p>
</form>
<?php } ?>
<?php include 'zz3.php'; ?>