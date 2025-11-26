<?php
session_start();


$login = 'admin';
$pass = 'password';


$mysqli = new mysqli("localhost", "root", "", "moja_strona");

if ($mysqli->connect_errno) {
    die("Błąd połączenia z bazą: " . $mysqli->connect_error);
}


function FormularzLogowania()
{
    $wynik = '
    <div class="logowanie">
     <h1 class="heading">Panel CMS:</h1>
      <div class="logowanie">
       <form method="post" name="LoginForm" enctype="multipart/form-data" action="'.$_SERVER['REQUEST_URI'].'">
        <table class="logowanie">
         <tr><td class="log4_t">[email]</td><td><input type="text" name="login_email" class="logowanie" /></td></tr>
         <tr><td class="log4_t">[haslo]</td><td><input type="password" name="login_pass" class="logowanie" /></td></tr>
         <tr><td>&nbsp;</td><td><input type="submit" name="x1_submit" class="logowanie" value="Zaloguj" /></td></tr>
        </table>
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
    echo $blad;
    echo FormularzLogowania();
    exit();
}

function ListaPodstron($mysqli) {
    $query = "SELECT * FROM page_list ORDER BY id ASC LIMIT 100";
    $result = $mysqli->query($query);

    if (!$result) {
        echo "<p style='color:red'>Błąd zapytania: " . $mysqli->error . "</p>";
        return;
    }

    while($row = $result->fetch_assoc()) {
        echo $row['id'].' '.$row['page_title'].' <br />';
    }
}

function EdytujPodstrone($mysqli, $id) {
    $id = intval($id);
    $stmt = $mysqli->prepare("SELECT * FROM page_list WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows == 0) {
        return "<p>Nie znaleziono podstrony o podanym ID.</p>";
    }

    $row = $result->fetch_assoc();
    $checked = ($row['status'] == 1) ? "checked" : "";

    $form = '
    <h2>Edytuj podstronę</h2>
    <form method="post" action="">
        <label>Tytuł:</label><br>
        <input type="text" name="tytul" value="'.htmlspecialchars($row['page_title']).'" style="width:300px"><br><br>

        <label>Treść strony:</label><br>
        <textarea name="tresc" rows="10" cols="60">'.htmlspecialchars($row['page_content']).'</textarea><br><br>

        <label>
            <input type="checkbox" name="aktywna" value="1" '.$checked.'>
            Strona aktywna
        </label><br><br>

        <input type="submit" name="zapisz_podstrone" value="Zapisz zmiany">
    </form>
    ';


    if (isset($_POST['zapisz_podstrone'])) {
        $tytul = $_POST['tytul'];
        $tresc = $_POST['tresc'];
        $aktywna = isset($_POST['status']) ? 1 : 0;

        $stmt_update = $mysqli->prepare("UPDATE page_list SET tytul=?, tresc=?, aktywna=? WHERE id=?");
        $stmt_update->bind_param("ssii", $tytul, $tresc, $aktywna, $id);
        if ($stmt_update->execute()) {
            echo "<p style='color:green'>Zmiany zapisane!</p>";
        } else {
            echo "<p style='color:red'>Błąd podczas zapisu.</p>";
        }
    }

    return $form;
}


function DodajNowaPodstrone($mysqli) {
    if (isset($_POST['dodaj_podstrone'])) {
        $tytul = $_POST['tytul'];
        $tresc = $_POST['tresc'];
        $aktywna = isset($_POST['aktywna']) ? 1 : 0;

        $stmt = $mysqli->prepare("INSERT INTO page_list (tytul, tresc, aktywna, data) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("ssi", $tytul, $tresc, $aktywna);

        if ($stmt->execute()) {
            echo "<p style='color:green;'>Podstrona została dodana poprawnie.</p>";
        } else {
            echo "<p style='color:red;'>Błąd podczas dodawania podstrony.</p>";
        }
    }

    $form = '
    <h2>Dodaj nową podstronę</h2>
    <form method="post" action="">
        <label>Tytuł:</label><br>
        <input type="text" name="tytul" value="" style="width:300px"><br><br>

        <label>Treść strony:</label><br>
        <textarea name="tresc" rows="10" cols="60"></textarea><br><br>

        <label>
            <input type="checkbox" name="aktywna" value="1">
            Strona aktywna
        </label><br><br>

        <input type="submit" name="dodaj_podstrone" value="Dodaj podstronę">
    </form>
    ';

    return $form;
}


function UsunPodstrone($mysqli, $id) {
    $id = intval($id);
    if ($id <= 0) {
        return "<p style='color:red;'>Nieprawidłowe ID podstrony.</p>";
    }

    $stmt = $mysqli->prepare("DELETE FROM page_list WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        return "<p style='color:green;'>Podstrona została usunięta.</p>";
    } else {
        return "<p style='color:red;'>Błąd podczas usuwania podstrony.</p>";
    }
}

function FormularzUsuwania($mysqli) {
    $wynik = '';
    if (isset($_POST['usun_podstrone_submit'])) {
        $id_do_usuniecia = intval($_POST['usun_id']);
        $wynik .= UsunPodstrone($mysqli, $id_do_usuniecia);
    }

    $wynik .= '
    <h2>Usuń podstronę</h2>
    <form method="post" action="">
        <label>Wpisz ID podstrony do usunięcia:</label><br>
        <input type="number" name="usun_id" style="width:100px" required>
        <input type="submit" name="usun_podstrone_submit" value="Usuń podstronę">
    </form>
    ';

    return $wynik;
}

echo "<h1>Panel CMS</h1>";
echo "<h2>Lista podstron:</h2>";
ListaPodstron($mysqli);

echo DodajNowaPodstrone($mysqli);
echo EdytujPodstrone($mysqli, 1);
echo FormularzUsuwania($mysqli);
?>