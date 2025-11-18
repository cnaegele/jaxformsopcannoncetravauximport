<?php
require_once '/data/dataweb/GoelandWeb/goeland/jaxforms/cncljaxforms.php';
header("Access-Control-Allow-Origin: *");

$oJaxForms = new CNJaxForms(
     jaxServer: 'api-vali.lausanne.ch'
    ,idForms: 'URB_dispense_permis_construire'
);
$bParamsOk = true;
if (isset($_GET['idfileattachment'])) {
    $idFileAttachment = $_GET['idfileattachment'];
} else {
    $bParamsOk = false;
    echo 'GET idfileattachment manquant';
}

if ($bParamsOk) {
    try {
        $jfFileAttachment = $oJaxForms->getFileAttachment(idFile: $idFileAttachment);
        $success = true;
        $message = 'OK';
    } catch (Exception $e) {
        $success = false;
        $message = $e->getMessage();
        $jfSearch = null;
    }
}
if ($success) {
    header("Content-type: " . $jfFileAttachment['mime_type']);
    echo $jfFileAttachment['content'];
} else {
    header('Content-Type: application/json');
    $result = [
        'success' => $success,
        'message' => $message,
    ];
    echo json_encode($result);
}
