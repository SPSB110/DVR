<?php

require_once "config.php";

// Ingresso sicuro: leggi e sanitizza i parametri GET
$FattoreEta = 0;
$FattoreAltezza = 0;
$FattoreDisclocazioneV = 0;
$FattoreDisclocazioneO = 0;
$FattoreDislocazioneAngolare = 0;
$FattoreGiudizioPresa = 0;
$FrequenzaGesti = 0;

// Leggi input
$sesso = filter_input(INPUT_GET, 'sesso', FILTER_SANITIZE_STRING);
$dataDiNascitaStr = filter_input(INPUT_GET, 'dataDiNascita', FILTER_SANITIZE_STRING);
$altezzaManiSoll = filter_input(INPUT_GET, 'altezzaManiSoll', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$altezzaManiVert = filter_input(INPUT_GET, 'altezzaManiVert', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$altezzaManiOr = filter_input(INPUT_GET, 'altezzaManiOr', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$DislocazioneAngolare = filter_input(INPUT_GET, 'DislocazioneAngolare', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$GiudizioPresa = filter_input(INPUT_GET, 'GiudizioPresa', FILTER_SANITIZE_STRING);
$FrequenzaGesti_in = filter_input(INPUT_GET, 'FrequenzaGesti', FILTER_SANITIZE_STRING);
$FrequenzaLavoro = filter_input(INPUT_GET, 'FrequenzaLavoro', FILTER_SANITIZE_STRING);
$PesoSollevato = filter_input(INPUT_GET, 'PesoSollevato', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

// Validazione minima
if (empty($dataDiNascitaStr) || empty($sesso) || $PesoSollevato === null || $PesoSollevato === false) {
    header('Location: index.php?error=missing_parameters');
    exit;
}

try {
    $dataDiNascita = new DateTime($dataDiNascitaStr);
} catch (Exception $e) {
    header('Location: index.php?error=invalid_date');
    exit;
}

$oggi = new DateTime();
$eta = $oggi->diff($dataDiNascita)->y;
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($eta > 18 && $_GET["sesso"] == "M") {
    $FattoreEta = 30;
}
elseif($eta > 18 && $_GET["sesso"] == "F"){
    $FattoreEta = 20;
}
elseif($eta <= 18 && $eta > 15 && $_GET["sesso"] == "M"){
    $FattoreEta = 20;
}
else{
    $FattoreEta = 15;
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($_GET["altezzaManiSoll"] > 0 && $_GET["altezzaManiSoll"] < 25) {

    $FattoreAltezza = 0.78;

}
elseif ($_GET["altezzaManiSoll"] >= 25 && $_GET["altezzaManiSoll"] < 50) {

    $FattoreAltezza = 0.85;

}
else if ($_GET["altezzaManiSoll"] >= 50 && $_GET["altezzaManiSoll"] < 75) {

    $FattoreAltezza = 0.93;

}
else if ($_GET["altezzaManiSoll"] >= 75 && $_GET["altezzaManiSoll"] < 100) {

    $FattoreAltezza = 1;

}
else if ($_GET["altezzaManiSoll"] >= 100 && $_GET["altezzaManiSoll"] < 125) {

    $FattoreAltezza = 0.93;

}
else if ($_GET["altezzaManiSoll"] >= 125 && $_GET["altezzaManiSoll"] < 150) {

    $FattoreAltezza = 0.85;

}
else if ($_GET["altezzaManiSoll"] >= 150 && $_GET["altezzaManiSoll"] < 175) {

    $FattoreAltezza = 0.78;

}
else {

    $FattoreAltezza = 0;

}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($_GET["altezzaManiVert"] > 25 && $_GET["altezzaManiVert"] < 30) {

    $FattoreDisclocazioneV = 1;

}
elseif ($_GET["altezzaManiVert"] >= 30 && $_GET["altezzaManiVert"] < 40) {

    $FattoreDisclocazioneV = 0.97;

}
else if ($_GET["altezzaManiVert"] >= 40 && $_GET["altezzaManiVert"] < 50) {

    $FattoreDisclocazioneV = 0.93;

}
else if ($_GET["altezzaManiVert"] >= 50 && $_GET["altezzaManiVert"] < 70) {

    $FattoreDisclocazioneV = 0.91;

}
else if ($_GET["altezzaManiVert"] >= 70 && $_GET["altezzaManiVert"] < 10) {

    $FattoreDisclocazioneV = 0.88;

}
else if ($_GET["altezzaManiVert"] >= 100 && $_GET["altezzaManiVert"] < 170) {

    $FattoreDisclocazioneV = 0.87;

}
else if ($_GET["altezzaManiVert"] >= 170 && $_GET["altezzaManiVert"] < 175) {

    $FattoreDisclocazioneV = 0.86;

}
else {

    $FattoreDisclocazioneV = 0;

}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($_GET["altezzaManiOr"] > 25 && $_GET["altezzaManiOr"] < 30) {

    $FattoreDisclocazioneO = 1;

}
elseif ($_GET["altezzaManiOr"] >= 30 && $_GET["altezzaManiOr"] < 40) {

    $FattoreDisclocazioneO = 0.83;

}
else if ($_GET["altezzaManiOr"] >= 40 && $_GET["altezzaManiOr"] < 50) {

    $FattoreDisclocazioneO = 0.63;

}
else if ($_GET["altezzaManiOr"] >= 50 && $_GET["altezzaManiOr"] < 55) {

    $FattoreDisclocazioneO = 0.50;

}
else if ($_GET["altezzaManiOr"] >= 55 && $_GET["altezzaManiOr"] < 60) {

    $FattoreDisclocazioneO = 0.45;

}
else if ($_GET["altezzaManiOr"] >= 60 && $_GET["altezzaManiOr"] < 63) {

    $FattoreDisclocazioneO = 0.42;

}
else {

    $FattoreDisclocazioneO = 0;

}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($_GET["DislocazioneAngolare"] > 0 && $_GET["DislocazioneAngolare"] < 30) {

    $FattoreDislocazioneAngolare = 1;

}
elseif ($_GET["DislocazioneAngolare"] > 30 && $_GET["DislocazioneAngolare"] < 60) {

    $FattoreDislocazioneAngolare = 0.90;

}
else if ($_GET["DislocazioneAngolare"] >= 60 && $_GET["DislocazioneAngolare"] < 90) {

    $FattoreDislocazioneAngolare = 0.81;

}
else if ($_GET["DislocazioneAngolare"] >= 90 && $_GET["DislocazioneAngolare"] < 120) {

    $FattoreDislocazioneAngolare = 0.71;

}
else if ($_GET["DislocazioneAngolare"] >= 120 && $_GET["DislocazioneAngolare"] < 135) {

    $FattoreDislocazioneAngolare = 0.62;

}
else if ($_GET["DislocazioneAngolare"] == 135) {

    $FattoreDislocazioneAngolare = 0.57;

}
else {

    $FattoreDislocazioneAngolare = 0;

}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($_GET["GiudizioPresa"] == "B") {

    $FattoreGiudizioPresa = 1;

}
else {
    $FattoreGiudizioPresa = 0.90;
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($FrequenzaGesti_in >= 0.20 && $FrequenzaGesti_in < 1){
    if($FrequenzaLavoro == "1"){
        $FrequenzaGesti = 1;
    }
    elseif($FrequenzaLavoro == "2"){
        $FrequenzaGesti = 0.95;
    }
    else{
        $FrequenzaGesti = 0.85;
    }
}
elseif($FrequenzaGesti_in >= 1 && $FrequenzaGesti_in < 4){
    if($FrequenzaLavoro == "1"){
        $FrequenzaGesti = 0.94;
    }
    elseif($FrequenzaLavoro == "2"){
        $FrequenzaGesti = 0.88;
    }
    else{
        $FrequenzaGesti = 0.75;
    }
}
elseif($FrequenzaGesti_in >= 4 && $FrequenzaGesti_in < 6){
    if($FrequenzaLavoro == "1"){
        $FrequenzaGesti = 0.84;
    }
    elseif($FrequenzaLavoro == "2"){
        $FrequenzaGesti = 0.72;
    }
    else{
        $FrequenzaGesti = 0.45;
    }
}
elseif($FrequenzaGesti_in >= 6 && $FrequenzaGesti_in < 9){
    if($FrequenzaLavoro == "1"){
        $FrequenzaGesti = 0.75;
    }
    elseif($FrequenzaLavoro == "2"){
        $FrequenzaGesti = 0.50;
    }
    else{
        $FrequenzaGesti = 0.27;
    }
}
elseif($FrequenzaGesti_in >= 9 && $FrequenzaGesti_in < 12){
    if($FrequenzaLavoro == "1"){
        $FrequenzaGesti = 0.52;
    }
    elseif($FrequenzaLavoro == "2"){
        $FrequenzaGesti = 0.30;
    }
    else{
        $FrequenzaGesti = 0.15;
    }
}
elseif($FrequenzaGesti_in >= 12){
    if($FrequenzaLavoro == "1"){
        $FrequenzaGesti = 0.37;
    }
    elseif($FrequenzaLavoro == "2"){
        $FrequenzaGesti = 0.21;
    }
    else{
        $FrequenzaGesti = 0;
    }
}
else{
    $FrequenzaGesti = 0;
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Usa le variabili sanitize al posto di $_GET

if($eta > 18 && $sesso == "M") {
    $FattoreEta = 30;
}
elseif($eta > 18 && $sesso == "F"){
    $FattoreEta = 20;
}
elseif($eta <= 18 && $eta > 15 && $sesso == "M"){
    $FattoreEta = 20;
}
else{
    $FattoreEta = 15;
}

if ($altezzaManiSoll > 0 && $altezzaManiSoll < 25) {

    $FattoreAltezza = 0.78;

}
elseif ($altezzaManiSoll => 25 && $altezzaManiSoll < 50) {

    $FattoreAltezza = 0.85;

}
else if ($altezzaManiSoll >= 50 && $altezzaManiSoll < 75) {

    $FattoreAltezza = 0.93;

}
else if ($altezzaManiSoll >= 75 && $altezzaManiSoll < 100) {

    $FattoreAltezza = 1;

}
else if ($altezzaManiSoll >= 100 && $altezzaManiSoll < 125) {

    $FattoreAltezza = 0.93;

}
else if ($altezzaManiSoll >= 125 && $altezzaManiSoll < 150) {

    $FattoreAltezza = 0.85;

}
else if ($altezzaManiSoll >= 150 && $altezzaManiSoll < 175) {

    $FattoreAltezza = 0.78;

}
else {

    $FattoreAltezza = 0;

}

if ($altezzaManiVert > 25 && $altezzaManiVert < 30) {

    $FattoreDisclocazioneV = 1;

}
elseif ($altezzaManiVert >= 30 && $altezzaManiVert < 40) {

    $FattoreDisclocazioneV = 0.97;

}
else if ($altezzaManiVert >= 40 && $altezzaManiVert < 50) {

    $FattoreDisclocazioneV = 0.93;

}
else if ($altezzaManiVert >= 50 && $altezzaManiVert < 70) {

    $FattoreDisclocazioneV = 0.91;

}
else if ($altezzaManiVert >= 70 && $altezzaManiVert < 100) {

    $FattoreDisclocazioneV = 0.88;

}
else if ($altezzaManiVert >= 100 && $altezzaManiVert < 170) {

    $FattoreDisclocazioneV = 0.87;

}
else if ($altezzaManiVert >= 170 && $altezzaManiVert < 175) {

    $FattoreDisclocazioneV = 0.86;

}
else {

    $FattoreDisclocazioneV = 0;

}

if ($altezzaManiOr > 25 && $altezzaManiOr < 30) {

    $FattoreDisclocazioneO = 1;

}
elseif ($altezzaManiOr >= 30 && $altezzaManiOr < 40) {

    $FattoreDisclocazioneO = 0.83;

}
else if ($altezzaManiOr >= 40 && $altezzaManiOr < 50) {

    $FattoreDisclocazioneO = 0.63;

}
else if ($altezzaManiOr >= 50 && $altezzaManiOr < 55) {

    $FattoreDisclocazioneO = 0.50;

}
else if ($altezzaManiOr >= 55 && $altezzaManiOr < 60) {

    $FattoreDisclocazioneO = 0.45;

}
else if ($altezzaManiOr >= 60 && $altezzaManiOr < 63) {

    $FattoreDisclocazioneO = 0.42;

}
else {

    $FattoreDisclocazioneO = 0;

}

if ($DislocazioneAngolare > 0 && $DislocazioneAngolare < 30) {

    $FattoreDislocazioneAngolare = 1;

}
elseif ($DislocazioneAngolare >= 30 && $DislocazioneAngolare < 60) {

    $FattoreDislocazioneAngolare = 0.90;

}
else if ($DislocazioneAngolare >= 60 && $DislocazioneAngolare < 90) {

    $FattoreDislocazioneAngolare = 0.81;

}
else if ($DislocazioneAngolare >= 90 && $DislocazioneAngolare < 120) {

    $FattoreDislocazioneAngolare = 0.71;

}
else if ($DislocazioneAngolare >= 120 && $DislocazioneAngolare < 135) {

    $FattoreDislocazioneAngolare = 0.62;

}
else if ($DislocazioneAngolare == 135) {

    $FattoreDislocazioneAngolare = 0.57;

}
else {

    $FattoreDislococazioneAngolare = 0;

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($GiudizioPresa == "B") {

    $FattoreGiudizioPresa = 1;

}
else {
    $FattoreGiudizioPresa = 0.90;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($FrequenzaGesti_in >= 0.20 && $FrequenzaGesti_in < 1){
    if($FrequenzaLavoro ==  1 ){
        $FrequenzaGesti = 1;
    }
    elseif($FrequenzaLavoro == 2){
        $FrequenzaGesti = 0.95;
    }
    else{
        $FrequenzaGesti = 0.85;
    }

}
elseif($FrequenzaGesti_in >=  1  && $FrequenzaGesti_in <  4 ){
    if($FrequenzaLavoro ==  1 ){
        $FrequenzaGesti = 0.94;
    }
    elseif($FrequenzaLavoro ==  2 ){
        $FrequenzaGesti = 0.88;
    }
    else{
        $FrequenzaGesti = 0.75;
    }
}

else if($FrequenzaGesti_in >=  4  && $FrequenzaGesti_in <  6 ){
    if($FrequenzaLavoro ==  1 ){
        $FrequenzaGesti = 0.84;
    }
    elseif($FrequenzaLavoro ==  2 ){
        $FrequenzaGesti = 0.72;
    }
    else{
        $FrequenzaGesti = 0.45;
    }
}

else if($FrequenzaGesti_in >=  6  && $FrequenzaGesti_in <  9 ){
    if($FrequenzaLavoro ==  1 ){
        $FrequenzaGesti = 0.75;
    }
    elseif($FrequenzaLavoro ==  2 ){
        $FrequenzaGesti = 0.50;
    }
    else{
        $FrequenzaGesti = 0.27;
    }
}

else if($FrequenzaGesti_in >=  9  && $FrequenzaGesti_in <  12 ){
    if($FrequenzaLavoro ==  1 ){
        $FrequenzaGesti = 0.52;
    }
    elseif($FrequenzaLavoro ==  2 ){
        $FrequenzaGesti = 0.30;
    }
    else{
        $FrequenzaGesti = 0.15;
    }
}

else if($FrequenzaGesti_in >=  12 ){
    if($FrequenzaLavoro ==  1 ){
        $FrequenzaGesti = 0.37;
    }
    elseif($FrequenzaLavoro ==  2 ){
        $FrequenzaGesti = 0.21;
    }
    else{
        $FrequenzaGesti = 0;
    }
}

else{
    $FrequenzaGesti = 0;
}

// Previeni divisione per zero
$den = $FattoreEta * $FattoreAltezza * $FattoreDisclocazioneV * $FattoreDisclocazioneO * $FattoreDislocazioneAngolare * $FattoreGiudizioPresa * $FrequenzaGesti;
if (!is_numeric($PesoSollevato) || $den <= 0) {
    header('Location: index.php?error=invalid_input');
    exit;
}

$IndiceSollevamento = $PesoSollevato / $den;

//fai ritornare ad index.php con i risultati
header("Location: index.php?IndiceSollevamento=" . $IndiceSollevamento);

?>

