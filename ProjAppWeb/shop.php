<?php
session_start();
include_once "cfg.php"; 
include_once "admin/cart.php";


$cart = new Cart;

// Dodawanie do koszyka
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    $stmt = $link->prepare("SELECT available_number, status, gabaryt FROM product_list WHERE id=?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
        $prod = $res->fetch_assoc();

        if ($prod['status'] != 1) {
            $msg = "Produkt nieaktywny!";
        } else {
            $found = false;

            if (isset($_SESSION['count']) && $_SESSION['count'] > 0) {
                // Sprawdzenie, czy produkt jest już w koszyku
                for ($i = 1; $i <= $_SESSION['count']; $i++) {
                    if (isset($_SESSION[$i.'_1']) && $_SESSION[$i.'_1'] == $product_id) {
                        $found = true;
                        $new_qty = $_SESSION[$i.'_2'] + $qty;

                        if ($new_qty > $prod['available_number']) {
                            $msg = "Nie można dodać większej ilości niż dostępna: {$prod['available_number']}";
                        } else {
                            $cart->editCount($i, $new_qty);
                            $msg = "Zwiększono ilość produktu w koszyku";
                        }
                        break;
                    }
                }
            }
            if (!$found) {
                if ($qty > $prod['available_number']) {
                    $msg = "Nie można dodać większej ilości niż dostępna: {$prod['available_number']}";
                } else {
                    $cart->addToCart($product_id, $qty, $prod['gabaryt']);
                    $msg = "Dodano produkt do koszyka";
                }
            }
        }
    } else {
        $msg = "Nie znaleziono produktu";
    }

}
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    $nr = (int)($_POST['nr'] ?? 0);
    $cart->removeFromCart($nr);
    $msg = "Produkt usunięty z koszyka";
}
// Możliwość filtrowania produktów po kategoriach
function DrzewoKategoriiLink($db, $matka = 0, $visited = [], $active_id = null) {
    if (in_array($matka, $visited)) return '';
    $visited[] = $matka;

    $stmt = mysqli_prepare($db, "SELECT id, nazwa FROM category_list WHERE matka=? ORDER BY nazwa");
    mysqli_stmt_bind_param($stmt, "i", $matka);
    mysqli_stmt_execute($stmt);
    $result = $stmt->get_result();

    if ($result->num_rows === 0) return '';

    $html = '<ul>';
    while ($row = $result->fetch_assoc()) {
        $style = ($active_id && $row['id'] == $active_id) ? 'font-weight:bold;color:green;' : '';
        $html .= '<li style="'.$style.'">';
        $html .= '<a href="shop.php?cat='.$row['id'].'">'.htmlspecialchars($row['nazwa']).'</a>';
        $html .= DrzewoKategoriiLink($db, $row['id'], $visited, $active_id);
        $html .= '</li>';
    }
    $html .= '</ul>';
    return $html;
}
// rekurencyjne szukanie produktów z podkategoriami
function PobierzPodkategorie($db, $id) {
    $ids = [$id];

    $stmt = mysqli_prepare($db, "SELECT id FROM category_list WHERE matka=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $child_ids = PobierzPodkategorie($db, $row['id']);
        $ids = array_merge($ids, $child_ids);
    }

    return $ids;
}

$selected_category = isset($_GET['cat']) ? (int)$_GET['cat'] : null;

$sql = "
SELECT p.id, p.title, p.description, p.date_created, p.date_modified, 
       p.date_expired, p.price, p.vat_tax, p.available_number, 
       p.status, p.gabaryt, p.image, c.nazwa AS category_name
FROM product_list p
LEFT JOIN category_list c ON p.category = c.id
";

if ($selected_category) {
    $all_ids = PobierzPodkategorie($link, $selected_category);
    $id_list = implode(",", $all_ids);
    $sql .= " WHERE p.category IN ($id_list)";
}

$sql .= " ORDER BY p.id ASC";

$result = $link->query($sql);
$products = [];
if ($result) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/Projekt1.css" />
    <title> Największe statki wodne świata. </title>
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <meta name="description" content="Projekt 1">
    <meta name="keywords" content="HTML5, CSS3, JavaScript">
    <meta name="author" content="Gabriel Ostrowski">
    <style>
        table { border-collapse: collapse; width: 100%; background-color: lightgray;}
        th, td { border: 1px solid #090707; padding: 8px; text-align: left; }
        img { max-width: 100px; max-height: 100px; }
    </style>
</head>
<body>
    <div id="Menu">
		<h1>Największe statki wodne na świecie.</h1>
		<nav>
			<ul>
                <li><a href="shop.php">Sklep</a></li>
                <li><a href="kontakt.php">Kontakt</a></li>
				<li><a href="index.php?idp=">Icon of the Seas</a></li>
				<li><a href="index.php?idp=knocknevis">TT Knock Nevis</a></li>
				<li><a href="index.php?idp=mscirina">MSC Irina</a></li>
				<li><a href="index.php?idp=arktika">Arktika</a></li>
				<li><a href="index.php?idp=USSEnterprise">USS Enterprise</a></li>
				<li><a href="index.php?idp=Filmy">Filmy</a></li>
			</ul>
		</nav>	
	</div>
    <div id="Strona">
<h2>Sklep</h2>

<?php if (!empty($msg)) echo "<p style='color:green'>{$msg}</p>"; ?>
<h2>Kategorie</h2>
<div>
<?= DrzewoKategoriiLink($link, 0, [], $selected_category) ?>
</div>
<table>
<tr>
    <th>ID</th>
    <th>Tytuł</th>
    <th>Opis</th>
    <th>Kategoria</th>
    <th>Gabaryt</th>
    <th>Dostępność</th>
    <th>Cena</th>
    <th>VAT</th>
    <th>Data utworzenia</th>
    <th>Data modyfikacji</th>
    <th>Data wygaśnięcia</th>
    <th>Status</th>
    <th>Obraz</th>
    <th>Akcja</th>
</tr>

<?php foreach ($products as $p): ?>
<tr>
    <td><?= $p['id'] ?></td>
    <td><?= htmlspecialchars($p['title']) ?></td>
    <td><?= htmlspecialchars($p['description']) ?></td>
    <td><?= htmlspecialchars($p['category_name'] ?? '-') ?></td>
    <td><?= htmlspecialchars($p['gabaryt']) ?></td>
    <td><?= $p['available_number'] ?></td>
    <td><?= number_format($p['price'], 2) ?> zł</td>
    <td><?= $p['vat_tax'] ?>%</td>
    <td><?= $p['date_created'] ?></td>
    <td><?= $p['date_modified'] ?></td>
    <td><?= $p['date_expired'] ?></td>
    <td><?= $p['status'] ? 'Aktywny' : 'Ukryty' ?></td>
    <td><?php if($p['image']) echo "<img src='admin/{$p['image']}'>"; ?></td>
    <td>
        <?php if($p['status'] && $p['available_number'] > 0): ?>
        <form method="post">
            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
            <input type="number" name="qty" value="1" min="1" max="<?= $p['available_number'] ?>">
            <input type="submit" name="add_to_cart" value="Dodaj do koszyka">
        </form>
        <?php else: ?>
            Niedostępny
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>

</table>

<h2>Koszyk</h2>
<?php 
$cart->showCart($link); 
?>
</div>
</body>
<footer>
	<p> <b>E-Mail:</b> 175324@student.uwm.edu.pl</p>
</footer>
</html>