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

class Folders_Media_Cleaner
{

    /**
     * ACF Media file list
     *
     * @var    string    $acfMediaFiles    ACF Media file list
     * @since  1.0.0
     * @access public
     */
    var $acfMediaFiles = [];

    /**
     * Media scan status
     *
     * @var    string    $isMediaScanned    Media scan status
     * @since  1.0.0
     * @access public
     */
    var $isMediaScanned = 0;

    /**
     * Meta key for Media Size
     *
     * @var    string    $meta_key    Meta key for Media Size
     * @since  1.0.0
     * @access public
     */
    var $meta_key;

    /**
     * Meta key for Media Size
     *
     * @var    string    $transientId    transient id to store ACF data
     * @since  1.0.0
     * @access public
     */
    var $transientId;


    /**
     * Class Constructor
     *
     * Initializes the object and sets up necessary hooks and actions.
     *
     * @return void
     * @since 2.8.2
     *
     */
    public function __construct() {
        $this->meta_key = "folders_media_size";

        add_action("wp_ajax_folders_scan_for_unused_files", [$this, "scan_for_unused_files"]);
        add_action("wp_ajax_folders_scan_for_files", [$this, "scan_for_files"]);

        // actions to scan files
        add_action("folders_scan_postmeta", [$this, "folders_scan_for_acf"]);

        add_action('admin_notices', [$this, 'admin_notices']);

        // to remove media files
        add_action('wp_ajax_wcp_remove_scanned_media', [$this, 'remove_scanned_media']);
        add_action('wp_ajax_wcp_remove_multiple_scanned_media', [$this, 'remove_multiple_scanned_media']);

        // Get Pagination Data
        add_action("wp_ajax_get_folders_scanned_files", [$this, 'get_media_files']);

    }//end __construct()


    /**
     * Retrieves media files based on specified parameters.
     *
     * @return void
     */
    public function get_media_files() {
        $response = [];

        $postData = filter_input_array(INPUT_POST);

        $currentPage = (isset($postData['page_number']) && is_numeric($postData['page_number']) && $postData['page_number'] > 0) ? esc_attr($postData['page_number']) : 0;
        $currentPage = intval($currentPage);

        $recordsPerPage = 40;

        $orderBy     = isset($postData['order_by']) ? esc_attr($postData['order_by']) : "size";
        $order       = isset($postData['order']) ? esc_attr($postData['order']) : "DESC";
        $order       = strtolower($order);

        if ($order != "asc" && $order != "desc") {
            $order = "desc";
        }

        if ($orderBy == "size") {
            $orderBy = "L.file_size";
        } else if ($orderBy == "date") {
            $orderBy = "P.post_date";
        } else if ($orderBy == "title") {
            $orderBy = "P.post_title";
        } else if ($orderBy == "id") {
            $orderBy = "P.ID";
        } else {
            $orderBy = "L.file_size";
        }

        global $wpdb;
        $table = $wpdb->prefix."fldr_media_scan";
        $query = "SELECT count(id) as total_records FROM {$table}";
        $total = $wpdb->get_var($query);
        $mediaFiles = [];
        if(!empty($total)) {
            $offset = ($currentPage - 1)*$recordsPerPage;
            $post_table = $wpdb->posts;
            $query = "SELECT * FROM {$table} AS L
                        LEFT JOIN {$post_table} AS P ON P.ID = L.post_id
                        ORDER BY {$orderBy} {$order}
                            LIMIT %d, %d";
            $query = $wpdb->prepare($query, [$offset, $recordsPerPage]);
            $records = $wpdb->get_results($query);
            if(!empty($records)) {
                foreach($records as $record) {
                    $mediaId = $record->post_id;
                    $mediaData       = [];
                    $mediaData['id'] = $mediaId;
                    $mediaData['thumb_url']   = wp_get_attachment_image_url($mediaId, "thumbnail", true);
                    $attachmentMeta           = wp_get_attachment_metadata($mediaId);
                    $mediaData['total_files'] = (isset($attachmentMeta['sizes']) && !empty($attachmentMeta['sizes'])) ? count($attachmentMeta['sizes']) : 0;
                    $file_size = $this->get_file_size($mediaId);
                    $mediaData['file_size'] = size_format($file_size, 2);
                    $mediaData['edit_url']  = admin_url("post.php?action=edit&post=".$mediaId);
                    $mediaData['title']     = get_the_title($mediaId);
                    $path = get_post_meta($mediaId, "_wp_attached_file", true);
                    if (!empty($mediaData['total_files']) && $mediaData['total_files'] > 1) {
                        $path .= sprintf(esc_html__(" (+%1\$d files)"), $mediaData['total_files']);
                    }

                    $mediaData['path']  = $path;
                    $mediaData['token'] = wp_create_nonce("wp_delete_attachment".$mediaId);
                    $mediaData['date']  = sprintf(esc_html__("%1\$s ago", "folders"), human_time_diff(strtotime(get_the_modified_date('Y-m-d H:i:s', $mediaId))));

                    $mediaFiles[] = $mediaData;
                }
            }
        }

        $response['status'] = 1;
        $response['files']  = $mediaFiles;
        $response['current_page'] = $currentPage;
        echo wp_json_encode($response);
        die;
    }


