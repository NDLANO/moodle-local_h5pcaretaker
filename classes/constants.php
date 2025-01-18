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
 * Constants for H5P Caretaker plugin.
 *
 * @package    local_h5pcaretaker
 * @copyright  2025 NDLA <explore@ndla.no>
 * phpcs:ignore moodle.Commenting.FileExpectedTags.LicenseTagInvalid
 * @license    https://opensource.org/license/mit MIT
 */

namespace local_h5pcaretaker;

/**
 * Constants class for H5P Caretaker.
 */
class constants {

    /** @var int HTTP status code for successful response */
    public const HTTP_STATUS_OK = 200;

    /** @var int HTTP status code for forbidden access */
    public const HTTP_STATUS_FORBIDDEN = 403;

    /** @var int HTTP status code for invalid request method */
    public const HTTP_STATUS_METHOD_NOT_ALLOWED = 405;

    /** @var int HTTP status code for file exceeding size limit */
    public const HTTP_STATUS_PAYLOAD_TOO_LARGE = 413;

    /** @var int HTTP status code for invalid file type or format */
    public const HTTP_STATUS_UNPROCESSABLE_ENTITY = 422;

    /** @var int HTTP status code for server-side errors */
    public const HTTP_STATUS_INTERNAL_SERVER_ERROR = 500;

    /** @var array List of allowed MIME types for H5P file uploads */
    public const ALLOWED_MIME_TYPES = [
        'application/zip',
        'application/x-zip-compressed',
        'application/x-zip',
    ];
}
