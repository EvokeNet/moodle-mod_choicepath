<?php

namespace mod_choicepath\model;

/**
 * Entity base interface.
 *
 * @package     mod_choicepath
 * @copyright   2024 Willian Mano <willianmanoaraujo@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base {
    protected $table;

    public function create($data) {
        global $DB, $CFG;

        try {
            $DB->insert_record($this->table, $data);

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

            return [true, get_string('updateitem:success', 'mod_choicepath')];
        } catch (\Exception $e) {
            if ($CFG->debugdisplay) {
                return [false, $e->getMessage()];
            }

            return [false, get_string('something_went_wrong', 'mod_choicepath')];
        }
    }

    public function delete($id) {
        global $DB, $CFG;

        try {
            $DB->delete_records($this->table, ['id' => $id]);

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
