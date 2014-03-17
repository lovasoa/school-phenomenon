<?php
require 'Mustache/Autoloader.php';
Mustache_Autoloader::register();

$mustache = new Mustache_Engine(array(
	"cache" => "templates/cache",
    "loader" => new Mustache_Loader_FilesystemLoader('templates'),
));
?>
