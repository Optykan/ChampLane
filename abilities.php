<?php
$champ = $_GET['champ'] or die("Missing GET parameters.");
$data = json_decode(file_get_contents("json/modalinfo.json"), true);
echo json_encode($data['data'][$champ]);
?>
