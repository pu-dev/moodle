<?php
require_once('../../config.php');
defined('MOODLE_INTERNAL') || die();

global $CFG;

$session_key = sesskey();
$url = "{$CFG->wwwroot}/auth/oauth2/login.php?id=1&wantsurl=%2F&sesskey={$session_key}";
$header = "Location: {$url}";

header($header);
