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
 * Plugin upgrade steps are defined here.
 *
 * @package     assignsubmission_ncmzoom
 * @category    upgrade
 * @copyright   2018 Nicolas Jourdain <nicolas.jourdain@navitas.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Execute assignsubmission_ncmzoom upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_assignsubmission_ncmzoom_upgrade($oldversion) {
    global $DB, $CFG;

    $dbman = $DB->get_manager();
    // Set the correct initial order for the plugins.
    require_once($CFG->dirroot . '/mod/assign/adminlib.php');
    $pluginmanager = new assign_plugin_manager('assignsubmission');

    // $pluginmanager->move_plugin('ncmzoom', 'down');
    // $pluginmanager->move_plugin('ncmzoom', 'down');
    // $pluginmanager->move_plugin('ncmzoom', 'down');
    // $pluginmanager->move_plugin('ncmzoom', 'down');

    return true;
}
