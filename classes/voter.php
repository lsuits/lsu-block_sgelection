<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    block_sgelection
 * @copyright  2014 Louisiana State University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once 'sgeobject.php';
require_once 'lib.php';

class voter extends sge_object {

    public $firstname, $lastname, $username, $userid, $major, $college, $year, $degree;

    const VOTER_NO_TIME   = 0;
    const VOTER_PART_TIME = 1;
    const VOTER_FULL_TIME = 2;

    public function __construct($userid){
        if(!is_numeric($userid)){
            throw new Exception(sprintf("rar! userid {$userid} is not an int!!!"));
        }
        global $DB;
        $usersql = "SELECT u.id userid, u.firstname, u.lastname, u.username"
                . " FROM {user} u"
                . " WHERE u.id = :userid";

        $params = $DB->get_record_sql($usersql, array('userid'=>$userid));

        $uessql = "SELECT name, value FROM {enrol_ues_usermeta} WHERE userid = :userid";

        $keyvalues = $DB->get_records_sql($uessql, array('userid'=>$userid));

        foreach($keyvalues as $pair){
            $name = sge::trim_prefix($pair->name, 'user_');
            $params->$name = $pair->value;
        }

        parent::__construct($params);
    }

    public function can_vote(election $election){
        $nays = array();
        $nays += sge::is_commissioner($this);
        $nays += sge::is_faculty_advisor($this);
        $nays += $this->is_parttime();
        $nays += $this->is_fulltime();
        $nays += $this->right_college();
        $nays += $election->polls_are_open();
    }

    public function is_parttime(){
        return array();
    }

    public function is_fulltime(){
        return array();
    }

    private function get_enrolled_hours(){
        global $DB;
        $sql = sprintf("SELECT sum(credit_hours) FROM {enrol_ues_students} WHERE userid = :userid AND status = 'enrolled'");

        return $DB->get_record_sql($sql, array('userid'=>$this->userid));
    }

    public function courseload(){
        global $DB;
        $hours = $DB->get_field('block_sgelection_hours', 'hours', array('userid'=>$this->userid));
        $parttime = get_config('block_sgelection', 'parttime');
        $fulltime = get_config('block_sgelection', 'fulltime');

        if($hours < $parttime){
            return self::VOTER_NO_TIME;
        }elseif($parttime <= $hours && $hours < $fulltime){
            return self::VOTER_PART_TIME;
        }else{
            return self::VOTER_FULL_TIME;
        }

    }

    public function right_college() {
        return array();
    }

}