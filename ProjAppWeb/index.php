<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
/* po tym komentarzu będzie kod do dynamicznego ładowania stron */
include('cfg.php');
include('showpage.php');
$alias = (isset($_GET['idp']) && $_GET['idp'] != '') ? $_GET['idp'] : 'iconoftheseas';
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
				<li><a href="index.php?idp=Filmy">Filmy</a></li>
			</ul>
		</nav>	
	</div>
	<?php
    echo PokazPodstrone($alias, $link);
    ?>
<footer>
	<p> <b>E-Mail:</b> 175324@student.uwm.edu.pl</p>
</footer>
<?php
$nr_indeksu = '175324';
$nrGrupy = 'ISI3';
$APP_VERSION = '1.5';
echo 'Autor: Gabriel Ostrowski '.$nr_indeksu.' grupa '.$nrGrupy.' <br /><br />';
echo 'Wersja aplikacji: v' .$APP_VERSION;
?>

</body>
</html>
