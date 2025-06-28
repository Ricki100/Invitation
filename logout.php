<?php
require_once 'config.php';

// Clear all session data
session_destroy();

// Redirect to login page
redirect('google_login.php');
?> 