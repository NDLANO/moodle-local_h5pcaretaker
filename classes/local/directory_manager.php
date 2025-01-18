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

namespace local_h5pcaretaker\local;

/**
 * Directory manager for H5P Caretaker.
 *
 * @package    local_h5pcaretaker
 * @copyright  2025 NDLA <explore@ndla.no>
 * @license    https://opensource.org/license/mit MIT
 */
class directory_manager {
    /**
     * Set up required directories.
     *
     * @param string $filehash Hash for the temporary directory
     * @return array Array containing paths to upload and cache directories
     * @throws \moodle_exception
     */
    public static function setup_directories($filehash) {
        global $CFG;

        $uploaddir = make_temp_directory('download/' . $filehash);
        $tmpextractdir = $uploaddir . DIRECTORY_SEPARATOR . 'uploads';
        $cachedir = $CFG->dataroot . '/local_h5pcaretaker/cache';

        self::ensure_directory($tmpextractdir);
        self::ensure_directory($cachedir);

        return [$tmpextractdir, $cachedir];
    }

    /**
     * Ensure directory exists and is writable.
     *
     * @param string $path Directory path
     * @throws \moodle_exception
     */
    private static function ensure_directory($path) {
        if (!is_dir($path) && !mkdir($path, 0755, true)) {
            throw new \moodle_exception('error:couldNotCreateDirectory', 'local_h5pcaretaker');
        }
    }
}
