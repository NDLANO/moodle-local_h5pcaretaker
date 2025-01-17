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

define('H5PCARETAKER_FORCELOGIN_NO', '0');
define('H5PCARETAKER_FORCELOGIN_YES', '1');

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
use Ndlano\H5PCaretaker\H5PCaretaker;

/**
 * External API for H5P Caretaker.
 *
 * @package    local_h5pcaretaker
 * @copyright  2025 NDLA <explore@ndla.no>
 * phpcs:ignore moodle.Commenting.FileExpectedTags.LicenseTagInvalid
 * @license    https://opensource.org/license/mit MIT
 */
class local_h5pcaretaker {

    /**
     * Handle API request.
     *
     * @return string The response message
     */
    public static function handler_start() {
        global $OUTPUT, $CFG, $SESSION;

        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'locale.php');

        $httpacceptlanguage = self::get_http_accept_language();
        $getlocale          = self::get_locale_from_query();

        // Set the language based on the browser's language.
        $locale = LocaleUtils::request_translation(
            empty($getlocale) ? locale_accept_from_http($httpacceptlanguage) : $getlocale
        );

        $currentlocale = current_language();

        // Set the language based on the request.
        $SESSION->lang = $locale;
        get_string_manager()->reset_caches();

        $distdir = join(DIRECTORY_SEPARATOR, [__DIR__, 'node_modules', 'h5p-caretaker-client', 'dist']);
        $disturl = '/local/h5pcaretaker/node_modules/h5p-caretaker-client/dist';

        self::render_html(
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
     * Handle API request.
     *
     * @return string The response message
     */
    public static function handler_upload() {
        global $USER;

        if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
            self::done(405, get_string('error:methodNotAllowed'));
        }

        $context = context_system::instance();
        $localh5pcaretakerconfig = get_config('local_h5pcaretaker');
        $localh5pcaretakerforcelogin = $localh5pcaretakerconfig->forcelogin ?? H5PCARETAKER_FORCELOGIN_YES;

        if ($localh5pcaretakerforcelogin === H5PCARETAKER_FORCELOGIN_YES || get_config('core', 'forcelogin')) {
            if (!isloggedin() || !has_capability('local/h5pcaretaker:use', $context)) {
                self::done(403, get_string('error:forbidden'));
            }
        }

        $maxbytes = get_max_upload_file_size();
        if (!isset($_FILES['file'])) {
            self::done(
                422,
                sprintf(
                    get_string('error:noFileOrTooLarge', 'local_h5pcaretaker'),
                    $maxbytes / 1024
                )
            );
        }

        $file = $_FILES['file'];
        if (strval($file['error']) !== strval( UPLOAD_ERR_OK ) ) {
            self::done(500, get_string('error:unknownError'));
        }

        if (intval($file['size']) > $maxbytes) {
            self::done(
                413,
                sprintf(
                    get_string('error:fileTooLarge', 'local_h5pcaretaker'),
                    $maxbytes / 1024
                )
            );
        }

        // Validate file type.
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedtypes = ['application/zip', 'application/x-zip-compressed', 'application/x-zip'];
        if (!in_array($mimetype, $allowedtypes, true)) {
            self::done(422, get_string('error:notAnH5Pfile', 'local_h5pcaretaker'));
        }

        // Create secure temporary directory.
        try {
            $filehash = bin2hex(random_bytes(32));
            $uploaddir = make_temp_directory('download/' . $filehash);

            $tmpextractdir = $uploaddir . DIRECTORY_SEPARATOR . 'uploads';
            if (!is_dir($tmpextractdir) && !mkdir($tmpextractdir, 0755, true)) {
                throw new \Exception(get_string('error:couldNotCreateUploadDirectory', 'local_h5pcaretaker'));
            }

            $cachedir = $uploaddir . DIRECTORY_SEPARATOR . 'cache';
            if (!is_dir($cachedir) && !mkdir($cachedir, 0755, true)) {
                throw new \Exception(get_string('error:couldNotCreateCacheDirectory', 'local_h5pcaretaker'));
            }
        } catch (\Exception $e) {
            self::done(500, $e->getMessage());
        }

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

        if (isset( $analysis['error'])) {
            self::done(422, $analysis['error']);
        }

        self::done( 200, $analysis['result'] );
    }

    /**
     * Get the locale from the HTTP Accept-Language header.
     *
     * @return string The locale from the HTTP Accept-Language header.
     */
    private static function get_http_accept_language() {
        return isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? clean_param($_SERVER['HTTP_ACCEPT_LANGUAGE'], PARAM_TEXT) : '';
    }

