<?php
error_reporting(E_ALL);
require __DIR__ . '/vendor/autoload.php';

function str_lreplace($search, $replace, $subject)
{
    $pos = strrpos($subject, $search);

    if($pos !== false)
    {
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }

    return $subject;
}

function createPdfSong($songName)
{
    $name = str_replace(' ', '_', $songName);
    $name = iconv('utf-8', 'ascii//TRANSLIT', $name);
    $name = str_replace(str_split("\:'*?<>.,!"), "", $name);
    $name = strtolower($name);
    $name .= ".md";

    $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/data/songs/' . $name));

    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false, false);

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Tomáš Kysela - Kyslík');
    $pdf->SetTitle($songName);
    $pdf->SetSubject('BoRR zpěvník');

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    $pdf->SetMargins(10, PDF_MARGIN_TOP, 10);

    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    $OpenSans = TCPDF_FONTS::addTTFfont(__DIR__ . '/data/font/OpenSans/OpenSans-Regular.ttf', 'TrueTypeUnicode', '', 32);
    $pdf->SetFont($OpenSans);

    $song = $object->body();
    $song = str_replace("<wrapper><chord>", '', $song);
    $song = str_replace('</chord></wrapper>', '', $song);
    $song = str_replace("<br>" . PHP_EOL . "<br>", '<br>', $song);
    $song = str_replace('&#x1d106;', '||:', $song);
    $song = str_replace('𝄆', '||:', $song);
    $song = str_replace('&#x1d107;', ':||', $song);
    $song = str_replace('𝄇', ':||', $song);
    $lineNo = substr_count($song, '<br>') + 1;
    if ($lineNo <= 20) {
        $verseNos = [];
        $songText = [];
        $songHeader = '';

        $songHeader .= "<h1>" . $object->matter('title') . "</h1>" . PHP_EOL;
        $songHeader .= "<h2>" . $object->matter('author') . "</h2>";
        if ($object->matter('capo') !== null) {
            $songHeader .= PHP_EOL . "<p>Capo " . $object->matter('capo') . "</p>";
        }

        $numberVerses = substr_count($song, '<verse');

        $verseOffset = 0;

        //From first to last but one verses
        for ($x=1; $x < $numberVerses; $x++) {
            $verseStart = strpos($song, '<verse number="', $verseOffset);
            $verseEnd = strpos($song, '<verse number="', 15 + $verseStart);
            $verseLength = $verseEnd - $verseStart -1;
            $verse = substr($song, $verseStart, $verseLength);

            $verseOffset = $verseEnd;

            $verseNoEnd = strpos($verse, '"></verse>');
            $verseNoLength = $verseNoEnd - 15;
            $verseNo = substr($verse, 15, $verseNoLength);

            $verseNos[]=$verseNo;
        }
        //Last verse
        $verseStart = strpos($song, '<verse', $verseOffset);
        $verse = substr($song, $verseStart);

        $verseNoEnd = strpos($verse, '"></verse>');
        $verseNoLength = $verseNoEnd - 15;
        $verseNo = substr($verse, 15, $verseNoLength);

        $verseNos[] = $verseNo;

        $verseOffset = 0;

        //From first to last but one verses
        for ($x=1; $x < $numberVerses; $x++) {
            $verseStart = strpos($song, '</verse>', $verseOffset)+8;
            $verseEnd = strpos($song, '<verse', $verseStart);
            $verseLength = $verseEnd - $verseStart;
            $verse = substr($song, $verseStart, $verseLength-1);

            $verseOffset = $verseLength + $verseOffset + ($verseStart - $verseOffset);

            $verse = str_lreplace('<br>', '</p>', $verse);
            $verse = str_replace('<br>', '<br />', $verse);
            $verse = '<p>' . $verse;

            $songText[] = $verse;
        }
        //Last verse
        $verseStart = strpos($song, '"></verse>', $verseOffset)+10;
        $verse = substr($song, $verseStart);

        $verse = str_replace('<br>', '<br />', $verse);
        $verse = '<p>' . $verse . '</p>';

        $songText[] = $verse;

        //write the song to PDF
        $pdf->AddPage();
        $pdf->setColumnsArray([['w'=>80, 's'=>2, 'y'=>0], ['w'=>137, 's'=>2, 'y'=>0], ['w'=>80, 's'=>2, 'y'=>0]]);
        $pdf->selectColumn($pdf->getColumn() + 1);
        $pdf->WriteHTML($songHeader, false, false, true, false, 'C');
        $pdf->selectColumn($pdf->getColumn() - 1);
        $pdf->SetY(40, false, true);
        for ($x=0; $x < $numberVerses; $x++) {
            $pdf->WriteHTML($verseNos[$x], false, false, true, false, 'R');
            $y = $pdf->GetY();
            $pdf->selectColumn($pdf->getColumn() + 1);
            $pdf->SetY($y, false, true);
            $pdf->WriteHTML($songText[$x], false, false, true, false, 'L');
            $y = $pdf->GetY()+2;
            $pdf->selectColumn($pdf->getColumn() - 1);
            $pdf->SetY($y, false, true);
        }
    }
    $pdf->Output('test.pdf', 'I');

}

createPdfSong('Test');