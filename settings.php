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
 * Plugin administration pages are defined here.
 *
 * @package     assignsubmission_ncmzoom
 * @category    admin
 * @copyright   2018 Nicolas Jourdain <nicolas.jourdain@navitas.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configcheckbox('assignsubmission_ncmzoom/default',
                    new lang_string('default', 'assignsubmission_ncmzoom'),
                    new lang_string('default_help', 'assignsubmission_ncmzoom'), 0));

    $apiurl = new admin_setting_configtext('assignsubmission_ncmzoom/apiurl',
        get_string('apiurl', 'assignsubmission_ncmzoom'),
        get_string('apiurl_desc', 'assignsubmission_ncmzoom'), 'https://api.zoom.us/v2/', PARAM_URL);
    $settings->add($apiurl);

    $apikey = new admin_setting_configtext('assignsubmission_ncmzoom/apikey',
        get_string('apikey', 'assignsubmission_ncmzoom'),
        get_string('apikey_desc', 'assignsubmission_ncmzoom'), '', PARAM_ALPHANUMEXT);
    $settings->add($apikey);

    $apisecret = new admin_setting_configtext('assignsubmission_ncmzoom/apisecret',
        get_string('apisecret', 'assignsubmission_ncmzoom'),
        get_string('apisecret_desc', 'assignsubmission_ncmzoom'), '', PARAM_ALPHANUMEXT);
    $settings->add($apisecret);
}