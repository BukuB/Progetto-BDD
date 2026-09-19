<?php
header('Content-Type: application/json');
$connessione = new mysqli("localhost", "root", "", "progetto");

$sql = "SELECT u.cf, u.username, u.ruolo_interno, u.professione, r.indirizzo_email 
        FROM UTENTE u 
        JOIN RECAPITO_EMAIL r ON u.cf = r.cf 
        WHERE u.ruolo_interno = 'revisore_esg'";
$risultato = $connessione->query($sql);

$revisori = [];
while($riga = $risultato->fetch_assoc()) {
    $revisori[] = $riga;
}

echo json_encode($revisori);
$connessione->close();
?>