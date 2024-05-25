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
 * Display information about all the mod_choicepath modules in the requested course.
 *
 * @package     mod_choicepath
 * @copyright   2024 Willian Mano <willianmanoaraujo@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');

$cmid = required_param('cmid', PARAM_INT);
$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('choicepath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$moduleinstance = $DB->get_record('choicepath', ['id' => $cm->instance], '*', MUST_EXIST);

$option = $DB->get_record('choicepath_options', ['id' => $id, 'choicepathid' => $cm->id], '*', MUST_EXIST);

require_login($course, true, $cm);

$redirecturl = new moodle_url('/mod/choicepath/view.php', ['id' => $cmid]);

if (!confirm_sesskey()) {
    redirect($redirecturl, get_string('invaliddata', 'error'), null, \core\output\notification::NOTIFY_ERROR);
}

try {
    $hasanswer = $DB->get_record('choicepath_answers', [
        'choicepathid' =>  $cm->id,
        'userid' => $USER->id
    ]);

    if ($hasanswer) {
        redirect($redirecturl, get_string('choosepath:hasanswer', 'mod_choicepath'), null, \core\output\notification::NOTIFY_ERROR);
    }

    $answer = new stdClass();
    $answer->choicepathid = $cm->id;
    $answer->optionid = $option->id;
    $answer->userid = $USER->id;
    $answer->timecreated = time();
    $answer->timemodified = time();

    $DB->insert_record('choicepath_answers', $answer);

    redirect($redirecturl, get_string('choosepath:success', 'mod_choicepath'), null, \core\output\notification::NOTIFY_SUCCESS);
} catch (Exception $ex) {
    if ($CFG->debug) {
        throw $ex;
    }

    redirect($redirecturl, get_string('something_went_wrong', 'mod_choicepath'), null, \core\output\notification::NOTIFY_ERROR);
}