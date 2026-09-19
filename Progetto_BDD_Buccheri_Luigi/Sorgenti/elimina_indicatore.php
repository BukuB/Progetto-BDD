<?php
session_start(); 

if (!isset($_SESSION['cf'])) {
    die("Errore: Utente non loggato o sessione scaduta.");
}

$connessione = new mysqli("localhost", "root", "", "progetto");

$cf_admin = $_SESSION['cf'];

if (isset($_POST['nome'])) {

    $nome = $connessione->real_escape_string($_POST['nome']);

    $sql_find = "SELECT nome FROM INDICATORE_ESG WHERE nome = '$nome'";
    $risultato = $connessione->query($sql_find);

    if ($risultato && $risultato->num_rows > 0) {
        
        $connessione->query("DELETE FROM AMBIENTALI WHERE nome_indicatore = '$nome'");
        $connessione->query("DELETE FROM SOCIALI WHERE nome_indicatore = '$nome'");

        $sql_delete_voce = "DELETE FROM INDICATORE_ESG WHERE nome = '$nome'";
        
        try {
            if ($connessione->query($sql_delete_voce) === TRUE) {
                require_once 'logger.php';
                registraLog("Indicatore Eliminato. Nome Indicatore: " . $nome . " - CF Admin: " . $cf_admin);
                echo "Indicatore eliminato con successo!";
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1451) {
                echo "Errore: Non puoi eliminare questo Indicatore perché è già stato utilizzato all'interno di uno o più Bilanci. Operazione bloccata per mantenere lo storico.";
            } else {
                echo "Errore durante l'eliminazione: " . $e->getMessage();
            }
        }
    } else {
        echo "Errore: Indicatore non trovato nel database.";
    }
}

$connessione->close();
?>