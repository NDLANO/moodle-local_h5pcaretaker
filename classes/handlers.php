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
 * lib.php
 *
 * @package    local_h5pcaretaker
 * @copyright  2025 NDLA <explore@ndla.no>
 * phpcs:ignore moodle.Commenting.FileExpectedTags.LicenseTagInvalid
 * @license    https://opensource.org/license/mit MIT
 */

namespace local_h5pcaretaker;
use core\context\system as context_system;

defined('MOODLE_INTERNAL') || die();

require_once(join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'vendor', 'autoload.php']));
require_once(join(DIRECTORY_SEPARATOR, [__DIR__, 'locale_utils.php']));
require_once(join(DIRECTORY_SEPARATOR, [__DIR__, 'render.php']));

use Ndlano\H5PCaretaker\H5PCaretaker;
use local_h5pcaretaker\constants;
use local_h5pcaretaker\locale_utils;
use local_h5pcaretaker\render;

/**
 * External API for H5P Caretaker.
 *
 * @package    local_h5pcaretaker
 * @copyright  2025 NDLA <explore@ndla.no>
 * phpcs:ignore moodle.Commenting.FileExpectedTags.LicenseTagInvalid
 * @license    https://opensource.org/license/mit MIT
 */
class handlers {

    /**
     * Handle API request to start the Caretaker procedure.
     *
     * @return string The response message.
     */
    public static function handler_start() {
        global $OUTPUT, $CFG, $SESSION;

        // Set the language based on the browser's language.
        $httpacceptlanguage = locale_utils::get_http_accept_language();
        $querylocale = locale_utils::get_locale_from_query();
        $locale = locale_utils::request_translation($querylocale ?? locale_accept_from_http($httpacceptlanguage));

        $currentlocale = current_language();

        // Set the language based on the request.
        $SESSION->lang = $locale;
        get_string_manager()->reset_caches();

        // Load the H5P Caretaker client.
        $distdir = join(
            DIRECTORY_SEPARATOR,
            [__DIR__, '..', 'node_modules', '@explorendla', 'h5p-caretaker-client', 'dist', '@explorendla']
        );
        $disturl = '/local/h5pcaretaker/node_modules/@explorendla/h5p-caretaker-client/dist/@explorendla';

        render::render_html(
            $disturl . '/' . self::get_file_by_pattern( $distdir, 'h5p-caretaker-client-*.js' ),
            $disturl . '/' . self::get_file_by_pattern( $distdir, 'h5p-caretaker-client-*.css' ),
            $locale,
            null,
            false
        );

        // Reset the language.
        $SESSION->lang = $currentlocale;
        get_string_manager()->reset_caches();

        exit(); // Ensure no other content is loaded.
    }

    /**
     * Handle API request to upload a file.
     *
     * @return string The response message
     */
    public static function handler_upload() {
        global $CFG;

        $file = self::validate_request();

        list($tmpextractdir, $cachedir) = self::create_directories();

        // Instantiate the Caretaker and return the analysis.
        $config = [
            'uploadsPath' => $tmpextractdir,
            'cachePath'   => $cachedir,
        ];

        $locale = clean_param(optional_param('locale', '', PARAM_TEXT), PARAM_TEXT);
        if ($locale !== '') {
            $config['locale'] = $locale;
        }

        $h5pcaretaker = new H5PCaretaker($config);
        $analysis = $h5pcaretaker->analyze(['file' => $file['tmp_name']]);

        if (isset($analysis['error'])) {
            self::done(constants::HTTP_STATUS_UNPROCESSABLE_ENTITY, $analysis['error']);
        }

        self::done(constants::HTTP_STATUS_OK, $analysis['result']);
    }

    /**
     * Get file by pattern.
     *
     * @param string $dir The directory to search in.
     * @param string $pattern The pattern to match.
     *
     * @return string The filename that matches the pattern.
     */
    private static function get_file_by_pattern($dir, $pattern) {
        $files = glob($dir . DIRECTORY_SEPARATOR . $pattern);
        return basename($files[0] ?? '');
    }

    /**
     * Validate the request.
     */
    private static function validate_request() {
        if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
            self::done(constants::HTTP_STATUS_METHOD_NOT_ALLOWED, get_string('error:methodNotAllowed'));
        }

        // Check if the user is allowed to use the Caretaker.
        $context = context_system::instance();
        $localh5pcaretakerforcelogin = get_config('local_h5pcaretaker', 'forcelogin') ?? constants::FORCELOGIN_YES;
        $forceloginrequired = $localh5pcaretakerforcelogin ===
            (constants::FORCELOGIN_YES || get_config('core', 'forcelogin'));
        if ($forceloginrequired && (!isloggedin() || !has_capability('local/h5pcaretaker:use', $context))) {
            self::done(constants::HTTP_STATUS_FORBIDDEN, get_string('error:forbidden'));
        }

        // Verify file upload (size, which could cause this to fail here, too).
        $maxbytes = get_max_upload_file_size();
        if (!isset($_FILES['file'])) {
            self::done(
                constants::HTTP_STATUS_UNPROCESSABLE_ENTITY,
                sprintf(get_string('error:noFileOrTooLarge', 'local_h5pcaretaker'), $maxbytes / 1024)
            );
        }

        // Validate file upload.
        $file = $_FILES['file'];
        if (strval($file['error']) !== strval( UPLOAD_ERR_OK ) ) {
            self::done(constants::HTTP_STATUS_INTERNAL_SERVER_ERROR, get_string('error:unknownError'));
        }

        // Validate file size.
        if (intval($file['size']) > $maxbytes) {
            self::done(
                constants::HTTP_STATUS_PAYLOAD_TOO_LARGE,
                sprintf(get_string('error:fileTooLarge', 'local_h5pcaretaker'), $maxbytes / 1024)
            );
        }

        // Validate file type.
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mimetype, constants::ALLOWED_MIME_TYPES, true)) {
            self::done(constants::HTTP_STATUS_UNPROCESSABLE_ENTITY, get_string('error:notAnH5PFile', 'local_h5pcaretaker'));
        }

        return $file;
    }

    /**
     * Create temporary and cache directories.
     *
     * @return array An array containing the paths to the temporary and cache directories.
     * @throws \Exception If the directories cannot be created.
     */
    private static function create_directories() {
        global $CFG;

        try {
            $filehash = bin2hex(random_bytes(32));
            $uploaddir = make_temp_directory('download/' . $filehash);

            $tmpextractdir = $uploaddir . DIRECTORY_SEPARATOR . 'uploads';
            if (!is_dir($tmpextractdir) && !mkdir($tmpextractdir, 0755, true)) {
                throw new \Exception(get_string('error:couldNotCreateUploadDirectory', 'local_h5pcaretaker'));
            }

            $cachedir = $CFG->dataroot . '/local_h5pcaretaker/cache';
            if (!is_dir($cachedir) && !mkdir($cachedir, 0755, true)) {
                throw new \Exception(get_string('error:couldNotCreateCacheDirectory', 'local_h5pcaretaker'));
            }

            return [$tmpextractdir, $cachedir];
        } catch (\Exception $e) {
            self::done(constants::HTTP_STATUS_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }

    /**
     * Exit the script with an optional HTTP status code.
     *
     * @param int    $code    The HTTP status code to send.
     * @param string $message The message to display.
     *
     * @return void
     */
    public static function done($code, $message) {
        if (isset($code)) {
            http_response_code($code);
        }

        if (isset($message)) {
            echo $message;
        }

        exit();
    }
}
