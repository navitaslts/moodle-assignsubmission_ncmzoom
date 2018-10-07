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

require_once($CFG->libdir.'/eventslib.php');

defined('MOODLE_INTERNAL') || die();

global $CFG;
//require_once($CFG->dirroot.'/mod/ncmzoom/lib.php');
require_once($CFG->dirroot.'/mod/assign/submission/ncmzoom/classes/webservice.php');

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
        return $DB->get_record('assignsubmission_ncmzoom', array('submission'=>$submissionid));
    }

    /**
     * Get the default setting for zoom recording submission plugin
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        global $CFG, $COURSE;

        // $defaultmaxfilesubmissions = $this->get_config('maxfilesubmissions');
        // $defaultmaxsubmissionsizebytes = $this->get_config('maxsubmissionsizebytes');
        // if ($this->assignment->has_instance()) {
        //     $defaultfiletypes = $this->get_config('filetypeslist');
        // } else {
        //     $defaultfiletypes = get_config('assignsubmission_ncmzoom', 'filetypes');
        // }
        // $defaultfiletypes = (string)$defaultfiletypes;

        // $settings = array();
        // $options = array();
        // for ($i = 1; $i <= get_config('assignsubmission_ncmzoom', 'maxfiles'); $i++) {
        //     $options[$i] = $i;
        // }

        // $name = get_string('maxfilessubmission', 'assignsubmission_ncmzoom');
        // $mform->addElement('select', 'assignsubmission_ncmzoom_maxfiles', $name, $options);
        // $mform->addHelpButton('assignsubmission_ncmzoom_maxfiles',
        //                       'maxfilessubmission',
        //                       'assignsubmission_ncmzoom');
        // $mform->setDefault('assignsubmission_ncmzoom_maxfiles', $defaultmaxfilesubmissions);
        // $mform->disabledIf('assignsubmission_ncmzoom_maxfiles', 'assignsubmission_ncmzoom_enabled', 'notchecked');

        // $choices = get_max_upload_sizes($CFG->maxbytes,
        //                                 $COURSE->maxbytes,
        //                                 get_config('assignsubmission_ncmzoom', 'maxbytes'));

        // $settings[] = array('type' => 'select',
        //                     'name' => 'maxsubmissionsizebytes',
        //                     'description' => get_string('maximumsubmissionsize', 'assignsubmission_ncmzoom'),
        //                     'options'=> $choices,
        //                     'default'=> $defaultmaxsubmissionsizebytes);

        // $name = get_string('maximumsubmissionsize', 'assignsubmission_ncmzoom');
        // $mform->addElement('select', 'assignsubmission_ncmzoom_maxsizebytes', $name, $choices);
        // $mform->addHelpButton('assignsubmission_ncmzoom_maxsizebytes',
        //                       'maximumsubmissionsize',
        //                       'assignsubmission_ncmzoom');
        // $mform->setDefault('assignsubmission_ncmzoom_maxsizebytes', $defaultmaxsubmissionsizebytes);
        // $mform->disabledIf('assignsubmission_ncmzoom_maxsizebytes',
        //                    'assignsubmission_ncmzoom_enabled',
        //                    'notchecked');

        // $name = get_string('acceptedfiletypes', 'assignsubmission_ncmzoom');
        // $mform->addElement('filetypes', 'assignsubmission_ncmzoom_filetypes', $name);
        // $mform->addHelpButton('assignsubmission_ncmzoom_filetypes', 'acceptedfiletypes', 'assignsubmission_ncmzoom');
        // $mform->setDefault('assignsubmission_ncmzoom_filetypes', $defaultfiletypes);
        // $mform->disabledIf('assignsubmission_ncmzoom_filetypes', 'assignsubmission_ncmzoom_enabled', 'notchecked');
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

        // $mform->addElement('text', 'name', get_string('forumname', 'forum'));

        $service = new assignsubmission_ncmzoom_webservice();
        $zoomid = $USER->profile['ncmzoomid'];
        $service->get_cloud_recordings($zoomid);

        $return = new stdClass();
        $result = $service->lastresponse;

        // echo "<br/>";
        // echo "<br/>";
        // echo "<br/>";

        // var_dump($result);

        $mform->addElement('html', "<p class='lead'>Select the Zoom Recording you want to submit</p>");

        if (empty($result->meetings)) {
            $mform->addElement('html', '<div class="alert alert-warning" role="alert">No meeting found.</div>');
        } else {

        // $radioarray=array(); 
            // Loop on each meeting
            foreach($result->meetings as $meeting) {
                // If there is any recording.
                if ($meeting->recording_count > 0) {
                    // Loop for each Recording file.
                    foreach ($meeting->recording_files as $recording_file) {
                        // We are only showing Video (MP4) recording.
                        if ($recording_file->file_type === 'MP4') {

                            $d = new DateTime($recording_file->recording_start);
                            $format = "D, d M Y H:i:s O";
                            $text = $d->format($format);

                            $radiovalue = $meeting->uuid . "#" . $recording_file->id;

                            // Add Radio Button
                            $mform->addElement('radio', 'recording', null, $text, $radiovalue);

                            // $mform->addElement('radio', 'repeateditall', null, get_string('repeateditthis', 'calendar'), 0);

                            $html = '<iframe width="320" height="240" style="border:0" src="'.$recording_file->play_url.'"></iframe><hr/>';
                            // Add Zoom IFrame
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
        
        // var_dump($data);

        // echo "<pre>data:";
        // var_dump($data);
        // echo "</pre>";

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

        // Unset the objectid and other field from params for use in submission events.
        //unset($params['objectid']);
        //unset($params['other']);
        
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
        
        // Show we show a link to view all files for this plugin?
        // $showviewlink = $count > assignsubmission_ncmzoom_MAXSUMMARYFILES;
        // if ($count <= assignsubmission_ncmzoom_MAXSUMMARYFILES) {
        //     return $this->assignment->render_area_files('assignsubmission_ncmzoom',
        //                                                 assignsubmission_ncmzoom_FILEAREA,
        //                                                 $submission->id);
        // } else {
        //     return get_string('countfiles', 'assignsubmission_ncmzoom', $count);
        // }

        // Never show a link to view full submission.
        $showviewlink = false;

        // echo "<pre>";
        // var_dump($submission);
        // echo "</pre>";

        
        $ncmzoomsubmission = $this->get_ncmzoom_submission($submission->id);
        if ($ncmzoomsubmission) {
            // echo "<pre>";
            // var_dump($ncmzoomsubmission);
            // echo "</pre>";
            $myrecording = $this->get_zoom_cloud_recording($ncmzoomsubmission);

            // echo "<pre>";
            // var_dump($myrecording);
            // echo "</pre>";
            $sd = new DateTime($myrecording->recording_start);
            $ed = new DateTime($myrecording->recording_end);

            $interval = $sd->diff($ed);
            $mydiff = $interval->format("%H:%I:%S");

            // echo "<pre>";
            // var_dump($interval);
            // echo "</pre>";

            $format = "D, d M Y H:i:s O";
            $text = $sd->format($format);
            $text .= " (" . $mydiff . ")";

            $o = $this->assignment->get_renderer()->container($text . " " , 'ncmzoomcontainer');
            $o .= "<a href=\"".$myrecording->play_url."\" target=\"_blank\"><i class=\"icon fa fa-external-link\"></a>";
    //     $o .= '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#ncmzoom">
    //     Launch Modal
    //   </button>';

    //     $o .= '<!-- Modal -->
    //     <div class="modal fade" id="ncmzoom" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    //       <div class="modal-dialog" role="document">
    //         <div class="modal-content">
    //           <div class="modal-header">
    //             <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
    //             <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    //               <span aria-hidden="true">&times;</span>
    //             </button>
    //           </div>
    //           <div class="modal-body" style="height:800px;">
    //             <iframe height="600px" width="600px" style="border:0" src="'.$myrecording->play_url.'"></iframe>
    //           </div>
    //           <div class="modal-footer">
    //             <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    //             <button type="button" class="btn btn-primary">Save changes</button>
    //           </div>
    //         </div>
    //       </div>
    //     </div>';
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
        // return $this->assignment->render_area_files('assignsubmission_ncmzoom',
        //                                             assignsubmission_ncmzoom_FILEAREA,
        //                                             $submission->id);

        $ncmzoomsubmission = $this->get_ncmzoom_submission($submission->id);

        $myrecording = $this->get_zoom_cloud_recording($ncmzoomsubmission);

        // $o = $this->assignment->get_renderer()->container($text . " " , 'ncmzoomcontainer');

        // return $o;
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
        // TODO: Need to call 
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
                            array('assignment'=>$this->assignment->get_instance()->id));

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
        // Find the recording file 
        foreach ($result->recording_files as $recordingfile) {
            if ($recordingfile->id === $ncmzoomsubmission->recordingfileid) {
                $myrecording = $recordingfile;
                break;
            }
        }
        return $myrecording;
    }
    

}