    /**
     * Admin Notices.
     *
     * This method is responsible for displaying admin notices on the scan page of a plugin. The notices provide important warnings and precautions before deleting any files. The method
     * checks if the current page is the scan page and if a scan is requested. If both conditions are met, an HTML notice is displayed.
     *
     * @return void
     * @since 2.8.3
     */
    public function admin_notices() {
        $isScanPage   = (isset($_REQUEST['page']) && $_REQUEST['page'] == "folders-media-cleaning" && (isset($_REQUEST['scan']) && $_REQUEST['scan'] == 1)) ? true : false;
        if ($isScanPage) { ?>
            <style>
                .media-notice {
                    margin: 15px 15px 2px;
                }
                .media-folder-notice {
                    display: flex;
                    align-items: baseline;
                }
                .media-folder-notice-left {
                    flex: 0 0 35px;
                    color: #d63638;
                }
                .media-folder-notice-right {
                    flex: 1;
                    font-size: 14px;
                }
                .media-folder-notice-right p {
                    font-size: 14px;
                }
                .media-folder-notice span.dashicons.dashicons-info-outline {
                    padding: 4px;
                    background-color: #fff2f2;
                    border-radius: 50%;
                }
                .media-folder-notice b {
                    font-weight: 600;
                }
            </style>
            <div class="notice notice-error media-notice">
                <div class="media-folder-notice">
                    <div class="media-folder-notice-left">
                        <span class="dashicons dashicons-info-outline"></span>
                    </div>
                    <div class="media-folder-notice-right">
                        <p>Please <b>be very careful before deleting</b> any files. <b>Take back up</b>, and make sure you test the website <b>before permanently deleting</b>. Some actively used files can still show up as unused files when searching. You <b>are responsible</b> for any damage if you delete anything important. So, please be careful 🙏</p>
                    </div>
                </div>
            </div>
        <?php }//end if

    }//end admin_notices()


    /**
     * Removes multiple scanned media attachments.
     *
     * This method checks if the current user has the capability to upload files.
     * It retrieves the attachment IDs and the nonce value from the POST data.
     * If the nonce is verified and the attachment IDs are not empty, it deletes each attachment using `wp_delete_attachment()`.
     * Finally, it returns the status and the list of attachment IDs as a JSON response.
     *
     * @return void
     */
    public function remove_multiple_scanned_media() {
        $status         = 0;
        $attachment_ids = [];
        if (current_user_can('upload_files')) {
            $postData = filter_input_array(INPUT_POST);
            if (isset($postData['attachment_ids']) && isset($postData['nonce'])) {
                $attachment_ids = $postData['attachment_ids'];
                $nonce = sanitize_text_field($postData['nonce']);
                if (wp_verify_nonce($nonce, 'remove_multiple_scanned_files') && !empty($attachment_ids)) {
                    foreach ($attachment_ids as $attachment_id) {
                        wp_delete_attachment(absint($attachment_id));
                    }

                    $status = 1;
                }
            }
        }

        if (!is_array($attachment_ids)) {
            $attachment_ids = [];
        }

        echo wp_json_encode(['status' => $status, "attachment_ids" => $attachment_ids]);
        die;

    }//end remove_multiple_scanned_media()


