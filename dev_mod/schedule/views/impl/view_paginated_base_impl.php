<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../inc.php');
mod_require_once('/views/impl/view_base_impl.php');

class view_time_value {

    public $label;
    public $start_date;
    public $stop_date;

    public function __construct($label, $start_date, $stop_date) {
        $this->label = $label;
        $this->start_date = $start_date;
        $this->stop_date = $stop_date;
    }
}


abstract class view_paginated_base_impl extends view_base_impl {

    protected $view_type;
    protected $view_date;

    public function __construct() {
        parent::__construct();

        // $this->view_type = optional_param('view', VIEW_ALL, PARAM_INT);
        // $this->view_date = optional_param('curdate', time(), PARAM_INT);

        // $this->tmp();
    }


    protected function tmp() {
        $month = date('M', $this->view_date);

        $next_month = date('M', strtotime('+1 month', $this->view_date));

        $date_next_month = date('Y-M-01', strtotime('+1 month', $this->view_date));
        $date_next_next_month = date('Y-M-01', strtotime('+2 month', $this->view_date));

        $epoch_next_month = strtotime($date_next_month);
        $epoch_next_next_month = strtotime($date_next_next_month);

        // $t = date('M', strtotime('+0 month', $tmp_epoch));
        // debug('month: '.$month);
        // debug('next_month: '.$next_month);
        // debug('cur date: '.$this->view_date);
        // debug('tmp date: '.$tmp_date);


        // debug('epoch next month: '.$epoch_next_month);
        // debug('date_next_month: '.$date_next_month);
        // debug('epoch next next month: '.$epoch_next_next_month);
        // debug('date_next_next_month: '.$date_next_next_month);

        // debug('t : '.$t);
    }


    function get_month_range() {
        $next_month = date('M', strtotime('+1 month', $this->view_date));

        $date_next_month = date('Y-M-01', strtotime('+1 month', $this->view_date));
        $date_next_next_month = date('Y-M-01', strtotime('+2 month', $this->view_date));

        $epoch_next_month = strtotime($date_next_month);
        $epoch_next_next_month = strtotime($date_next_next_month);

        return new view_time_value(
            $next_month, 
            $epoch_next_month, 
            $epoch_next_next_month
        );

    }


    protected function render() {
        // $html = parent::render();
        $html ='';
        $html .= $this->render_view_type_controls();
        $html .= $this->render_set_date_controls();
        return $html;
    }



    protected function render_view_type_controls() {
        # todo
        $views[VIEW_ALL] = 'All';
        $views[VIEW_MONTH] = 'Months';
        $views[VIEW_WEEK] = 'Weeks';

        $view_type_controls = '';


        foreach ($views as $key => $view_name) {
            $url_params = array(
                'id' => $this->cm->id,
                'view' => $key
            );
            $url = tools::get_self_url($url_params);

            $tag_params = array(
                'class' => 'mod-schedule-set-view-btn');

            $link = \html_writer::link($url, $view_name);
            $view_type_controls .= \html_writer::tag(
                'span', 
                $link, 
                $tag_params);
        }

        $out =  \html_writer::tag('nobr', $view_type_controls);
        return $out;
    }


    protected function render_set_date_controls() {
        global $CFG;
        global $OUTPUT;
        $curdatecontrols = '';
        // if ($fcontrols->curdatetxt) {
            // $this->page->requires->strings_for_js(array('calclose', 'caltoday'), 'attendance');
            // $jsvals = array(
            //         'cal_months'    => explode(',', get_string('calmonths', 'attendance')),
            //         'cal_week_days' => explode(',', get_string('calweekdays', 'attendance')),
            //         'cal_start_weekday' => $CFG->calendar_startwday,
            //         'cal_cur_date'  => $fcontrols->curdate);
            // $curdatecontrols = html_writer::script(js_writer::set_variable('M.attendance', $jsvals));

            // $this->page->requires->js('/mod/attendance/calendar.js');

            $curdatecontrols .= \html_writer::link("http://wp.pl/1", $OUTPUT->larrow());
            $params = array(
                    'title' => get_string('calshow', 'attendance'),
                    'id'    => 'show',
                    'class' => 'btn btn-secondary',
                    'type'  => 'button');
            $buttonform = \html_writer::tag('button', 'text', $params);

            // foreach ($fcontrols->url_params(array('curdate' => '')) as $name => $value) {
            //     $params = array(
            //             'type'  => 'hidden',
            //             'id'    => $name,
            //             'name'  => $name,
            //             'value' => $value);
            //     $buttonform .= html_writer::empty_tag('input', $params);
            // }
            $params = array(
                    'id'        => 'currentdate',
                    'action'    => "http://wp.pl/2",
                    'method'    => 'post'
            );

            $buttonform = \html_writer::tag('form', $buttonform, $params);
            $curdatecontrols .= $buttonform;

            $curdatecontrols .= \html_writer::link("http://wp.pl/3",       $OUTPUT->rarrow());
        // }

        $out =  \html_writer::tag('nobr', $curdatecontrols);
    
        return $out;
    }




}


