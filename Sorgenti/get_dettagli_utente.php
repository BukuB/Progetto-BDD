<?php
header('Content-Type: application/json');
$connessione = new mysqli("localhost", "root", "", "progetto");

if (isset($_POST['username'])) {

    $user_da_cercare = $_POST['username']; 

    $sql = "SELECT u.username, u.cf, u.data_nascita, u.luogo_nascita, u.ruolo_interno, u.professione, r.indirizzo_email 
            FROM UTENTE u 
            JOIN RECAPITO_EMAIL r ON u.cf = r.cf 
            WHERE u.username = '$user_da_cercare'";

    $risultato = $connessione->query($sql);

    if ($risultato && $risultato->num_rows > 0) {
        $riga = $risultato->fetch_assoc();
        $riga['successo'] = true; 
        echo json_encode($riga);
    } else {
        echo json_encode(["successo" => false, "messaggio" => "Utente $user_da_cercare non trovato."]);
    }
} else {
    echo json_encode(["successo" => false, "messaggio" => "Errore: Username non ricevuto dal PHP."]);
}
$connessione->close();
?>