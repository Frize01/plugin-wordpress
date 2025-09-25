<?php
/*
Plugin Name: Brevo Auto Campaign
Description: Envoie un mail automatique aux abonnés brevo quand un post est publié.
Version: 0.1.0
Author: Kévin V.
*/

declare(strict_types=1);

include_once __DIR__ . '/helpers/secure-storage.php';
include_once __DIR__ . '/helpers/brevo-api.php';
include_once __DIR__ . '/src/admin.php';
include_once __DIR__ . '/src/handle.php';
include_once __DIR__ . '/src/hook.php';