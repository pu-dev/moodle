<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../inc.php');
mod_require_once('/tools.php');
mod_require_once('/views/impl/view_teacher_base_impl.php');
mod_require_once('/components/teacher_availability_form.php');
mod_require_once('/components/teacher_availability_form_handler.php');
mod_require_once('/components/teacher_manage_lesson_list.php');
mod_require_once('/actions/action_teacher_cancel_class.php');


class view_teacher_availability_impl extends view_teacher_base_impl {
    public const ACTION_NONE = 10;
    public const ACTION_CLASS_CANCEL = 11;

    public function __construct() {
        parent::__construct(teacher_class_tabs::TAB_AVAILABILITY_LESSON);
    }

    protected function render() {
        $html = parent::render();
        $html .= $this->process_action();
        $html .= $this->render_teacher_form();
        $html .= $this->render_teacher_lesson_list();
        return new view_result_html($html);
    }

    private function render_teacher_form() {
        $form_params = array('id' => $this->cm->id);
        $url_params = $form_params;

        $url = tools::get_self_url($url_params);
        
        debug("Teache availability form target url: {$url}");

        $teacher_form = new teacher_availability_form($url, $form_params);
        $teacher_form_handler = new teacher_availability_form_handler($teacher_form);

        $teacher_form_handler->process_form();
        return $teacher_form->render();
    }


    private function render_teacher_lesson_list() {
        $class_list = new teacher_manage_lesson_list($this->cm);
        return \html_writer::table($class_list->get_class_table());
    }


    private function process_action() {
        $action = optional_param('action', self::ACTION_NONE, PARAM_INT);
        $html = '';

        switch ($action) {
            case self::ACTION_NONE:
                // Do nothing here
                break;

            case self::ACTION_CLASS_CANCEL:
                $html = $this->action_class_cancel();    
                break;
            default:
                print_error("Invalid action: {$action}");
        }

        return $html;
    }

    private function action_class_cancel() {
        $class_id = required_param('class_id', PARAM_INT);
        $action = new action_teacher_cancel_class($this->cm, $class_id);
        $result = $action->execute();
        $hmlt = '';

        if ($result->ok) {
            $msg = get_string('class_canceled_ok', 'schedule');
            $html = $this->alert_success($msg);
        }
        else {
            $msg = get_string('class_canceled_failed', 'schedule');
            $html = $this->alert_success($msg);
        }
        return $html;
    }
}
