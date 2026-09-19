<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['cf'])) {
    die("Errore: Sessione mancante. Effettua il login come Revisore ESG.");
}

$connessione = new mysqli("localhost", "root", "", "progetto"); 
if ($connessione->connect_error) {
    die("Connessione fallita: " . $connessione->connect_error);
}

$cf_revisore = $_SESSION['cf'];

$id_bilancio = isset($_POST['id_bilancio']) ? intval($_POST['id_bilancio']) : 0;

$voci_contabili = isset($_POST['esg']) ? $_POST['esg'] : [];

if ($id_bilancio === 0) {
    die("Errore: Nessun bilancio selezionato.");
}

$successo = true;
$errori = "";

foreach ($voci_contabili as $nome_voce => $dati) {
    
    if (!empty(trim($dati['nota']))) {
        
        $data = $connessione->real_escape_string($dati['data']);
        $nota = $connessione->real_escape_string($dati['nota']);
        $nome_voce_pulito = $connessione->real_escape_string($nome_voce);
        
        $sql = "CALL CreaNota('$data', '$nota', '$cf_revisore', $id_bilancio, '$nome_voce_pulito')";
        
        $risultato = $connessione->query($sql);
        
        if ($risultato) {
            while($connessione->more_results() && $connessione->next_result()) {
                if ($res = $connessione->store_result()) {
                    $res->free();
                }
            }
        } else {
            $successo = false;
            $errori .= "- Errore su '$nome_voce_pulito': " . $connessione->error . "<br>";
        }
    }
}

if ($successo && empty($errori)) {

    require_once 'logger.php';
    registraLog("Aggiunte Note alle Voci di Bilancio. Id Bilancio: " . $id_bilancio . " - CF Revisore: " . $cf_revisore);

    header("Location: success_nota.html");
    exit();
} else {
    echo "<h3>Si sono verificati dei problemi:</h3>";
    echo $errori;
    echo "<br><a href='javascript:history.back()'>Torna indietro e correggi</a>";
}

$connessione->close();
?>