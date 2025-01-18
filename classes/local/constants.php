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
 * Constants for H5P Caretaker.
 *
 * @package    local_h5pcaretaker
 * @copyright  2025 NDLA <explore@ndla.no>
 * @license    https://opensource.org/license/mit MIT
 */
class constants {
    /** HTTP status code for OK. */
    public const HTTP_STATUS_OK = 200;

    /** HTTP status code for forbidden. */
    public const HTTP_STATUS_FORBIDDEN = 403;

    /** HTTP status code for method not allowed. */
    public const HTTP_STATUS_METHOD_NOT_ALLOWED = 405;

    /** HTTP status code for payload too large. */
    public const HTTP_STATUS_PAYLOAD_TOO_LARGE = 413;

    /** HTTP status code for unprocessable entity. */
    public const HTTP_STATUS_UNPROCESSABLE_ENTITY = 422;

    /** HTTP status code for internal server error. */
    public const HTTP_STATUS_INTERNAL_SERVER_ERROR = 500;
}
