<?php
session_start();

if (!isset($_SESSION['cf'])) {
    die("Errore: Utente non loggato o sessione scaduta.");
}

$connessione = new mysqli("localhost", "root", "", "progetto");

if ($connessione->connect_error) {
    die("Connessione fallita: " . $connessione->connect_error);
}

$cf_responsabile = $_SESSION['cf']; 
$partita_iva = $_POST['partita_iva'];
$valori_voci = $_POST['voci'];

$query_max_id = "SELECT COALESCE(MAX(id), 0) + 1 AS nuovo_id FROM BILANCIO_DI_ESERCIZIO";
$risultato_id = $connessione->query($query_max_id);
$riga_id = $risultato_id->fetch_assoc();
$nuovo_id = $riga_id['nuovo_id'];

$data_odierna = date('Y-m-d');
$stato = 'bozza';

$sql_creazione = "CALL CreazioneBilancio($nuovo_id, '$data_odierna', '$stato', '$partita_iva', '$cf_responsabile')";
$risultato_creazione = $connessione->query($sql_creazione);

if ($risultato_creazione) {
    $riga_esito = $risultato_creazione->fetch_assoc();
    
    if (strpos($riga_esito['Esito'], 'Autorizzato') !== false) {
        
        while($connessione->next_result()) $connessione->store_result();

        foreach ($valori_voci as $nome_voce => $valore_numerico) {
            if ($valore_numerico != "") { 
                $valore_pulito = intval($valore_numerico);
                
                $sql_popolamento = "CALL PopolamentoBilancio('$nome_voce', $nuovo_id, $valore_pulito, '$cf_responsabile')";
                $connessione->query($sql_popolamento);
                
                while($connessione->next_result()) $connessione->store_result();
            }
        }
        
        require_once 'logger.php';
        registraLog("Nuovo Bilancio creato. Piva: " . $partita_iva . " - Id: " . $nuovo_id . " - CF Responsabile: " . $cf_responsabile);

        header("Location: creazione_bilancio_success.html");
        exit(); 
        
    } else {
        echo "<div class='alert alert-danger m-5'><strong>Errore dal DB:</strong> " . $riga_esito['Esito'] . "</div>";
    }
} else {
    echo "Errore imprevisto nella query: " . $connessione->error;
}

$connessione->close();
?>