# NDLA's H5P Caretaker
The "H5P Caretaker" plugin for Moodle allows you to use NDLA's library of the same name to
check H5P content files for improvement options, e.g. accessibility issues, conflicting licenses
across subcontents or images that take too much storage space for their respective purpose.

## Features
The plugin will set up a dedicated URL that hosts NDLA's H5P Caretaker tool.
Users can upload files temporarily for checking and receive a report that they can navigate in.

![H5P Caretaker: Report](docs/screenshot_report.jpg?raw=true)

## Install
### Moodle Plugin Directory
Install the _NDLA's H5P Caretaker_ plugin via the Moodle Plugin directory (not available yet).

### Upload ZIP file
1. Go to https://github.com/NDLANO/moodle-local_h5pcaretaker/releases.
2. Pick the latest release (or the one that you want to use) and download the
   `moodle-local_h5pcarateker-<plugin-version>.zip` file.
3. Log in to your Moodle site as an admin and go to _Site administration > Plugins > Install plugins_.
4. Upload the ZIP file with the plugin code.
5. Check the plugin validation report and finish the installation.

## Configure
Set the capability _local/h5pcaretaker:use_ as required. Only users/roles with this
capability will be able to use the H5P Caretaker unless it is configured to be public.

Go to the settings _Site administration > Plugins > Local Plugins > NDLA's H5P Caretaker_ and
- choose whether the tool should be usable publicly or only by users with the respective
  capability (not public by default),
- add introductory text that should be displayed on the page on top of the upload button, and
- add footer text that should be displayed at the bottom of the page.

## Usage
The plugin will set up the URL <your-moodle-site>/local/h5p-caretaker. Go there, upload an H5P file
and check the report for potential improvements of the content. If you want to be the page to be available under a different URL, you can do this by setting up appropriate rewrite rules on your server.

The plugin will also add an `H5P Caretaker` menu item to the toolbar of a course.

## Privacy
Please note that the uploaded H5P file will be removed immediately after analyzing it. It will not be stored permanently or used for anything else.
Please also note that the plugin will fetch the H5P accessibility reports from https://studio.libretexts.org/help/h5p-accessibility-guide and display those inside the report if appropriate. No personal information is shared in that process.

## License
The H5P Caretaker plugin for Moodle is is licensed under the [MIT License](https://opensource.org/license/mit).
