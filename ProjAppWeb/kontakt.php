<?php
include_once "contact.php";

$alias = (isset($_GET['idp']) && $_GET['idp'] != '') ? $_GET['idp'] : 'iconoftheseas';

$pages = [];
$sql = "SELECT alias, page_title FROM page_list ORDER BY id ASC";
$result = $link->query($sql);
if ($result) {
    $pages = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Kontakt</title>
    <link rel="stylesheet" href="css/Projekt1.css">
    <script src="js/kolorujtlo.js" type="text/javascript"></script>
</head>
<body>
<div id="Menu">
		<h1>Największe statki wodne na świecie.</h1>
		<nav>
			<ul>
				<li><a href="shop.php">Sklep</a></li>
				<li><a href="kontakt.php">Kontakt</a></li>
				<?php foreach ($pages as $p): ?>
            		<li>
                	<a href="index.php?idp=<?= htmlspecialchars($p['alias'] ?? '') ?>">
                    <?= htmlspecialchars($p['page_title'] ?? '') ?>
                	</a>
            		</li>
        		<?php endforeach; ?>
				</a></li>
			</ul>
		</nav>	
		<FORM METHOD="POST" NAME="background">
			<INPUT TYPE ="button" VALUE="żółty" onclick="changeBackground('#FFF000')">
			<INPUT TYPE ="button" VALUE="czarny" onclick="changeBackground('#000000')">
			<INPUT TYPE ="button" VALUE="biały" onclick="changeBackground('#FFFFFF')">
			<INPUT TYPE ="button" VALUE="zielony" onclick="changeBackground('#00FF00')">
			<INPUT TYPE ="button" VALUE="niebieski" onclick="changeBackground('#0000FF')">
			<INPUT TYPE ="button" VALUE="pomarańczowy" onclick="changeBackground('#FF8000')">
			<INPUT TYPE ="button" VALUE="szary" onclick="changeBackground('#c0c0c0')">
			<INPUT TYPE ="button" VALUE="czerwony" onclick="changeBackground('#FF0000')">
		</FORM>
	</div>
<div class="kontakt">

    <h1 class="kontakt__title">Skontaktuj się z nami</h1>

    <div class="kontakt__box">

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wyslij_kontakt'])) {

            $success = WyslijMailKontakt(
                'gabrielostrowski2004@gmail.com',
                $_POST['temat'],
                $_POST['tresc'],
                $_POST['email']
            );
            if ($success) {
                header("Location: kontakt.php?sent=1");
            } else {
                header("Location: kontakt.php?sent=0");
            }
            exit;
        }

        if (isset($_GET['sent'])) {
            if ($_GET['sent'] == 1) {
                echo '<p class="success">Wiadomość została wysłana</p>';
            } else {
                echo '<p class="error">Wystąpił błąd podczas wysyłania</p>';
            }
        }
        echo PokazKontakt();
        ?>

    </div>

</div>
<footer>
	<p> <b>E-Mail:</b> 175324@student.uwm.edu.pl</p>
</footer>
</body>
</html>