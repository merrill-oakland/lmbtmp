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
 * A class for collecting XML data.
 *
 * @package    enrol_lmb
 * @author     Eric Merrill <merrill@oakland.edu>
 * @copyright  2016 Oakland University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_lmb\local;
defined('MOODLE_INTERNAL') || die();

/**
 * Collects and builds a tree of XML data..
 *
 * @package    enrol_lmb
 * @author     Eric Merrill <merrill@oakland.edu>
 * @copyright  2016 Oakland University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class xml_node implements \Iterator {
    /** @var xml_node Parent of this node */
    protected $parent = null;

    /** @var string Name (path element) of this node */
    protected $name = null;

    /** @var array Children of this node */
    protected $children = array();

    /** @var array Array of node attributes */
    protected $attrs = array();

    /** @var mixed Value of the node */
    protected $value;

    /**
     * If the node is finished. Used to determine if an incoming node of the same name is merged or a sibling.
     * @var bool
     */
    protected $finished = false;

    /** @var int An pointer key used for child arrays. */
    protected $arraykey = 0;

    /**
     * Basic constructor.
     *
     * @param string $name The path name of the current element
     */
    public function __construct($name = null) {
        $this->name = $name;
    }

    /**
     * Takes a data array and recursivly builds itself and children.
     *
     * @param array $data An deep associative array for data.
     */
    public function build_from_array($data) {
        // Check to see if this seems to be a "data" node.
        if (isset($data['name']) && !is_array($data['name']) && (array_key_exists('cdata', $data) || isset($data['attrs']))) {
            // Unset the name, cdata, and attrs so that we don't make children for them.
            unset($data['name']);
            if (array_key_exists('cdata', $data)) {
                $this->value = $data['cdata'];
                unset($data['cdata']);
            }
            if (array_key_exists('attrs', $data)) {
                $this->attrs = $data['attrs'];
                unset($data['attrs']);
            }
        }

        // Go through each remaining element.
        foreach ($data as $name => $value) {
            // Check to see if we have a child with that name.
            if (array_key_exists($name, $this->children)) {
                // Get the child.
                $child = $this->children[$name];
                if (is_array($child)) {
                    // If the child is an array, get the last member of that array.
                    $child = end($child);
                    // Move back the pointer.
                    reset($this->children[$name]);
                }
                if (!$child->is_finished()) {
                    // If the child is not finished, it means that we can "merge" the data with it.
                    $child->build_from_array($value);
                    continue;
                }
            }

            // Getting here means we need a new child.
            $child = new xml_node($name);
            $child->build_from_array($value);
            $this->add_child($child);
        }
    }

    /**
     * Checks to see if the current node is finished.
     *
     * @return bool
     */
    public function is_finished() {
        return $this->finished;
    }

    /**
     * Takes an array of path elements, and marks the final element as finished.
     *
     * @param array $patharray An array of path elements
     */
    public function mark_node_finished($patharray) {
        if (is_array($patharray) && empty($patharray)) {
            // If the path list is empty, then we have hit the node.
            $this->finished = true;
            return;
        }
        // Get the next path name and move the pointer forward.
        $child = array_shift($patharray);
        if (!array_key_exists($child, $this->children)) {
            // If we don't have a child, just discard the finish mark.
            return;
        }

        $node = $this->children[$child];
        if (is_array($node)) {
            // If the child is an array, then we want to work on the last child of the array.
            $node = end($node);
            // Reset the array pointer.
            reset($this->children);
        }

        if (is_object($node)) {
            // If it's an object, pass the now shortened array on.
            $node->mark_node_finished($patharray);
        }
    }

    public function add_child($child) {
        $name = $child->name;
        $child->set_parent($this);
        if (array_key_exists($name, $this->children)) {
            if (!is_array($this->children[$name])) {
                $this->children[$name] = array($this->children[$name]);
            }
            $this->children[$name][] = $child;
        } else {
            $this->children[$name] = $child;
        }

    }

    public function set_parent($parent) {
        $this->parent = $parent;
    }

    public function get_parent() {
        return $this->parent;
    }

    public function get_attributes() {
        return $this->attrs;
    }

    public function get_attribute($attribute) {
        if (!array_key_exists($attribute, $this->attrs)) {
            return null;
        }
        return $this->attrs[$attribute];
    }

    public function get_value() {
        return $this->value;
    }

    public function has_data() {
        return !empty($this->attrs) || !is_null($this->value);
    }

    /**
     * ----------------------- Iterator interface -----------------------.
     */

    public function current() {
        $current = current($this->children);
        if (is_array($current)) {
            return $current[$this->arraykey];
            return current($current);
        }

        return $current;
    }

    public function key() {
        static $i = 0;
        $i++;
        return key($this->children).$i;
    }

    public function next() {
        $current = current($this->children);
        if (is_array($current)) {
            $this->arraykey++;
            if (array_key_exists($this->arraykey, $current)) {
                return;
            }
            $this->arraykey = 0;
        }
        $next = next($this->children);

        if (is_array($next)) {
            $this->arraykey = 0;
        }
    }

    public function rewind() {
        $current = current($this->children);
        if (is_array($current)) {
            reset($current);
        }
        reset($this->children);
        $this->arraykey = 0;
    }

    public function valid() {
        $current = current($this->children);
        if ($current === false) {
            return false;
        }
        if (is_array($current)) {
            if (!array_key_exists($this->arraykey, $current)) {
                return false;
            }
        }

        return true;
    }
}