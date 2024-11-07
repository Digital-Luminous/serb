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

class Folders_Media_Cleaner_Actions
{

    var $fldb;

    /**
     * Class constructor.
     *
     * This method initializes the object of the class and sets up the necessary actions for media scanning.
     * It creates an instance of the `Folders_Media_Cleaner_DB` class and assigns it to the `fldb` property.
     * It also hooks several methods to specific actions for media scanning purposes.
     *
     * @return void
     */
    public function __construct() {
        $this->fldb = new Folders_Media_Cleaner_DB();
        add_action("fldrs_scan_for_post", [$this, "scan_for_post"], 10, 1);
        add_action("fldrs_scan_for_post_content", [$this, "scan_for_post_content"], 10, 1);

        /* Create/Clear DB Tables */
        add_action("fldrs_create_media_scan_tables", [$this, "create_media_scan_tables"]);
        add_action("fldrs_clear_media_scan_tables", [$this, "clear_media_scan_tables"]);

        /* Save scanned media results */
        add_action("fldr_save_scan_media_result", [$this, "save_scan_media_result"], 10, 2);

        /* Fetching WC Category image and save it*/
        add_action("fldrs_scan_for_term_meta", [$this, "scan_for_term_meta"], 10, 3);
    }

    /**
     * Scans for term meta and saves media ID log.
     *
     * This method saves the media ID log by calling the `save_media_id_log` method of the `fldb` property. The media ID log includes the term ID, image ID, and file size.
     *
     * @param int $termId The ID of the term.
     * @param int $image_id The ID of the image.
     * @param int $fileSize The size of the file.
     * @return void
     */
    function scan_for_term_meta($termId, $image_id, $fileSize) {
        $this->fldb->save_media_id_log($image_id, "Term Meta: #".$termId, $fileSize);
    }

    /**
     * Saves the scan media result.
     *
     * This method calls the `save_scan_log` method of the `fldb` property to save the scan log for the provided post ID and file size.
     *
     * @param int $post_id The ID of the post.
     * @param int $file_size The size of the scanned media file.
     *
     * @return void
     */
    function save_scan_media_result($post_id, $file_size) {
        $this->fldb->save_scan_log($post_id, $file_size);
    }

    /**
     * Creates media scan tables.
     *
     * This method calls the `create_tables` method of the `fldb` property to create the necessary tables for media scanning.
     *
     * @return void
     */
    function create_media_scan_tables() {
        $this->fldb->create_tables();
    }

    /**
     * Clears the media scan tables.
     *
     * This method clears the media scan tables in the database.
     * It invokes the clear_tables() method of the associated database object.
     *
     * @return void
     */
    function clear_media_scan_tables() {
        $this->fldb->clear_tables();
    }

    /**
     * Scans the given post for relevant media IDs and URLs.
     *
     * @param int $postId The ID of the post to scan.
     * @return void
     */
    function scan_for_post($postId)
    {
        $image_id = get_post_meta($postId, "_thumbnail_id", true);
        if(!empty($image_id) && is_numeric($image_id)) {
            $this->fldb->save_media_id_log($image_id, "Post Thumb: #".$postId);
        }
        $image_ids = get_post_meta($postId, "_product_image_gallery", true);
        if(!empty($image_ids)) {
            $image_ids = explode(",", $image_ids);
            if(!empty($image_ids) && is_array($image_ids)) {
                foreach($image_ids as $image_id) {
                    $this->fldb->save_media_id_log($image_id, "WC Gallery: #".$postId);
                }
            }
        }
        $edd_files = get_post_meta($postId, "edd_download_files", true);
        if(!empty($edd_files)) {
            foreach($edd_files as $edd_file) {
                $this->fldb->save_media_url_log($edd_file['file'], "EDD File: #".$postId);
           }
        }
        $files = get_post_meta($postId, "_downloadable_files", true);
        if(!empty($files)) {
            foreach($files as $file) {
                $this->fldb->save_media_url_log($file['file'], "WC File: #".$postId);
           }
        }
    }

