<?php
/**
 * Class Media Clean
 *
 * @author  : Premio <contact@premio.io>
 * @license : GPL2
 * */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Class Folders_Media_Cleaner_DB
 *
 * This class provides functions for managing the database tables
 * related to media scanning and logging.
 *
 */
class Folders_Media_Cleaner_DB {

    /**
     * Creates tables needed for media scanning and logging if they do not exist.
     *
     * @return void
     * @global object $wpdb WordPress database access abstraction object.
     *
     */
    public function create_tables() {
        global $wpdb;
        $table_name = $wpdb->prefix . "fldr_media_scan";
        if ($wpdb->get_var("show tables like '{$table_name}'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_name (
                id BIGINT(20) NOT NULL AUTO_INCREMENT,
                post_id BIGINT(20) NULL,
                scan_path TINYTEXT NULL,
                scan_url TINYTEXT NULL,
                file_size INT(10) NULL,
                PRIMARY KEY  (id)
            ) " . $charset_collate . ";";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

        $table_name = $wpdb->prefix . "fldr_media_logs";
        if ($wpdb->get_var("show tables like '{$table_name}'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_name (
                id BIGINT(20) NOT NULL AUTO_INCREMENT,
                post_id BIGINT(20) NULL,
                scan_path TINYTEXT NULL,
                scan_url TINYTEXT NULL,
                file_size INT(10) NULL,
                PRIMARY KEY  (id)
            ) " . $charset_collate . ";";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }


    /**
     * Clears the tables used for media scanning and logging if they exist.
     *
     * @return void
     * @global object $wpdb WordPress database access abstraction object.
     *
     */
    public function clear_tables() {
        global $wpdb;
        $table_name = $wpdb->prefix . "fldr_media_scan";
        if ($wpdb->get_var("show tables like '{$table_name}'") == $table_name) {
            $query = "TRUNCATE $table_name";
            $wpdb->query($query);
        }

        $table_name = $wpdb->prefix . "fldr_media_logs";
        if ($wpdb->get_var("show tables like '{$table_name}'") == $table_name) {
            $query = "TRUNCATE $table_name";
            $wpdb->query($query);
        }
    }

    /**
     * Save media IDs to the logs table.
     *
     * @param array|int $post_ids The IDs of the posts associated with the media.
     * @param string $path The scan path of the media.
     * @return void
     * @global object $wpdb WordPress database access abstraction object.
     */
    public function save_media_id_log($post_ids, $path) {
        if(empty($post_ids)) {
            return;
        }
        $post_ids = is_array($post_ids)?$post_ids:array($post_ids);
        global $wpdb;

        $table_name = $wpdb->prefix . "fldr_media_logs";
        foreach ($post_ids as $post_id) {
            $insert = [
                'post_id' => $post_id,
                'scan_path' => $path,
                'file_size' => $this->get_file_size($post_id)
            ];
            $wpdb->insert($table_name, $insert);
        }
    }

    /**
     * Saves a media scan log entry to the database.
     *
     * @param int $post_id The ID of the post associated with the scanned media.
     * @param int $file_size The size of the scanned media file in bytes.
     *
     * @return void
     * @global object $wpdb WordPress database access abstraction object.
     *
     */
    public function save_scan_log($post_id, $file_size) {
        if(empty($post_id)) {
            return;
        }
        global $wpdb;
        $insert = [
            'post_id' => $post_id,
            'file_size' => $file_size
        ];
        $table_name = $wpdb->prefix . "fldr_media_scan";
        $wpdb->insert($table_name, $insert);
    }

    /**
     * Save media URL logs.
     *
     * @param string|array $urls The URLs of the media to be logged.
     * @param string $path The path of the media being scanned.
     * @return void
     * @global object $wpdb WordPress database access abstraction object.
     *
     */
    public function save_media_url_log($urls, $path) {
        if(empty($urls)) {
            return;
        }
        global $wpdb;
        $urls = is_array($urls)?$urls:array($urls);
        $table_name = $wpdb->prefix . "fldr_media_logs";
        foreach($urls as $url) {
            if(strpos($url, "wp-content/uploads/") !== false){
                $url = explode("wp-content/uploads/", $url);

                if(isset($url[1])) {

                    $query = "SELECT post_id from {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value = %s";
                    $query = $wpdb->prepare($query, [$url[1]]);
                    $post_id = $wpdb->get_var($query);

                    if(!empty($post_id)) {
                        $insert = [
                            'post_id' => $post_id,
                            'scan_url' => $url[1],
                            'scan_path' => $path,
                            'file_size' => $this->get_file_size($post_id)
                        ];
                    } else {
                        $insert = [
                            'scan_url' => $url[1],
                            'scan_path' => $path
                        ];
                    }

                    $wpdb->insert($table_name, $insert);
                }
            }
        }
    }

    /**
     * Retrieves the size of a media file.
     *
     * @param int $mediaId The ID of the media file.
     * @return int The size of the media file in bytes.
     */
    function get_file_size($mediaId)
    {
        $fileSize = get_post_meta($mediaId, "folders_media_size", true);
        if (!empty($fileSize)) {
            return $fileSize;
        }

        $file_size = 0;
        $meta_data = wp_get_attachment_metadata($mediaId);
        if (isset($meta_data['filesize'])) {
            $file_size = $meta_data['filesize'];
        }

        if (isset($meta_data['sizes'])) {
            foreach ($meta_data['sizes'] as $size) {
                if (isset($size['filesize'])) {
                    $file_size += intval($size['filesize']);
                }
            }
        }

        update_post_meta($mediaId, "folders_media_size", $file_size);
        return $file_size;

    }//end get_file_size()
}
