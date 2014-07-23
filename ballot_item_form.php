<?php
require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

class ballot_item_form extends moodleform {
    function definition() {
        global $DB;
        $mform =& $this->_form;
        var_dump($this->_customdata);
        // ADD CANDIDATES HEADER
        $mform->addElement('header', 'displayinfo', get_string('create_new_candidate', 'block_sgelection'));

        $attributes = array('size' => '50', 'maxlength' => '100');
        $mform->addElement('text', 'username', get_string('paws_id_of_candidate', 'block_sgelection'), $attributes);
        $mform->setType('username', PARAM_TEXT);
        
        //add office dropdown
        $attributes = array('dave' => 'dave', 'elliott' => 'elliott');
        $mform->addElement('select', 'affiliation', get_string('affiliation', 'block_sgelection'), $attributes);
        
        // add affiliation dropdown
        $options = $DB->get_records_menu('block_sgelection_office');
        $mform->addElement('select', 'office', get_string('office_candidate_is_running_for', 'block_sgelection'),$options);
        
        $buttons = array(
            $mform->createElement('submit', 'save_candidate', get_string('savechanges')),
            $mform->createElement('submit', 'delete', get_string('delete')),
            $mform->createElement('cancel')
        );
        $mform->addGroup($buttons, 'buttons', 'actions', array(' '), false);
        
        // add resolution header
        $mform->addElement('header', 'displayinfo', get_string('create_new_resolution', 'block_sgelection'));

        $attributes = array('size' => '50', 'maxlength' => '100');
        $mform->addElement('text', 'title_of_resolution', get_string('title_of_resolution', 'block_sgelection'), $attributes);
        $mform->setType('title_of_resolution', PARAM_TEXT);
        
        $attributes = array('size' => '50', 'maxlength' => '100');
        $mform->addElement('textarea', 'resolution_text', get_string('resolution_text', 'block_sgelection'), $attributes);
        $mform->setType('resolution_text', PARAM_TEXT);        
        // add affiliation dropdown
        $options = $DB->get_records('block_sgelection_office');
        for($i = 1; $i <= count($options); ++$i) {
            $officeName[$options[$i]->name] = $options[$i]->name;
        }
        $buttons = array(
            $mform->createElement('submit', 'save_resolution', get_string('savechanges')),
            $mform->createElement('submit', 'delete', get_string('delete')),
            $mform->createElement('cancel')
        );
        $mform->addGroup($buttons, 'buttons', 'actions', array(' '), false);        
        
        // add office header
        $mform->addElement('header', 'displayinfo', get_string('create_new_office', 'block_sgelection'));

        $attributes = array('size' => '50', 'maxlength' => '100');
        $mform->addElement('text', 'title_of_office', get_string('title_of_office', 'block_sgelection'), $attributes);
        $mform->setType('title_of_office', PARAM_TEXT);
        
        $attributes = array('size' => '50', 'maxlength' => '100');
        $mform->addElement('text', 'number_of_openings', get_string('number_of_openings', 'block_sgelection'), $attributes);
        $mform->setType('number_of_openings', PARAM_TEXT);
        
        // Limit to College
        $attributes = array('None' => 'none','Agriculture' => 'Agriculture', 'Art & Design' => 'Art & Design', 
            'Business, E. J. Ourso' => 'Business, E. J. Ourso', 'Coast and Environment' => 'Coast and Environment', 
            'Continuing Education' => 'Continuing Education', 'Engineering' => 'Engineering', 'Graduate School' => 'Graduate School', 
            'Honors College' => 'Honors College');
        
        $mform->addElement('select', 'limit_to_college', get_string('limit_to_college', 'block_sgelection'), $attributes);
        
        $buttons = array(
            $mform->createElement('submit', 'save_office', get_string('savechanges')),
            $mform->createElement('submit', 'delete', get_string('delete')),
            $mform->createElement('cancel')
        );
        $mform->addGroup($buttons, 'buttons', 'actions', array(' '), false);        
    }
}
