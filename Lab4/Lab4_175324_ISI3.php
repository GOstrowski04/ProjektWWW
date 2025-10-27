<?php
    $nr_indeksu = '175324';
    $nrGrupy = 'ISI3';

    echo 'Gabriel Ostrowski ' .$nr_indeksu. ' grupa '.$nrGrupy.' <br /><br />';

    echo 'Zastosowanie metody include() <br />';
    include 'test1.php';
    echo 'Zmienna z pliku test1.php: ' .$test. '<br />';
    echo 'Zastosowanie metody require_once() <br /><br />';
    require_once 'test1.php';
    echo 'Zastosowanie if, elseif, else i switch <br />';
    $x = 3;
    $y = 6;
    $z = 9;
    if ($y == $z) {
        echo "$y jest rowne zmiennej $z.";
    } elseif ($x == $y) {
        echo "$y jest równe zmiennej $x.";
    } else {
        echo "$y nie jest równe innym zmiennym.";
    }
    echo '<br />';
    switch ($z) {
        case $y:
            echo "$z jest równe $y.";
            break;
        case $x:
            echo "$z jest równe $x.";
            break;
    }
    echo '<br />Zastosowanie pętli while() i for() <br />';
    while ($x < 5) {
        echo $x++;
        echo ' ';
    }
    echo '<br />';
    for ($i = 1; $i <= 10; $i++) {
        echo $i;
        echo ' ';
    }
    echo '<br />';
    echo 'Hello ' . htmlspecialchars($_GET["name"]) . '!';
    echo 'Hello ' . htmlspecialchars($_POST["name"]) . '!';
    session_start();
    $_SESSION['user'] = 'Gabriel';
    echo $_SESSION['user'];
?>