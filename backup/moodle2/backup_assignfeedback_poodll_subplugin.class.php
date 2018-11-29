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
 * This file contains the backup code for the feedback_poodll plugin.
 *
 * @package   assignfeedback_poodll
 * @copyright 2013 Justin Hunt {@link http://www.poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

use assignfeedback_poodll\constants;

/**
 * Provides the information to backup PoodLL feedback 
 *
 * This just adds its filearea to the annotations and records the number of files
 *
 * @package   assignfeedback_poodll
 * @copyright 2013 Justin Hunt {@link http://www.poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_assignfeedback_poodll_subplugin extends backup_subplugin {

    /**
     * Returns the subplugin information to attach to feedback element
     * @return backup_subplugin_element
     */
    protected function define_grade_subplugin_structure() {

        // create XML elements
        $subplugin = $this->get_subplugin_element(); // virtual optigroup element
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subpluginelement = new backup_nested_element('feedback_poodll', null, array('poodlltype', 'grade'));

        // connect XML elements into the tree
        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginelement);

        // set source to populate the data
        $subpluginelement->set_source_table(constants::M_TABLE, array('grade' => backup::VAR_PARENTID));

        $subpluginelement->annotate_files(constants::M_COMPONENT, constants::M_FILEAREA, 'grade');// The parent is the grade
        return $subplugin;
    }

}
