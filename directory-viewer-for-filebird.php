<?php

/**
 * Plugin Name: Directory Viewer for Filebird
 * 
 * Plugin URI: https://github.com/pumpkinslayer12/directory-viewer-for-filebird
 *
 * Description: A plugin to enable directory and file viewing within the FileBird plugin using a shortcode.
 *
 * Author: pumpkinslayer12
 * 
 * Author URI: https://https://github.com/pumpkinslayer12
 * 
 * Version: 1.0
 *
 * Text Domain: directory-viewer-for-filebird
 * 
 * License: GPL v3 or later
 *
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Directory_Viewer_For_Filebird
 */

/**
 * Checks if the required FileBird plugin is active.
 *
 * @since 1.0.0
 */
function dvff_filebird_plugin_dependency_check()
{
    // Check if the required plugin is active
    if (is_plugin_active('filebird/filebird.php')) {
        // Plugin actions can go here
    } else {
        // Display an error message or deactivate your plugin
        add_action('admin_notices', 'dvff_plugin_dependency_error');
    }
}

/**
 * Handles the case when the required FileBird plugin is not active.
 *
 * @since 1.0.0
 */
function dvff_plugin_dependency_error()
{
    echo '<div class="error"><p>Your plugin requires the FileBird Plugin to be active.</p></div>';
    deactivate_plugins(plugin_basename(__FILE__));
}

add_action('admin_init', 'dvff_filebird_plugin_dependency_check');


use FileBird\Model\Folder as FolderModel;
use FileBird\Classes\Tree;
use FileBird\Classes\Helpers as Helpers;

/**
 * Enqueues assets and handles shortcode attributes for Filebird directory structure.
 *
 * @since 1.0.0
 *
 * @param array $atts Shortcode attributes.
 *
 * @return string Buffered HTML output of directory structure.
 */
function dvff_filebird_structure_shortcode($atts)
{
    // Extract shortcode attributes
    $atts = shortcode_atts(array('folder_id' => 0), $atts, 'filebird_structure');

    $assetFolderPath = plugins_url('assets/', __FILE__);
    wp_enqueue_style('dvff_jstree_css', $assetFolderPath . 'style.min.css', array(), '3.3.15');
    wp_enqueue_style('dvff_jstree_styles', $assetFolderPath . 'dvff_styles.css', array(), '3.3.15');

    wp_enqueue_script('dvff_jstree', $assetFolderPath . 'jstree.min.js', array('jquery'), '3.3.15', true);
    wp_enqueue_script('dvff_jstree_initialize', $assetFolderPath . 'dvff_jstree_initialize.js', array('jquery', 'dvff_jstree'), '1.0', true);

    // Get folder structure
    $folders = Tree::getFolders(null);

    // Recursively create array structure for jstree
    $tree_structure = dvff_create_tree_structure($folders, (int) $atts['folder_id']);

    $jstreeID = 'jstree-structure';
    wp_localize_script(
        'dvff_jstree_initialize',
        'dvff_jstree_initialize',
        array(
            'tree_data' => json_encode($tree_structure),
            'id' => esc_attr($jstreeID) // Pass the id in the array
        )
    );
    // Start output buffering
    ob_start();

    // Output search form for jstree
    echo '<div><input id="search-input" class="search-input dvff-jstree-search" placeholder="File search"/> </div>';
    // Output div for jstree
    echo '<div id="' . esc_attr($jstreeID) . '"></div>';
    // Return the buffered content
    return ob_get_clean();
}
add_shortcode('directory_viewer_filebird', 'dvff_filebird_structure_shortcode');

/**
 * Creates the directory tree structure for display.
 *
 * @since 1.0.0
 *
 * @param array $folders The array of folders to process.
 * @param int   $parent_id The ID of the parent folder.
 *
 * @return array An array representing the tree structure of the directory.
 */
function dvff_create_tree_structure($folders, $parent_id = 0)
{
    $tree = array();

    foreach ($folders as $folder) {
        // Check if we are on the starting folder or if the current folder is a child of the parent_id
        if ($folder['id'] == $parent_id || $folder['li_attr']['data-parent'] == $parent_id) {
            // Create node for the folder
            $node = array();
            $node['id'] = 'folder-' . esc_attr($folder['id']);
            $node['text'] = $folder['text'];

            // Determine the icon class based on whether it's a folder or a file
            $iconClass = 'folder-icon';
            if (isset($folder['li_attr']['data-is-file']) && $folder['li_attr']['data-is-file']) {
                $iconClass = 'file-icon';
            }
            $node['icon'] = $iconClass;

            // Create child nodes for the folder's attachments
            $attachments = Helpers::getAttachmentIdsByFolderId($folder['id']);
            if (!empty($attachments)) {
                $node['children'] = array();
                foreach ($attachments as $attachment_id) {
                    $attachment_node = array();
                    $attachment_node['id'] = 'attachment-' . $attachment_id;
                    // Get the attachment URL
                    $attachment_url = wp_get_attachment_url($attachment_id);
                    // Get the file extension
                    $file_extension = pathinfo($attachment_url, PATHINFO_EXTENSION);
                    // Include the file extension in the attachment node text
                    $attachment_node['text'] = get_the_title($attachment_id) . '.' . $file_extension;
                    $attachment_node['a_attr'] = array(
                        'href' => $attachment_url // Set the attachment URL
                    );

                    $mime_type = get_post_mime_type($attachment_id);
                    $attachment_node['icon'] = dvff_get_icon_class_by_mime_type($mime_type); // Assign the file icon class to attachments
                    $node['children'][] = $attachment_node;
                }
            }

            // Recursively create child nodes for the folder's child folders
            if (!empty($folder['children'])) {
                $child_folders = dvff_create_tree_structure($folder['children'], (int) $folder['id']);
                if (isset($node['children'])) {
                    $node['children'] = array_merge($node['children'], $child_folders);
                } else {
                    $node['children'] = $child_folders;
                }
            }

            $tree[] = $node;
        }
    }

    return $tree;
}

/**
 * Gets an icon class based on MIME type of the file.
 *
 * @since 1.0.0
 *
 * @param string $mime_type The MIME type of the file.
 *
 * @return string An icon class corresponding to the file type.
 */

function dvff_get_icon_class_by_mime_type($mime_type)
{
    if (strstr($mime_type, "video/")) {
        return 'video-icon';
    } elseif (strstr($mime_type, "image/")) {
        return 'image-icon';
    } elseif (strstr($mime_type, "application/pdf")) {
        return 'pdf-icon';
    } else {
        return 'file-icon';
    }
}