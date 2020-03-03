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

    echo (
            '<div class="success">Písnička ' . $title . ' byla úspěšně přidána!</div>'
    );

}
else {
    exit;
}
?>
<script>
    function autoGrow(element) {
        element.style.height = "5px";
        element.style.height = (element.scrollHeight) + "px";
    }
</script>
<h1>Editor písniček</h1>
<div class="editor">
<form method="post" id="input_form">
    Název: <input class="editor" type="text" name="title"><br>
    Autor: <input class="editor" type="text" name="author"><br>
    Capo: <input class="editor" type="number" name="capo"><br>
    <button class="editor" type="submit">Dokončit</button>
</form>
<br>
<textarea wrap="soft" oninput="autoGrow(this)" class="editor" name="body" form="input_form"></textarea>
</div>
</body>
</html>
