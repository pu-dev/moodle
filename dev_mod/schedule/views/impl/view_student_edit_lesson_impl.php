<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../inc.php');
mod_require_once('/views/impl/view_base_impl.php');
mod_require_once('/components/student_edit_lesson_form.php');
mod_require_once('/components/student_edit_lesson_form_handler.php');


class view_student_edit_lesson_impl extends view_base_impl {
    private $lesson_id;

    public function __construct() {
        $this->lesson_id = required_param('lesson_id', PARAM_INT);
        parent::__construct();
    }

    protected function render() {
        $form = $this->create_lesson_form();
        $view_result = $this->process_lesson_form($form);
        return $view_result;
    }

    private function create_lesson_form() {
        global $DB;
        $lesson = $DB->get_record('schedule_lesson', array('id' => $this->lesson_id));

        $url_params = array(
            'id' => $this->cm->id,
            'lesson_id' => $this->lesson_id
        );

        $url = tools::get_self_url($url_params);

        $form = new student_edit_lesson_form($url, array('lesson'=>$lesson));
        return $form;
    }

    private function process_lesson_form($form) {
        $form_handler = new student_edit_lesson_form_handler($form);
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
        $url_base = '/views/view_student_old_lesson.php';
        $url_params = array($this->cm->id);
        $url = tools::get_module_url($url_base, $url_params);

        return new view_result_redirect($url);
    }
}
