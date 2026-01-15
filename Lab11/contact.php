<?php
/// Funkcja wyświetlająca formularz kontaktowy.
function PokazKontakt(){
    $formularz = '
    <h2>Formularz kontaktowy</h2>

    <form method="post" action="'.$_SERVER['REQUEST_URI'].'">
        <label>Twój email:<br>
            <input type="email" name="email" required>
        </label><br><br>

        <label>Temat wiadomości:<br>
            <input type="text" name="temat" required>
        </label><br><br>

        <label>Treść wiadomości:<br>
            <textarea name="tresc" rows="6" cols="50" required></textarea>
        </label><br><br>

        <input type="submit" name="wyslij_kontakt" value="Wyślij">
    </form>
    ';

    return $formularz;
}
/// Funkcja symulująca wysłanie maila, zamiast wysyłania zapisuje informacje i treść do pliku tekstowego.
function WyslijMailKontakt($odbiorca){
if(empty($_POST['temat']) || empty($_POST['tresc']) || empty($_POST['email']))
{
    echo '[nie_wypelniles_pola]';
    echo PokazKontakt();
}
else
{
    $mail['subject'] = $_POST['temat'];
    $mail['body'] = $_POST['tresc'];
    $mail['sender'] = $_POST['email'];
    $mail['reciptient'] = $odbiorca;

    $header = "From: Formularz kontaktowy <".$mail['sender'].">\n";
    $header .= "MIME-Version: 1.0\nContent-Type: text/plain; charset=utf-8\nContent-Transfer-Encoding:\n";
    $header .= "X-Sender: <".$mail['sender'].">\n";
    $header .= "X-Mailer: PRapWWW mail 1.2\n";
    $header .= "X-Priority: 3\n";
    $header .= "Return-Path: <".$mail['sender'].">\n";
    /// Zapisanie do pliku mail_log.txt
    file_put_contents("mail_log.txt",
    "OD: ".$mail['sender']."\n".
    "DO: ".$mail['reciptient']."\n".
    "TEMAT: ".$mail['subject']."\n".
    "TRESC:\n".$mail['body']."\n\n",
    FILE_APPEND
);
    echo '[wiadomosc_wyslana]';
}
}

/// Funkcja wysyłająca hasło na podany adres email.
function PrzypomnijHaslo(){
    include_once "cfg.php";
    $odbiorca = "admin@gmail.com";

    $_POST['temat'] = "Przypomnienie hasła do panelu administracyjnego";
    $_POST['tresc'] = "Twoje dane logowania:\nLogin: $login\nHasło: $pass";
    $_POST['email'] = "system@twojastrona.pl";
    WyslijMailKontakt($odbiorca);

    return '<p style="color:green">Hasło zostało wysłane na adres administratora.</p>';
}

if (isset($_POST['wyslij_kontakt'])) {
    WyslijMailKontakt("admin@gmail.com"); 
} else {
    echo PokazKontakt();
}
?>