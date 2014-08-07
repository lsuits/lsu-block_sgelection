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
require_once($CFG->dirroot . '/blocks/sgelection/lib.php');
require_once('sgedatabaseobject.php');

class voter extends sge_database_object {

    public static $tablename = 'block_sgelection_voters';

    public
            $firstname,
            $lastname,
            $username,
            $userid,
            $college,
            $major,
            $year,
            $courseload,
            $ip_address,
            $hours;

    const VOTER_NO_TIME   = 'X';
    const VOTER_PART_TIME = 'P';
    const VOTER_FULL_TIME = 'F';

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
        $this->ip_address = getremoteaddr();
        $this->courseload = $this->courseload();
    }

    public function at_least_parttime(){
        $pt = sge::config('parttime');
        return $this->hours >= $pt;
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
        $this->hours = $hours ? $hours : 0;

        if($hours < $parttime){
            $courseload = self::VOTER_NO_TIME;
        }elseif($parttime <= $hours && $hours < $fulltime){
            $courseload = self::VOTER_PART_TIME;
        }else{
            $courseload = self::VOTER_FULL_TIME;
        }

        $this->courseload = $courseload;
        return $this->courseload;
    }

    public static function courseload_string($courseload){
        $parttime = get_config('block_sgelection', 'parttime');
        $fulltime = get_config('block_sgelection', 'fulltime');
        switch($courseload){
            case 'X':
                return sprintf("Less than part-time enrollment (%s hours)",$parttime);
                break;
            case 'P':
                return sprintf("Part-time enrollment (%s hours)",$parttime);
                break;
            case 'F':
                return sprintf("Full-time enrollment (%s hours)",$fulltime);
                break;
        }
    }

    public function right_college() {
        return array();
    }

    /**
     * Is this voter the elections commissioner
     * @return boolean
     */
    public function is_commissioner() {
        if($this->username == sge::config('commissioner')){
            return true;
        }
        return false;
    }

    /**
     * Is this voter the SG Faculty advisor?
     * @return boolean
     */
    public function is_faculty_advisor() {
        if($this->username == sge::config('facadvisor')){
           return true;
        }
        return false;
    }

    public function mark_as_voted(election $election) {
        $row = new stdClass();
        $row->userid = $this->userid;
        $row->election_id = $election->id;

        global $DB;
        return $DB->insert_record('block_sgelection_voted', $row);
    }

}