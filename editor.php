<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BoRR zpěvník</title>
    <link rel="icon" href="data/borr.png">
    <link rel="stylesheet" href="css.css">
    <script>
        let host = window.location.hostname;
        if (host !== 'localhost') {
            let prot = window.location.protocol;
            if (prot === "http:") {
                window.location.href = window.location.href.replace('http://','https://');
            }
        }
    </script>
</head>
<body>
<?php
require __DIR__ . '/vendor/autoload.php';
if ($_SERVER['REQUEST_METHOD']=='POST') {
    $title = $_REQUEST['title'];
    $author = $_REQUEST['author'];
    $capo = $_REQUEST['capo'];
    $body = $_REQUEST['body'];

    if ($title != null){
        $name = str_replace(' ', '_', $title);
        $name = iconv('utf-8', 'ascii//TRANSLIT', $name);
        $name = strtolower($name);
        $name = str_replace(str_split('\:*?<>.,!'), '', $name);

        $file = fopen(__DIR__ . '/songs/' . $name . '.md', 'w');
        fwrite($file, "---\n");
        fwrite($file, "title: '" . $title . "'\n");
        fwrite($file, "author: '" . $author . "'\n");
        if ($capo === 0 || $capo ==='0') {
            fwrite($file, "capo: null\n");
        }
        else {
            fwrite($file, "capo: " . $capo . "\n");
        }
        fwrite($file, "---\n");
        fwrite($file, "\n");
        fwrite($file, $body);
        fclose($file);

        echo(
            '<div class="success">Písnička ' . $title . ' byla úspěšně přidána!</div>'
        );
    }
}
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $song_file = $_REQUEST['song'];
    $song_contents = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/songs/' . $song_file));
    $song_contents_title = $song_contents->matter('title');
    $song_contents_author = $song_contents->matter('author');
    if ($song_contents->matter('capo') == null) {
        $song_contents_capo = 0;
    }
    else {
        $song_contents_capo = $song_contents->matter('capo');
    }
    $song_contents_body = $song_contents->body();
}
?>
<div>
    <a href="index.html"><button class="icon_home"></button></a>
    <a href="help.html"><button class="icon_help"></button></a>
</div>
<div style="position: absolute; width: 64vw; left: 18vw;top: 0">
    <h1>Editor písniček</h1>
