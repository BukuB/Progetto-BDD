<?php
header('Content-Type: application/json');
$connessione = new mysqli("localhost", "root", "", "progetto");

$sql = "SELECT u.username, u.ruolo_interno, u.professione, r.indirizzo_email 
        FROM UTENTE u 
        JOIN RECAPITO_EMAIL r ON u.cf = r.cf 
        WHERE u.ruolo_interno = 'revisore_esg' OR u.ruolo_interno = 'responsabile_aziendale'";
$risultato = $connessione->query($sql);

$utenti = [];
while($riga = $risultato->fetch_assoc()) {
    $utenti[] = $riga;
}

echo json_encode($utenti);
$connessione->close();
?>