<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
Header('Content-Type: text/html; charset=utf-8');
define('MODE_UTF8', 'on');

define('DB_MDP', '');
set_include_path('/home/cedric/www/o/utilitaires/include/' . PATH_SEPARATOR . '/home/cedric/www/o/utilitaires/include/PEAR');
require CHEMIN_UTILITAIRES . 'tests.lib.php';

TU::Configurer(dirname(realpath(__FILE__)) . '/');

TU::main('orgabaka');