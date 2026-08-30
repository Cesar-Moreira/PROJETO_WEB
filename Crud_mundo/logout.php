<?php
require_once __DIR__ . '/php/config/auth.php';

$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;
