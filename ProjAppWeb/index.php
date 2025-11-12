<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
/* po tym komentarzu będzie kod do dynamicznego ładowania stron */
if($_GET['idp'] == '') $strona = 'html/iconoftheseas.html';
if($_GET['idp'] == 'knocknevis') $strona = 'html/knocknevis.html';
if($_GET['idp'] == 'mscirina') $strona = 'html/mscirina.html';
if($_GET['idp'] == 'arktika') $strona = 'html/arktika.html';
if($_GET['idp'] == 'USSEnterprise') $strona = 'html/USSEnterprise.html';
?>
<!DOCTYPE html>

<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="css/Projekt1.css" />
<title> Największe statki wodne świata. </title>
<link rel="icon" type="image/x-icon" href="img/favicon.ico">
<meta name="description" content="Projekt 1">
<meta name="keywords" content="HTML5, CSS3, JavaScript">
<meta name="author" content="Gabriel Ostrowski">
</head>
<body>
	<div id="Menu">
		<h1>Największe statki wodne na świecie.</h1>
		<nav>
			<ul>
				<li><a href="index.php?idp=">Icon of the Seas</a></li>
				<li><a href="index.php?idp=knocknevis">TT Knock Nevis</a></li>
				<li><a href="index.php?idp=mscirina">MSC Irina</a></li>
				<li><a href="index.php?idp=arktika">Arktika</a></li>
				<li><a href="index.php?idp=USSEnterprise">USS Enterprise</a></li>
			</ul>
		</nav>	
	</div>
	<?php include($strona);
	if (!file_exists($strona)) {
    echo "<p style='color:red;'>BŁĄD: plik $strona nie istnieje!</p>";
} ?>
<footer>
	<p> <b>E-Mail:</b> 175324@student.uwm.edu.pl</p>
</footer>
<?php
$nr_indeksu = '175324';
$nrGrupy = 'ISI3';
echo 'Autor: Gabriel Ostrowski '.$nr_indeksu.' grupa '.$nrGrupy.' <br /><br />';
?>
</body>
</html>
