<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BoRR zpěvník</title>
    <link rel="icon" href="data/borr.png">
    <link rel="stylesheet" href="css.css">
</head>
<body>
<?php
if ($_SERVER['REQUEST_METHOD']=='POST') {
    $title = $_REQUEST['title'];
    $author = $_REQUEST['author'];
    $capo = $_REQUEST['capo'];
    $body = $_REQUEST['body'];

    if ($title == null){
        exit();
    }
    else {
        $name = str_replace(' ', '_', $title);
        $name = strtolower($name);
        $name = iconv('utf-8', 'ascii//TRANSLIT', $name);

        if ($capo === 0) {
            $capo = null;
        }

        $file = fopen(__DIR__ . '/songs/' . $name . '.md', 'w');
        fwrite($file, "---\n");
        fwrite($file, "title: '" . $title . "'\n");
        fwrite($file, "author: '" . $author . "'\n");
        fwrite($file, "capo: '" . $capo . "'\n");
        fwrite($file, "---\n");
        fwrite($file, "\n");
        fwrite($file, $body);
        fclose($file);

        echo(
            '<div class="success">Písnička ' . $title . ' byla úspěšně přidána!</div>'
        );
    }
}
?>
<h1>Editor písniček</h1>
<div class="editor">
<form method="post" id="input_form">
    Název: <input class="editor" type="text" name="title" id="title" required><br>
    Autor: <input class="editor" type="text" name="author" id="author" required><br>
    Capo: <input class="editor" type="number" name="capo" id="capo" required><br>
    <button class="editor" type="submit">Dokončit</button>
</form><br>
<script>
    function autoGrow(element) {
        element.style.height = "5px";
        element.style.height = (element.scrollHeight) + "px";
    }
    function previewMaker() {
        var text = document.getElementById('song').value;
        document.getElementById('preview').innerHTML = text;
    }
    function onInputFnc(element) {
        autoGrow(element);
        previewMaker();
    }
    function addVerse() {
        var number = prompt('Číslo sloky:', '1');
        var text = document.getElementById('song').value;
        document.getElementById('song').value = text + '<verse number="' + number + ':"></verse>';
    }
    function addChord() {
        var chord = prompt('Akord:', 'C');
        var text = document.getElementById('song').value;
        document.getElementById('song').value = text + '<wrapper><chord>' + chord + '</chord></wrapper>';
    }
    function addBreak() {
        var text = document.getElementById('song').value;
        document.getElementById('song').value = text + '<br>' + '\n';
    }
</script>
<div style="width: max-content; left: 2vw; bottom: 10px; margin: 10px; position: sticky">
    <button onclick="addVerse()" class="editor_button">Přidat sloku</button>
    <button onclick="addChord()" class="editor_button">Přidat akord</button>
    <button onclick="addBreak()" class="editor_button">Přidat konec řádku</button>
</div>
<textarea wrap="soft" oninput="onInputFnc(this)" class="editor" style="transform: translate(-48vw)" name="body" form="input_form" id="song" required></textarea>
<p onchange="autoGrow(this)" class="editor" style="transform: translate(2vw)" id="preview"></p>
</div>
</body>
</html>