<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../inc.php');
require_once($CFG->libdir.'/formslib.php');


class student_edit_lesson_form extends \moodleform {
    const COLUMNS = 35;
    const NOTES_ROWS = 5;

    private $lesson;
    /**
    */
    public function definition() {
        $this->lesson = $this->_customdata['lesson'];

        $this->create_date();
        $this->create_topic();
        $this->create_notes();
        $this->create_buttons();
        $this->create_lesson_id();
    }


    /**
    */
    private function create_topic() {
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


    private function create_notes() {
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


    private function create_date() {
        $mform =& $this->_form;
        $mform->addElement(
            'text', 
            'date',
            # todo
            'Date',
            array(
                'size' => self::COLUMNS,
                'disabled' => null
            )
        );

        $date = tools::epoch_to_date($this->lesson->date);
        $mform->setType('date', PARAM_NOTAGS);
        $mform->setDefault('date', $date);
    }


    private function create_lesson_id() {
        $mform =& $this->_form;
        $mform->addElement('hidden', 'lesson_id');
        $mform->setType('lesson_id', PARAM_INT);
        $mform->setDefault('lesson_id', $this->lesson->id);
    }


    private function create_buttons() {
        $mform =& $this->_form;

        $buttonarray=array();
        $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }
}
