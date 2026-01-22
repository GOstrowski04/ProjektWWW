<?php
include_once "../cfg.php";
include_once "../contact.php";
session_start();

function FormularzLogowania()
{
    $wynik = '
    <div class="logowanie">
     <h1 class="heading">Panel CMS:</h1>
      <div class="logowanie">
       <form method="post" name="LoginForm" enctype="multipart/form-data" action="'.$_SERVER['REQUEST_URI'].'">
        <table class="logowanie">
         <tr><td class="log4_t">[login]</td><td><input type="text" name="login_email" class="logowanie" /></td></tr>
         <tr><td class="log4_t">[haslo]</td><td><input type="password" name="login_pass" class="logowanie" /></td></tr>
         <tr><td>&nbsp;</td>
            <td>
            <input type="submit" name="x1_submit" class="logowanie" value="Zaloguj" />
        </td> 
        </tr>
        </table>
       </form>
       <form method="post" action="'.$_SERVER['REQUEST_URI'].'">
           <input type="submit" name="przypomnij" value="Przypomnij hasło" class="logowanie" />
       </form>
      </div>
     </div>
     ';
    
    return $wynik;
}

$blad = '';

if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true) {
    if (isset($_POST['x1_submit'])) {
        if ($_POST['login_email'] === $login && $_POST['login_pass'] === $pass) {
            $_SESSION['zalogowany'] = true;
        } else {
            $blad = '<p style="color:red">Błędny login lub hasło!</p>';
        }
}
}
if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true) {
    if (isset($_POST['przypomnij'])) {
        PrzypomnijHaslo($login, $pass);
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
}

if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true) {
    echo $blad;
    echo FormularzLogowania();
    exit();
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

function listaPodstron($db) {
    $res = mysqli_query($db, "SELECT * FROM page_list ORDER BY id DESC");
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function edytujPodstrone($db, $id, $d) {
    $stmt = mysqli_prepare($db, "
        UPDATE page_list
        SET page_title=?, page_content=?, status=?, alias=?
        WHERE id=?
    ");
    mysqli_stmt_bind_param($stmt, "ssisi",
        $d['title'], $d['content'], $d['status'], $d['alias'], $id
    );
    return mysqli_stmt_execute($stmt);
}


function dodajPodstrone($db, $d) {
    $stmt = mysqli_prepare($db, "
        INSERT INTO page_list (page_title, page_content, status, alias)
        VALUES (?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmt, "ssis",
        $d['title'], $d['content'], $d['status'], $d['alias']
    );
    return mysqli_stmt_execute($stmt);
}


function usunPodstrone($db, $id) {
    $stmt = mysqli_prepare($db, "DELETE FROM page_list WHERE id=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    return mysqli_stmt_execute($stmt);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {

    $data = [
        'title'   => $_POST['title'],
        'alias'   => $_POST['alias'],
        'content' => $_POST['content'],
        'status'  => isset($_POST['status']) ? 1 : 0
    ];

    if (!empty($_POST['id'])) {
        edytujPodstrone($link, (int)$_POST['id'], $data);
    } else {
        dodajPodstrone($link, $data);
    }

    header("Location: admin.php");
    exit;
}

if (isset($_GET['delete'])) {
    usunPodstrone($link, (int)$_GET['delete']);
    header("Location: admin.php");
    exit;
}

$pages = listaPodstron($link);

$edit_data = null;
if (isset($_GET['edit'])) {
    foreach ($pages as $p) {
        if ($p['id'] == $_GET['edit']) {
            $edit_data = $p;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>CMS – Pages</title>
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
<h1>CMS – Zarządzanie podstronami</h1>

<form method="post">
    <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">

    Tytuł:<br>
    <input type="text" name="title" value="<?= $edit_data['page_title'] ?? '' ?>" required><br><br>

    Alias:<br>
    <input type="text" name="alias" value="<?= $edit_data['alias'] ?? '' ?>" required><br><br>

    Treść:<br>
    <textarea name="content" rows="8" required><?= $edit_data['page_content'] ?? '' ?></textarea><br><br>

    <label>
        Strona aktywna
        <input type="checkbox" name="status" <?= !empty($edit_data['status']) ? 'checked' : '' ?>>
    </label><br><br>

    <input type="submit" value="<?= $edit_data ? 'Zapisz zmiany' : 'Dodaj podstronę' ?>">
</form>

<h2>Lista podstron</h2>
<table border="1" cellpadding="5">
<tr>
    <th>ID</th>
    <th>Tytuł</th>
    <th>Alias</th>
    <th>Status</th>
    <th>Akcje</th>
</tr>

<?php foreach ($pages as $p): ?>
<tr>
    <td><?= $p['id'] ?></td>
    <td><?= htmlspecialchars($p['page_title']) ?></td>
    <td><?= htmlspecialchars($p['alias']) ?></td>
    <td><?= $p['status'] ? 'Aktywna' : 'Ukryta' ?></td>
    <td>
        <a href="?edit=<?= $p['id'] ?>">Edytuj</a> |
        <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Usunąć?')">Usuń</a>
    </td>
</tr>
<?php endforeach; ?>

</table>

<p><a href="?logout=1">Wyloguj</a></p>

</body>
</html>