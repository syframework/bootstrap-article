<?php
include __DIR__ . '/ServiceContainer.php';

define('WEB_ROOT', '/webroot');
define('CONTROLLER_TRIGGER', 'controller');
define('ACTION_TRIGGER', 'action');
define('ACTION_PARAM', 'action_param');
define('LANG_DIR', dirname(__DIR__) . '/lang');
define('LANG', 'fr');
define('LANGS', ['fr' => 'Fran&ccedil;ais', 'en' => 'English', 'es' => 'Espa&ntilde;ol', 'it' => 'Italiano']);
define('UPLOAD_DIR', sys_get_temp_dir() . '/sy-article-upload');
define('UPLOAD_ROOT', '/upload');
define('AVATAR_DIR', sys_get_temp_dir() . '/sy-article-avatar');
