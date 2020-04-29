<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die(__FILE__);

require_once(dirname(__FILE__).'/../../inc.php');
mod_require_once('/views/impl/view_student_base_impl.php');


class view_student_book_lesson_calendar_impl extends view_student_base_impl {
    public function __construct() {
        parent::__construct(student_class_tabs::TAB_BOOK_LESSON_CALENDAR);
        $this->load_calendar_libs();
        $this->render_calendar_js();
    }

    protected function render() {
        $html = parent::render();
        $html .= $this->render_calendar_container();
        return new view_result_html($html);
    }

    private function render_calendar_container() {
        # todo
        $html = "
            <div style='width:85%; margin: auto' id='calendar-container'>
                <div id='calendar'></div>
            </div>";
        return $html;
    }

    private function load_calendar_libs() {
        global $CFG, $PAGE;

        $min = 'min.';
        if ( $CFG->debugdeveloper ) {
            $min = '';
        }

        $url_base = "/mod/schedule/js/fullcalendar_4.4.0/packages";

        $PAGE->requires->css("{$url_base}/core/main.{$min}css", true);
        $PAGE->requires->css("{$url_base}/daygrid/main.{$min}css", true);
        $PAGE->requires->css("{$url_base}/timegrid/main.{$min}css", true);
        $PAGE->requires->css("{$url_base}/list/main.{$min}css", true);

        $PAGE->requires->js("{$url_base}/core/main.{$min}js");
        $PAGE->requires->js("{$url_base}/interaction/main.{$min}js");
        $PAGE->requires->js("{$url_base}/daygrid/main.{$min}js");
        $PAGE->requires->js("{$url_base}/timegrid/main.{$min}js");
        $PAGE->requires->js("{$url_base}/list/main.{$min}js");
    }

    private function render_calendar_js() {
        global $PAGE, $USER;
        $opts = array(
            'cmid' => $this->cm->id,
            'user' => $USER->id
        );

        $PAGE->requires->js_call_amd('mod_schedule/main_student_manage_lesson', 'init', array($opts));
    }
}
