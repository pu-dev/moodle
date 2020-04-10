<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/inc.php');


class view_tools {
    static public function render($class_name) {
        $module_name = 'mod_schedule';
        $class_name = "\\{$module_name}\\{$class_name}";
        $view = new $class_name();
        return $view->view();    
    }

    static public function view_by_name($class_name) {
        echo self::render($class_name);
    }



    // static public function render_by_file($view_name) {
    //     $module_name = 'mod_schedule';
    //     $view_name = "\\{$module_name}\\{$view_name}";
    //     $view = new $view_name();
    //     return $view->view();    
    // }

    static public function view_by_file($file) {
        global $CFG;

        $path = pathinfo($file);
        $class_name = $path['filename'] . '_impl';
        $php_file = $path['dirname'] . '/impl/' . $class_name . '.php';
        require_once($php_file);
        debug("Loading view from file: " . $php_file);
        echo self::render($class_name);
    }    

}


