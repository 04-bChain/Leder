<?php


$array= [];
$index = 0;

$file = fopen('gtfirmen.csv', 'r');

while(! feof($file))
{
    $array[$index] = fgetcsv($file); 
    $index++;
}
if ($index == 0){
    echo "No companies found";
    exit;
    //echo "<br>";
}
fclose($file);

$log_url = "https://www.t3versions.com/api/auth/login/";

$log_curl = curl_init($log_url);
curl_setopt($log_curl, CURLOPT_URL, $log_url);
curl_setopt($log_curl, CURLOPT_POST, true);
curl_setopt($log_curl, CURLOPT_RETURNTRANSFER, true);

$header = array(
    "Content-Type: application/json"
);
curl_setopt($log_curl, CURLOPT_HTTPHEADER, $header);

$data = '{"username":"FAKEUSERNAME","password":"FAKEPASSWORD"}';

curl_setopt($log_curl, CURLOPT_POSTFIELDS, $data);

//for debug only!
curl_setopt($log_curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($log_curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($log_curl);
curl_close($log_curl);

$token = json_decode($resp, true)["token"];

//echo $token;




$resultsTypo3Versions = [];

$counterCheck = 0;

foreach ($array as $temp) {
//    if($counterCheck > 10) {
//        return false;
//    }

    $check_url = "https://www.t3versions.com/api/v1/check/";

    $check_curl = curl_init($check_url);
// curl optionen setzen
    curl_setopt($check_curl, CURLOPT_URL, $check_url);
    curl_setopt($check_curl, CURLOPT_POST, true);
    curl_setopt($check_curl, CURLOPT_RETURNTRANSFER, true);

// curl spezifischen header mitgeben
    $headers = array(
        "Content-Type: application/json",
        "Authorization: Token " . $token
    );
    curl_setopt($check_curl, CURLOPT_HTTPHEADER, $headers);

    $urls = '{ "domain": "' . $temp[0] . '" }';
    curl_setopt($check_curl, CURLOPT_POSTFIELDS, $urls);

    curl_setopt($check_curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($check_curl, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($check_curl);

    curl_close($check_curl);
    $result_url = json_decode($response, true)["result_url"] . "?format=json";
//4-5 sekunden sleep ohne csv schreiben bei 21 einträgen // mit CSV schreiben 25% nicht beendet quote
//bei 7-8 sekunden 5% fehlerquote
// 10-12 testen auf 100%
    sleep(12);

    $url = $result_url;

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
        "Accept: application/json",
        "Authorization: Token " . $token
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//for debug only!
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $resp = curl_exec($curl);
    curl_close($curl);
    array_push($resultsTypo3Versions, json_decode($resp,true));

    $counterCheck++;
    $i = 0;
    $i++;
    if($i == 2){
        exit;
    }
}
//check attribute finished for true or false, if false attache it to the end of the first csv list



// in $file kommen die fehlerhaften oder nicht ausgelesenen resultate an das Ende der liste
    $fp = fopen('gt_result.csv', 'w');
    $file = fopen('gtfirmen.csv', 'a');

    fputcsv($fp, array("Beendet?", "Domain", "SSL Verschlüsselt?", "Ist eine TYPO3 Installation?", "TYPO3 Version", "Ist über Composer installiert?", "Nachricht"));
//    fputcsv($file, array (\n));

    foreach ($resultsTypo3Versions as $arrays) {
//        var_dump($arrays);
        if ($arrays["finished"] == false) {
            $resultCrawlTypo3Array0 = [];
            $resultCrawlTypo3Array0["domain"] = $arrays["domain"];
                fputcsv($file, $resultCrawlTypo3Array0); // Speichert CSV-Zeile in Array
        }
        else {
            if (is_array($arrays)) {
                $resultCrawlTypo3Array = [];
                $resultCrawlTypo3Array["finished"] = $arrays["finished"];
                $resultCrawlTypo3Array["domain"] = $arrays["domain"];
                $resultCrawlTypo3Array["has_ssl"] = $arrays["has_ssl"];
                $resultCrawlTypo3Array["is_typo3"] = $arrays["is_typo3"];
                $resultCrawlTypo3Array["major_version"] = $arrays["major_version"];
                $resultCrawlTypo3Array["composer"] = $arrays["composer"];
                $resultCrawlTypo3Array["message"] = $arrays["message"];

                fputcsv($fp, $resultCrawlTypo3Array);
            }
        }
    }
    fclose($fp);
    fclose($file);



