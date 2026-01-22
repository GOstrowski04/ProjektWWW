<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/includes/PHPMailer/src/Exception.php';
require_once __DIR__ . '/includes/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/includes/PHPMailer/src/SMTP.php';
/// Funkcja wyświetlająca formularz kontaktowy.
include_once "cfg.php";
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
// funkcja wysyłająca mail
function WyslijMailKontakt($odbiorca, $temat, $tresc) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'gabrielostrowski2004@gmail.com';
        $mail->Password   = 'ducwrftnulsdjpyf';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('gabrielostrowski2004@gmail.com', 'Formularz kontaktowy');
        $mail->addAddress($odbiorca);

        $mail->isHTML(false);
        $mail->Subject = $temat;
        $mail->Body    = $tresc;

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}

/// Funkcja wysyłająca hasło na podany adres email.
function PrzypomnijHaslo($login, $pass){
    $odbiorca = "gabrielOstrowski2004@gmail.com";
    $_POST['temat'] = "Przypomnienie hasła do panelu administracyjnego";
    $_POST['tresc'] = "Twoje dane logowania:\nLogin: $login\nHasło: $pass";
    $_POST['email'] = "system@twojastrona.pl";
    WyslijMailKontakt(
                'gabrielostrowski2004@gmail.com',
                $_POST['temat'],
                $_POST['tresc'],
                $_POST['email']
            );

    return '<p style="color:green">Hasło zostało wysłane na adres administratora.</p>';
}
