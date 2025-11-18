<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
$filename = '/data/dataweb/GoelandWeb/goeland/document/nouveau/json/opcannoncetravauximportdejaxforms.json';
if (file_exists($filename)) {
    echo file_get_contents($filename);
} else {
    echo '{"message":"fichier ' . $filename . ' introuvable"}';
}
