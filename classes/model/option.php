<?php

namespace mod_choicepath\model;

use core\context\system as context_system;
use core\context\module as context_module;

class option extends base {
    protected $table = 'choicepath_options';

    public function create($data) {
        global $DB, $CFG;

        try {
            $optionid = $DB->insert_record($this->table, $data);

            $context = context_module::instance($data->choicepathid);

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

    public function update($data) {
        global $DB, $CFG;

        try {
            $DB->update_record($this->table, $data);

            $context = context_module::instance($data->choicepathid);

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

    public function get_all_by_cmid($cmid) {
        global $DB;

        $records = $DB->get_records($this->table, ['choicepathid' => $cmid]);

        if (!$records) {
            return [];
        }

        $data = [];
        foreach ($records as $record) {
            $data[] = [
                'id' => $record->id,
                'title' => format_string($record->title),
                'description' => format_text($record->description, $record->descriptionformat),
                'image' => $this->get_image($record->id, $cmid),
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
        $context = context_module::instance($choicepathid);

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

            $images = $this->get_image_files($id, $option->choicepathid);

            if ($images) {
                foreach ($images as $image) {
                    $image->delete();
                }
            }

            return [true, get_string('deleteitem:success', 'local_library')];
        } catch (\Exception $e) {
            if ($CFG->debugdisplay) {
                return [false, $e->getMessage()];
            }

            return [false, get_string('something_went_wrong', 'local_library')];
        }
    }

}