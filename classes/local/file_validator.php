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
 * File validator for H5P Caretaker.
 *
 * @package    local_h5pcaretaker
 * @copyright  2025 NDLA <explore@ndla.no>
 * @license    https://opensource.org/license/mit MIT
 */
class file_validator {
    /**
     * Validate uploaded file.
     *
     * @param array $file The uploaded file data
     * @param int $maxbytes Maximum allowed file size
     * @return void
     * @throws \moodle_exception
     */
    public static function validate_upload($file, $maxbytes) {
        if (!isset($file)) {
            throw new \moodle_exception('error:noFileOrTooLarge', 'local_h5pcaretaker', '', $maxbytes / 1024);
        }

        if (strval($file['error']) !== strval(UPLOAD_ERR_OK)) {
            throw new \moodle_exception('error:unknownError', 'local_h5pcaretaker');
        }

        if (intval($file['size']) > $maxbytes) {
            throw new \moodle_exception('error:fileTooLarge', 'local_h5pcaretaker', '', $maxbytes / 1024);
        }

        $mimetype = self::get_mime_type($file['tmp_name']);
        if (!self::is_valid_mime_type($mimetype)) {
            throw new \moodle_exception('error:notAnH5Pfile', 'local_h5pcaretaker');
        }
    }

    /**
     * Get MIME type of file.
     *
     * @param string $filepath Path to the file
     * @return string MIME type
     */
    private static function get_mime_type($filepath) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $filepath);
        finfo_close($finfo);
        return $mimetype;
    }

    /**
     * Check if MIME type is valid for H5P files.
     *
     * @param string $mimetype MIME type to check
     * @return bool Whether the MIME type is valid
     */
    private static function is_valid_mime_type($mimetype) {
        return in_array($mimetype, [
            'application/zip',
            'application/x-zip-compressed',
            'application/x-zip',
        ], true);
    }
}
