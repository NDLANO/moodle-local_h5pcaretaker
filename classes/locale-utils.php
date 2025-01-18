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
 * Locale utilities for H5P Caretaker.
 *
 * @package    local_h5pcaretaker
 * @copyright  2025 NDLA <explore@ndla.no>
 * phpcs:ignore moodle.Commenting.FileExpectedTags.LicenseTagInvalid
 * @license    https://opensource.org/license/mit MIT
 */
class locale_utils {
    /**
     * The path to the locale files in a wWordPress plugin.
     *
     * @var string
     */
    private static $localepath = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "lang";

    /**
     * The default locale.
     *
     * @var string
     */
    private static $defaultlocale = 'en';

    /**
     * Get the available locales.
     *
     * @return array The available locales.
     */
    public static function get_available_locales() {
        if ( ! is_dir( self::$localepath ) ) {
            return [self::$defaultlocale];
        }

        $foundlocales = array_filter(
            scandir( self::$localepath ),
            function ( $folder ) {
                return is_dir( self::$localepath . DIRECTORY_SEPARATOR . $folder ) &&
                    file_exists(
                        self::$localepath . DIRECTORY_SEPARATOR .
                            $folder . DIRECTORY_SEPARATOR .
                            'local_h5pcaretaker.php'
                    );
            }
        );

        $moodlelocales = get_string_manager()->get_list_of_translations();
        $moodlelocales = array_keys(array_filter($moodlelocales, function ($key) {
            return strpos($key, '_') === false; // Caretaker does not support regional locales yet.
        }, ARRAY_FILTER_USE_KEY));

        // Moodle can only set the languages if it has the corresponding language pack installed.
        $availablelocales = array_filter($foundlocales, function ($locale) use ($moodlelocales) {
            return in_array($locale, $moodlelocales, true);
        });

        return array_unique(array_merge([self::$defaultlocale], $availablelocales));
    }

    /**
     * Request a translation for a given locale.
     * If the locale is not available, the default locale is used.
     *
     * @param string $localerequested The locale that could be served.
     */
    public static function request_translation($localerequested) {
        $locale = explode('_', $localerequested)[0]; // Caretaker does not support regional locales yet.
        $availablelocales = self::get_available_locales();

        if (!in_array($locale, $availablelocales, true)) {
            return self::$defaultlocale;
        }

        return $locale;
    }
}