    /**
     * Get the locale from the query.
     *
     * @return string The locale from the query.
     */
    private static function get_locale_from_query() {
        return clean_param(optional_param('locale', '', PARAM_TEXT), PARAM_TEXT);
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
     * Render the HTML for the page.
     *
     * @param string $filejs The filename of the JavaScript file.
     * @param string $filecss The filename of the CSS file.
     * @param string $locale The locale to use.
     * @param string $path The path to the H5P file if preset.
     * @param string $exportremoveid The ID of the H5P content to remove the export for.
     */
    private static function render_html($filejs, $filecss, $locale, $path, $exportremoveid = false) {
        global $OUTPUT, $CFG;

        $data = [
            'locale' => str_replace('_', '-', $locale),
            'title' => get_string('site:title', 'local_h5pcaretaker'),
            'filecss' => $filecss,
            'filejs' => $filejs,
            'path' => $path,
            'exportremoveid' => $exportremoveid,
            'endpoint' => $CFG->wwwroot . '/local/h5pcaretaker/index.php',
            'sessionKeyName' => 'sesskey',
            'sessionKeyValue' => sesskey(),
            'select_language' => self::render_select_language($locale),
            'takecareofyourh5p' => get_string('headline', 'local_h5pcaretaker'),
            'checkyourh5pfileforimprovements' => get_string('callToAction', 'local_h5pcaretaker'),
            'uncoveraccessibilityissues' => get_string('callToActionDetails', 'local_h5pcaretaker'),
            'orDragTheFileHere' => get_string('dropzone:orDragTheFileHere', 'local_h5pcaretaker'),
            'removeFile' => get_string('dropzone:removeFile', 'local_h5pcaretaker'),
            'selectYourLanguage' => get_string('languageSelect:a11ySelectLanguage', 'local_h5pcaretaker'),
            'uploadProgress' => get_string('dropzone:uploadProgress', 'local_h5pcaretaker'),
            'uploadYourH5Pfile' => get_string('dropzone:uploadH5PFile', 'local_h5pcaretaker'),
            'yourFileIsBeingChecked' => get_string('dropzone:fileBeingChecked', 'local_h5pcaretaker'),
            'yourFileWasCheckedSuccessfully' => get_string('dropzone:fileCheckedSuccessfully', 'local_h5pcaretaker'),
            'allFilteredOut' => get_string('filter:allFilteredOut', 'local_h5pcaretaker'),
            'contentFilter' => get_string('filter:contentFilter', 'local_h5pcaretaker'),
            'showAll' => get_string('filter:showAll', 'local_h5pcaretaker'),
            'showSelected' => get_string('filter:showSelected', 'local_h5pcaretaker'),
            'showNone' => get_string('filter:showNone', 'local_h5pcaretaker'),
            'filterByContent' => get_string('filter:filterByContent', 'local_h5pcaretaker'),
            'reset' => get_string('filter:reset', 'local_h5pcaretaker'),
            'totalMessages' => get_string('results:totalMessages', 'local_h5pcaretaker'),
            'issues' => get_string('results:issues', 'local_h5pcaretaker'),
            'results' => get_string('results:results', 'local_h5pcaretaker'),
            'filterBy' => get_string('results:filterBy', 'local_h5pcaretaker'),
            'groupBy' => get_string('results:groupBy', 'local_h5pcaretaker'),
            'download' => get_string('results:download', 'local_h5pcaretaker'),
            'expandAllMessages' => get_string('expand:expandAllMessages', 'local_h5pcaretaker'),
            'collapseAllMessages' => get_string('expand:collapseAllMessages', 'local_h5pcaretaker'),
            'reportTitleTemplate' => get_string('report:titleTemplate', 'local_h5pcaretaker'),
        ];

        echo $OUTPUT->render_from_template('local_h5pcaretaker/h5pcaretaker', $data);
    }

    /**
     * Render the language selection dropdown.
     *
     * @param string $locale The current locale to set selected.
     * @return string The rendered HTML.
     */
    public static function render_select_language($locale) {
        global $OUTPUT;

        $availablelocales = LocaleUtils::get_available_locales();
        $localeslookup = array_combine(
            $availablelocales,
            array_map('\Locale::getDisplayLanguage', $availablelocales, $availablelocales)
        );
        asort($localeslookup);

        $locales = array_map(function($availablelocale) use ($locale, $localeslookup) {
            return [
                'locale' => $availablelocale,
                'name' => $localeslookup[$availablelocale],
                'selected' => $availablelocale === $locale,
            ];
        }, $availablelocales);

        return $OUTPUT->render_from_template('local_h5pcaretaker/select_language', ['locales' => $locales]);
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
        if (isset($message)) {
            echo $message;
        }

        if (isset($code)) {
            http_response_code($code);
        }

        exit();
    }
}