</div>
<div class="editor" style="position: relative; top: 100px">
    <div id="selection" class="selection">
        <div style="direction: ltr">
        <form id="songopen_form" method="get">
            <input type="hidden" name="song" id="songopen" value="value">
        </form>
        <script>
            function songSelector(file) {
                document.getElementById('songopen').value = file;
            }
        </script>
            <ul>
                <?php
                $files = scandir(__DIR__ . '/songs/');
                foreach ($files as $file){
                    if ($file === '.' || $file==='..') {
                        continue;
                    }
                    $object = \Spatie\YamlFrontMatter\YamlFrontMatter::parse(file_get_contents(__DIR__ . '/songs/' . $file));
                    $name = str_replace(' ', '_', $object->matter('title'));
                    $name = str_replace(str_split('\:*?<>.,!'), '', $name);
                    $name = iconv('utf-8', 'ascii//TRANSLIT', $name);
                    $name = strtolower($name);
                    $name .= ".md'";
                    $name = "'" . $name;
                    echo ('<button class="song_selector" onclick="songSelector(' . $name . ')">' . $object->matter('title') . '</button><br>');
                }
                ?>
            </ul>
        </div>
    </div>
    <div style="position: absolute; width: 60px; height: 168px; left: 50%; transform: translate(-30px)">
        <button class="icon_right" form="input_form" type="submit"></button>
        <button class="icon_left" form="songopen_form" type="submit"></button>
    </div>
    <form method="post" id="input_form" style="width: 45vw; margin: 0">
        Název: <input class="editor" type="text" name="title" id="title" required value="<?php echo (isset($song_contents_title))?$song_contents_title:'';?>"><br>
        Autor: <input class="editor" type="text" name="author" id="author" required value="<?php echo (isset($song_contents_author))?$song_contents_author:'';?>"><br>
        Capo: <input class="editor" type="number" name="capo" id="capo" min="0" required value="<?php echo (isset($song_contents_capo))?$song_contents_capo:'';?>"><br>
    </form>
    <script src="data/libs/jquery-3.4.1.min.js"></script>
    <div style="width: max-content; left: 2.5vw; bottom: 10px; margin: 10px; position: sticky">
        <button onclick="addVerse()" class="editor_button">Přidat sloku</button>
        <button onclick="addChord()" class="editor_button">Přidat akord</button>
        <button onclick="addBreak()" class="editor_button">Přidat konec řádku</button>
        <button onclick="addRepetitionStart()" class="editor_button">&#x1d106;</button>
        <button onclick="addRepetitionEnd()" class="editor_button">&#x1d107;</button>
        <button onclick="addFlat()" class="editor_button">&flat;</button>
    </div>
    <textarea wrap="soft" oninput="onInputFnc(this)" class="editor" style="transform: translate(-47.5vw)" name="body" form="input_form" id="song" required><?php echo (isset($song_contents_body))?$song_contents_body:'';?></textarea>
    <div id="preview_div" class="editor" style="position: absolute; min-height: 30vh; width: 45vw; left: 50%; margin: 0; resize: none; overflow: hidden; height: max-content; transform: translate(2.5vw)">
        <p oninput="onInputFnc(this)" contenteditable="true" style="margin: 0; min-height: 30vh;width: 40vw; transform: translate(5vw)" id="preview"></p>
        <script>
            document.getElementById('preview').addEventListener('keydown', (e) => {
                switch (e.key) {
                    case 'Enter':
                        document.execCommand('insertHTML', false, '<br><br>');
                        break;
                    default:
                        return;
                }
                e.preventDefault()
            });
            window.currFocus = document;
            $(window).on( 'focusin', function () {
                window.prevFocus = window.currFocus;
                window.currFocus = document.activeElement;
            });
            function insertAtCursor(myValue) {
                let myField = window.prevFocus;
                if (myField.id !== 'song' && myField.id !== 'preview') {
                    myField = document.getElementById('song')
                }
                if (myField.tagName === 'TEXTAREA') {
                    if (document.selection) {
                        myField.focus();
                        let sel = document.selection.createRange();
                        sel.text = myValue;
                    } else if (myField.selectionStart || myField.selectionStart == '0') {
                        let startPos = myField.selectionStart;
                        let endPos = myField.selectionEnd;
                        myField.value = myField.value.substring(0, startPos)
                            + myValue
                            + myField.value.substring(endPos, myField.value.length);
                    } else {
                        myField.value += myValue;
                    }
                }
                else if (myField.tagName === 'DIV') {
                    if (document.selection) {
                        myField.focus();
                        let sel = document.selection.createRange();
                        sel.text = myValue;
                    } else if (myField.selectionStart || myField.selectionStart == '0') {
                        let startPos = myField.selectionStart;
                        let endPos = myField.selectionEnd;
                        myField.innerHTML = myField.innerHTML.substring(0, startPos)
                            + myValue
                            + myField.innerHTML.substring(endPos, myField.innerHTML.length);
                    } else {
                        myField.innerHTML += myValue;
                    }
                }
            }
            function autoGrow(element) {
                element.style.height = "5px";
                element.style.height = (element.scrollHeight) + "px";
            }
            function previewHandler(element) {
                if (element.id === 'song') {
                    document.getElementById('preview').innerHTML = document.getElementById('song').value;
                }

                else if (element.id === 'preview') {
                    let text = document.getElementById('preview').innerHTML;
                    document.getElementById('song').value = text;
                }
            }
            function onInputFnc(element) {
                autoGrow(element);
                previewHandler(element);
            }
            function addVerse() {
                let number = prompt('Číslo sloky:', '1');
                number = number.replace(/ /g, '&nbsp;');
                let text = '<verse number="' + number + ':"></verse>';
                insertAtCursor(text);
            }
            function addChord() {
                let chord = prompt('Akord:', 'C');
                chord = chord.replace(/ /g, '&nbsp;')
                let text = '<wrapper><chord>' + chord + '</chord></wrapper>';
                insertAtCursor(text);
            }
            function addBreak() {
                let text = '<br>\n';
                insertAtCursor(text);
                let selection = document.getSelection();
                document.getElementById('song').focus();
                selection.modify('move', 'forward', 'character')
            }
            function addRepetitionStart() {
                let text = '&#x1d106;';
                insertAtCursor(text);
            }
            function addRepetitionEnd() {
                let text = '&#x1d107;';
                insertAtCursor(text);
            }
            function addFlat() {
                let text = '&flat;';
                insertAtCursor(text);
            }
            document.addEventListener("DOMContentLoaded", () => {
                const buttons = document.getElementsByClassName("editor_button");
                const song = document.getElementById("song");

                song.dispatchEvent(new Event("input")); //Render the initial state

                for (let i = 0; i < buttons.length; i++) {
                    buttons[i].addEventListener("click", () => {
                        song.dispatchEvent(new Event("input")); //Re-render on edits using buttons
                    });
                }
            });
        </script>
    </div>
</div>
</body>
</html>