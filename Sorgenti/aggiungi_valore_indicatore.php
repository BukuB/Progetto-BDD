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
$id_bilancio = intval($_POST['id_bilancio']); 
$voci_esg = $_POST['esg']; 

$successo = true;
$errori = "";

foreach ($voci_esg as $nome_voce => $dati) {
    
    if (!empty($dati['indicatore'])) {

        $indicatore = $connessione->real_escape_string($dati['indicatore']);
        $valore = !empty($dati['valore']) ? intval($dati['valore']) : 0; 
        $fonte = $connessione->real_escape_string($dati['fonte']);
        $data = $connessione->real_escape_string($dati['data']);
        
        $sql = "CALL AssociamentoIndicatoriEBilanci($id_bilancio, '$nome_voce', '$indicatore', $valore, '$fonte', '$data', '$cf_responsabile')";
        $risultato = $connessione->query($sql);
        
        if ($risultato) {
            $riga_esito = $risultato->fetch_assoc();

            if (strpos($riga_esito['Esito'], 'Autorizzato') === false) {
                $successo = false;
                $errori .= "- Voce '$nome_voce': " . $riga_esito['Esito'] . "<br>";
            }

            while($connessione->next_result()) $connessione->store_result();
        } else {
            $successo = false;
            $errori .= "- Errore SQL su '$nome_voce': " . $connessione->error . "<br>";
        }
    }
}

if ($successo && empty($errori)) {

    require_once 'logger.php';
    registraLog("Aggiunti Valori agli Indicatori ESG. P.IVA: " . $partita_iva . " - ID Bilancio: " . $id_bilancio . " - CF Responsabile: " . $cf_responsabile);
    header("Location: succes_associazione_indicatori.html");
    exit(); 
} else {
    echo "<h3>Si sono verificati dei problemi:</h3>";
    echo $errori;
    echo "<br><p>Controlla che l'ID Bilancio e la Partita IVA siano corretti.</p>";
    echo "<a href='javascript:history.back()'>Torna al modulo e correggi</a>";
}

$connessione->close();
?>