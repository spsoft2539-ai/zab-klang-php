<?php
require_once 'auth.php';
clearUserSession();
header('Location: login.php');
exit;
