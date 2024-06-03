<?php

/**
 * All the steps to restore mod_choicepath are defined here.
 *
 * @package     mod_choicepath
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

// More information about the backup process: {@link https://docs.moodle.org/dev/Backup_API}.
// More information about the restore process: {@link https://docs.moodle.org/dev/Restore_API}.

/**
 * Defines the structure step to restore one mod_choicepath activity.
 */
class restore_choicepath_activity_structure_step extends restore_activity_structure_step {

    /**
     * Defines the structure to be restored.
     *
     * @return restore_path_element[].
     */
    protected function define_structure() {
        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('choicepath', '/activity/choicepath');
        $paths[] = new restore_path_element('choicepath_option', '/activity/choicepath/options/option');

        if ($userinfo) {
            $paths[] = new restore_path_element('choicepath_answer', '/activity/choicepath/options/option/answers/answer');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Processes the elt restore data.
     *
     * @param array $data Parsed element data.
     */
    protected function process_choicepath($data) {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();

        $newitemid = $DB->insert_record('choicepath', $data);

        $this->apply_activity_instance($newitemid);
    }

    protected function process_choicepath_option($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->choicepathid = $this->get_new_parentid('choicepath');

        $newitemid = $DB->insert_record('choicepath_options', $data);

        $this->set_mapping('choicepath_option', $oldid, $newitemid, true);

        $this->add_related_files('mod_choicepath', 'image', 'choicepath_option', null, $oldid);
    }

    protected function process_choicepath_answer($data) {
        global $DB;

        $data = (object)$data;

        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->optionid = $this->get_mappingid('choicepath_option', $data->optionid);
        $data->choicepathid = $this->get_new_parentid('choicepath');

        $DB->insert_record('choicepath_answers', $data);
    }

    protected function after_execute() {
        $this->add_related_files('mod_choicepath', 'intro', null);
        $this->add_related_files('mod_choicepath', 'description', 'choicepath_option');
    }
}
