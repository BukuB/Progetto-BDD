<?php
session_start();
$connessione = new mysqli("localhost", "root", "", "progetto");

if (!isset($_SESSION['cf'])) {
    die("Errore: Utente non loggato o sessione scaduta.");
}
$cf_admin = $_SESSION['cf']; 

if (isset($_POST['username'])) {
    $user = $connessione->real_escape_string($_POST['username']);

    $sql_find = "SELECT cf FROM UTENTE WHERE username = '$user'";
    $risultato = $connessione->query($sql_find);

    if ($risultato && $risultato->num_rows > 0) {
        $riga = $risultato->fetch_assoc();
        $cf_utente = $riga['cf'];

        $connessione->begin_transaction();

        try {
            $connessione->query("DELETE FROM RECAPITO_EMAIL WHERE cf = '$cf_utente'");
            $connessione->query("DELETE FROM AMMINISTRATORE WHERE cf = '$cf_utente'");
            $connessione->query("DELETE FROM REVISORE_ESG WHERE cf = '$cf_utente'");
            $connessione->query("DELETE FROM RESPONSABILE_AZIENDALE WHERE cf = '$cf_utente'");
            $connessione->query("DELETE FROM UTENTE WHERE username = '$user'");
            
            $connessione->commit();

            require_once 'logger.php';
            registraLog("Utente Eliminato. Nome Utente: " . $user . " - CF Admin: " . $cf_admin);
            echo "Utente eliminato con successo!";
            
        } catch (mysqli_sql_exception $e) {

            $connessione->rollback(); 
            
            if ($e->getCode() == 1451) {
                echo "Impossibile eliminare l'utente: ha già registrato aziende, bilanci o giudizi nel sistema. L'operazione è bloccata per mantenere lo storico.";
            } else {
                echo "Errore imprevisto durante l'eliminazione: " . $e->getMessage();
            }
        }
    } else {
        echo "Errore: Utente non trovato nel database.";
    }
}

$connessione->close();
?>