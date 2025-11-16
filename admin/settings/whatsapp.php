<?php
require_once __DIR__ . '/../../includes/auth.php';

requireAdmin();

header('Location: /admin/settings/general.php#messaging');
exit;
