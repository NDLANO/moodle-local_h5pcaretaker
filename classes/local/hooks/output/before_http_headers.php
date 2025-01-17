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

namespace local_h5pcaretaker\local\hooks\output;
use action_link, context_system, moodle_url, navigation_node;

/**
 * Hook for before http headers.
 *
 * @package    local_h5pcaretaker
 * @copyright  2025 NDLA <explore@ndla.no>
 * phpcs:ignore moodle.Commenting.FileExpectedTags.LicenseTagInvalid
 * @license    https://opensource.org/license/mit MIT
 */
class before_http_headers {
    /**
     * Callback to allow to modify headers.
     *
     * @param \core\hook\output\before_http_headers $hook
     */
    public static function callback(\core\hook\output\before_http_headers $hook): void {
        self::add_caretaker_link();
    }

    /**
     * Add link to H5P Caretaker in the course navigation.
     */
    private static function add_caretaker_link() {
        global $PAGE;

        $context = context_system::instance();
        if (!has_capability('local/h5pcaretaker:use', $context)) {
            return;
        }

        if (!$PAGE->course || $PAGE->course->id <= 1) {
            return;
        }

        $PAGE->secondarynav->add(
            get_string('h5pcaretaker', 'local_h5pcaretaker'),
            new action_link(
                new moodle_url('/local/h5pcaretaker/index.php'),
                get_string('h5pcaretaker', 'local_h5pcaretaker'),
                null,
                ['target' => '_blank']
            ),
            navigation_node::TYPE_CUSTOM,
            null,
            'h5pcaretaker'
        );
    }
}