    /**
     * Removes scanned media based on the provided attachment ID and nonce.
     *
     * @return void
     */
    public function remove_scanned_media() {
        $status        = 0;
        $attachment_id = 0;
        if (current_user_can('upload_files')) {
            if (isset($_POST['attachment_id']) && isset($_POST['nonce'])) {
                $attachment_id = folders_sanitize_text('attachment_id', 'post');
                $nonce         = folders_sanitize_text('nonce', 'post');
                if (wp_verify_nonce($nonce, 'wp_delete_attachment'.$attachment_id)) {
                    wp_delete_attachment(absint($attachment_id));
                    $status = 1;
                }
            }
        }

        echo wp_json_encode(['status' => $status, "attachment_id" => $attachment_id]);
        die;

    }//end remove_scanned_media()

    /**
     * Scan for files based on input parameters and perform actions accordingly.
     *
     * @return void
     */
    public function scan_for_files() {
        $response = [];
        $postData = filter_input_array(INPUT_POST);
        $currentPage = (isset($postData['current']) && is_numeric($postData['current']) && $postData['current'] > 0) ? esc_attr($postData['current']) : 0;
        $currentPage = intval($currentPage);

        if(empty($currentPage)) {
            $currentPage = 1;
        }

        $action = filter_input(INPUT_POST, 'scan_action');
        $actionNumber = 0;

        $totalPages = 0;
        $status = 0;
        $perPage = 25;

        global $wpdb;

        if($action == "cleaning_data") {
            $totalPages = 2;
            $status = 1;
            if($currentPage == 1) {
                $this->check_for_media_tables();
            } else {
                $currentPage = 2;
                $this->reset_media_tables();
            }
        } elseif ($action == "content_scan") {

            $status = 1;
            $total = (isset($postData['total']) && is_numeric($postData['total']) && $postData['total'] > 0) ? esc_attr($postData['total']) : 0;

            if($currentPage == 1 || empty($total)) {
                $post_type_not_in = "'attachment', 'shop_order', 'shop_order_refund', 'nav_menu_item', 'revision', 'auto-draft', 'wphb_minify_group', 'customize_changeset', 'oembed_cache', 'nf_sub', 'jp_img_sitemap'";
                $post_type_not_in = apply_filters("folders_filter_media_scan_post_types", $post_type_not_in);

                $post_status_not_in = "'inherit', 'trash', 'auto-draft'";
                $post_status_not_in = apply_filters("folders_filter_media_scan_post_status", $post_status_not_in);

                $query = "SELECT COUNT(DISTINCT(p.ID)) AS total_records FROM {$wpdb->posts} AS p
                        WHERE p.post_status NOT IN ({$post_status_not_in})
                        AND p.post_type NOT IN ({$post_type_not_in})
                        AND p.post_type NOT LIKE 'dlssus_%'
                        AND p.post_type NOT LIKE 'ml-slide%'
                        AND p.post_type NOT LIKE '%acf-%'
                        AND p.post_type NOT LIKE '%edd_%'";
                $totalRecords = $wpdb->get_var($query);
                $totalPages = ceil($totalRecords/$perPage);
            } else {
                $totalPages = $total;
            }

            $post_type_not_in = "'attachment', 'shop_order', 'shop_order_refund', 'nav_menu_item', 'revision', 'auto-draft', 'wphb_minify_group', 'customize_changeset', 'oembed_cache', 'nf_sub', 'jp_img_sitemap'";
            $post_type_not_in = apply_filters("folders_filter_media_scan_post_types", $post_type_not_in);

            $post_status_not_in = "'inherit', 'trash', 'auto-draft'";
            $post_status_not_in = apply_filters("folders_filter_media_scan_post_status", $post_status_not_in);

            $offset = ($currentPage-1)*$perPage;
            $query = "SELECT p.ID FROM {$wpdb->posts} AS p
                    WHERE p.post_status NOT IN ({$post_status_not_in})
                    AND p.post_type NOT IN ({$post_type_not_in})
                    AND p.post_type NOT LIKE 'dlssus_%'
                    AND p.post_type NOT LIKE 'ml-slide%'
                    AND p.post_type NOT LIKE '%acf-%'
                    AND p.post_type NOT LIKE '%edd_%'
                    ORDER BY p.ID
                    LIMIT %d, %d";
            $query = $wpdb->prepare($query, [$offset, $perPage]);
            $results = $wpdb->get_col($query);
            if(!empty($results)) {
                foreach($results as $result) {
                    $this->check_for_content($result);
                }
            }
        } else if($action == "category_scan") {
            $status = 1;
            $total = (isset($postData['total']) && is_numeric($postData['total']) && $postData['total'] > 0) ? esc_attr($postData['total']) : 0;
            $term_table = $wpdb->termmeta;
            if($currentPage == 1 || empty($total)) {
                $query = "SELECT COUNT(DISTINCT(term_id)) as total_records 
                            FROM {$term_table}
                            WHERE meta_key = 'thumbnail_id'";
                $totalRecords = $wpdb->get_var($query);
                $totalPages = ceil($totalRecords/$perPage);
            }

            $offset = ($currentPage-1)*$perPage;
            $query = "SELECT term_id, meta_value 
                        FROM {$term_table}
                        WHERE meta_key = 'thumbnail_id'
                        LIMIT %d, %d";
            $query = $wpdb->prepare($query, [$offset, $perPage]);
            $results = $wpdb->get_results($query);
            if(!empty($results)) {
                foreach($results as $result) {
                    $this->save_term_meta($result->term_id, $result->meta_value);
                }
            }

        } else if($action == "media_scan") {
            $status = 1;
            $total = (isset($postData['total']) && is_numeric($postData['total']) && $postData['total'] > 0) ? esc_attr($postData['total']) : 0;

            $post_table = $wpdb->posts;
            $log_table = $wpdb->prefix."fldr_media_logs";
            if($currentPage == 1 || empty($total)) {
                $query = "SELECT count(DISTINCT(p.ID)) FROM {$post_table} AS p
                    LEFT JOIN {$log_table} AS L ON L.post_id = p.ID
                    WHERE L.post_id IS NULL AND p.post_type = 'attachment'
                    ";
                $totalRecords = $wpdb->get_var($query);
                $totalPages = ceil($totalRecords/$perPage);
            } else {
                $totalPages = $total;
            }

            $offset = ($currentPage-1)*$perPage;
            $query = "SELECT DISTINCT(p.ID) FROM {$post_table} AS p
                    LEFT JOIN {$log_table} AS L ON L.post_id = p.ID
                    WHERE L.post_id IS NULL AND p.post_type = 'attachment'
                    ORDER BY p.ID
                    LIMIT %d, %d";
            $query = $wpdb->prepare($query, [$offset, $perPage]);
            $results = $wpdb->get_col($query);
            if(!empty($results)) {
                foreach($results as $result) {
                    do_action("fldr_save_scan_media_result", $result, $this->get_file_size($result));
                }
            }
            $response['records'] = $results;
        }
        $response['status'] = $status;
        $response['action'] = $actionNumber;
        $response['total'] = $totalPages;
        $response['current'] = $currentPage;
        echo wp_json_encode($response);
        exit;
    }

