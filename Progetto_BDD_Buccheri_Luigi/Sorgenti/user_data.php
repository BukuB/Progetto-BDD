<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['utente_loggato']) && $_SESSION['utente_loggato'] === true) {
    
    $connessione = new mysqli("localhost", "root", "", "progetto");

    if ($connessione->connect_error) {
        echo json_encode(["loggato" => false, "errore" => "Connessione fallita"]);
        exit();
    }

    $username_loggato = $_SESSION['username'];

    $sql = "SELECT u.username, u.cf, u.data_nascita, u.luogo_nascita, u.ruolo_interno, u.professione, r.indirizzo_email
            FROM UTENTE u 
            JOIN RECAPITO_EMAIL r ON u.cf = r.cf 
            WHERE u.username = '$username_loggato'";
            
    $risultato = $connessione->query($sql);
    
    if ($risultato && $risultato->num_rows > 0) {

        $riga_utente = $risultato->fetch_assoc();

        $risposta = [
            "loggato" => true,
            "username" => $riga_utente['username'],
            "indirizzo_email" => $riga_utente['indirizzo_email'], 
            "cf" => $riga_utente['cf'], // Abbiamo corretto la colonna
            "data" => $riga_utente['data_nascita'],
            "luogo" => $riga_utente['luogo_nascita'],
            "ruolo_interno" => $riga_utente['ruolo_interno'],
            "professione" => $riga_utente["professione"]
        ];
    } else {
        $risposta = ["loggato" => false];
    }

    $connessione->close();

} else {
    $risposta = ["loggato" => false];
}

echo json_encode($risposta);
?>