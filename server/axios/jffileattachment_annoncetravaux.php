<?php
require_once '/data/dataweb/GoelandWeb/goeland/jaxforms/cncljaxforms.php';
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

$oJaxForms = new CNJaxForms(
     jaxServer: 'api-vali.lausanne.ch'
    ,idForms: 'URB_dispense_permis_construire'
);
$bParamsOk = true;
if (isset($_GET['jsoncriteres'])) {
    $jsonCriteres = $_GET['jsoncriteres'];
    $oCritere = json_decode($jsonCriteres, false);
    if (isset($oCritere->idfileattachment)) {
        $idFileAttachment = $oCritere->idfileattachment;
    } else {
        $bParamsOk = false;
        $message = 'critere idfileattachment manquant';
    }
} else {
    $bParamsOk = false;
    $message = 'GET jsoncriteres manquant';
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
} else {
    $success = false;
    $jfData = null;
}

$data = [
    'b64content' => base64_encode($jfFileAttachment['content']),
    'mime_type' => $jfFileAttachment['mime_type'],
    'size' => $jfFileAttachment['size'],
];
$result = [
    'success' => $success,
    'message' => $message,
    'data' => $data,
];
echo json_encode($result);
