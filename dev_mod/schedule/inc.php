<?php namespace mod_schedule;

require_once(dirname(__FILE__).'/../../config.php');
mod_require_once('/debug.php');

function mod_require_once($file) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/schedule' . $file);
}