    /**
     * Saves term meta for a given term ID and media ID.
     *
     * @param int $termId The ID of the term.
     * @param int $mediaId The ID of the media.
     *
     * @return void
     */
    function save_term_meta($termId, $mediaId) {
        do_action("fldrs_scan_for_term_meta", $termId, $mediaId, $this->get_file_size($mediaId));
    }

    /**
     * Check for content in a specific post.
     *
     * @param int $postId The ID of the post to check for content.
     *
     * @return void
     *
     * @since 2.8.2
     */
    function check_for_content($postId) {
        do_action("fldrs_scan_for_post", $postId);
        do_action("fldrs_scan_for_post_content", $postId);
    }

    /**
     * Resets the media tables.
     *
     * This method triggers the "fldrs_clear_media_scan_tables" action to reset the media scan tables.
     *
     * @since 2.8.2
     */
    function reset_media_tables() {
        do_action("fldrs_clear_media_scan_tables");
    }


    /**
     * Since: 2.8.2
     * Trigger the creation of media scan tables
     *
     * @return void
     */
    function check_for_media_tables()
    {
        do_action("fldrs_create_media_scan_tables");
    }


    /**
     * Scan for unused files.
     *
     * @return void
     */
    public function scan_for_unused_files()
    {
        $postData = filter_input_array(INPUT_POST);

        $currentPage = (isset($postData['page_number']) && is_numeric($postData['page_number']) && $postData['page_number'] > 0) ? esc_attr($postData['page_number']) : 0;
        $currentPage = (intval($currentPage) + 1);

        $recordsPerPage = 40;

        $transientId = isset($postData['transient_id']) ? esc_attr($postData['transient_id']) : "";
        $orderBy     = isset($postData['order_by']) ? esc_attr($postData['order_by']) : "size";
        $order       = isset($postData['order']) ? esc_attr($postData['order']) : "DESC";
        $order       = strtolower($order);
        if ($order != "asc" && $order != "desc") {
            $order = "desc";
        }

        if (empty($transientId)) {
            $transientId       = uniqid();
            $this->transientId = $transientId;
        } else {
            $this->transientId = $transientId;
        }

        $args = [
            'post_type'      => 'attachment',
            'posts_per_page' => $recordsPerPage,
            'post_status'    => 'inherit',
            'fields'         => 'ids',
            'paged'          => $currentPage,
        ];

        if ($orderBy == "size") {
            $args['orderby']  = "meta_value_num";
            $args['meta_key'] = $this->meta_key;
            $args['order']    = $order;
        } else if ($orderBy == "date") {
            $args['orderby'] = "post_date";
            $args['order']   = $order;
        } else if ($orderBy == "title") {
            $args['orderby'] = "post_title";
            $args['order']   = $order;
        } else if ($orderBy == "id") {
            $args['orderby'] = "ID";
            $args['order']   = $order;
        }

        $results    = new WP_Query($args);
        $mediaFiles = [];
        if (!empty($results->posts)) {
            foreach ($results->posts as $mediaId) {
                $mediaStatus = $this->check_in_cache_files($mediaId);
                $mediaStatus = $mediaStatus &&  $this->check_for_featured_image($mediaId);
                $mediaStatus = $mediaStatus && $this->check_for_id_in_post_content($mediaId);
                $mediaStatus = $mediaStatus && $this->check_for_id_in_postmeta_content($mediaId);
                $mediaStatus = $mediaStatus && $this->check_for_url_in_post_content($mediaId);
                $mediaStatus = $mediaStatus && $this->check_for_url_in_postmeta_content($mediaId);
                $mediaStatus = $mediaStatus && $this->check_in_other_field($mediaId);
                if ($mediaStatus) {
                    $mediaData       = [];
                    $mediaData['id'] = $mediaId;
                    $mediaData['thumb_url']   = wp_get_attachment_image_url($mediaId, "thumbnail", true);
                    $attachmentMeta           = wp_get_attachment_metadata($mediaId);
                    $mediaData['total_files'] = (isset($attachmentMeta['sizes']) && !empty($attachmentMeta['sizes'])) ? count($attachmentMeta['sizes']) : 0;
                    $file_size = $this->get_file_size($mediaId);
                    $mediaData['file_size'] = size_format($file_size, 2);
                    $mediaData['edit_url']  = admin_url("post.php?action=edit&post=".$mediaId);
                    $mediaData['title']     = get_the_title($mediaId);
                    $path = get_post_meta($mediaId, "_wp_attached_file", true);
                    if (!empty($mediaData['total_files']) && $mediaData['total_files'] > 1) {
                        $path .= sprintf(esc_html__(" (+%1\$d files)"), $mediaData['total_files']);
                    }

                    $mediaData['path']  = $path;
                    $mediaData['token'] = wp_create_nonce("wp_delete_attachment".$mediaId);
                    $mediaData['date']  = sprintf(esc_html__("%1\$s ago", "folders"), human_time_diff(strtotime(get_the_modified_date('Y-m-d H:i:s', $mediaId))));

                    $mediaFiles[] = $mediaData;
                }//end if
            }//end foreach
        }//end if

        delete_transient($this->transientId."_folder_files");
        set_transient($this->transientId."_folder_files", $this->acfMediaFiles, (3 * HOUR_IN_SECONDS));

        $response           = [];
        $response['status'] = 1;
        $response['files']  = $mediaFiles;
        $response['transient_id'] = $transientId;
        $response['current_page'] = $currentPage;
        echo wp_json_encode($response);
        die;

    }//end scan_for_unused_files()


