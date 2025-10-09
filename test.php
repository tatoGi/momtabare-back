<?php
session_start();
$_SESSION['test'] = 'ok';
echo session_id();
print_r($_SESSION);