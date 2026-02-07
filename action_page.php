<?php

require_once "config.php";

$FattoreEta = 0;
$FattoreAltezza = 0;
$FattoreDisclocazioneV = 0;
$FattoreDisclocazioneO = 0;
$FattoreDislocazioneAngolare = 0;
$FattoreGiudizioPresa = 0;
$FrequenzaGesti = 0;

// calcola eta data di nascita
$dataDiNascita = new DateTime($_GET["dataDiNascita"]);
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
if ($_GET["altezzaManiSoll"] > 0 && $_GET["altezzaManiSoll"] < 24) {

    $FattoreAltezza = 0.78;

}
elseif ($_GET["altezzaManiSoll"] > 25 && $_GET["altezzaManiSoll"] < 49) {

    $FattoreAltezza = 0.85;

}
else if ($_GET["altezzaManiSoll"] >= 50 && $_GET["altezzaManiSoll"] < 74) {

    $FattoreAltezza = 0.93;

}
else if ($_GET["altezzaManiSoll"] >= 75 && $_GET["altezzaManiSoll"] < 99) {

    $FattoreAltezza = 1;

}
else if ($_GET["altezzaManiSoll"] >= 100 && $_GET["altezzaManiSoll"] < 124) {

    $FattoreAltezza = 0.93;

}
else if ($_GET["altezzaManiSoll"] >= 125 && $_GET["altezzaManiSoll"] < 149) {

    $FattoreAltezza = 0.85;

}
else if ($_GET["altezzaManiSoll"] >= 150 && $_GET["altezzaManiSoll"] < 174) {

    $FattoreAltezza = 0.78;

}
else {

    $FattoreAltezza = 0;

}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($_GET["altezzaManiVert"] > 25 && $_GET["altezzaManiVert"] < 29) {

    $FattoreDisclocazioneV = 1;

}
elseif ($_GET["altezzaManiVert"] > 30 && $_GET["altezzaManiVert"] < 39) {

    $FattoreDisclocazioneV = 0.97;

}
else if ($_GET["altezzaManiVert"] >= 40 && $_GET["altezzaManiVert"] < 49) {

    $FattoreDisclocazioneV = 0.93;

}
else if ($_GET["altezzaManiVert"] >= 50 && $_GET["altezzaManiVert"] < 69) {

    $FattoreDisclocazioneV = 0.91;

}
else if ($_GET["altezzaManiVert"] >= 70 && $_GET["altezzaManiVert"] < 99) {

    $FattoreDisclocazioneV = 0.88;

}
else if ($_GET["altezzaManiVert"] >= 100 && $_GET["altezzaManiVert"] < 169) {

    $FattoreDisclocazioneV = 0.87;

}
else if ($_GET["altezzaManiVert"] >= 170 && $_GET["altezzaManiVert"] < 174) {

    $FattoreDisclocazioneV = 0.86;

}
else {

    $FattoreDisclocazioneV = 0;

}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($_GET["altezzaManiOr"] > 25 && $_GET["altezzaManiOr"] < 29) {

    $FattoreDisclocazioneO = 1;

}
elseif ($_GET["altezzaManiOr"] > 30 && $_GET["altezzaManiOr"] < 39) {

    $FattoreDisclocazioneO = 0.83;

}
else if ($_GET["altezzaManiOr"] >= 40 && $_GET["altezzaManiOr"] < 49) {

    $FattoreDisclocazioneO = 0.63;

}
else if ($_GET["altezzaManiOr"] >= 50 && $_GET["altezzaManiOr"] < 54) {

    $FattoreDisclocazioneO = 0.50;

}
else if ($_GET["altezzaManiOr"] >= 55 && $_GET["altezzaManiOr"] < 59) {

    $FattoreDisclocazioneO = 0.45;

}
else if ($_GET["altezzaManiOr"] >= 60 && $_GET["altezzaManiOr"] < 62) {

    $FattoreDisclocazioneO = 0.42;

}
else {

    $FattoreDisclocazioneO = 0;

}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($_GET["DislocazioneAngolare"] > 0 && $_GET["DislocazioneAngolare"] < 29) {

    $FattoreDislocazioneAngolare = 1;

}
elseif ($_GET["DislocazioneAngolare"] > 30 && $_GET["DislocazioneAngolare"] < 59) {

    $FattoreDislocazioneAngolare = 0.90;

}
else if ($_GET["DislocazioneAngolare"] >= 60 && $_GET["DislocazioneAngolare"] < 89) {

    $FattoreDislocazioneAngolare = 0.81;

}
else if ($_GET["DislocazioneAngolare"] >= 90 && $_GET["DislocazioneAngolare"] < 119) {

    $FattoreDislocazioneAngolare = 0.71;

}
else if ($_GET["DislocazioneAngolare"] >= 120 && $_GET["DislocazioneAngolare"] < 134) {

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

switch ($_GET["FrequenzaGesti"]) {
    case "0.20":
        if($_GET["FrequenzaLavoro"] == "1"){
            $FrequenzaGesti = 1;
        }
        elseif($_GET["FrequenzaLavoro"] == "2"){
            $FrequenzaGesti = 0.95;
        }
        else{
            $FrequenzaGesti = 0.85;
        }
        break;
    case "1":
        if($_GET["FrequenzaLavoro"] == "1"){
            $FrequenzaGesti = 0.94;
        }
        elseif($_GET["FrequenzaLavoro"] == "2"){
            $FrequenzaGesti = 0.88;
        }
        else{
            $FrequenzaGesti = 0.75;
        }
        break;
    case "4":
        if($_GET["FrequenzaLavoro"] == "1"){
            $FrequenzaGesti = 0.84;
        }
        elseif($_GET["FrequenzaLavoro"] == "2"){
            $FrequenzaGesti = 0.72;
        }
        else{
            $FrequenzaGesti = 0.45;
        }
        break;
    case "6":
        if($_GET["FrequenzaLavoro"] == "1"){
            $FrequenzaGesti = 0.75;
        }
        elseif($_GET["FrequenzaLavoro"] == "2"){
            $FrequenzaGesti = 0.50;
        }
        else{
            $FrequenzaGesti = 0.27;
        }
        break;
    case "9":
        if($_GET["FrequenzaLavoro"] == "1"){
            $FrequenzaGesti = 0.52;
        }
        elseif($_GET["FrequenzaLavoro"] == "2"){
            $FrequenzaGesti = 0.30;
        }
        else{
            $FrequenzaGesti = 0.15;
        }
        break;
    case "12":
        if($_GET["FrequenzaLavoro"] == "1"){
            $FrequenzaGesti = 0.37;
        }
        elseif($_GET["FrequenzaLavoro"] == "2"){
            $FrequenzaGesti = 0.21;
        }
        else{
            $FrequenzaGesti = 0;
        }
        break;
    default:
        $FrequenzaGesti = 0;

    }
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$IndiceSollevamento = $_GET["PesoSollevato"] / ($FattoreEta * $FattoreAltezza * $FattoreDisclocazioneV * $FattoreDisclocazioneO * $FattoreDislocazioneAngolare * $FattoreGiudizioPresa * $FrequenzaGesti);

//fai ritornare ad index.php con i risultati
header("Location: index.php?IndiceSollevamento=" . $IndiceSollevamento);

?>