    /**
     * Since: 2.8.2
     * Checking media id in featured image
     * */
    function check_in_cache_files($mediaId)
    {
        if (!empty($this->acfMediaFiles)) {
            if (in_array($mediaId, $this->acfMediaFiles)) {
                return false;
            }
        } else if (!empty($this->transientId)) {
            $cachedIds = get_transient($this->transientId."_folder_files");
            if (!empty($cachedIds)) {
                $this->acfMediaFiles = $cachedIds;
            }

            if (in_array($mediaId, $this->acfMediaFiles)) {
                return false;
            }
        }

        return true;

    }//end check_in_cache_files()


    /**
     * Since: 2.8.2
     * Checking media id in featured image
     * */
    function get_file_size($mediaId)
    {
        $fileSize = get_post_meta($mediaId, $this->meta_key, true);
        if (!empty($fileSize)) {
            return $fileSize;
        }

        $file_size = 0;
        $meta_data = wp_get_attachment_metadata($mediaId);
        if (isset($meta_data['filesize'])) {
            $file_size = $meta_data['filesize'];
        }

        if(!empty($file_size)) {
            update_post_meta($mediaId, 'folders_media_size', $file_size);
        }

        if (isset($meta_data['sizes'])) {
            foreach ($meta_data['sizes'] as $size) {
                if (isset($size['filesize'])) {
                    $file_size += intval($size['filesize']);
                }
            }
        }

        if (empty($file_size)) {
            $is_file_exist = file_exists(get_attached_file($mediaId));
            $file_size     = 0;
            if ($is_file_exist) {
                $file_size = filesize(get_attached_file($mediaId));
            }

            update_post_meta($mediaId, 'folders_media_size', $file_size);
        }

        update_post_meta($mediaId, "all_".$this->meta_key, $file_size);
        return $file_size;

    }//end get_file_size()


