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

function line_contains($search, $needle)
{
    if (strpos($search, $needle) !== false) {
        return true;
    } else {
        return false;
    }
}

function verse_contains_chord($search)
{
    $lineEnd = strpos($search, '<br />');
    if (line_contains(substr($search, 0, $lineEnd), '<chord>') !== false) {
        return true;
    } else {
        return false;
    }
}

function createPdfSong($songName)
{
    $name = str_replace(' ', '_', $songName);
    $name = iconv('utf-8', 'ascii//TRANSLIT', $name);
    $name = str_replace(str_split("\:'*?<>.,!"), "", $name);
    $name = strtolower($name);

    $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/data/songs/' . $name . '.md'));

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
    $pdf->SetFont($OpenSans, '', 12);
    $OpenSansBold = TCPDF_FONTS::addTTFfont(__DIR__ . '/data/font/OpenSans/OpenSans-Bold.ttf', 'TrueTypeUnicode', '', 32);

    $song = $object->body();
    $song = str_replace("<wrapper><chord>", '<chord>', $song);
    $song = str_replace('</chord></wrapper>', '</chord>', $song);
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
        for ($n=1; $n < $numberVerses; $n++) {
            $verseStart = strpos($song, '<verse number="', $verseOffset);
            $verseEnd = strpos($song, '<verse number="', 15 + $verseStart);
            $verseLength = $verseEnd - $verseStart -1;
            $verse = substr($song, $verseStart, $verseLength);

            $verseOffset = $verseEnd;

            $verseNoEnd = strpos($verse, '"></verse>');
            $verseNoLength = $verseNoEnd - 15;
            $verseNo = substr($verse, 15, $verseNoLength);

            $verseNos[]='<b>' . $verseNo . '</b>';
        }
        //Last verse
        $verseStart = strpos($song, '<verse', $verseOffset);
        $verse = substr($song, $verseStart);

        $verseNoEnd = strpos($verse, '"></verse>');
        $verseNoLength = $verseNoEnd - 15;
        $verseNo = substr($verse, 15, $verseNoLength);

        $verseNos[] = '<b>' . $verseNo . '</b>';

        $verseOffset = 0;

        //From first to last but one verses
        for ($n=1; $n < $numberVerses; $n++) {
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
        if (verse_contains_chord($songText[0]) !== false) {
            $y = $pdf->GetY()+8;
        } else {
            $y = $pdf->GetY()+3;
        }
        $pdf->selectColumn($pdf->getColumn() - 1);
        $pdf->SetY($y, false, true);
        for ($n=0; $n < $numberVerses; $n++) {
            $pdf->WriteHTML($verseNos[$n], false, false, true, false, 'R');
            $y = $pdf->GetY();
            $pdf->selectColumn($pdf->getColumn() + 1);
            $pdf->SetY($y, false, true);

            $chordOffset = 0;
            for ($m=1; $m <= substr_count($songText[$n], '<chord>'); $m++) {
                $chordStart = strpos($songText[$n], '<chord>', $chordOffset);
                $chordEnd = strpos($songText[$n], '</chord>', $chordStart);
                if (line_contains(substr($songText[$n], $chordOffset, $chordStart-$chordOffset), '<br />')) {
                    $break = strpos($songText[$n], '<br />', $chordOffset)+6;
                    $partOne = str_replace(' ', '&nbsp;', substr($songText[$n], $chordOffset, $break-$chordOffset));
                    $partTwo = str_replace(' ', '&nbsp;', substr($songText[$n], $break, $chordStart-$break));
                    print '<p>string: '.$partOne.'<br>';
                    print 'simple: '.$pdf->GetStringWidth($partOne).'<br>';
                    print 'full: '.$pdf->GetStringWidth($partOne, $OpenSans, '', 12, false).'</p><hr>';
                    $pdf->WriteHTMLCell(
                        $pdf->GetStringWidth($partOne), $pdf->getStringHeight($pdf->GetStringWidth($partOne), $partOne), $pdf->GetX(), $pdf->GetY(), $partOne, 0, 0, false, true, 'L', false
                    );
                    $pdf->WriteHTMLCell(
                        $pdf->GetStringWidth($partTwo), $pdf->getStringHeight($pdf->GetStringWidth($partTwo), $partTwo), $pdf->GetX(), $pdf->GetY()+5, $partTwo, 0, 0, false, true, 'L', false
                    );
                } else {
                    $pdf->WriteHTMLCell($pdf->GetStringWidth(substr($songText[$n], $chordOffset, $chordStart - $chordOffset)), $pdf->getStringHeight($pdf->GetStringWidth(substr($songText[$n], $chordOffset, $chordStart - $chordOffset)), substr($songText[$n], $chordOffset, $chordStart - $chordOffset)),
                        $pdf->GetX(), $pdf->GetY(), substr($songText[$n], $chordOffset, $chordStart - $chordOffset), 0, 0, false, true, 'L', false
                    );
                }
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->SetFont($OpenSansBold, 'B', 12);
                $pdf->WriteHTMLCell(
                    $pdf->GetStringWidth(substr($songText[$n], $chordStart+7, $chordEnd-$chordStart-7), '', 'B'), $pdf->getStringHeight($pdf->GetStringWidth(substr($songText[$n], $chordStart+7, $chordEnd-$chordStart-7), '', 'B'), substr($songText[$n], $chordStart+7, $chordEnd-$chordStart-7)), $x, $y-4.7, substr($songText[$n], $chordStart+7, $chordEnd-$chordStart-7),
                    0, 0, false, true, 'L', false
                );
                $pdf->SetFont($OpenSans, '', 12);
                $pdf->WriteHTMLCell(0.1, 0, $x, $y, '', 0, 0, false, true, 'L', false);
                $chordOffset=$chordEnd+8;
            }
            $pdf->WriteHTML(substr($songText[$n], $chordOffset), false, false, true, false, 'L');

            if ($n !== $numberVerses-1) {
                if (verse_contains_chord($songText[$n+1]) !== false) {
                    $y = $pdf->GetY() + 8;
                } else {
                    $y = $pdf->GetY() + 3;
                }
            }
            $pdf->selectColumn($pdf->getColumn() - 1);
            $pdf->SetY($y, false, true);
        }
    }
    //$pdf->Output($name . '.pdf', 'I');

}

createPdfSong('Blemst');