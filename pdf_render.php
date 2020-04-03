<?php
require __DIR__ . '/vendor/autoload.php';

$files = scandir(__DIR__ . '/data/songs/');
$files = array_diff($files, ['.', '..']);

$pdf = new \Mpdf\Mpdf([
    'mode' => 'utf-32',
    'format' => 'A4',
    'orientation' => 'L'
]);

$pdf->useSubstitutions = false;
$pdf->simpleTables = true;

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
    $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/data/songs/' . $file));
    $song = $object->body();
    $song = str_replace('<wrapper><chord>', '', $song);
    $song = str_replace('</wrapper></chord>', '', $song);
    $lineNo = substr_count($song, '<br>') + 1;
    if ($lineNo <=20) {
        $pdf->AddPage();
        $pdf->SetColumns(2, 'J', 2);
        $pdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);

        $numberVerses = substr_count($song, '<verse');

        $verseOffset = 0;

        for ($x=1; $x < $numberVerses; $x++) {
            $firstVerse = strpos($song, '<verse', $verseOffset);
            $secondVerse = strpos($song, '<verse', 6 + $firstVerse);
            $lengthVerse = $secondVerse - $firstVerse;
            $verse = substr($song, $firstVerse, $lengthVerse - 1);

            $verseOffset = $verseOffset+$lengthVerse+$firstVerse;

            $verseNoEnd = strpos($verse, '"></verse>');
            $verseNoLength = $verseNoEnd - 15;
            $verseNo = substr($verse, 15, $verseNoLength);

            $numLines = substr_count($verse, '<br>');

            $output = $verseNo;
            for ($y = 1; $y <= $numLines; $y++) {
                $output .= "<br>\n";
            }
            if ($x = 1) {
                $pdf->WriteHTML($output, \Mpdf\HTMLParserMode::HTML_BODY, true, false);
            } else {
                $pdf->WriteHTML($output, \Mpdf\HTMLParserMode::HTML_BODY, false, false);
            }
        }

        //last verse
        $verseStart = strpos($song, '<verse', $verseOffset);
        $verse = substr($song, $verseStart);

        $verseNoEnd = strpos($verse, '"></verse>');
        $verseNoLength = $verseNoEnd - 15;
        $verseNo = substr($verse, 15, $verseNoLength);

        $numLines = substr_count($verse, '<br>');

        $output = $verseNo;
        for ($y = 1; $y < $numLines; $y++) {
            $output .= "<br>\n";
        }
        $pdf->WriteHTML($output, \Mpdf\HTMLParserMode::HTML_BODY, false, true);

        $pdf->AddColumn();

        $verseOffset=0;

        for ($x=1; $x < $numberVerses; $x++) {
            $verseStart = strpos($song, '</verse>', $verseOffset) + 8;
            $verseEnd = strpos($song, '<verse', $verseStart);
            $verseLength = $verseEnd - $verseStart;
            $verse = substr($song, $verseStart,$verseLength);

            $verseOffset = $verseEnd;

            $output .=$verse;
        }

        //Last verse
        $verseStart = strpos($song, '</verse>', $verseOffset) + 8;
        $verse = substr($song, $verseStart);

        $output .=$verse;

        $pdf->WriteHTML($output, \Mpdf\HTMLParserMode::HTML_BODY, true, true);
    }
}

$pdf->Output('zpevnik_borr.pdf', \Mpdf\Output\Destination::DOWNLOAD);
?>