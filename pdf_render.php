<?php
error_reporting(E_ALL);
require __DIR__ . '/vendor/autoload.php';

$files = scandir(__DIR__ . '/data/songs/');
$files = array_diff($files, ['.', '..']);

$pdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'orientation' => 'L'
]);

$date = date('d. m. o');

$stylesheet = file_get_contents('css.css');

//Title page
$pdf->AddPage();
$pdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
$pdf->WriteHTML(
    '<div>
        <h1>BoRR</h1>
        <h1>zpěvník</h1>
    </div>
    <div style="position: absolute; bottom: 0; text-align: center; width: 297mm;left: 0 ; font-size: 5mm">
        <p>zpěvník aktuální k ' . $date . '</p>
        <p>napsal Tomáš Kysela - Kyslík</p> 
        <p>&#169;2020 BoRR</p>
    </div>'
);

//List
$authors = array();
$songs = array();

foreach ($files as $f) {
    $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/data/songs/' . $f));
    $song = str_replace(' ', '&nbsp;', $object->matter('title'));
    $song .= '<br>';
    array_push($songs, $song);
    $author = str_replace(' ', '&nbsp;', $object->matter('author'));
    $author .= '<br>';
    array_push($authors, $author);
}

$pdf->AddPage();
$pdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
$pdf->WriteHTML(
    '<div style="position: absolute; width: 190.08mm; left: 53.46mm;top: 0">
        <h1>
            Rejstřík
        </h1>
        </div>
        <div style="width: 49%; left: 0; top: 12em; position: absolute ; text-align: right; margin: 0; font-size: 1.5em">',
    \Mpdf\HTMLParserMode::HTML_BODY, true, false
);
foreach ($songs as $song) {
    $pdf->WriteHTML($song, \Mpdf\HTMLParserMode::HTML_BODY, false, false);
}
$pdf->WriteHTML('</div>
        <div style="width: 49%; right: 0; top: 12em; position: absolute ; text-align: left; margin: 0; font-size: 1.5em">',
    \Mpdf\HTMLParserMode::HTML_BODY, false, false
);
foreach ($authors as $author) {
    $pdf->WriteHTML($author, \Mpdf\HTMLParserMode::HTML_BODY, false, false);
}
$pdf->WriteHTML('</div>', \Mpdf\HTMLParserMode::HTML_BODY, false, true);

foreach ($files as $file) {
    $pdf->AddPage();
    $pdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
    $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/data/songs/' . $file));
    $pdf->WriteHTML(
        '<div style="position: absolute; width: 190.08mm; left: 53.46mm;top: 0; max-height: 265px">
              <h1>' . $object->matter('title') . '</h1>
              <h2>' . $object->matter('author') . '</h2>
              <div class="song_body"><p id="song_text">' . $object->body() . '</p></div></div>
        <div style="bottom: 0; left: -20mm; margin: 20px; position: relative">
        <p>',
    \Mpdf\HTMLParserMode::HTML_BODY, true, false
    );
    if (strpos($object->matter('made'), '{male}') !== false) {
    $made = str_replace(' {male}', '', $object->matter('made'));
    $pdf->WriteHTML('Zpracoval: ' . $made, \Mpdf\HTMLParserMode::HTML_BODY, false, false);
    } elseif (strpos($object->matter('made'), '{female}') !== false) {
    $made = str_replace(' {female}', '', $object->matter('made'));
    $pdf->WriteHTML('Zpracovala: ' . $made, \Mpdf\HTMLParserMode::HTML_BODY, false, false);
    } else {
        $made = $object->matter('made');
        $pdf->WriteHTML('Zpracoval(a): ' . $made, \Mpdf\HTMLParserMode::HTML_BODY, false, false);
    }
    if ($object->matter('revision') != null && sizeof($object->matter('revision')) != 1) {
        $pdf->WriteHTML('</p><p>Upravil', \Mpdf\HTMLParserMode::HTML_BODY, false, false);
        $start = $object->matter('revision')[0];
        $onlyFemales = true;
        foreach ($object->matter('revision') as $maker) {
            $makers = '';
            if (strpos($maker, '{male}') !== false) {
                $onlyFemales = false;
            }
            $maker = str_replace(' {female}', '', $maker);
            $maker = str_replace(' {male}', '', $maker);
            if ($start != $maker) {
                $makers .= ', ' . $maker;
            } else {
                $makers .= $maker;
            }
        }
        if ($onlyFemales) {
            $pdf->WriteHTML('y: ' . $makers, \Mpdf\HTMLParserMode::HTML_BODY, false, false);
        } else {
            $pdf->WriteHTML('i: ' . $makers, \Mpdf\HTMLParserMode::HTML_BODY, false, false);
        }
    } elseif (($object->matter('revision') != null && sizeof($object->matter('revision')) == 1)) {
        if (strpos($object->matter('revision')[0], '{male}') !== false) {
            $revision = str_replace(' {male}', '', $object->matter('revision')[0]);
            $pdf->WriteHTML('</p><p>Upravil: ' . $revision, \Mpdf\HTMLParserMode::HTML_BODY, false, false);
        } elseif (strpos($object->matter('revision')[0], '{female}') !== false) {
            $revision = str_replace(' {female}', '', $object->matter('revision')[0]);
            $pdf->WriteHTML('</p><p>Upravila: ' . $revision, \Mpdf\HTMLParserMode::HTML_BODY, false, false);
        } else {
            $pdf->WriteHTML('</p><p>Upravil(a): ' . $object->matter('revision')[0], \Mpdf\HTMLParserMode::HTML_BODY, false, false);
        }
    }
    $pdf->WriteHTML(
        '</p>
        </div>
        </div>',
        \Mpdf\HTMLParserMode::HTML_BODY, false, false
    );
    if ($object->matter('capo') != null) {
        $pdf->WriteHTML('<div class="capo">Capo ' . $object->matter('capo') . '</div>', \Mpdf\HTMLParserMode::HTML_BODY, false, true);
    }
}

$pdf->Output('zpevnik_borr.pdf', \Mpdf\Output\Destination::DOWNLOAD);
?>