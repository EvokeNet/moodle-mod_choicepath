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
 * Options renderer
 *
 * @package     mod_choicepath
 * @copyright   2024 Willian Mano <willianmanoaraujo@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_choicepath\output;

use mod_choicepath\model\option;
use renderable;
use templatable;
use renderer_base;

/**
 * Options renderable class.
 *
 * @package     local_library
 * @copyright   2024 Willian Mano {@link https://conecti.me}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view implements renderable, templatable {
    protected $cm;
    protected $choicepath;

    public function __construct($cm, $choicepath) {
        $this->cm = $cm;
        $this->choicepath = $choicepath;
    }

    public function export_for_template(renderer_base $output) {
        global $DB, $USER;

        $model = new option();

        $options = $model->get_all_by_choicepathid($this->choicepath->id);

        $hasanswer = $DB->get_record('choicepath_answers', [
            'choicepathid' =>  $this->choicepath->id,
            'userid' => $USER->id
        ]);

        if ($hasanswer) {
            foreach ($options as $key => $option) {
                $options[$key]['extraclass'] = 'disabled';
                if ($option['id'] == $hasanswer->optionid) {
                    $options[$key]['extraclass'] = 'active';
                }
            }
        }

        return [
            'cmid' => $this->cm->id,
            'name' => format_string($this->choicepath->name),
            'intro' => format_text($this->choicepath->intro, $this->choicepath->introformat),
            'options' => $options,
            'columns' => count($options),
            'hasanswer' => $hasanswer != null,
        ];
    }
}