    /**
     * Since: 2.8.2
     * Checking media id in featured image
     * */
    public function check_for_featured_image($mediaId)
    {
        global $wpdb;
        $tbl_postmeta = $wpdb->prefix."postmeta";

        $query        = "SELECT meta_id FROM {$tbl_postmeta} WHERE meta_key = '_thumbnail_id' AND meta_value = '%d' LIMIT 1";
        $query        = $wpdb->prepare($query, $mediaId);
        $attachmentId = $wpdb->get_col($query);
        if (!empty($attachmentId)) {
            return false;
        }

        return true;

    }//end check_for_featured_image()


    /**
     * Since: 2.8.2
     * Checking media id in post content
     * */
    public function check_for_id_in_post_content($mediaId)
    {

        global $wpdb;
        $tbl_post = $wpdb->prefix."posts";

        $query     = "SELECT ID, post_content FROM {$tbl_post} WHERE post_content LIKE '%wp-image-".esc_sql($mediaId)."%'";
        $records   = $wpdb->get_results($query);
        $imageData = [];
        if (!empty($records)) {
            foreach ($records as $record) {
                preg_match_all('/wp-image-(\d*)/', $record->post_content, $images);
                if (!empty($images)) {
                    if (isset($images[1][0]) && !in_array($images[1][0], $imageData)) {
                        $imageData[] = $images[1][0];
                    }
                }
            }
        }

        if (!empty($imageData) && in_array($mediaId, $imageData)) {
            return false;
        }

        return true;

    }//end check_for_id_in_post_content()


