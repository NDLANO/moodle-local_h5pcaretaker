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
 * Settings for H5P Caretaker.
 *
 * @package    local_h5pcaretaker
 * @copyright  2025 NDLA <explore@ndla.no>
 * phpcs:ignore moodle.Commenting.FileExpectedTags.LicenseTagInvalid
 * @license    https://opensource.org/license/mit MIT
 */

defined('MOODLE_INTERNAL') || die;

require_once(__DIR__ . '/lib.php');

use local_h5pcaretaker\constants;

if (!$hassiteconfig) {
    return;
}

$settings = new admin_settingpage(
  'local_h5pcaretaker_settings',
  get_string('pluginname', 'local_h5pcaretaker')
);

// Settings for H5P Caretaker visibility.
$settings->add(new admin_setting_configselect(
  'local_h5pcaretaker/forcelogin',
  get_string('moodle:forcelogin', 'local_h5pcaretaker'),
  get_string('moodle:forcelogin_desc', 'local_h5pcaretaker'),
  constants::FORCELOGIN_YES,
  [
      constants::FORCELOGIN_NO => get_string('moodle:public', 'local_h5pcaretaker'),
      constants::FORCELOGIN_YES => get_string('moodle:needscapability', 'local_h5pcaretaker'),
  ]
));

// HTML field for additional introductory text.
$settings->add(new admin_setting_confightmleditor(
  'local_h5pcaretaker/extratext_intro',
  get_string('moodle:extratext_intro', 'local_h5pcaretaker'),
  get_string('moodle:extratext_intro_desc', 'local_h5pcaretaker'),
  null,
  PARAM_RAW
));

// HTML field for additional footer text.
$settings->add(new admin_setting_confightmleditor(
  'local_h5pcaretaker/extratext_footer',
  get_string('moodle:extratext_footer', 'local_h5pcaretaker'),
  get_string('moodle:extratext_footer_desc', 'local_h5pcaretaker'),
  null,
  PARAM_RAW
));

$ADMIN->add('localplugins', $settings);
