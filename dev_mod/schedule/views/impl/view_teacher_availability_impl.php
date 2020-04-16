<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../inc.php');
mod_require_once('/tools.php');
mod_require_once('/views/impl/view_teacher_base_impl.php');
mod_require_once('/components/teacher_availability_form.php');
mod_require_once('/components/teacher_availability_form_handler.php');
mod_require_once('/components/teacher_multi_availability_form.php');
mod_require_once('/components/teacher_multi_availability_form_handler.php');
mod_require_once('/components/teacher_manage_lesson_list.php');
mod_require_once('/actions/action_teacher_cancel_class.php');


class view_teacher_availability_impl extends view_teacher_base_impl {
    public const ACTION_NONE = 10;
    public const ACTION_CLASS_CANCEL = 11;

    public function __construct() {
        parent::__construct(teacher_class_tabs::TAB_AVAILABILITY_LESSON);
    }

    protected function render() {
        $single_form_ret = $this->render_single_date_form();
        $mutli_form_ret = $this->render_multi_date_form();

        $html = parent::render();
        $html .= $this->process_action();
        $html .= $single_form_ret['msg'];
        $html .= $mutli_form_ret['msg'];
        $html .= $single_form_ret['form'];
        $html .= $mutli_form_ret['form'];
        $html .= $this->render_lesson_list();
        return new view_result_html($html);
    }

    private function render_single_date_form() {
        $msgs = array(
            form_handler_base::FORM_SAVED => 'Single session has been saved'
        );
        return $this->render_form(
            'teacher_availability_form',
            'teacher_availability_form_handler',
            $msgs
        );
    }

    private function render_multi_date_form() {
        $msgs = array(
            form_handler_base::FORM_SAVED => 'Multi sessions have been saved'
        );
        return $this->render_form(
            'teacher_multi_availability_form',
            'teacher_multi_availability_form_handler',
            $msgs
        );
    }

    private function render_form(
        $class_form_name,
        $class_handler_name,
        $msgs) 
    {
        $form_params = array('id' => $this->cm->id);
        $url_params = $form_params;
        $url = tools::get_self_url($url_params);
        
        $class_form_name = '\\mod_schedule\\'.$class_form_name;
        $class_handler_name = '\\mod_schedule\\'.$class_handler_name;
        $form = new $class_form_name($url, $form_params);
        $form_handler = new $class_handler_name($form);

        $form_result = $form_handler->process_form();
        $form_msg = $this->render_form_message($form_result, $msgs);

        return array(
            'form' => $form->render(),
            'msg' => $form_msg
        );
    }

    private function render_form_message($form_result, $msgs) {
        $html = '';
        
        switch ($form_result) {
            case form_handler_base::FORM_SAVED:
                $html = $this->alert_success($msgs[$form_result]);                
                break;

            case form_handler_base::FORM_DISPLAYED:
                // $html = $this->alert_success("display");
                break;
            
            default:
                // $html = $this->alert_success("default");
                break;
        }

        return $html;               
    }

    private function render_lesson_list() {
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
            #todo 
            $msg = get_string('class_canceled_ok', 'schedule');
            $html = $this->alert_success($msg);
        }
        else {
            #todo
            $msg = get_string('class_canceled_failed', 'schedule');
            $html = $this->alert_success($msg);
        }
        return $html;
    }
}
