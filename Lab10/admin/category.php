<?php
include_once "../contact.php";
session_start();


$login = 'admin';
$pass = 'password';


$mysqli = new mysqli("localhost", "root", "", "moja_strona");

if ($mysqli->connect_errno) {
    die("Błąd połączenia z bazą: " . $mysqli->connect_error);
}

function DodajKategorie($mysqli) {
    if (isset($_POST['dodaj_kategorie'])) {
        $nazwa = trim($_POST['nazwa'] ?? '');
        $matka = (int)($_POST['matka'] ?? 0);

        if ($nazwa === '') {
            echo "<p style='color:red'>Podaj nazwę.</p>";
        } else {
            $stmt = $mysqli->prepare(
                "INSERT INTO category_list (nazwa, matka) VALUES (?, ?)"
            );
            $stmt->bind_param("si", $nazwa, $matka);

            if ($stmt->execute()) {
                echo "<p style='color:green'>Dodano kategorię.</p>";
            } else {
                echo "<p style='color:red'>Błąd dodawania.</p>";
            }
        }
    }

    return '
    <h2>Dodaj kategorię</h2>
    <form method="post">
        <label>Nazwa:</label><br>
        <input type="text" name="nazwa" style="width:300px"><br><br>

        <label>Matka (ID):</label><br>
        <input type="number" name="matka" value="0" min="0"><br><br>

        <input type="submit" name="dodaj_kategorie" value="Dodaj">
    </form>';
}
function EdytujKategorie($mysqli) {
    $html = '';
    $row = null;
    $id = 0;

    if (isset($_POST['wybierz_id'])) {
        $id = (int)$_POST['id_kategorii'];

        if ($id > 0) {
            $stmt = $mysqli->prepare(
                "SELECT * FROM category_list WHERE id = ?"
            );
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $html .= "<p style='color:red'>Nie znaleziono kategorii.</p>";
            } else {
                $row = $result->fetch_assoc();
            }
        }
    }

    if (isset($_POST['zapisz_kategorie'])) {
        $id = (int)$_POST['id'];
        $nazwa = trim($_POST['nazwa']);
        $matka = (int)$_POST['matka'];

        if ($nazwa === '') {
            $html .= "<p style='color:red'>Nazwa nie może być pusta.</p>";
        } elseif ($matka === $id) {
            $html .= "<p style='color:red'>Kategoria nie może być własną matką.</p>";
            return $html;   
        }
         else {
            $stmt_up = $mysqli->prepare(
                "UPDATE category_list SET nazwa=?, matka=? WHERE id=?"
            );
            $stmt_up->bind_param("sii", $nazwa, $matka, $id);

            if ($stmt_up->execute()) {
                $html .= "<p style='color:green'>Zapisano zmiany.</p>";
            } else {
                $html .= "<p style='color:red'>Błąd zapisu.</p>";
            }
        }
    }

    $html .= '
    <h2>Edytuj kategorię</h2>
    <form method="post">
        <label>ID kategorii:</label><br>
        <input type="number" name="id_kategorii" required>
        <input type="submit" name="wybierz_id" value="Wczytaj">
    </form><br>';

    if ($row) {
        $html .= '
        <form method="post">
            <input type="hidden" name="id" value="'.$row['id'].'">

            <label>Nazwa:</label><br>
            <input type="text" name="nazwa" value="'.htmlspecialchars($row['nazwa']).'" style="width:300px"><br><br>

            <label>Matka (ID):</label><br>
            <input type="number" name="matka" value="'.$row['matka'].'" min="0"><br><br>

            <input type="submit" name="zapisz_kategorie" value="Zapisz">
        </form>';
    }

    return $html;
}

function UsunKategorie($mysqli) {
    $html = '';

    if (isset($_POST['usun_kategorie_submit'])) {
        $id = (int)($_POST['usun_id'] ?? 0);

        if ($id <= 0) {
            $html .= "<p style='color:red;'>Nieprawidłowe ID kategorii.</p>";
        } else {
            $stmt = $mysqli->prepare(
                "DELETE FROM category_list WHERE id = ? LIMIT 1"
            );
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $html .= "<p style='color:green;'>Kategoria została usunięta.</p>";
                } else {
                    $html .= "<p style='color:red;'>Nie znaleziono kategorii.</p>";
                }
            } else {
                $html .= "<p style='color:red;'>Błąd podczas usuwania.</p>";
            }
        }
    }

    $html .= '
    <h2>Usuń kategorię</h2>
    <form method="post">
        <label>ID kategorii:</label><br>
        <input type="number" name="usun_id" required style="width:100px">
        <input type="submit" name="usun_kategorie_submit" value="Usuń">
    </form>';

    return $html;
}

function DrzewoKategorii($mysqli, $matka = 0, $visited = []) {
    if (in_array($matka, $visited)) {
        return '';
    }

    $visited[] = $matka;

    $stmt = $mysqli->prepare(
        "SELECT id, nazwa FROM category_list WHERE matka = ? ORDER BY nazwa"
    );
    $stmt->bind_param("i", $matka);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return '';
    }

    $html = '<ul>';

    while ($row = $result->fetch_assoc()) {
        $html .= '<li>';
        $html .= htmlspecialchars($row['nazwa']);
        $html .= DrzewoKategorii($mysqli, $row['id'], $visited);
        $html .= '</li>';
    }

    $html .= '</ul>';

    return $html;
}

echo DodajKategorie($mysqli);
echo EdytujKategorie($mysqli);
echo UsunKategorie($mysqli);
echo DrzewoKategorii($mysqli);
?>