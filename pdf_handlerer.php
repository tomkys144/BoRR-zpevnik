<?php
error_reporting(E_ALL);
require __DIR__ . '/vendor/autoload.php';
require  __DIR__ . '/data/libs/TCPDF/tcpdf.php';

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
    $song = str_replace("<br>
<br>", '<br>', $song);
    $song = str_replace('&#x1d106;', '||:', $song);
    $song = str_replace('𝄆', '||:', $song);
    $song = str_replace('&#x1d107;', ':||', $song);
    $song = str_replace('𝄇', ':||', $song);
    $lineNo = substr_count($song, '<br>') + 1;
    if ($lineNo <= 20) {
        $pdf->AddPage();
        $pdf->setColumnsArray([['w'=>80, 's'=>2, 'y'=>0], ['w'=>137, 's'=>2, 'y'=>0], ['w'=>80, 's'=>2, 'y'=>0]]);

        $verseNos = '';
        $songText = '';
        $songHeader = '';

        $songHeader .= "<h1>" . $object->matter('title') . "</h1>\n";
        $songHeader .= "<h2>" . $object->matter('author') . "</h2>";

        $numberVerses = substr_count($song, '<verse');

        $verseOffset = 0;

        //From first to last but one verses
        for ($x=1; $x < $numberVerses; $x++) {
            $verseStart = strpos($song, '<verse number="', $verseOffset);
            $verseEnd = strpos($song, '<verse number="', 15 + $verseStart);
            $verseLength = $verseEnd - $verseStart;
            $verse = substr($song, $verseStart, $verseLength - 1);

            $verseOffset = $verseOffset+$verseLength + ($verseStart - $verseOffset);

            $verseNoEnd = strpos($verse, '"></verse>');
            $verseNoLength = $verseNoEnd - 15;
            $verseNo = substr($verse, 15, $verseNoLength);

            $numLines = substr_count($verse, '<br>');

            $verseNos .= '<p>' . $verseNo;
            for ($y=1; $y < $numLines; $y++) {
                $verseNos .= '<br />';
            }
            $verseNos .= '</p>';
        }
        //Last verse
        $verseStart = strpos($song, '<verse', $verseOffset);
        $verse = substr($song, $verseStart);

        $verseNoEnd = strpos($verse, '"></verse>');
        $verseNoLength = $verseNoEnd - 15;
        $verseNo = substr($verse, 15, $verseNoLength);

        $numLines = substr_count($verse, '<br>');

        $verseNos .= '<p>' . $verseNo;
        for ($y=1; $y < $numLines; $y++) {
            $verseNos .= '<br />';
        }
        $verseNos .= '</p>';

        $verseOffset = 0;

        //From first to last but one verses
        for ($x=1; $x < $numberVerses; $x++) {
            $verseStart = strpos($song, '</verse>', $verseOffset)+8;
            $verseEnd = strpos($song, '<verse', $verseOffset);
            $verseLength = $verseEnd - $verseStart;
            $verse = substr($song, $verseStart, $verseLength-1);
            print $verse;

            $verseOffset = $verseLength + $verseOffset + ($verseStart - $verseOffset);

            $verse = str_lreplace('<br>', '</p>', $verse);
            $verse = str_replace('<br>', '<br />', $verse);
            $verse = '<p>' . $verse;

            $songText .= $verse;
        }
        //Last verse
        $verseStart = strpos($song, '"></verse>', $verseOffset)+10;
        $verse = substr($song, $verseStart);

        $verse = str_replace('<br>', '<br />', $verse);
        $verse = '<p>' . $verse . '</p>';

        $songText .= $verse;

        //write the song to PDF
        //$pdf->SetY(40, false, true);
        //$pdf->WriteHTML($verseNos, false, false, true, false, 'R');
        //$pdf->selectColumn($pdf->getColumn()+1);
        //$pdf->WriteHTML($songHeader, false, false, true, false, 'C');
        //$pdf->SetY(40, false, true);
        //$pdf->WriteHTML($songText, false, false, true, false, 'L');
    }
    //$pdf->Output('test.pdf', 'I');
}

createPdfSong('Test');

//<p>blemst<br />blemst<br />blemst</p>