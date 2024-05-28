<?php

namespace mod_choicepath\form;

use core\context\module as context_module;

require_once($CFG->dirroot.'/lib/formslib.php');

class options extends \moodleform {
    /**
     * The form definition.
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'title', get_string('title', 'mod_choicepath'));
        $mform->addRule('title', get_string('required'), 'required', null, 'client');
        $mform->addRule('title', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->setType('title', PARAM_TEXT);
        if (isset($this->_customdata->title)) {
            $mform->setDefault('title', $this->_customdata->title);
        }

        $mform->addElement('editor', 'description', get_string('description', 'mod_choicepath'));
        $mform->addRule('description', get_string('required'), 'required', null, 'client');
        $mform->setType('description', PARAM_CLEANHTML);
        if (isset($this->_customdata->description)) {
            $mform->setDefault('description', [
                'text' => $this->_customdata->description,
                'format' => $this->_customdata->descriptionformat ?? 1
            ]);
        }

        $mform->addElement('filemanager', 'image', get_string('image', 'mod_choicepath'), null,
            ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['optimised_image']]);
        $mform->addRule('image', get_string('required'), 'required', null, 'client');

        $this->add_action_buttons();
    }

    /**
     * A bit of custom validation for this form
     *
     * @param array $data An assoc array of field=>value
     * @param array $files An array of files
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $title = isset($data['title']) ? $data['title'] : null;

        if ($this->is_submitted() && (empty($title) || strlen($title) < 5)) {
            $errors['title'] = get_string('validation:fieldlen', 'mod_choicepath', 5);
        }

        return $errors;
    }

    public function definition_after_data() {
        $mform = $this->_form;

        if (isset($this->_customdata->id)) {
            $optionmodel = new \mod_choicepath\model\option();

            $option = $optionmodel->find($this->_customdata->id);

            $context = context_module::instance($option->choicepathid);

            $draftitemid = file_get_submitted_draft_itemid('image');
            file_prepare_draft_area($draftitemid, $context->id, 'mod_choicepath', 'image', $option->id, ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['optimised_image']]);
            $mform->getElement('image')->setValue($draftitemid);
        }
    }
}
