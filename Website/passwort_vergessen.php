<?php include 'zz1.php'; ?>
<?php if ($loggedin == 1) { exit; } ?>
<title>Passwort vergessen | Ballmanager.de</title>
<?php include 'zz2.php'; ?>
<?php
$timeout = getTimestamp('-5 hours');
$ou1 = "DELETE FROM ".$prefix."users_newpw WHERE zeit < ".$timeout;
$ou2 = mysql_query($ou1);
if (isset($_GET['e']) && isset($_GET['k'])) {
	$user = mysql_real_escape_string(trim(strip_tags($_GET['e'])));
	$key = md5(mysql_real_escape_string(trim(strip_tags($_GET['k']))));
	$ou1 = "SELECT user, newpw FROM ".$prefix."users_newpw WHERE user = '".$user."' AND keywert = '".$key."' AND zeit > ".$timeout;
	$ou2 = mysql_query($ou1);
	if (mysql_num_rows($ou2) != 0) {
		$ou3 = mysql_fetch_assoc($ou2);
		$in1 = "UPDATE ".$prefix."users SET password = '".$ou3['newpw']."' WHERE ids = '".$ou3['user']."'";
		$in2 = mysql_query($in1);
		$in1 = "DELETE FROM ".$prefix."users_newpw WHERE user = '".$ou3['user']."'";
		$in2 = mysql_query($in1);
		echo addInfoBox('Dein neues Passwort wurde aktiviert. Du kannst Dich jetzt damit einloggen.');
	}
	else {
		echo addInfoBox('Das Passwort konnte nicht aktiviert werden. Bitte rufe den Link noch einmal auf oder fordere ein neues Passwort an.');
	}
}
elseif (isset($_POST['email'])) {
	$email = mysql_real_escape_string(trim(strip_tags($_POST['email'])));
	$ou1 = "SELECT ids, username, regdate FROM ".$prefix."users WHERE email = '".$email."'";
	$ou2 = mysql_query($ou1);
	if (mysql_num_rows($ou2) != 0) {
        $ou3 = mysql_fetch_assoc($ou2);
    	$user = $ou3['ids'];
		if ($user != DEMO_USER_ID) {
			$username = $ou3['username'];
			$key = md5(md5($ou3['regdate']).md5(time()).'29');
			$key_db = md5($key);
			$newpw = mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9).mt_rand(1,9);
			$newpw_db = md5('1'.$newpw.'29');
			$in1 = "INSERT INTO ".$prefix."users_newpw (user, zeit, keywert, newpw) VALUES ('".$user."', '".time()."', '".$key_db."', '".$newpw_db."')";
			$in2 = mysql_query($in1);
			if ($in2 == FALSE) {
				echo addInfoBox('Für diese E-Mail-Adresse wurde in den letzten 5 Stunden schon ein Passwort angefordert.');
			}
			else {
				if (isset($_SERVER['REMOTE_ADDR'])) {
					$aip = 'Wenn Du kein neues Passwort angefordert hast, wurde diese Funktion von jemand anderem missbraucht. Die Anfrage kam von der IP-Adresse '.$_SERVER['REMOTE_ADDR'];
				}
				else {
					$aip = '';
				}
// E-MAIL VERSENDEN
$header = 'From: Ballmanager <system@ballmanager.de>';
$empfaenger = $email;
$betreff = 'Ballmanager: Passwort vergessen';
$nachricht = '
Hallo '.$username.',

Du hast auf www.Ballmanager.de ein neues Passwort angefordert.
Dein Neues Passwort lautet: '.$newpw.'
Du musst das neue Passwort aber noch aktivieren, indem Du den folgenden Link anklickst:
http://www.ballmanager.de/passwort_vergessen.php?e='.$user.'&k='.$key.'
Wir wünschen Dir noch viel Spaß beim Ballmanager.

Mit freundlichen Grüßen
www.ballmanager.de';
if ($aip != '') {
$nachricht .= '

-----------
'.$aip;
}
$absender = 'From: Ballmanager <system@ballmanager.de>
Content-type: text/plain; charset=UTF-8';
mail($empfaenger, $betreff, $nachricht, $absender);
// E-MAIL VERSENDEN
				echo addInfoBox('Der Vorgang war erfolgreich. Wir senden Dir jetzt eine E-Mail mit weiteren Informationen zu.');
			} // if in2 == FALSE
		}
	}
	else {
		echo addInfoBox('Es konnte kein User mit der angegebenen E-Mail-Adresse gefunden werden. Bitte versuche es noch einmal.');
	}
}
?>
<h1>Passwort vergessen</h1>
<p>Du hast Dein Passwort vergessen? Dann gib hier bitte einfach die E-Mail-Adresse ein, mit der Du Dich registriert hast. Wir schicken Dir dann eine E-Mail mit weiteren Informationen, damit Du ein neues Passwort wählen kannst.<br />
<i>Wichtig:</i> Der Link in der E-Mail, die wir Dir senden, ist nur fünf Stunden lang gültig. Danach musst Du die E-Mail erneut anfordern.</p>
<form method="post" action="/passwort_vergessen.php" accept-charset="utf-8">
<p>E-Mail-Adresse:<br /><input type="text" name="email" id="email" style="width:200px" /></p>
<p><input type="submit" value="Anfordern" /></p>
</form>
<?php include 'zz3.php'; ?>