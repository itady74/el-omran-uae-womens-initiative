<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();

logout();
redirect(APP_URL . '/login.php');
