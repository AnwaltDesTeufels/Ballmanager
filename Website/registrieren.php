<?php if (!isset($_POST['reg_benutzername']) OR !isset($_POST['reg_email'])) { exit; } ?>
<?php include 'zz1.php'; ?>
<?php if ($loggedin == 1) { exit; } ?>
<title>Registrierung - Daten prüfen | Ballmanager.de</title>
<?php include 'zz2.php'; ?>
<h1>Registrierung - Daten prüfen</h1>
<?php
function validEmail($email) {
   $isValid = TRUE;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex) {
      $isValid = FALSE;
   }
   else {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64) {
         // local part length exceeded
         $isValid = FALSE;
      }
      else if ($domainLen < 1 || $domainLen > 255) {
         // domain part length exceeded
         $isValid = FALSE;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.') {
         // local part starts or ends with '.'
         $isValid = FALSE;
      }
      else if (preg_match('/\\.\\./', $local)) {
         // local part has two consecutive dots
         $isValid = FALSE;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
         // character not valid in domain part
         $isValid = FALSE;
      }
      else if (preg_match('/\\.\\./', $domain)) {
         // domain part has two consecutive dots
         $isValid = FALSE;
      }
      else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
         // character not valid in local part unless local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
            $isValid = FALSE;
         }
      }
   }
   return $isValid;
}
function getMailHost($email) {
	$host = '';
	$temp = explode('@', $email, 2);
	if (count($temp) == 2) {
		$host = trim(strip_tags($temp[1]));
	}
	return $host;
}
function in_blacklist($text, $list) {
	$meldung = FALSE;
	foreach ($list as $eintrag) {
		if (strpos($text, $eintrag) !== FALSE) {
			$meldung = TRUE;
		}
	}
	return $meldung;
}
$mailHostBlacklist = array('trash-mail.com', 'emailgo.de', 'spambog.com', 'spambog.de', 'discardmail.com', 'discardmail.de', 'sofort-mail.de', 'wegwerfemail.de', 'trashemail.de', 'safetypost.de', 'trashmail.net');
$uName = trim($_POST['reg_benutzername']);
$uMail = trim($_POST['reg_email']);
if (!validUsername($uName)) {
	echo '<p>Dein Managername darf nur die folgenden Zeichen enthalten (Länge: 3-30).</p>';
	echo '<p><strong>Buchstaben:</strong> A-Z + Umlaute (groß und klein)<br /><strong>Zahlen:</strong> 0-9<br /><strong>Sonderzeichen:</strong> Bindestrich</p>';
	echo '<p>Nicht erlaubt sind also Leerzeichen, Punkt, Komma, Sternchen usw.</p>';
	echo '<p><a href="/index.php">Bitte klicke hier und versuche es noch einmal.</a></p>';
}
elseif (!validEmail($uMail)) {
	echo '<p>Du hast keine gültige E-Mail-Adresse angegeben.</p>';
	echo '<p><a href="/index.php">Bitte klicke hier und versuche es noch einmal.</a></p>';
}
elseif (in_blacklist(getMailHost($uMail), $mailHostBlacklist)) {
	echo '<p>E-Mail-Adressen von diesem Anbieter können leider nicht genutzt werden.</p>';
	echo '<p><a href="/index.php">Bitte klicke hier und versuche es noch einmal.</a></p>';
}
else {
?>
<form method="post" action="/registrierung.php" accept-charset="utf-8" class="imtext">
<p>Ich möchte als <strong><?php echo $uName; ?></strong> mitspielen. Das Spiel ist zu 100% kostenlos. Meine E-Mail-Adresse ist <strong><?php echo $uMail; ?></strong>. An diese Adresse soll mir jetzt gleich ein Passwort zugeschickt werden, mit dem ich mich dann einloggen kann.</p>
<p>Die <a target="_blank" href="/regeln.php#datenschutz">Datenschutzrichtlinien</a> und die <a target="_blank" href="/regeln.php#regeln">Regeln</a> des Spiels habe ich gelesen und ich akzeptiere diese.</p>
<p>Dein Team und die Liga kannst Du Dir beim ersten Login frei aussuchen.</p>
<p><input type="hidden" name="reg_benutzername" id="reg_benutzername" value="<?php echo $uName; ?>" /><input type="hidden" name="reg_email" id="reg_email" value="<?php echo $uMail; ?>" /><input type="submit" value="Jetzt mit diesen Daten registrieren" /></p>
<p>Du kannst die Registrierung an dieser Stelle noch abbrechen. Deine Daten wurden noch nicht gespeichert.</p>
</form>
<?php } ?>
<?php include 'zz3.php'; ?>