<?php include 'zz1.php'; ?>
<title>Impressum | Ballmanager.de</title>
<?php include 'zz2.php'; ?>
<h1>Impressum</h1>
<?php
if (date('m') == 4 && (date('d') >= 16 && date('d') <= 18)) {
	echo '<p>info@ballmanager.de</p>';
}
else {
	echo '<p><img src="http://s3.amazonaws.com/ballmanager.de/images/impressum.png" alt="Impressum" title="Impressum" style="border:0" /></p>';
}
?>
<h1>Danke ...</h1>
<p>... an <a href="http://famfamfam.com/">famfamfam</a> für die <a href="http://famfamfam.com/lab/icons/silk/">&quot;Silk Icons&quot;</a></p>
<p>... an <a href="http://famfamfam.com/">famfamfam</a> für die <a href="http://famfamfam.com/lab/icons/flags/">&quot;Flags&quot;</a></p>
<p>... an <a href="http://prothemedesign.com/">Pro Theme Design</a> für die <a href="http://prothemedesign.com/free-webdesign-tools/circular-icons/">&quot;Circular Icons&quot;</a></p>
<?php include 'zz3.php'; ?>