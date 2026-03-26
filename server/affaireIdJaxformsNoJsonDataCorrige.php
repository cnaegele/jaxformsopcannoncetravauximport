<?php
require_once 'gdt/cldbgoeland.php';
require_once '/data/dataweb/GoelandWeb/goeland/jaxforms/cncljaxforms.php';
$oJaxForms = new CNJaxForms(
    jaxServer: 'api.lausanne.ch'
    ,idForms: 'URB_dispense_permis_construire'
);
$dbgo = new DBGoeland();
$sSql = "cn_affaire_idjaxform_jsondatanull_liste";
$dbgo->queryRetJson($sSql);
$oRes = json_decode($dbgo->resString, true);
$nbrCorrections = 0;
foreach ($oRes as $res) {
    $idaffaire = $res['id_affaire'];
    $idjaxforms = $res['id_jaxforms'];
    $jsonData = $oJaxForms->dataForms(idFormsElement: $idjaxforms);
    $jsonData = str_replace("'", "''", $jsonData);
    $sSql = "cn_affaire_idjaxforms_update_json_data $idaffaire, '$jsonData'";
    $dbgo->queryRetNothing($sSql, 'W');
    echo "Correction jsondata pour idaffaire $idaffaire<br>";
    $nbrCorrections++;
}
unset($dbgo);
unset($oJaxForms);
echo "correction de $nbrCorrections affaires";