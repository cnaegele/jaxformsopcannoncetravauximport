<?php
require_once 'gdt/cldbgoeland.php';
header("Access-Control-Allow-Origin: *");
$idAffaire = 0;
if (isset($_GET['idjaxforms'])) {
    $idJaxForms = $_GET['idjaxforms'];
    if (preg_match('/^[a-zA-Z0-9-]{1,50}$/', $idJaxForms)) {
        $sSql = "cn_affaire_idjaxforms_get_idaffaire '$idJaxForms'";
        $dbgo = new DBGoeland();
        $dbgo->queryRetInt($sSql);
        $idAffaire = $dbgo->resInt;
    }
}
echo $idAffaire;
