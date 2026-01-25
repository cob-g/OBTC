<?php
require __DIR__ . '/../app/bootstrap.php';

auth_logout();
redirect(url('/auth/login.php'));
