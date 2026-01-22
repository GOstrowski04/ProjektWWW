<?php
session_start();
require_once '../cfg.php';

if (!isset($_SESSION['zalogowany'])) {
    exit('Brak dostępu');
}



function dodajProdukt($db, $d) {
    $stmt = mysqli_prepare($db, "
        INSERT INTO product_list
        (title, description, date_created, date_modified, date_expired,
         price, vat_tax, available_number, status, category, gabaryt, image)
        VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param(
        $stmt,
        "ssssdiissss",
        $d['title'], $d['description'], $d['date_created'],
        $d['date_expired'], $d['price'], $d['vat_tax'],
        $d['available_number'], $d['status'], $d['category'],
        $d['gabaryt'], $d['image']
    );
    return mysqli_stmt_execute($stmt);
}
// Edytowanie produktu
function edytujProdukt($db, $id, $d) {
    $stmt = mysqli_prepare($db, "
        UPDATE product_list SET
        title=?, description=?, date_modified=NOW(), date_expired=?,
        price=?, vat_tax=?, available_number=?, status=?, category=?, gabaryt=?, image=?
        WHERE id=?
    ");
    mysqli_stmt_bind_param(
        $stmt,
        "sssdiissssi",
        $d['title'], $d['description'], $d['date_expired'],
        $d['price'], $d['vat_tax'], $d['available_number'],
        $d['status'], $d['category'], $d['gabaryt'],
        $d['image'], $id
    );
    return mysqli_stmt_execute($stmt);
}

function usunProdukt($db, $id) {
    $stmt = mysqli_prepare($db, "DELETE FROM product_list WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    return mysqli_stmt_execute($stmt);
}

function pokazProdukty($db) {
    $res = mysqli_query($db, "SELECT * FROM product_list ORDER BY id DESC");
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $image_path = $_POST['existing_image'] ?? null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $folder = "uploads/";
        if (!is_dir($folder)) mkdir($folder, 0777, true);
        $filename = basename($_FILES['image']['name']);
        $target_file = $folder . time() . "_" . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }

    $data = [
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'date_created' => $_POST['date_created'],
        'date_expired' => $_POST['date_expired'],
        'price' => $_POST['price'],
        'vat_tax' => $_POST['vat_tax'],
        'available_number' => $_POST['available_number'],
        'status' => $_POST['status'],
        'category' => $_POST['category'],
        'gabaryt' => $_POST['gabaryt'],
        'image' => $image_path
    ];

    if (!empty($_POST['id'])) {
        edytujProdukt($link, (int)$_POST['id'], $data);
    } else {
        dodajProdukt($link, $data);
    }

    header("Location: product.php");
    exit;
}

if (isset($_GET['delete'])) {
    usunProdukt($link, (int)$_GET['delete']);
    header("Location: product.php");
    exit;
}

$produkty = pokazProdukty($link);

$edit_data = null;
if (isset($_GET['edit'])) {
    foreach ($produkty as $p) {
        if ($p['id'] == $_GET['edit']) {
            $edit_data = $p;
            break;
        }
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>CMS - Zarządzanie Produktami</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        form { margin-bottom: 20px; padding: 10px; border: 1px solid #ccc; }
        img { max-width: 100px; }
    </style>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-nav">
    <h1 class="admin-nav__title">Panel CMS</h1>
    <ul class="admin-nav__menu">
        <li><a href="admin.php">Strony</a></li>
        <li><a href="category.php">Kategorie</a></li>
        <li><a href="product.php">Produkty</a></li>
        <li><a href="?logout=1">Wyloguj</a></li>
    </ul>
</div>
<h1>CMS - Zarządzanie Produktami</h1>

<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">
    <input type="hidden" name="existing_image" value="<?= $edit_data['image'] ?? '' ?>">

    Tytuł: <input type="text" name="title" value="<?= $edit_data['title'] ?? '' ?>" required><br>
    Opis: <textarea name="description" required><?= $edit_data['description'] ?? '' ?></textarea><br>
    Data utworzenia: <input type="date" name="date_created" value="<?= $edit_data['date_created'] ?? date('Y-m-d') ?>" required><br>
    Data wygaśnięcia: <input type="date" name="date_expired" value="<?= $edit_data['date_expired'] ?? '' ?>" required><br>
    Cena: <input type="number" name="price" value="<?= $edit_data['price'] ?? '' ?>" required><br>
    VAT: <input type="number" name="vat_tax" value="<?= $edit_data['vat_tax'] ?? '' ?>" required><br>
    Ilość: <input type="number" name="available_number" value="<?= $edit_data['available_number'] ?? '' ?>" required><br>
    Status: <input type="number" name="status" value="<?= $edit_data['status'] ?? '' ?>" required><br>
    Kategoria: <input type="number" name="category" value="<?= $edit_data['category'] ?? '' ?>" required><br>
    Gabaryt: <input type="text" name="gabaryt" value="<?= $edit_data['gabaryt'] ?? '' ?>" required><br>
    Zdjęcie: <input type="file" name="image"><br>
    <?php if(!empty($edit_data['image'])): ?>
        <img src="<?= $edit_data['image'] ?>" alt="obraz"><br>
    <?php endif; ?>
    <input type="submit" value="<?= isset($edit_data) ? 'Zapisz zmiany' : 'Dodaj produkt' ?>">
</form>

<h2>Lista produktów</h2>
<table>
    <tr>
        <th>ID</th><th>Tytuł</th><th>Opis</th><th>Cena</th><th>VAT</th><th>Ilość</th><th>Status</th><th>Kategoria</th><th>Gabaryt</th><th>Obraz</th><th>Akcje</th>
    </tr>
    <?php foreach($produkty as $p): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= $p['title'] ?></td>
            <td><?= $p['description'] ?></td>
            <td><?= $p['price'] ?></td>
            <td><?= $p['vat_tax'] ?></td>
            <td><?= $p['available_number'] ?></td>
            <td><?= $p['status'] ?></td>
            <td><?= $p['category'] ?></td>
            <td><?= $p['gabaryt'] ?></td>
            <td><?php if($p['image']) echo "<img src='{$p['image']}'>"; ?></td>
            <td>
                <a href="?edit=<?= $p['id'] ?>">Edytuj</a> | 
                <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Czy na pewno chcesz usunąć?')">Usuń</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
</body>
</html>