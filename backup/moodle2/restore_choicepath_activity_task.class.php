<?php

defined('MOODLE_INTERNAL') || die();

/**
 * The task that provides a complete restore of mod_choicepath is defined here.
 *
 * @package     mod_choicepath
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

// More information about the backup process: {@link https://docs.moodle.org/dev/Backup_API}.
// More information about the restore process: {@link https://docs.moodle.org/dev/Restore_API}.

require_once($CFG->dirroot.'//mod/choicepath/backup/moodle2/restore_choicepath_stepslib.php');

/**
 * Restore task for mod_choicepath.
 */
class restore_choicepath_activity_task extends restore_activity_task {

    /**
     * Defines particular settings that this activity can have.
     */
    protected function define_my_settings() {
        return;
    }

    /**
     * Defines particular steps that this activity can have.
     *
     * @return base_step.
     */
    protected function define_my_steps() {
        $this->add_step(new restore_choicepath_activity_structure_step('choicepath_structure', 'choicepath.xml'));
    }

    /**
     * Defines the contents in the activity that must be processed by the link decoder.
     *
     * @return array.
     */
    public static function define_decode_contents() {
        $contents = array();

        // Define the contents.
        $contents[] = new restore_decode_content('choicepath', ['intro'], 'choicepath');
        $contents[] = new restore_decode_content('choicepath_options', ['description'], 'option');

        return $contents;
    }

    /**
     * Defines the decoding rules for links belonging to the activity to be executed by the link decoder.
     *
     * @return array.
     */
    public static function define_decode_rules() {
        $rules = array();

        // Define the rules.
        $rules[] = new restore_decode_rule('CHOICEPATHVIEWBYID', '/mod/choicepath/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('CHOICEPATHINDEX', '/mod/choicepath/index.php?id=$1', 'course');

        return $rules;
    }

    /**
     * Defines the restore log rules that will be applied by the
     * {@see restore_logs_processor} when restoring mod_choicepath logs. It
     * must return one array of {@see restore_log_rule} objects.
     *
     * @return array.
     */
    public static function define_restore_log_rules() {
        $rules = array();

        // Define the rules.

        return $rules;
    }
}
