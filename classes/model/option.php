<?php

namespace mod_choicepath\model;

use core\context\module as context_module;

class option {
    protected $table = 'choicepath_options';

    public function create($data, $context) {
        global $DB, $CFG;

        try {
            $optionid = $DB->insert_record($this->table, $data);

            // Process attachments.
            $draftitemid = file_get_submitted_draft_itemid('image');
            file_save_draft_area_files($draftitemid, $context->id, 'mod_choicepath', 'image', $optionid, ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['optimised_image']]);

            return [true, get_string('createitem:success', 'mod_choicepath')];
        } catch (\Exception $e) {
            if ($CFG->debugdisplay) {
                return [false, $e->getMessage()];
            }

            return [false, get_string('something_went_wrong', 'mod_choicepath')];
        }
    }

    public function update($data, $context) {
        global $DB, $CFG;

        try {
            $DB->update_record($this->table, $data);

            // Process attachments.
            $draftitemid = file_get_submitted_draft_itemid('image');
            file_save_draft_area_files($draftitemid, $context->id, 'mod_choicepath', 'image', $data->id, ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['optimised_image']]);

            return [true, get_string('updateitem:success', 'mod_choicepath')];
        } catch (\Exception $e) {
            if ($CFG->debugdisplay) {
                return [false, $e->getMessage()];
            }

            return [false, get_string('something_went_wrong', 'mod_choicepath')];
        }
    }

    public function get_all_by_choicepathid($choicepathid) {
        global $DB;

        $records = $DB->get_records($this->table, ['choicepathid' => $choicepathid]);

        if (!$records) {
            return [];
        }

        $data = [];
        foreach ($records as $record) {
            $data[] = [
                'id' => $record->id,
                'title' => format_string($record->title),
                'description' => format_text($record->description, $record->descriptionformat),
                'image' => $this->get_image($record->id, $choicepathid),
            ];
        }

        return $data;
    }

    public function get_image($optionid, $choicepathid) {
        $files = $this->get_image_files($optionid, $choicepathid);

        if (!$files) {
            return false;
        }

        foreach ($files as $file) {
            $path = [
                '',
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $optionid . $file->get_filepath() . $file->get_filename()
            ];

            $fileurl = \moodle_url::make_file_url('/pluginfile.php', implode('/', $path));

            return $fileurl->out();
        }

        return false;
    }

    public function get_image_files($optionid, $choicepathid) {
        global $DB;

        $moduleinstance = $DB->get_record('choicepath', ['id' => $choicepathid], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('choicepath', $moduleinstance->id, $moduleinstance->course, false, MUST_EXIST);

        $context = context_module::instance($cm->id);

        $fs = get_file_storage();

        $files = $fs->get_area_files($context->id,
            'mod_choicepath',
            'image',
            $optionid,
            'timemodified',
            false);

        if (!$files) {
            return false;
        }

        return $files;
    }

    public function delete($id) {
        global $DB, $CFG;

        try {
            $option = $this->find($id);

            $DB->delete_records($this->table, ['id' => $id]);

            $DB->delete_records('choicepath_answers', ['optionid' => $id]);

            $images = $this->get_image_files($id, $option->choicepathid);

            if ($images) {
                foreach ($images as $image) {
                    $image->delete();
                }
            }

            list($course, $cm) = get_course_and_cm_from_instance($option->choicepathid, 'choicepath');

            $event = \mod_choicepath\event\option_deleted::create(['context' => $cm->context, 'objectid' => $id]);
            $event->trigger();

            return [true, get_string('deleteitem:success', 'mod_choicepath')];
        } catch (\Exception $e) {
            if ($CFG->debugdisplay) {
                return [false, $e->getMessage()];
            }

            return [false, get_string('something_went_wrong', 'mod_choicepath')];
        }
    }

    public function find($id, $fields = '*', $strictness = MUST_EXIST) {
        global $DB;

        return $DB->get_record($this->table, ['id' => $id], $fields, $strictness);
    }

    public function get_all() {
        global $DB;

        $records = $DB->get_records($this->table);

        if (!$records) {
            return [];
        }

        return array_values($records);
    }

    public function count() {
        global $DB;

        return $DB->count_records($this->table);
    }
}