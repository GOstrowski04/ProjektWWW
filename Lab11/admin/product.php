<?php
session_start();


$login = 'admin';
$pass = 'password';


$mysqli = new mysqli("localhost", "root", "", "moja_strona");

if ($mysqli->connect_errno) {
    die("Błąd połączenia z bazą: " . $mysqli->connect_error);
}

class ZarzadzajProduktami {
    private $conn;

    public function __construct($mysqli) {
        $this->conn = $mysqli;
    }

    // Dodawanie produktu
    public function dodajProdukt($title, $description, $date_created, $date_expired, $price, $vat_tax, $available_number, $status, $category, $gabaryt, $image_path) {
        $sql = "INSERT INTO product_list (title, description, date_created, date_modified, date_expired, price, vat_tax, available_number, status, category, gabaryt, image)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $date_modified = date('Y-m-d'); 
        $stmt->bind_param(
            "ssssddiissss",
            $title,
            $description,
            $date_created,
            $date_modified,
            $date_expired,
            $price,
            $vat_tax,
            $available_number,
            $status,
            $category,
            $gabaryt,
            $image_path
        );
        return $stmt->execute();
    }

    // Edytowanie produktu
    public function edytujProdukt($id, $title, $description, $date_expired, $price, $vat_tax, $available_number, $status, $category, $gabaryt, $image_path) {
        $sql = "UPDATE product_list SET 
                    title = ?, 
                    description = ?, 
                    date_modified = ?, 
                    date_expired = ?, 
                    price = ?, 
                    vat_tax = ?, 
                    available_number = ?, 
                    status = ?, 
                    category = ?, 
                    gabaryt = ?, 
                    image = ?
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $date_modified = date('Y-m-d');
        $stmt->bind_param(
            "sssdiiissssi",
            $title,
            $description,
            $date_modified,
            $date_expired,
            $price,
            $vat_tax,
            $available_number,
            $status,
            $category,
            $gabaryt,
            $image_path,
            $id
        );
        return $stmt->execute();
    }

    // Usuwanie produktu
    public function usunProdukt($id) {
        $sql = "DELETE FROM produkty WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function pokazProdukty() {
        $sql = "SELECT * FROM product_list";
        $result = $this->conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $produkty = [];
            while($row = $result->fetch_assoc()) {
                $produkty[] = $row;
            }
            return $produkty;
        } else {
            return [];
        }
    }
}
$zarzadzaj = new ZarzadzajProduktami($mysqli);
/// sprawdzić
if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true) {
    echo $blad;
    echo FormularzLogowania();
    exit();
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

    if (!empty($_POST['id'])) {
        $zarzadzaj->edytujProdukt(
            $_POST['id'], $_POST['title'], $_POST['description'], $_POST['date_expired'],
            $_POST['price'], $_POST['vat_tax'], $_POST['available_number'], $_POST['status'],
            $_POST['category'], $_POST['gabaryt'], $image_path
        );
    } else {
        $zarzadzaj->dodajProdukt(
            $_POST['title'], $_POST['description'], $_POST['date_created'], $_POST['date_expired'],
            $_POST['price'], $_POST['vat_tax'], $_POST['available_number'], $_POST['status'],
            $_POST['category'], $_POST['gabaryt'], $image_path
        );
    }
    header("Location: product.php");
    exit;
}

if (isset($_GET['delete'])) {
    $zarzadzaj->usunProdukt($_GET['delete']);
    header("Location: product.php");
    exit;
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $produkty = $zarzadzaj->pokazProdukty();
    foreach ($produkty as $p) {
        if ($p['id'] == $_GET['edit']) $edit_data = $p;
    }
}

$produkty = $zarzadzaj->pokazProdukty();
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
</head>
<body>
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