<?php
require_once '/data/dataweb/GoelandWeb/goeland/jaxforms/cncljaxforms.php';
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

$oJaxForms = new CNJaxForms(
     jaxServer: 'api-vali.lausanne.ch'
    ,idForms: 'URB_dispense_permis_construire'
);
$bParamsOk = true;
if (isset($_GET['jsonparams'])) {
    $jsonParams = $_GET['jsonparams'];
    $oParams = json_decode($jsonParams, false);
    if (isset($oParams->idformselement)) {
        $idFormElement = $oParams->idformselement;
    } else {
        $bParamsOk = false;
        $message = 'critere idformselement manquant';
    }
} else {
    $bParamsOk = false;
    $message = 'GET jsoncriteres manquant';
}

if ($bParamsOk) {
    try {
        $jfData = $oJaxForms->dataForms(idFormsElement: $idFormElement);
        $success = true;
        $message = 'OK';
    } catch (Exception $e) {
        $success = false;
        $message = $e->getMessage();
        $jfSearch = null;
    }
} else {
    $success = false;
    $jfData = null;
}
echo $jfData;