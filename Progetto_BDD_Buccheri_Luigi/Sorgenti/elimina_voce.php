<?php
session_start();
$connessione = new mysqli("localhost", "root", "", "progetto");


$cf_admin = $_SESSION['cf']; 

if (isset($_POST['nome'])) {
    $nome = $connessione->real_escape_string($_POST['nome']);

    $sql_find = "SELECT nome FROM VOCE_CONTABILE WHERE nome = '$nome'";
    $risultato = $connessione->query($sql_find);

    if ($risultato && $risultato->num_rows > 0) {
        $riga = $risultato->fetch_assoc();
        $nomevoc = $riga['nome'];

        $sql_delete_voce = "DELETE FROM VOCE_CONTABILE WHERE nome = '$nome'";
        
        try {

            if ($connessione->query($sql_delete_voce) === TRUE) {
                require_once 'logger.php';
                registraLog("Voce eliminata dal Template. Nome Voce: " . $nome . " - CF Admin: " . $cf_admin);
                echo "Voce eliminata con successo!";
            }
        } catch (mysqli_sql_exception $e) {

            if ($e->getCode() == 1451) {
                echo "Errore: Non puoi eliminare questa Voce Contabile perché è già stata utilizzata all'interno di uno o più Bilanci. Per mantenere lo storico, l'operazione è stata bloccata.";
            } else {
                echo "Errore durante l'eliminazione: " . $e->getMessage();
            }
        }
    } else {
        echo "Errore: Voce non trovata nel database.";
    }
}

$connessione->close();
?>