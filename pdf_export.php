<?php
require ('vendor/autoload.php');

$files = scandir(__DIR__ . '/songs/');
$files = array_diff($files, ['.', '..']);

$stylesheet = file_get_contents('css.css');

$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$date = date('d. m. o');

$pdf = new \Mpdf\Mpdf([
    'fontDir' => array_merge($fontDirs, [
        __DIR__ . '/data/font'
    ]),
    'fontData' => $fontData + [
        'raleway' => [
            'R' => 'Raleway-Regular.ttf',
            'B' => 'Raleway-Bold.ttf'
        ]
    ],
    'mode' => 'utf-8',
    'format' => 'A4',
    'orientation' => 'L'
]);
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

$authors = array();
$songs = array();

foreach ($files as $f) {
    $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/songs/' . $f));
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
        <div style="width: 89.1mm; left: 50%; top: 12em; transform: translate(-92.07mm, 0); position: absolute ; text-align: right; margin: 0; font-size: 1.5em">
        ' . $songs . '
        </div>
        <div style="width: 89.1mm; left: 50%; top: 12em; transform: translate(2.97mm, 0); position: absolute ; text-align: left; margin: 0; font-size: 1.5em">
        ' . $authors . '
        </div>',
    \Mpdf\HTMLParserMode::HTML_BODY
);

foreach ($files as $f) {
    $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/songs/' . $f));
    $song = str_replace('<verse', '<p class="verse"', $object->body());
    $song = str_replace('</verse>', '</p>', $song);
    $song = str_replace('<wrapper><chord>', '<p class="wrapper"><p class="chord">', $song);
    $song = str_replace('</chord></wrapper>', '</p></p>', $song);
    $pdf->AddPage();
    $pdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
    if ($object->matter('capo') != null) {
        $pdf->WriteHTML('<div class="capo">Capo ' . $object->matter('capo') . '</div>', \Mpdf\HTMLParserMode::HTML_BODY);
    }
    $pdf->WriteHTML(
        '<div style="position: absolute; width: 190.08mm; left: 53.46mm;top: 0; max-height: 265px">
              <h1>' . $object->matter('title') . '</h1>
              <h2>' . $object->matter('author') . '</h2>
              <div class="song_body"><p id="song_text">' . $song . '</p></div></div>',
        \Mpdf\HTMLParserMode::HTML_BODY
    );
}
$pdf->Output('zpevnik_borr.pdf', \Mpdf\Output\Destination::DOWNLOAD);

header('Location: index.html');
exit;