<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../inc.php');
mod_require_once('/views/impl/view_base_impl.php');


abstract class view_edit_lesson_base_impl extends view_base_impl {
    private $lesson_id;
    private $form;
    private $form_handler;
    private $final_url;


    public function __construct($class_form, $class_form_handler, $final_url) {
        parent::__construct();

        $this->lesson_id = required_param('lesson_id', PARAM_INT);

        $this->form = "\\mod_schedule\\{$class_form}";
        $this->form_handler = "\\mod_schedule\\{$class_form_handler}";
        $this->final_url = $final_url;

    }

    protected function render() {
        $form = $this->create_lesson_form();
        $view_result = $this->process_lesson_form($form);
        return $view_result;
    }

    private function create_lesson_form() {
        global $DB;
        $lesson = $DB->get_record(
            'schedule_lesson', 
            array('id' => $this->lesson_id));

        $url_params = array(
            'id' => $this->cm->id,
            'lesson_id' => $this->lesson_id
        );

        $url = tools::get_self_url($url_params);
        $form_data = array('lesson'=>$lesson);

        return new $this->form($url, $form_data);
    }


    private function process_lesson_form($form) {
        $form_handler = new $this->form_handler($form);
        $form_status = $form_handler->process_form();
        
        if ($form_status == form_handler_base::FORM_DISPLAYED) {
            $html = $form->render();
            return new view_result_html($html);
        }
        else {
            return $this->redirect();
        }
    }

    private function redirect() {
        $url_params = array('id' => $this->cm->id);
        $url = tools::get_module_url($this->final_url, $url_params);

        return new view_result_redirect($url);
    }
}
