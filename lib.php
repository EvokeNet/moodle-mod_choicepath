<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Library of interface functions and constants.
 *
 * @package     mod_choicepath
 * @copyright   2024 Willian Mano <willianmanoaraujo@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return bool|string|null
 */
function choicepath_supports($feature) {
    switch ($feature) {
        case FEATURE_GROUPINGS:
        case FEATURE_MOD_INTRO:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_COMPLETION_HAS_RULES:
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_MOD_PURPOSE: return MOD_PURPOSE_COMMUNICATION;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_choicepath into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_choicepath_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function choicepath_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('choicepath', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the mod_choicepath in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_choicepath_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function choicepath_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('choicepath', $moduleinstance);
}

/**
 * Removes an instance of the mod_choicepath from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function choicepath_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('choicepath', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('choicepath', array('id' => $id));

    return true;
}

/**
 * Returns the lists of all browsable file areas within the given module context.
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@see file_browser::get_file_info_context_module()}.
 *
 * @package     mod_choicepath
 * @category    files
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return string[].
 */
function choicepath_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for mod_choicepath file areas.
 *
 * @package     mod_choicepath
 * @category    files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info Instance or null if not found.
 */
function choicepath_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the mod_choicepath file areas.
 *
 * @package     mod_choicepath
 * @category    files
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object.
 * @param stdClass $context The mod_choicepath's context.
 * @param string $filearea The name of the file area.
 * @param array $args Extra arguments (itemid, path).
 * @param bool $forcedownload Whether or not force download.
 * @param array $options Additional options affecting the file serving.
 */
function choicepath_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options = array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    $itemid = (int)array_shift($args);
    if ($itemid == 0) {
        return false;
    }

    $relativepath = implode('/', $args);

    $fullpath = "/{$context->id}/mod_choicepath/$filearea/$itemid/$relativepath";

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
}

/**
 * This function extends the settings navigation block for the site.
 *
 * It is safe to rely on PAGE here as we will only ever be within the module
 * context when this is called
 *
 * @param settings_navigation $settings
 * @param navigation_node $choicepathnode
 * @return void
 */
function choicepath_extend_settings_navigation(settings_navigation $settings, navigation_node $choicepathnode) {
    // We want to add these new nodes after the Edit settings node, and before the
    // Locally assigned roles node. Of course, both of those are controlled by capabilities.
    $keys = $choicepathnode->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if ($i === false and array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    if (has_capability('mod/choicepath:addinstance', $settings->get_page()->cm->context)) {
        $node = navigation_node::create(get_string('options', 'mod_choicepath'),
            new moodle_url('/mod/choicepath/options.php', ['cmid' => $settings->get_page()->cm->id]),
            navigation_node::TYPE_SETTING, null, 'mod_choicepath_options', new pix_icon('t/edit', ''));
        $choicepathnode->add_node($node, $beforekey);
    }
}
