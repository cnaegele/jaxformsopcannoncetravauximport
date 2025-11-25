<?php
class CNJaxForms {
    protected $server;
    protected $idForms;
    protected $apikey;
    protected $client_id;
    protected $client_secret;
    protected $urlToken;
    protected $urlSearch;
    protected $urlData;
    protected $urlFileAttachment;
    protected $urlWorkflow;
    protected $typeReturnData; //json (défaut) | xml | object
    protected $typeReturnSearch; //json (défaut) | object

    function __construct($jaxServer,
                         $idForms,
                         $apikey = "eee68070-96aa-11ec-b909-0242ac120002",
                         $client_id = "d8f73909-64ee-41b3-9464-161689b5b2f9",
                         $client_secret = "fe07277d70a26e33213115eff6202e18",
                         $typeReturnData = "json",
                         $typeReturnSearch = "json") {
        $this->server = "https://$jaxServer";
        $this->idForms = $idForms;
        $this->apikey = $apikey;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->typeReturnData = $typeReturnData;
        $this->typeReturnSearch = $typeReturnSearch;
        $this->urlToken = "$this->server/formservice/services/rest/auth/token";
        $this->urlSearch = "$this->server/formservice/services/rest/search/forms/$this->idForms";
        $this->urlWorkflow = "$this->server/formservice/services/rest/workflow/perform/completed_archived";
        if ($this->typeReturnData == "object") {
            $retData = "json";
        } else {
            $retData = $this->typeReturnData;
        }
        $this->urlData = "$this->server/formservice/services/rest/data/$retData/$this->idForms";
        $this->urlFileAttachment = "$this->server/formservice/services/rest/data/attachment";
    }

    public function getToken() {
        $url = $this->urlToken;

        $headers = [
            "Content-Type: application/json",
            "apikey: $this->apikey"
        ];

        $data = [
            "client_id" => $this->client_id,
            "client_secret" => $this->client_secret,
            "grant_type" => "client_credentials"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new Exception("Erreur cURL: " . curl_error($ch));
        }
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("Erreur HTTP: " . $httpCode);
        }

        return json_decode($response, true);
    }

    function searchForms($accessToken = '', $pageSize = 10, $offset = 0, $demandestatus = 0) {
        if (!$accessToken) {
            $jfToken = $this->getToken();
            $accessToken = $jfToken['access_token'];
        }
        $url = $this->urlSearch;
        if ($demandestatus > 0) {
            $url .= '/' . strval($demandestatus);
        }

        $headers = [
            "Content-Type: application/json",
            "apikey: $this->apikey",
            "Authorization: Bearer " . $accessToken
        ];

        $data = [
            "countTotal" => true,
            "pageSize" => $pageSize,
            "offset" => $offset,
            "includeXML" => false
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new Exception("Erreur cURL: " . curl_error($ch));
        }
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("Erreur HTTP: " . $httpCode);
        }

        if ($this->typeReturnSearch == "object") {
            return json_decode($response, true);
        } else {
            return $response;
        }
    }

    function dataForms($idFormsElement, $accessToken = '') {
        if (!$accessToken) {
            $jfToken = $this->getToken();
            $accessToken = $jfToken['access_token'];
        }
        $url = "$this->urlData/$idFormsElement";

        $headers = [
            "Content-Type: application/json",
            "apikey: $this->apikey",
            "Authorization: Bearer " . $accessToken
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new Exception("Erreur cURL: " . curl_error($ch));
        }
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("Erreur HTTP: " . $httpCode);
        }

        if ($this->typeReturnData == "object") {
            return json_decode($response, true);
        } else {
            return $response;
        }
    }

    function getFileAttachment($idFile, $accessToken = '') {
        if (!$accessToken) {
            $jfToken = $this->getToken();
            $accessToken = $jfToken['access_token'];
        }
        $url = "$this->urlFileAttachment/$idFile";

        $headers = [
            "apikey: $this->apikey",
            "Authorization: Bearer " . $accessToken
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $fileSize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);

        if (curl_errno($ch)) {
            throw new Exception("Erreur cURL: " . curl_error($ch));
        }
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("Erreur HTTP: " . $httpCode);
        }

        return [
            'content' => $response,
            'mime_type' => $contentType,
            'size' => $fileSize,
            'http_code' => $httpCode
        ];
    }

    function putStatusArchives($idFormsElement, $accessToken = '') {
        if (!$accessToken) {
            $jfToken = $this->getToken();
            $accessToken = $jfToken['access_token'];
        }
        $url = $this->urlWorkflow;
        $data = [
            "formID" => $this->idForms,
            "guid" => $idFormsElement
        ];
        $jsonData = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $accessToken,
            "apikey: " . $this->apikey
        ]);

        curl_exec($ch);
        if (curl_errno($ch)) {
            $txtresponse = 'ERROR CURL:' . curl_error($ch);
        } else {
            $txtresponse = 'OK';
        }
        curl_close($ch);

        $response = [ 'response' => $txtresponse];
        if ($this->typeReturnData == "object") {
            return json_decode($response, true);
        } else {
            return $response;
        }
    }
}