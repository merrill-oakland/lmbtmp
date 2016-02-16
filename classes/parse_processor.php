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
 * Processor for the XML parser.
 *
 * @package    enrol_lmb
 * @author     Eric Merrill <merrill@oakland.edu>
 * @copyright  2016 Oakland University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_lmb;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/backup/util/xml/parser/processors/simplified_parser_processor.class.php');
/**
 * Processes XML chunks from the parser.
 *
 * @package    enrol_lmb
 * @author     Eric Merrill <merrill@oakland.edu>
 * @copyright  2016 Oakland University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class parse_processor extends \simplified_parser_processor {
    protected $pathclasses = array();
    protected $merged = array();

    public function __construct() {
        parent::__construct();

        $this->load_paths();
    }

    protected function load_paths() {
        local\types::register_processor_paths($this);
    }

    public function process_chunk($data) {
        if ($this->path_is_selected($data['path'])) {
            print_r($data);
        } else if ($parent = $this->selected_parent_exists($data['path'])) {
            $this->expand_path($parent, $data);
            print_r($data);
        }

        // Do nothing.
    }

    protected function notify_path_start($path) {
        // Nothing to do.
    }

    protected function notify_path_end($path) {
        // Nothing to do.
    }

    protected function dispatch_chunk($path) {
        // Nothing to do.
    }

    public function register_path($type, $path, $grouped = false) {
        if ($grouped) {
            $this->pathclasses[$path] = $type;
            $this->pathclasses[$path] = '/enterprise'.$type;
        }

        $this->add_path($path);
        $this->add_path('/enterprise'.$path);
    }

    protected function expand_path($grouped, &$data) {
        $path = $data['path'];

        $hierarchyarr = explode('/', str_replace($grouped . '/', '', $path));
        $hierarchyarr = array_reverse($hierarchyarr, false);
        foreach ($hierarchyarr as $element) {
            $data['tags'] = array($element => $data['tags']);
        }
        $data['path'] = $grouped;
    }
}