    /**
     * Since: 2.8.2
     * Checking media id in post content
     * */
    public function check_for_id_in_postmeta_content($mediaId)
    {

        global $wpdb;
        $tbl_postmeta = $wpdb->prefix."postmeta";

        $query     = "SELECT meta_id, meta_value FROM {$tbl_postmeta} WHERE meta_value LIKE '%wp-image-".esc_sql($mediaId)."%'";
        $records   = $wpdb->get_results($query);
        $imageData = [];
        if (!empty($records)) {
            foreach ($records as $record) {
                preg_match_all('/wp-image-(\d*)/', $record->meta_value, $images);
                if (!empty($images)) {
                    if (isset($images[1][0]) && !in_array($images[1][0], $imageData)) {
                        $imageData[] = $images[1][0];
                    }
                }
            }
        }

        if (!empty($imageData) && in_array($mediaId, $imageData)) {
            return false;
        }

        return true;

    }//end check_for_id_in_postmeta_content()


    /**
     * Since: 2.8.2
     * Checking media url in post content
     * */
    public function check_for_url_in_post_content($mediaId)
    {
        $attachmentMeta = wp_get_attachment_metadata($mediaId);
        $imageURL       = wp_get_attachment_url($mediaId);
        if ($this->check_for_string_in_post_content($imageURL)) {
            return false;
        }

        if (isset($attachmentMeta['sizes']) && !empty($attachmentMeta['sizes'])) {
            foreach ($attachmentMeta['sizes'] as $key => $file) {
                $imageURL = wp_get_attachment_image_url($mediaId, $key);
                if ($this->check_for_string_in_post_content($imageURL)) {
                    return false;
                }
            }
        }

        return true;

    }//end check_for_url_in_post_content()


    /**
     * Since: 2.8.2
     * Checking media url in post content
     * */
    public function check_for_url_in_postmeta_content($mediaId)
    {
        $attachmentMeta = wp_get_attachment_metadata($mediaId);
        $imageURL       = wp_get_attachment_url($mediaId);
        if ($this->check_for_string_in_postmeta_content($imageURL)) {
            return false;
        }

        if (isset($attachmentMeta['sizes']) && !empty($attachmentMeta['sizes'])) {
            foreach ($attachmentMeta['sizes'] as $key => $file) {
                $imageURL = wp_get_attachment_image_url($mediaId, $key);
                if ($this->check_for_string_in_postmeta_content($imageURL)) {
                    return false;
                }
            }
        }

        return true;

    }//end check_for_url_in_postmeta_content()


    /*
     * Since: 2.8.2
     * Checking media url in post content
     * */
    public function check_for_string_in_post_content($imageURL)
    {
        global $wpdb;
        $tbl_post = $wpdb->prefix."posts";

        $query        = "SELECT ID as total_record FROM {$tbl_post} WHERE post_content LIKE '%".esc_attr($imageURL)."%' LIMIT 1";
        $attachmentId = $wpdb->get_var($query);
        if (!empty($attachmentId)) {
            return true;
        }

        return false;

    }//end check_for_string_in_post_content()


    /*
     * Since: 2.8.2
     * Checking media url in post content
     * */
    public function check_for_string_in_postmeta_content($imageURL)
    {
        global $wpdb;

        $tbl_postmeta = $wpdb->prefix."postmeta";
        $query        = "SELECT meta_id as total_record FROM {$tbl_postmeta} WHERE meta_value LIKE '%".esc_attr($imageURL)."%'  LIMIT 1";
        $attachmentId = $wpdb->get_var($query);
        if (!empty($attachmentId)) {
            return true;
        }

        return false;

    }//end check_for_string_in_postmeta_content()


    /**
     * Since: 2.8.2
     * Checking media id in product image gallery for woocommerce
     * */
    public function check_in_product_image_gallery($mediaId)
    {
        global $wpdb;
        $tbl_postmeta = $wpdb->prefix."postmeta";

        $query        = "SELECT meta_id FROM {$tbl_postmeta} WHERE meta_key = '_product_image_gallery' AND FIND_IN_SET(%d, meta_value)";
        $query        = $wpdb->prepare($query, $mediaId);
        $attachmentId = $wpdb->get_col($query);
        if (!empty($attachmentId)) {
            return false;
        }

        return true;

    }//end check_in_product_image_gallery()


