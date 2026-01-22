<?php
/// Funkcja służąca do wyświetlania podstron z bazy danych
function PokazPodstrone($alias, $link)
{
    $alias_clear = mysqli_real_escape_string($link, $alias);

    $query = "SELECT * FROM page_list WHERE alias='$alias_clear' LIMIT 1";
    $result = mysqli_query($link, $query);

    if (!$result) return '[BŁĄD BAZY DANYCH]';

    $row = mysqli_fetch_array($result);

    if (empty($row['id'])) {
        return '[nie_znaleziono_strony]';
    } else {
        return $row['page_content'];
    }
}

?>