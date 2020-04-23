<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../inc.php');
require_once($CFG->libdir.'/formslib.php');


abstract class edit_lesson_form_base extends \moodleform {
    private const COLUMNS = 40;
    private const NOTES_ROWS = 12;

    protected $lesson;
    protected $final_url;

    /**
    */
    public function definition() {
        $this->lesson = $this->_customdata['lesson'];
        $this->final_url = $this->_customdata['final_url'];

        $this->create_date();
        $this->create_topic();
        $this->create_notes();
        $this->create_buttons();
        $this->create_lesson_id();
        $this->create_final_url();
    }


    /**
    */
    protected function create_topic() {
        $mform =& $this->_form;
        $mform->addElement(
            'text', 
            'topic',
            # todo
            'Topic',
            array('size' => self::COLUMNS)
        );
        $mform->setType('topic', PARAM_NOTAGS);
        $mform->setDefault('topic', $this->lesson->topic);
    }


    protected function create_notes() {
        $mform =& $this->_form;
        $mform->addElement(
            'textarea', 
            'notes',
            # todo
            'Notes',
            array(
                'cols' => self::COLUMNS,
                'rows' => self::NOTES_ROWS,
                'wrap' => 'virtual'
            )
        );
        $mform->setDefault('notes', $this->lesson->notes);
    }


    protected function create_date() {
        $mform =& $this->_form;
        
        $input_date = $mform->createElement(
            'text', 
            'date',
            '',
            array(
                'size' => self::COLUMNS / 2 - 3,
                'disabled' => null
            )
        );
        $date = tools::epoch_to_date($this->lesson->date);
        $mform->setType('date', PARAM_NOTAGS);
        $mform->setDefault('date', $date);


        $input_time = $mform->createElement(
            'text', 
            'time',
            '',
            array(
                'size' => self::COLUMNS / 2 - 2,
                'disabled' => null
            )
        );

        $time = tools::epoch_to_time($this->lesson->date);
        $mform->setType('time', PARAM_NOTAGS);
        $mform->setDefault('time', $time);

        $group = array(
            $input_date,
            $input_time);

        # todo
        $mform->addGroup(
            $group, 
            '', 
            'Date', 
            array(' '), 
            false);
    }


    protected function create_lesson_id() {
        $mform =& $this->_form;
        $mform->addElement('hidden', 'lesson_id');
        $mform->setType('lesson_id', PARAM_INT);
        $mform->setDefault('lesson_id', $this->lesson->id);
    }


    protected function create_buttons() {
        $mform =& $this->_form;
        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }


    protected function create_final_url() {
        $mform =& $this->_form;
        $mform->addElement('hidden', 'final_url');
        $mform->setType('final_url', PARAM_LOCALURL);
        $mform->setDefault('final_url', $this->final_url);
    }

}
