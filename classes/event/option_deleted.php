<?php

namespace mod_choicepath\event;

/**
 * The points_added event class.
 *
 * @package     local_evokegame
 * @category    event
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class option_deleted extends \core\event\base {
    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The option with was deleted.";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return 'Path option deleted';
    }

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'evokegame_points';
    }

    public static function get_objectid_mapping() {
        return array('db' => 'choicepath_options', 'restore' => 'choicepath_options');
    }
}
