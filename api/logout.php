<?php

require_once __DIR__ . '/../includes/api.php';

requireApiMethod(['POST']);
requireApiCsrf();

logoutUser();

sendJson(null, 'Logged out successfully.', 200);