    /**
     * Since: 2.8.2
     * Checking media id in ACF field
     * */
    public function check_in_other_field($mediaId)
    {
        if (!$this->isMediaScanned) {
            $this->isMediaScanned = 1;

            global $wpdb;

            $q       = "SELECT p.ID FROM $wpdb->posts p
                WHERE p.post_status NOT IN ('inherit', 'trash', 'auto-draft')
                AND p.post_type NOT IN ('attachment', 'shop_order', 'shop_order_refund', 'nav_menu_item', 'revision', 'auto-draft', 'wphb_minify_group', 'customize_changeset', 'oembed_cache', 'nf_sub')
                AND p.post_type NOT LIKE 'dlssus_%'
                AND p.post_type NOT LIKE 'ml-slide%'
                AND p.post_type NOT LIKE '%acf-%'
                AND p.post_type NOT LIKE '%edd_%'";
            $postIds = $wpdb->get_col($q);

            if (!empty($postIds)) {
                foreach ($postIds as $postId) {
                    do_action('folders_scan_postmeta', $postId);
                }
            }
        }

        if (!empty($this->acfMediaFiles)) {
            if (in_array($mediaId, $this->acfMediaFiles)) {
                return false;
            }
        }

        return true;

    }//end check_in_other_field()

    /**
     * Since: 2.8.2
     * Checking media id in ACF Plugins
     * */
    public function folders_scan_for_acf($postId)
    {
        if (!function_exists("get_field_objects")) {
            return "";
        }

        $fields = get_field_objects($postId);
        if (is_array($fields)) {
            foreach ($fields as $field) {
                $this->scan_postmeta_acf_field($field, $postId, 8);
            }
        }

    }//end folders_scan_for_acf()

    /**
     * Since: 2.8.2
     * Checking media id in ACF meta field
     * */
    public function scan_postmeta_acf_field($field, $id, $recursion_limit=-1)
    {
        if (!isset($field['type'])) {
            return;
        }

        $recursives = [
            'repeater',
            'flexible_content',
            'group',
        ];

        $is_recursive = in_array($field['type'], $recursives);

        $is_recursive_with_data = $is_recursive && have_rows($field['name'], $id);

        if ($is_recursive_with_data) {
            if ($recursion_limit == 0) {
                return;
                // Too much recursion
            }

            do {
                $row = the_row(true);
                foreach ($row as $col => $value) {
                    // Iterate over columns (subfields)
                    $subfield = get_sub_field_object($col, true, true);
                    if (!is_array($subfield)) {
                        continue;
                    }

                    $this->scan_postmeta_acf_field($subfield, $id, ($recursion_limit - 1));
                    // Recursion
                }
            } while (have_rows($field['name'], $id));
            return;
        }//end if

        // checking for image field
        if (isset($field['type']) && $field['type'] == "image") {
            if (isset($field['value']['ID'])) {
                $imageId = $field['value']['ID'];
                if (!in_array($imageId, $this->acfMediaFiles)) {
                    $this->acfMediaFiles[] = $imageId;
                }
            }
        } else if (isset($field['type']) && $field['type'] == "gallery") {
            if (isset($field['value']) && !empty($field['value'])) {
                foreach ($field['value'] as $imageData) {
                    if (isset($imageData['ID']) && !in_array($imageData['ID'], $this->acfMediaFiles)) {
                        $this->acfMediaFiles[] = $imageData['ID'];
                    }
                }
            }
        }

    }//end scan_postmeta_acf_field()


}//end class


/**
 * Class Folders_media_cleaner
 *
 * This class represents a media cleaner that is responsible for cleaning up unused media files in a folder.
 * It provides methods to scan a folder, identify unused media files, and delete them from the disk.
 */
if (class_exists("Folders_Media_Cleaner")) {
    require_once WCP_PRO_FOLDER_DIR."includes/media.clean.db.php";
    require_once WCP_PRO_FOLDER_DIR."includes/media.clean.actions.php";
    $Folders_media_cleaner = new Folders_Media_Cleaner();
}

