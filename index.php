<?php
require_once 'includes/functions.php';

// Halaman awal: arahkan sesuai status login
redirect(isLoggedIn() ? 'dashboard.php' : 'login.php');