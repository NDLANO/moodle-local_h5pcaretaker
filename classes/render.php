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
 * Render utilities for H5P Caretaker.
 *
 * @package    local_h5pcaretaker
 * @copyright  2025 NDLA <explore@ndla.no>
 * phpcs:ignore moodle.Commenting.FileExpectedTags.LicenseTagInvalid
 * @license    https://opensource.org/license/mit MIT
 */

namespace local_h5pcaretaker;

/**
 * Utility class for rendering HTML and language selection.
 */
class render {
    /**
     * Render the HTML for the page.
     *
     * @param string $filejs The filename of the JavaScript file.
     * @param string $filecss The filename of the CSS file.
     * @param string $locale The locale to use.
     * @param string $path The path to the H5P file if preset.
     * @param string $exportremoveid The ID of the H5P content to remove the export for.
     */
    public static function render_html($filejs, $filecss, $locale, $path, $exportremoveid = false) {
        global $OUTPUT, $CFG;

        $data = [
            'locale' => str_replace('_', '-', $locale),
            'title' => get_string('site:title', 'local_h5pcaretaker'),
            'filecss' => $filecss,
            'filejs' => $filejs,
            'path' => $path,
            'exportremoveid' => $exportremoveid,
            'intro' => get_config('local_h5pcaretaker', 'extratext_intro'),
            'footer' => get_config('local_h5pcaretaker', 'extratext_footer'),
            'noBranding'=> get_config('local_h5pcaretaker', 'no_branding'),
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
            'showDetails' => get_string('results:showDetails', 'local_h5pcaretaker'),
            'hideDetails' => get_string('results:hideDetails', 'local_h5pcaretaker'),
            'h5pcaretaker' => get_string('h5pcaretaker', 'local_h5pcaretaker'),
            'unknownError' => get_string('error:unknownError', 'local_h5pcaretaker'),
            'checkServerLog' => get_string('error:checkServerLog', 'local_h5pcaretaker'),
            'expandList' => get_string('filter:expandList', 'local_h5pcaretaker'),
            'collapseList' => get_string('filter:collapseList', 'local_h5pcaretaker'),
            'changeSortingGrouping' => get_string('results:changeSortingGrouping', 'local_h5pcaretaker'),
            'previousMessage' => get_string('results:previousMessage', 'local_h5pcaretaker'),
            'nextMessage' => get_string('results:nextMessage', 'local_h5pcaretaker'),
        ];

        echo $OUTPUT->render_from_template('local_h5pcaretaker/h5pcaretaker', $data);
    }

    /**
     * Render the language selection dropdown.
     *
     * @param string $locale The current locale to set selected.
     *
     * @return string The rendered HTML.
     */
    public static function render_select_language($locale) {
        global $OUTPUT;

        $availablelocales = locale_utils::get_available_locales();
        $localeslookup = array_combine(
            $availablelocales,
            array_map('\Locale::getDisplayLanguage', $availablelocales, $availablelocales)
        );
        asort($localeslookup);

        $locales = array_map(function($availablelocale) use ($locale, $localeslookup) {
            return [
                'locale' => $availablelocale,
                'name' => ucfirst($localeslookup[$availablelocale]),
                'selected' => $availablelocale === $locale,
            ];
        }, $availablelocales);

        return $OUTPUT->render_from_template('local_h5pcaretaker/select_language', ['locales' => array_values($locales)]);
    }
}
