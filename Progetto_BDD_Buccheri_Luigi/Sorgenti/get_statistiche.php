<?php
header('Content-Type: application/json');

// Connessione al database
$connessione = new mysqli("localhost", "root", "", "progetto");
if ($connessione->connect_error) {
    die(json_encode(["error" => "Connessione fallita: " . $connessione->connect_error]));
}

// Struttura base dell'array
$dati = [
    "totale_aziende" => 0,
    "totale_revisori" => 0,
    "azienda_top" => ["nome" => "Dati insufficienti", "percentuale" => 0],
    "classifica" => []
];

// ---------------------------------------------------------
// 1. STATISTICA: Numero di aziende registrate
// ---------------------------------------------------------
$sql1 = "SELECT COUNT(*) AS totale FROM AZIENDA";
$res1 = $connessione->query($sql1);
if ($res1 && $row = $res1->fetch_assoc()) {
    $dati["totale_aziende"] = $row["totale"];
}

// ---------------------------------------------------------
// 2. STATISTICA: Numero di revisori ESG
// ---------------------------------------------------------
$sql2 = "SELECT COUNT(*) AS totale FROM REVISORE_ESG";
$res2 = $connessione->query($sql2);
if ($res2 && $row = $res2->fetch_assoc()) {
    $dati["totale_revisori"] = $row["totale"];
}

// ---------------------------------------------------------
// 3. STATISTICA: Azienda più affidabile
// ---------------------------------------------------------
$sql3 = "SELECT a.nome AS nome, 
         (SUM(CASE WHEN g.esito = 'approvazione' THEN 1 ELSE 0 END) / COUNT(b.id)) * 100 AS percentuale
         FROM AZIENDA a
         JOIN BILANCIO_DI_ESERCIZIO b ON a.partita_IVA = b.partita_IVA
         JOIN GIUDIZIO g ON b.id = g.id_bilancio
         GROUP BY a.partita_IVA, a.nome
         ORDER BY percentuale DESC 
         LIMIT 1";

$res3 = $connessione->query($sql3);
if ($res3 && $row = $res3->fetch_assoc()) {
    $dati["azienda_top"]["nome"] = $row["nome"];
    // Arrotonda a due decimali
    $dati["azienda_top"]["percentuale"] = round($row["percentuale"], 2); 
}

// ---------------------------------------------------------
// 4. STATISTICA: Classifica Bilanci per Indicatori ESG
// ---------------------------------------------------------
$sql4 = "SELECT b.id AS id_bilancio, a.nome AS nome_azienda, COUNT(i.nome_indicatore) AS totale_indicatori
         FROM BILANCIO_DI_ESERCIZIO b
         JOIN AZIENDA a ON b.partita_IVA = a.partita_IVA
         JOIN ASSOCIAZIONE_INDICATORE_ESG i ON b.id = i.id_bilancio
         GROUP BY b.id, a.nome
         ORDER BY totale_indicatori DESC";

$res4 = $connessione->query($sql4);
if ($res4) {
    while($row = $res4->fetch_assoc()) {
        $dati["classifica"][] = $row;
    }
}

// Stampa finale del pacchetto JSON
echo json_encode($dati);

$connessione->close();
?>