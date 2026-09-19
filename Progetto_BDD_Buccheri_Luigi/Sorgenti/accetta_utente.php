<?php
session_start();
header('Content-Type: application/json');
$connessione = new mysqli("localhost", "root", "", "progetto");

$cf_admin = $_SESSION['cf']; 

if (isset($_POST['username']) && isset($_POST['professione'])) {
    
    $user = $_POST['username'];
        $professione = $_POST['professione'];

    $sql_find = "SELECT cf FROM UTENTE WHERE username = '$user'";
    $risultato = $connessione->query($sql_find);

    if ($risultato && $risultato->num_rows > 0) {
        $riga = $risultato->fetch_assoc();
        $cf_utente = $riga['cf'];

        $sql_update = "UPDATE UTENTE SET ruolo_interno = '$professione' WHERE username = '$user'";
        
        if ($connessione->query($sql_update) === TRUE) {
            
            if ($professione === 'revisore_esg') {
                $connessione->query("INSERT IGNORE INTO REVISORE_ESG (cf) VALUES ('$cf_utente')");
            } elseif ($professione === 'responsabile_aziendale') {
                $connessione->query("INSERT IGNORE INTO RESPONSABILE_AZIENDALE (cf, curriculum_vitae) VALUES ('$cf_utente', 'nessun_cv.pdf')");
            }

            require_once 'logger.php';
            registraLog("Utente approvato. Nome Utente: " . $user . " - Professione: " . $professione . " - CF Admin: " . $cf_admin);

            echo json_encode(["successo" => true, "messaggio" => "Utente accettato e smistato correttamente!"]);
        } else {
            echo json_encode(["successo" => false, "messaggio" => "Errore aggiornamento: " . $connessione->error]);
        }
    } else {
        echo json_encode(["successo" => false, "messaggio" => "Utente non trovato."]);
    }
} else {
    echo json_encode(["successo" => false, "messaggio" => "Dati mancanti dal form."]);
}

$connessione->close();
?>