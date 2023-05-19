# Directory Viewer for Filebird

**Contributors:** pumpkinslayer12

**Tags:** filebird, directory, viewer

**Requires at least:** 5.0

**Tested up to:** 5.9

**Stable tag:** 1.0

**License:** GPLv3 or later

**License URI:** [https://www.gnu.org/licenses/gpl-3.0.html](https://www.gnu.org/licenses/gpl-3.0.html)

This plugin is a directory viewer for the FileBird plugin that allows you to view all directories and files using a shortcode.

## Description

Directory Viewer for Filebird enhances the functionality of the FileBird WordPress plugin by allowing users to view all directories and files using a simple shortcode. It requires FileBird to be installed and activated on your WordPress site.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/directory-viewer-for-filebird` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Make sure the FileBird plugin is installed and activated.

## Usage

Use the shortcode `[directory_viewer_filebird folder_id=0]` to display the directory structure starting from the root directory. You can change the `folder_id` to display another directory.

To get a specific folder id, inspect the output directory structure using console. Each directory will have the attribute id="folder-x", where x is the folder id.

## Frequently Asked Questions

**What is the prerequisite for this plugin?**

This plugin requires the FileBird plugin to be installed and activated on your WordPress site.

**What is the shortcode to display the directory structure?**

The shortcode is `[directory_viewer_filebird folder_id=0]`. You can change the `folder_id` to display another directory.

**How many shortcodes can be used on one page?**

Currently only one shortcode can be used, per page. This limitation will be addressed in subsequent releases.

## Changelog

**1.0**

- Initial release.

## Upgrade Notice

**1.0**

- Initial release.