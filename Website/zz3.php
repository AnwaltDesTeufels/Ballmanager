</div>
</div>
<div id="footer"></div>
</div>
<?php echo showInfoBox($showInfoBox); /* Meldungen ausgeben */ ?>
<div><span id="rfooter" style="color:#666; width:820px; margin-left:auto; margin-right:auto; height:55px; text-align:center; font-size:80%; text-decoration:none">
	<a href="/regeln.php#regeln" rel="nofollow">Regeln</a> |
	<a href="/impressum.php" rel="nofollow">Impressum</a> |
	<a href="/regeln.php#datenschutz" rel="nofollow">Datenschutz</a><br />
	Alle Vereine, Spieler und Sponsoren sind frei erfunden und haben keinen Bezug zu realen Ligen. Das Geld im Spiel ist nur virtuell und es erfolgen niemals Auszahlungen.</span>
</div>
<?php
// CRONJOBS AUSFUEREN ANFANG
if (Chance_Percent(33)) {
	$aktuelle_stunde = date('H');
	$sql1 = "SELECT id, datei FROM ".$prefix."cronjobs WHERE (zuletzt+intervall) < ".time()." AND stunde_min <= ".$aktuelle_stunde." AND stunde_max >= ".$aktuelle_stunde." ORDER BY zuletzt ASC LIMIT 0, 1";
	$sql2 = mysql_query($sql1);
	if (mysql_num_rows($sql2) != 0) {
		$sql3 = mysql_fetch_assoc($sql2);
		$id = $sql3['id'];
		$dateiname = 'http://www.ballmanager.de/'.$sql3['datei'];
		$requestTXT = file_get_contents($dateiname);
		$sql4 = "UPDATE ".$prefix."cronjobs SET zuletzt = ".time()." WHERE id = ".$id;
		$sql5 = mysql_query($sql4);
	}
}
// CRONJOBS AUSFUEREN ENDE
?>
</body>
</html>