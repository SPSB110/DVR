<?php
// Legacy redirect — certificates are now accessed through role-based dashboards
require_once __DIR__ . '/config.php';
header('Location: /');
exit;
