<!--//Nadel geht auf $url und sucht die Seite nach Firmen- title und URL, speichert den array in $tmp//-->
<!--//$iterations gibt an wie viele Seiten geblättert werden//-->
<!--//$debug Zeigt an, wie viele Seiten er schon durchgeschaut hat//-->
<?php
$title = [];
$iterations = 41;
$debug = true;



for($x = 1; $x < $iterations; $x++) {
    echo "page-" . $x;
    if ($x == 1) {
        $url = "https://de.kompass.com/d/bielefeld/de_05_05711/";
    } else {
        $url = "https://de.kompass.com/d/bielefeld/de_05_05711/page-" . $x . "/";
    }

    $dom = new DOMDocument;
    $dom->loadHTML(file_get_contents($url));
    $tmp = [];

    //Firmenname mit raussuchen
//    $counter = 1;
//
//    foreach ($dom->getElementsByTagName("span") as $tag) {
//        if ($tag->getAttribute("class") === "titleSpan") {
//            $tmp[$counter][">Firmen<"] = $tag->nodeValue;
//            $counter++;
//        }
//    }

    if ($debug) echo "Firmen rangeholt. <br>";

    $counter = 1;
    foreach ($dom->getElementsByTagName("div") as $divtag) {
        if ($divtag->getAttribute("class") === "companyWeb") {
            foreach ($divtag->getElementsByTagName("a") as $atag) {
                $tmp[$counter][">URL<"] = $atag->getAttribute("href");
                $counter++;

            }
        }
    }
    //datei wird als daten.csv abgespeichert//
    $fp = fopen('bifirmen.csv', 'a');

    foreach ($tmp as $arrays) {
        fputcsv($fp, $arrays);
    }
    fclose($fp);
}

?>
