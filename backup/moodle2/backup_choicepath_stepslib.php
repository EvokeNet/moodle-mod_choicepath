<?php

/**
 * Backup steps for mod_choicepath are defined here.
 *
 * @package     mod_choicepath
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

// More information about the backup process: {@link https://docs.moodle.org/dev/Backup_API}.
// More information about the restore process: {@link https://docs.moodle.org/dev/Restore_API}.

/**
 * Define the complete structure for backup, with file and id annotations.
 */
class backup_choicepath_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the structure of the resulting xml file.
     *
     * @return backup_nested_element The structure wrapped by the common 'activity' element.
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        // Build the tree with these elements with $choicepath as the root of the backup tree.
        $choicepath = new backup_nested_element('choicepath', ['id'], [
            'course', 'name', 'intro', 'introformat', 'completionsubmit', 'timecreated', 'timemodified']);

        $options = new backup_nested_element('options');
        $option = new backup_nested_element('option', ['id'], [
            'choicepathid', 'title', 'description', 'descriptionformat', 'timecreated', 'timemodified']);

        $answers = new backup_nested_element('answers');
        $answer = new backup_nested_element('answer', ['id'], [
            'choicepathid', 'optionid', 'userid', 'timecreated', 'timemodified']);

        $choicepath->add_child($options);
        $options->add_child($option);

        // Define the source tables for the elements.
        $choicepath->set_source_table('choicepath', ['id' => backup::VAR_ACTIVITYID]);
        $option->set_source_table('choicepath_options', ['choicepathid' => backup::VAR_ACTIVITYID]);

        // User options and answers are included only if we are including user info.
        if ($userinfo) {
            $option->add_child($answers);
            $answers->add_child($answer);
            // Define sources.
            $answer->set_source_table('choicepath_answers', ['choicepathid' => backup::VAR_ACTIVITYID, 'optionid' => backup::VAR_PARENTID]);
            $answer->annotate_ids('user', 'userid');
        }

        // Define file annotations.
        $choicepath->annotate_files('mod_choicepath', 'intro', null);
        $option->annotate_files('mod_choicepath', 'intro', 'id');
        $option->annotate_files('mod_choicepath', 'image', 'id');

        return $this->prepare_activity_structure($choicepath);
    }
}
