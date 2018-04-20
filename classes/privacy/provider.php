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
 * Privacy Subsystem implementation for assignfeedback_poodll.
 *
 * @package    assignfeedback_poodll
 * @copyright  2018 Justin Hunt https://poodll.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignfeedback_poodll\privacy;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/mod/assign/feedback/poodll/locallib.php');

use \core_privacy\local\metadata\collection;
use \core_privacy\local\metadata\provider as metadataprovider;
use \mod_assign\privacy\assignfeedback_provider;
use \mod_assign\privacy\feedback_request_data;

/**
 * Privacy Subsystem for assignfeedback_poodll implementing null_provider.
 *
 * @copyright  2018 Justin Hunt https://poodll.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

///class provider implements metadataprovider, \mod_assign\privacy\assignfeedback_provider {
///    use \core_privacy\local\legacy_polyfill;
///    use \mod_assign\privacy\assignfeedback_provider\legacy_polyfill;


class provider implements metadataprovider {
    use \core_privacy\local\legacy_polyfill;


    /**
     * Return meta data about this plugin.
     *
     * @param  collection $collection A list of information to add to.
     * @return collection Return the collection after adding to it.
     */
    public static function _get_metadata(collection $collection) {
        $collection->link_subsystem('core_files', 'privacy:metadata:filepurpose');
        return $collection;
    }
    /**
    * No need to fill in this method as all information can be acquired from the assign_grades table in the mod assign
     * provider.
     *
     * @param  int $userid The user ID that we are finding contexts for.
     * @param  contextlist $contextlist A context list to add sql and params to for contexts.
     */
    public static function _get_context_for_userid_within_feedback($userid, contextlist $contextlist) {
        // This is already fetched from mod_assign.
    }
    /**
     * This is also covered by the mod_assign provider and it's queries.
     *
     * @param  \mod_assign\privacy\useridlist $useridlist An object for obtaining user IDs of students.
     */
    public static function _get_student_user_ids(\mod_assign\privacy\useridlist $useridlist) {
        // No need.
    }
    /**
     * Export all user data for this plugin.
     *
     * @param  feedback_request_data $exportdata Data used to determine which context and user to export and other useful
     * information to help with exporting.
     */
    public static function _export_feedback_user_data(feedback_request_data $exportdata) {
        $currentpath = $exportdata->get_subcontext();
        $currentpath[] = get_string('privacy:path', ASSIGNFEEDBACK_POODLL_COMPONENT);
        $plugin = $exportdata->get_subplugin();
        $gradeid = $exportdata->get_grade()->id;
        $filefeedback = $plugin->get_file_feedback($gradeid);
        if ($filefeedback) {
            $fileareas = $plugin->get_file_areas();
            $fs = get_file_storage();
            foreach ($fileareas as $filearea => $notused) {
                \core_privacy\local\request\writer::with_context($exportdata->get_context())
                    ->export_area_files($currentpath, ASSIGNFEEDBACK_POODLL_COMPONENT, $filearea, $gradeid);
            }
        }
    }

    /**
     * Any call to this method should delete all user data for the context defined in the deletion_criteria.
     *
     * @param  feedback_request_data $requestdata Data useful for deleting user data from this sub-plugin.
     */
    public static function _delete_feedback_for_context(feedback_request_data $requestdata) {
        $plugin = $requestdata->get_subplugin();
        $fileareas = $plugin->get_file_areas();
        $fs = get_file_storage();
        foreach ($fileareas as $filearea => $notused) {
            // Delete feedback files.
            $fs->delete_area_files($requestdata->get_context()->id, ASSIGNFEEDBACK_POODLL_COMPONENT, $filearea);
        }
        $plugin->delete_instance();
    }

    /**
     * Calling this function should delete all user data associated with this grade.
     *
     * @param  feedback_request_data $requestdata Data useful for deleting user data.
     */
    public static function _delete_feedback_for_grade(feedback_request_data $requestdata) {
        global $DB;
        $plugin = $requestdata->get_subplugin();
        $fileareas = $plugin->get_file_areas();
        $fs = get_file_storage();
        foreach ($fileareas as $filearea => $notused) {
            // Delete feedback files.
            $fs->delete_area_files($requestdata->get_context()->id, ASSIGNFEEDBACK_POODLL_COMPONENT, $filearea,
                $requestdata->get_grade()->id);
        }
        // Delete table entries.
        $DB->delete_records(ASSIGNFEEDBACK_POODLL_TABLE, ['assignment' => $requestdata->get_assign()->get_instance()->id,
            'grade' => $requestdata->get_grade()->id]);
    }


}
