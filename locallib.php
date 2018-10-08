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
  * Plugin administration pages are defined here.
  *
  * @package     assignsubmission_ncmzoom
  * @category    admin
  * @copyright   2018 Nicolas Jourdain <nicolas.jourdain@navitas.com>
  * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/mod/assign/submission/ncmzoom/classes/webservice.php');
require_once($CFG->libdir.'/eventslib.php');

class assign_submission_ncmzoom extends assign_submission_plugin {

    /**
     * Get the name of the zoom recording submission plugin
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'assignsubmission_ncmzoom');
    }

    /**
     * Get Zoom Recording submission information from the database
     *
     * @param int $submissionid
     * @return mixed
     */
    private function get_ncmzoom_submission($submissionid) {
        global $DB;
        return $DB->get_record('assignsubmission_ncmzoom', array('submission' => $submissionid));
    }

    /**
     * Get the default setting for zoom recording submission plugin
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        global $CFG, $COURSE;

    }

    /**
     * Save the settings for file submission plugin
     *
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(stdClass $data) {

        // TODO: Based on the user list return false if the user is not in the list;

        // $this->set_config('maxfilesubmissions', $data->assignsubmission_ncmzoom_maxfiles);
        // $this->set_config('maxsubmissionsizebytes', $data->assignsubmission_ncmzoom_maxsizebytes);

        // if (!empty($data->assignsubmission_ncmzoom_filetypes)) {
        //     $this->set_config('filetypeslist', $data->assignsubmission_ncmzoom_filetypes);
        // } else {
        //     $this->set_config('filetypeslist', '');
        // }

        return true;
    }

    /**
     * Add elements to submission form
     *
     * @param mixed $submission stdClass|null
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return bool
     */
    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data) {
        global $OUTPUT, $USER, $CFG;

        $config = get_config('mod_ncmzoom');

        $service = new assignsubmission_ncmzoom_webservice();
        $zoomid = $USER->profile['ncmzoomid'];
        var_dump($USER);
        $service->get_cloud_recordings($zoomid);

        $return = new stdClass();
        $result = $service->lastresponse;

        $mform->addElement('html', "<p class='lead'>Select the Zoom Recording you want to submit:</p>");
        $mform->addElement('html', "<p>Only recordings from the last 30 days are displayed.</p>");

        if (empty($result->meetings)) {
            $mform->addElement('html', '<div class="alert alert-warning" role="alert">No meeting found.</div>');
        } else {

            // Loop on each meeting.
            foreach ($result->meetings as $meeting) {
                // If there is any recording.
                if ($meeting->recording_count > 0) {
                    // Loop for each Recording file.
                    foreach ($meeting->recording_files as $recordingfile) {
                        // We are only showing Video (MP4) recording.
                        if ($recordingfile->file_type === 'MP4') {

                            $d = new DateTime($recordingfile->recording_start);
                            $tz = new DateTimeZone($USER->timezone);
                            $d->setTimezone($tz);
                            $format = "D, d M Y H:i:s";
                            $text = $d->format($format);

                            $radiovalue = $meeting->uuid . "#" . $recordingfile->id;

                            // Add Radio Button.
                            $mform->addElement('radio', 'recording', null, $text, $radiovalue);
                            $html = '<iframe width="320" height="240" style="border:0" src="
                                '.$recordingfile->play_url.'"></iframe><hr/>';
                            // Add Zoom IFrame.
                            $mform->addElement('html', $html);
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Save the files and trigger plagiarism plugin, if enabled,
     * to scan the uploaded files via events trigger
     *
     * @param stdClass $submission
     * @param stdClass $data
     * @return bool
     */
    public function save(stdClass $submission, stdClass $data) {
        global $USER, $DB;

        $ncmzoomsubmission = $this->get_ncmzoom_submission($submission->id);

        $selectedrecording = explode('#', $data->recording);
        $meetinguuid = $selectedrecording[0];
        $recordingfileid = $selectedrecording[1];

        $groupname = null;
        $groupid = 0;

        $params = array(
            'context' => context_module::instance($this->assignment->get_course_module()->id),
            'courseid' => $this->assignment->get_course()->id,
        );

        // Get the group name as other fields are not transcribed in the logs and this information is important.
        if (empty($submission->userid) && !empty($submission->groupid)) {
            $groupname = $DB->get_field('groups', 'name', array('id' => $submission->groupid), MUST_EXIST);
            $groupid = $submission->groupid;
        } else {
            $params['relateduserid'] = $submission->userid;
        }

        $params['other'] = array(
            'submissionid' => $submission->id,
            'submissionattempt' => $submission->attemptnumber,
            'submissionstatus' => $submission->status,
            'groupid' => $groupid,
            'groupname' => $groupname,
            'meetinguuid' => $meetinguuid,
            'recordingfileid' => $recordingfileid
        );

        if ($ncmzoomsubmission) {
            $ncmzoomsubmission->meetinguuid = $meetinguuid;
            $ncmzoomsubmission->recordingfileid = $recordingfileid;
            $updatestatus = $DB->update_record('assignsubmission_ncmzoom', $ncmzoomsubmission);
            $params['objectid'] = $ncmzoomsubmission->id;

            $event = \assignsubmission_ncmzoom\event\submission_updated::create($params);
            $event->set_assign($this->assignment);
            $event->trigger();
            return $updatestatus;
        } else {
            $ncmzoomsubmission = new stdClass();
            $ncmzoomsubmission->submission = $submission->id;
            $ncmzoomsubmission->assignment = $this->assignment->get_instance()->id;
            $ncmzoomsubmission->meetinguuid = $meetinguuid;
            $ncmzoomsubmission->recordingfileid = $recordingfileid;

            $ncmzoomsubmission->id = $DB->insert_record('assignsubmission_ncmzoom', $ncmzoomsubmission);
            $params['objectid'] = $ncmzoomsubmission->id;

            $event = \assignsubmission_ncmzoom\event\submission_created::create($params);
            $event->set_assign($this->assignment);
            $event->trigger();
            return $ncmzoomsubmission->id > 0;
        }
    }

    /**
     * Display the list of files  in the submission status table
     *
     * @param stdClass $submission
     * @param bool $showviewlink Set this to true if the list of files is long
     * @return string
     */
    public function view_summary(stdClass $submission, & $showviewlink) {

        global $DB;

        // Never show a link to view full submission.
        $showviewlink = false;

        $ncmzoomsubmission = $this->get_ncmzoom_submission($submission->id);
        if ($ncmzoomsubmission) {
            $myrecording = $this->get_zoom_cloud_recording($ncmzoomsubmission);

            $sd = new DateTime($myrecording->recording_start);
            $ed = new DateTime($myrecording->recording_end);

            // $interval = $sd->diff($ed);
            // $mydiff = $interval->format("%H:%I:%S");

            $format = "D, d M Y H:i:s O";
            $text = $sd->format($format);
            // $text .= " (" . $mydiff . ")";

            $itemname = $myrecording->other->topic . ' on ' . $text .', Zoom Meeting '. $myrecording->other->meeting_id;
            $itemname = '<a href="'.$myrecording->play_url.'">'.$itemname.'</a>';
            $o = $this->assignment->get_renderer()->container($itemname . " " , 'ncmzoomcontainer');
            return $o;
        } else {
            return 'none';
        }
    }

    /**
     * No full submission view - the summary contains the list of files and that is the whole submission
     *
     * @param stdClass $submission
     * @return string
     */
    public function view(stdClass $submission) {

        $ncmzoomsubmission = $this->get_ncmzoom_submission($submission->id);

        $myrecording = $this->get_zoom_cloud_recording($ncmzoomsubmission);

        $html = "<a href=\"".$myrecording->play_url."\" target=\"_blank\">External Link</a>";
        $html .= "<iframe src=\"".$myrecording->play_url."\" width='600px' height='380px' style='border:0'></iframe>";
        return $html;
    }

    /**
     * Return true if there are no meeting uuid and recording file id
     * @param stdClass $submission
     */
    public function is_empty(stdClass $submission) {
        return false;
        // TODO: Need to call.
    }

    /**
     * Copy the student's submission from a previous submission. Used when a student opts to base their resubmission
     * on the last submission.
     * @param stdClass $sourcesubmission
     * @param stdClass $destsubmission
     */
    public function copy_submission(stdClass $sourcesubmission, stdClass $destsubmission) {
        global $DB;

        // Copy the files across.
        $contextid = $this->assignment->get_context()->id;
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid,
                                     'assignsubmission_ncmzoom',
                                     assignsubmission_ncmzoom_FILEAREA,
                                     $sourcesubmission->id,
                                     'id',
                                     false);
        foreach ($files as $file) {
            $fieldupdates = array('itemid' => $destsubmission->id);
            $fs->create_file_from_storedfile($fieldupdates, $file);
        }

        // Copy the assignsubmission_ncmzoom record.
        if ($filesubmission = $this->get_ncmzoom_submission($sourcesubmission->id)) {
            unset($filesubmission->id);
            $filesubmission->submission = $destsubmission->id;
            $DB->insert_record('assignsubmission_ncmzoom', $filesubmission);
        }
        return true;
    }

    /**
     * Formatting for log info
     *
     * @param stdClass $submission The submission
     * @return string
     */
    public function format_for_log(stdClass $submission) {
        // Format the info for each submission plugin (will be added to log).
        return get_string('zoomrecforlog', 'assignsubmission_ncmzoom');
    }

    /**
     * The assignment has been deleted - cleanup
     *
     * @return bool
     */
    public function delete_instance() {
        global $DB;
        // Will throw exception on failure.
        $DB->delete_records('assignsubmission_ncmzoom',
                            array('assignment' => $this->assignment->get_instance()->id));

        return true;
    }

    /**
     * Return the plugin configs for external functions.
     *
     * @return array the list of settings
     * @since Moodle 3.2
     */
    public function get_config_for_external() {
        return (array) $this->get_config();
    }

    /**
     * The submission comments plugin has no submission component so should not be counted
     * when determining whether to show the edit submission link.
     * @return boolean
     */
    public function allow_submissions() {
        return true;
    }

    protected function get_zoom_cloud_recording($ncmzoomsubmission) {

        $service = new assignsubmission_ncmzoom_webservice();
        $service->get_meeting_cloud_recordings($ncmzoomsubmission->meetinguuid);

        $result = $service->lastresponse;
        $myrecording = null;
        // Find the recording file.
        foreach ($result->recording_files as $recordingfile) {
            if ($recordingfile->id === $ncmzoomsubmission->recordingfileid) {
                $myrecording = $recordingfile;
                $myrecording->other->meeting_id = $result->id;
                $myrecording->other->topic = $result->topic;
                break;
            }
        }
        return $myrecording;
    }
}