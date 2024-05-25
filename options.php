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

// Course module id.
$cmid = required_param('cmid', PARAM_INT);
$id = optional_param('id', null, PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHANUMEXT);

$cm = get_coursemodule_from_id('choicepath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$moduleinstance = $DB->get_record('choicepath', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

require_capability('mod/choicepath:addinstance', $modulecontext);

$optionmodel = new \mod_choicepath\model\option();

$params = ['cmid' => $cmid];

$option = null;
if ($id) {
    $params['id'] = $id;

    $option = $optionmodel->find($id);
}

if ($action) {
    $params['action'] = $action;
}

$PAGE->set_url('/mod/choicepath/options.php', $params);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($moduleinstance->name));
$PAGE->set_context($modulecontext);

$renderer = $PAGE->get_renderer('mod_choicepath');

if (!$action) {
    $contentrenderable = new \mod_choicepath\output\options($cm);

    echo $OUTPUT->header();

    echo $renderer->render($contentrenderable);

    echo $OUTPUT->footer();

    exit;
}

$redirecturl = new moodle_url('/mod/choicepath/options.php', ['cmid' => $cmid]);

if ($action == 'delete') {
    try {
        if (!confirm_sesskey()) {
            redirect($redirecturl, get_string('invaliddata', 'error'), null, \core\output\notification::NOTIFY_ERROR);
        }

        list($success, $message) = $optionmodel->delete($option->id);

        if ($success) {
            redirect($redirecturl, $message, null, \core\output\notification::NOTIFY_SUCCESS);
        }

        redirect($redirecturl, $message, null, \core\output\notification::NOTIFY_ERROR);

    } catch (\Exception $e) {
        redirect($redirecturl, get_string('invaliddata', 'error'), null, \core\output\notification::NOTIFY_ERROR);
    }
}

$form = new \mod_choicepath\form\options(new moodle_url('/mod/choicepath/options.php', $params), $option);

if ($form->is_cancelled()) {
    redirect($redirecturl);
}

if ($formdata = $form->get_data()) {
    $option = new \stdClass();
    $option->choicepathid = $cm->id;
    $option->title = $formdata->title;
    $option->description = $formdata->description;
    $option->descriptionformat = 1;
    $option->timemodified = time();

    $success = false;
    $message = '';
    if ($action == 'create') {
        $option->timecreated = time();

        list($success, $message) = $optionmodel->create($option);
    }

    if ($action == 'update') {
        $option->id = $id;

        list($success, $message) = $optionmodel->update($option);
    }

    if ($success) {
        redirect($redirecturl, $message, null, \core\output\notification::NOTIFY_SUCCESS);
    }

    redirect($redirecturl, $message, null, \core\output\notification::NOTIFY_ERROR);
}

echo $OUTPUT->header();

$form->display();

echo $OUTPUT->footer();
