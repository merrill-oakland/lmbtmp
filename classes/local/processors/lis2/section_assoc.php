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
 * Works on types of messages.
 *
 * @package    enrol_lmb
 * @author     Eric Merrill <merrill@oakland.edu>
 * @copyright  2018 Oakland University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_lmb\local\processors\lis2;

defined('MOODLE_INTERNAL') || die();

use enrol_lmb\local\processors\types;
use enrol_lmb\local\data;

/**
 * Class for working with message types.
 *
 * @package    enrol_lmb
 * @author     Eric Merrill <merrill@oakland.edu>
 * @copyright  2018 Oakland University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section_assoc extends base {
    /**
     * Namespace associated with this object.
     */
    const NAMESPACE_DEF = "www.imsglobal.org/services/lis/cmsv1p0/wsdl11/sync/imscms_v1p0";

    /**
     * The data object path for this object.
     */
    const DATA_CLASS = '\\enrol_lmb\\local\\data\\crosslist';

    /**
     * Path to this objects mappings.
     */
    const MAPPING_PATH = '/enrol/lmb/classes/local/processors/lis2/mappings/section_assoc.json';

    protected $newmembers = [];

    /**
     * Basic constructor.
     */
    public function __construct() {
        $this->load_mappings();
    }

    /**
     * Process the section nodes.
     *
     * @param xml_node|array $node The XML node to process, or array of nodes
     * @param array $mapping The mapping for the field
     */
    protected function process_section_node($node, $mapping) {
        // We need to add a member for each time this node is called.
        $member = new data\crosslist_member();
        $member->sdid = $node->get_value();
        if (isset($this->dataobj->sdidsource)) {
            $member->sdidsource = $this->dataobj->sdidsource;
        }
        // By definition, it is active by being here.
        $member->status = 1;

        $this->newmembers[$member->sdid] = $member;
    }

    protected function post_mappings() {
        $existing = $this->dataobj->load_existing_members();

        $results = $this->newmembers;
        if (!empty($existing)) {
            foreach($existing as $id => $member) {
                if (!isset($results[$id])) {
                    $member->status = 0;
                    $results[$member->sdid] = $member;
                }
            }
        }

        $this->dataobj->set_members($results);
    }
}
