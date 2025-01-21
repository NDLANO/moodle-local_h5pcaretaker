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
 * Display H5P Caretaker page.
 *
 * @package    local_h5pcaretaker
 * @copyright  2025 NDLA <explore@ndla.no>
 * phpcs:ignore moodle.Commenting.FileExpectedTags.LicenseTagInvalid
 * @license    https://opensource.org/license/mit MIT
 */

// Login is not a mandatory requirement for this page.
// phpcs:ignore moodle.Files.RequireLogin.Missing
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/classes/handlers.php');
use core\context\system as context_system;
use local_h5pcaretaker\handlers;
use local_h5pcaretaker\constants;

defined('MOODLE_INTERNAL') || die();

global $PAGE, $OUTPUT;

// Set up the page.
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/h5pcaretaker/index.php'));
$PAGE->set_title(get_string('site:title', 'local_h5pcaretaker'));
$PAGE->set_heading(get_string('site:title', 'local_h5pcaretaker'));

// Page is supposed to be accessed by users who are not logged in if plugin is explicitly configured to allow it.
$localh5pcaretakerforcelogin = get_config('local_h5pcaretaker', 'forcelogin') ?? constants::FORCELOGIN_YES;
if ($localh5pcaretakerforcelogin === constants::FORCELOGIN_YES || get_config('core', 'forcelogin')) {
    require_login(); // H5P Caretaker is not set to be public, so require login.
    require_capability('local/h5pcaretaker:use', $context); // Logged in users must have the capability to use H5P Caretaker.
}

try {
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_sesskey(); // Verify CSRF token for POST requests.

        // Handle file upload.
        $response = handlers::handler_upload();
    } else {
        // Display upload form with CSRF protection.
        $response = handlers::handler_start();
    }

    // This site is not supposed to have a header or footer.
    echo $OUTPUT->container($response, 'h5pcaretaker-content');
} catch (moodle_exception $e) {
    echo $OUTPUT->notification($e->getMessage(), \core\output\notification::NOTIFY_ERROR);
}