    /**
     * Scans the post content and meta for URLs and image IDs.
     *
     * @param int $postId The ID of the post to scan.
     *
     * @return void
     */
    function scan_for_post_content($postId)
    {
        $post_content = get_post_field("post_content", $postId);
        $urls = $this->fetch_urls_from_content($post_content);
        $ids = $this->fetch_ids_from_content($post_content);
        $this->fldb->save_media_id_log($ids, "Post Content: #".$postId);
        $this->fldb->save_media_url_log($urls, "Post Content: #".$postId);

        global $wpdb;
        $postmeta_table = $wpdb->postmeta;
        $query = "SELECT meta_value FROM {$postmeta_table}
                WHERE post_id = '".esc_sql($postId)."' AND meta_value LIKE '%wp-image-%'";
        $records = $wpdb->get_col($query);
        if(!empty($records)) {
            foreach ($records as $record) {
                $urls = $this->fetch_urls_from_content($record);
                $ids = $this->fetch_ids_from_content($record);
                $this->fldb->save_media_id_log($ids, "Post Meta: #" . $postId);
                $this->fldb->save_media_url_log($urls, "Post Meta: #" . $postId);
            }
        }
    }

    /**
     * Fetches the URLs from the given content.
     *
     * @param string $content The content to extract URLs from.
     *
     * @return array An array of URLs found in the content.
     */
    function fetch_urls_from_content($content) {
        $urls = [];
        if(empty($content)) {
            return $urls;
        }
        $dom = new DOMDocument();
        @$dom->loadHTML($content);
        $links = $dom->getElementsByTagName('a');
        foreach ($links as $link){
           $url = $link->getAttribute('href');
           if($this->is_valid_url($url)) {
               $urls[] = $url;
           }
        }
        $links = $dom->getElementsByTagName('img');
        foreach ($links as $link){
            //Extract and show the "scr" attribute.
            $url = $link->getAttribute('scr');
            if($this->is_valid_url($url)) {
                $urls[] = $url;
            }
        }

        /* Fetching all URLs from Site */
        $links = $this->findLinksFromContent($content);
        foreach ($links as $url){
            $urls[] = $url;
        }
        return $urls;
    }

    /**
     * Finds all links from the given content string that are valid and contain "wp-content/uploads/" in the URL.
     *
     * @param string $string The content string to search links from.
     *
     * @return array An array of valid URLs containing "wp-content/uploads/".
     */
    function findLinksFromContent($string) {
        $urls = [];
        preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $string, $match);
        if(isset($match[0]) && !empty($match[0])) {
            foreach($match[0] as $url) {
                if($this->is_valid_url($url)) {
                    if(strpos($url, "wp-content/uploads/") !== false) {
                        $urls[] = $url;
                    }
                }
            }
        }
        return $urls;
    }

    /**
     * Checks if a given URL is valid.
     *
     * @param string $url The URL to be checked.
     *
     * @return bool True if the URL is valid, false otherwise.
     */
    function is_valid_url($url) {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return true;
        }
        return false;
    }


    /**
     * Fetches the image IDs from the given content.
     *
     * @param string $content The content to extract image IDs from.
     *
     * @return array An array of image IDs found in the content.
     */
    function fetch_ids_from_content($content) {
        $imageData = [];
        preg_match_all('/wp-image-(\d*)/', $content, $images);
        if (!empty($images)) {
            if(isset($images[1]) && is_array($images[1])) {
                foreach ($images[1] as $image) {
                    if (is_numeric($image) && !in_array($image, $imageData)) {
                        $imageData[] = $image;
                    }
                }
            }
        }

        /* For the DIVI Theme */
        $pattern = '/gallery_ids="([^"]*)"/i';
        preg_match($pattern, $content, $matches);
        if (!empty($matches)) {
            if (isset($matches[1])) {
                $galleryIds = $matches[1];
                if(!empty($galleryIds)) {
                    $galleryIds = explode(",", $galleryIds);
                    foreach($galleryIds as $imageId) {
                        if (is_numeric($imageId) && !in_array($imageId, $imageData)) {
                            $imageData[] = $imageId;
                        }
                    }
                }
            }
        }
        return $imageData;
    }
}
$Folders_Media_Cleaner_Actions = new Folders_Media_Cleaner_Actions();
