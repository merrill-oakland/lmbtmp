<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Data model object.
 *
 * @package    enrol_lmb
 * @author     Eric Merrill <merrill@oakland.edu>
 * @copyright  2016 Oakland University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_lmb\local\data;
use enrol_lmb\local\types;
use enrol_lmb\logging;

defined('MOODLE_INTERNAL') || die();

/**
 * Object that represents the internal data structure of a section object.
 *
 * @package    enrol_lmb
 * @author     Eric Merrill <merrill@oakland.edu>
 * @copyright  2016 Oakland University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section extends base {
    /**
     * The table name of this object.
     */
    const TABLE = 'enrol_lmb_section';

    /**
     * The class of the Moodle converter for this data object.
     */
    const MOODLE_CLASS = '\\enrol_lmb\\local\\moodle\\section';

    /** @var array Array of keys that go in the database object */
    protected $dbkeys = array('id', 'sdidsource', 'sdid', 'title', 'begindate', 'enddate',
                              'deptname', 'termsdidsource', 'termsdid', 'coursesdidsource', 'coursesdid',
                              'additional', 'timemodified');

    /** @var array An array of default property->value pairs */
    protected $defaults = array();

    /** @var array An array of property->function pairs for converting incoming values */
    protected $handlers = array('beginrestrict' => 'handler_boolean',
                                'endrestrict' => 'handler_boolean',
                                'enrollaccept' => 'handler_boolean',
                                'enrollallowed' => 'handler_boolean',
                                'begindate' => 'handler_date',
                                'enddate' => 'handler_date');

    /**
     * Log a unique line to id this object.
     */
    public function log_id() {
        $id = $this->__get('sdid');
        $source = $this->__get('sdidsource');
        $desc = $this->__get('title');
        if (empty($id) || empty($source)) {
            throw new \enrol_lmb\local\exception\message_exception('exception_bad_term');
        } else {
            logging::instance()->log_line("Section \"{$desc}\", ID \"{$id}\" from \"{$source}\"");
        }
    }

    /**
     * Retreive an exiting db record for this record.
     *
     * @return object|false The record or false if not found.
     */
    protected function get_record() {
        global $DB;

        $params = array('sdid' => $this->__get('sdid'), 'sdidsource' => $this->__get('sdidsource'));

        return $DB->get_record('enrol_lmb_term', $params);
    }

}