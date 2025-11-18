<?php
require_once '/data/dataweb/GoelandWeb/goeland/jaxforms/cncljaxforms.php';
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

$oJaxForms = new CNJaxForms(
     jaxServer: 'api-vali.lausanne.ch'
    ,idForms: 'URB_dispense_permis_construire'
);
$pageSize = 20;
$offset = 0;
$criteriastatus = 0;
if (isset($_GET['jsonparams'])) {
    $jsonParams = $_GET['jsonparams'];
    $oParams = json_decode($jsonParams, false);
    if (isset($oParams->pagesize)) {
        $pageSize = $oParams->pagesize;
    }
    if (isset($oParams->offset)) {
        $offset = $oParams->offset;
    }
    if (isset($oParams->demandestatus)) {
        $demandestatus = $oParams->demandestatus;
    }
}

try {
    $jfSearch = $oJaxForms->searchForms(pageSize: $pageSize, offset: $offset, demandestatus: $demandestatus);
}  catch (Exception $e) {
    //$success = false;
    //$message = $e->getMessage();
    $jfSearch = null;
}
echo $jfSearch;
