<?php

/**
 * Upgrade file.
 *
 * @package    mod_choicepath
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Upgrade code for the eMailTest local plugin.
 *
 * @param int $oldversion - the version we are upgrading from.
 *
 * @return bool result
 *
 * @throws ddl_exception
 * @throws downgrade_exception
 * @throws upgrade_exception
 */
function xmldb_choicepath_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2024060100) {
        $options = $DB->get_records('choicepath_options');

        foreach ($options as $option) {
            $cm = get_coursemodule_from_id('choicepath', $option->choicepathid, 0, false, MUST_EXIST);
            $moduleinstance = $DB->get_record('choicepath', ['id' => $cm->instance], '*', MUST_EXIST);

            $option->choicepathid = $moduleinstance->id;

            $DB->update_record('choicepath_options', $option);

            $answers = $DB->get_records('choicepath_answers', ['optionid' => $option->id]);

            if (!$answers) {
                continue;
            }

            foreach ($answers as $answer) {
                $answer->choicepathid = $moduleinstance->id;

                $DB->update_record('choicepath_answers', $answer);
            }
        }

        upgrade_plugin_savepoint(true, 2024060100, 'mod', 'choicepath');
    }

    return true;
}
