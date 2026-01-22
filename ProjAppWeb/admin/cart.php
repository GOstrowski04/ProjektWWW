<?php


    // zapisanie w tablicy sesji danych produktow
    class Cart {
    public function addToCart($id_prod, $ile_sztuk, $wielkosc) {
        if (!isset($_SESSION['count']))
        {
            $_SESSION['count'] = 1;
        } else {
            $_SESSION['count']++;}
        $nr = $_SESSION['count'];
        $data = time();
        $_SESSION[$nr.'_0'] = $nr;
        $_SESSION[$nr.'_1'] = $id_prod;
        $_SESSION[$nr.'_2'] = $ile_sztuk;
        $_SESSION[$nr.'_3'] = $wielkosc;
        $_SESSION[$nr.'_4'] = $data;
    }
    // usuwanie z tablicji sesji danego produktu
    public function removeFromCart($nr) {
        if (!isset($_SESSION['count'])) {
            echo '<p>Koszyk jest pusty.</p>';
        }
        elseif ($nr > $_SESSION['count'] or $nr <= 0) {
            echo '<p>Zły numer elementu koszyka.</p>';
        }
        else {
            unset($_SESSION[$nr.'_0']);
            unset($_SESSION[$nr.'_1']);
            unset($_SESSION[$nr.'_2']);
            unset($_SESSION[$nr.'_3']);
            unset($_SESSION[$nr.'_4']);
        }
    }
    // edytowanie ilości produktu w tablicji sesji
    public function editCount($nr, $new_count) {
        if (isset($_SESSION[$nr.'_2'])) 
            {
                $_SESSION[$nr.'_2'] = $new_count;
            }
    }
    // pokazywanie zawartości koszyka
    public function showCart($link) {
        if (!isset($_SESSION['count']) || $_SESSION['count'] <= 0) {
        echo '<p>Koszyk jest pusty.</p>';
        return;
    }
        echo '<table border="1" cellpadding="5">';
        echo '<tr><th>Nr</th><th>Nazwa</th><th>ID Produktu</th><th>Ilość</th><th>Wielkość</th><th>Data dodania</th><th>Akcje</th></tr>';


        for ($i = 1; $i <= $_SESSION['count']; $i++) {
            if (!isset($_SESSION[$i.'_1'])) continue;

            $stmt = $link->prepare("SELECT title FROM product_list WHERE id=?");
            $stmt->bind_param("i", $_SESSION[$i.'_1']);
            $stmt->execute();
            $res = $stmt->get_result();
            $name = ($res && $res->num_rows > 0) ? $res->fetch_assoc()['title'] : '-';

            echo '<tr>';
            echo '<td>'.$_SESSION[$i.'_0'].'</td>';
            echo '<td>'.htmlspecialchars($name).'</td>';
            echo '<td>'.$_SESSION[$i.'_1'].'</td>';
            echo '<td>'.$_SESSION[$i.'_2'].'</td>';
            echo '<td>'.htmlspecialchars($_SESSION[$i.'_3']).'</td>';
            echo '<td>'.date('Y-m-d H:i:s', $_SESSION[$i.'_4']).'</td>';
            echo '<td> <form method="post" style="margin:0;">
                        <input type="hidden" name="nr" value="'.$i.'">
                        <input type="submit" name="remove_from_cart" value="Usuń">
                    </form> </td>';
            echo '</tr>';
        }

        echo '</table>';
    }
    }
?>