<?php
include_once "../cfg.php";
session_start();


if (!isset($_SESSION['zalogowany'])) {
    exit('Brak dostępu');
}

function listaKategorii($db) {
    $res = mysqli_query($db, "SELECT * FROM category_list ORDER BY id ASC");
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function dodajKategorie($db, $data) {
    $stmt = mysqli_prepare($db, "INSERT INTO category_list (nazwa, matka) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "si", $data['nazwa'], $data['matka']);
    return mysqli_stmt_execute($stmt);
}

function edytujKategorie($db, $id, $data) {
    $stmt = mysqli_prepare($db, "UPDATE category_list SET nazwa=?, matka=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "sii", $data['nazwa'], $data['matka'], $id);
    return mysqli_stmt_execute($stmt);
}

function usunKategorie($db, $id) {
    $stmt = mysqli_prepare($db, "DELETE FROM category_list WHERE id=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    return mysqli_stmt_execute($stmt);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nazwa' => $_POST['nazwa'] ?? '',
        'matka' => (int)($_POST['matka'] ?? 0)
    ];

    if (!empty($_POST['id'])) {
        edytujKategorie($link, (int)$_POST['id'], $data);
    } elseif (isset($_POST['dodaj_kategorie'])) {
        dodajKategorie($link, $data);
    } elseif (isset($_POST['usun_id'])) {
        usunKategorie($link, (int)$_POST['usun_id']);
    }

    header("Location: category.php");
    exit;
}

$categories = listaKategorii($link);
$edit_data = null;
if (isset($_GET['edit'])) {
    foreach ($categories as $c) {
        if ($c['id'] == $_GET['edit']) {
            $edit_data = $c;
            break;
        }
    }
}

function DrzewoKategorii($db, $matka = 0, $visited = []) {
    if (in_array($matka, $visited)) return '';
    $visited[] = $matka;

    $stmt = mysqli_prepare($db, "SELECT id, nazwa FROM category_list WHERE matka=? ORDER BY nazwa");
    mysqli_stmt_bind_param($stmt, "i", $matka);
    mysqli_stmt_execute($stmt);
    $result = $stmt->get_result();

    if ($result->num_rows === 0) return '';

    $html = '<ul>';
    while ($row = $result->fetch_assoc()) {
        $html .= '<li>' . htmlspecialchars($row['nazwa']);
        $html .= DrzewoKategorii($db, $row['id'], $visited);
        $html .= '</li>';
    }
    $html .= '</ul>';
    return $html;
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
    <title>CMS – Kategorie</title>
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
<h1>CMS – Zarządzanie kategoriami</h1>

<h2>Dodaj kategorię</h2>
<form method="post">
    Nazwa:<br>
    <input type="text" name="nazwa" value="" required><br><br>
    Matka (ID):<br>
    <input type="number" name="matka" value="0" min="0"><br><br>
    <input type="submit" name="dodaj_kategorie" value="Dodaj">
</form>

<?php if ($edit_data): ?>
<h2>Edytuj kategorię</h2>
<form method="post">
    <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
    Nazwa:<br>
    <input type="text" name="nazwa" value="<?= htmlspecialchars($edit_data['nazwa']) ?>" required><br><br>
    Matka (ID):<br>
    <input type="number" name="matka" value="<?= $edit_data['matka'] ?>" min="0"><br><br>
    <input type="submit" value="Zapisz zmiany">
</form>
<?php endif; ?>

<h2>Usuń kategorię</h2>
<form method="post">
    ID:<br>
    <input type="number" name="usun_id" required><br><br>
    <input type="submit" value="Usuń" onclick="return confirm('Na pewno usunąć kategorię?')">
</form>

<h2>Drzewo kategorii</h2>
<?= DrzewoKategorii($link) ?>

<h2>Lista kategorii</h2>
<table border="1" cellpadding="5">
<tr>
    <th>ID</th>
    <th>Nazwa</th>
    <th>Matka</th>
    <th>Akcje</th>
</tr>
<?php foreach ($categories as $c): ?>
<tr>
    <td><?= $c['id'] ?></td>
    <td><?= htmlspecialchars($c['nazwa']) ?></td>
    <td><?= $c['matka'] ?></td>
    <td>
        <a href="?edit=<?= $c['id'] ?>">Edytuj</a>
    </td>
</tr>
<?php endforeach; ?>
</table>

</body>
</html>