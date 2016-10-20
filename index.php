<?php
require_once('vendor/autoload.php');
require_once('src/Controller.php');
require_once('src/GiphyApi.php');



$controller = main\Controller::getInstance();
$controller->execute();
