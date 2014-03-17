<?php
require_once "create_gcontact_file.php";

$id_contact = create_gcontact_file($_POST);
echo $id_contact;
?>
