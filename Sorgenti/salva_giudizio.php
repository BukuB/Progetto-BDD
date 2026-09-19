<?php
session_start();

if (!isset($_SESSION['cf'])) {
    die("Errore: Sessione scaduta.");
}

$connessione = new mysqli("localhost", "root", "", "progetto");
$cf_revisore = $_SESSION['cf'];

$id_bilancio = intval($_POST['id_bilancio']);
$esito = $connessione->real_escape_string($_POST['esito']);
$data = $connessione->real_escape_string($_POST['data']);
$rilievi = $connessione->real_escape_string($_POST['rilievi']);

// ==========================================
// CONTROLLO DUPLICATI CON POP-UP
// ==========================================
$sql_check = "SELECT id_bilancio FROM GIUDIZIO WHERE id_bilancio = $id_bilancio";
$risultato_check = $connessione->query($sql_check);

if ($risultato_check && $risultato_check->num_rows > 0) {
    // Stampiamo il codice JavaScript per il pop-up e il ritorno alla pagina precedente
    echo "<script>
            alert('Attenzione: Hai già espresso un giudizio per questo bilancio!');
            window.history.back();
          </script>";
    exit(); // FONDAMENTALE: ferma lo script e impedisce l'inserimento del duplicato!
}

// Se non ci sono duplicati, procediamo con la creazione
$sql = "CALL CreaGiudizio('$esito', '$data', '$rilievi', '$cf_revisore', $id_bilancio)";
$risultato = $connessione->query($sql);

if ($risultato) {
    $riga = $risultato->fetch_assoc();
    
    while($connessione->next_result()) $connessione->store_result();

    if (strpos($riga['Esito'], 'Autorizzato') !== false) {

        $sql_update_stato = "UPDATE BILANCIO_DI_ESERCIZIO SET stato = 'valutato' WHERE id = $id_bilancio";
        $connessione->query($sql_update_stato);

        require_once 'logger.php';
        registraLog("Aggiunto Giudizio al Bilancio. Id Bilancio: " . $id_bilancio . " - CF Revisore: " . $cf_revisore);

        $connessione->close(); 

        header("Location: success_giudizio.html");
        exit();
    } else {
        echo "Errore: " . $riga['Esito'];
    }
} else {
    echo "Errore SQL: " . $connessione->error;
}

$connessione->close();
?>