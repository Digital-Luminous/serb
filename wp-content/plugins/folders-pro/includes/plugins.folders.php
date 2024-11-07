<?php
/**
 * Class Folders Plugins import/export
 *
 * @author  : Premio <contact@premio.io>
 * @license : GPL2
 * */

if (! defined('ABSPATH')) {
    exit;
}

// Free/Pro Class name change
class WCP_Folders_for_Plugins extends WCP_Pro_Folders
{

    public static $isEnabled = false;
    public $type = "folders4plugins";
    public $folder_table;
    public $folder_item_table;

    public function __construct()
    {
        global $wpdb;
        $this->folder_table = $wpdb->prefix."prm_custom_folders";
        $this->folder_item_table = $wpdb->prefix."prm_custom_folder_items";
        $folders_settings = get_option("folders_settings");
        $folders_settings = !is_array($folders_settings)?[]:$folders_settings;
        if(in_array("folders4plugins", $folders_settings)) {
            self::$isEnabled = true;
        }

        $isAJAX = defined('DOING_AJAX') && DOING_AJAX;
        if(!$isAJAX) {
            add_action('admin_footer', [$this, 'admin_footer']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_script']);
            add_action('admin_init', [$this, 'admin_init']);
        }

        // Save Data
        add_action('wp_ajax_prm_add_new_folder', [$this, 'add_new_folder']);
        add_action('wp_ajax_prm_copy_premio_folders', [$this, 'copy_folders']);
        add_action('wp_ajax_prm_remove_multiple_folder', [$this, 'remove_multiple_folder']);
        add_action('wp_ajax_prm_lock_unlock_all_folders', [$this, 'lock_unlock_all_folders']);

        // Folders Operations
        add_action("wp_ajax_prm_change_multiple_post_folder", [$this, 'change_multiple_post_folder']);
        add_action("wp_ajax_prm_remove_post_folder", [$this, 'remove_post_folder']);
        add_action("wp_ajax_prm_check_for_other_folders", [$this, 'check_for_other_folders']);
        add_action("wp_ajax_prm_get_folders_default_list", [$this, 'get_folders_default_list']);
        add_action("wp_ajax_prm_save_folder_last_status", [$this, 'save_folder_last_status']);
        add_action("wp_ajax_save_prm_folder_state", [$this, 'save_folder_state']);
        add_action("wp_ajax_prm_save_folder_order", [$this, 'save_folder_order']);
        add_action("wp_ajax_prm_update_folder", [$this, 'update_folder']);
        add_action("wp_ajax_prm_make_sticky_folder", [$this, 'make_sticky_folder']);
        add_action("wp_ajax_prm_mark_un_mark_folder", [$this, 'mark_un_mark_folder']);
        add_action("wp_ajax_prm_lock_unlock_folder", [$this, 'lock_unlock_folder']);
        add_action("wp_ajax_prm_remove_folder", [$this, 'remove_folder']);
        add_action("wp_ajax_prm_change_all_status", [$this, 'change_all_status']);
        add_action("wp_ajax_prm_folders_by_order", [$this, 'folders_by_order']);
        add_action("wp_ajax_prm_dynamic_folder_state", [$this, 'dynamic_folder_state']);
        add_action("wp_ajax_prm_change_color_folder", [$this, 'change_folder_color']);


        add_action("all_plugins", [$this, 'filter_plugins'], 10, 1);
    }

    public function admin_init() {
        global $current_screen;
        $requestURL = sanitize_text_field($_SERVER['REQUEST_URI']);
        if(strpos($requestURL, "/wp-admin/plugins.php") !== false) {
            $folders_settings = get_option('folders_settings');
            if(in_array("folders4plugins", $folders_settings)) {
                $default_folders = get_option('default_folders');
                if(isset($default_folders["folders4plugins"])) {
                    if(!isset($_GET['folders4plugins_folder']) && !isset($_GET['plugin_status'])) {
                        $admin_url = admin_url("plugins.php?folders4plugins_folder=".esc_attr($default_folders["folders4plugins"]));
                        wp_redirect($admin_url);
                        exit;
                    }
                }
            }
        }
    }

    public function dynamic_folder_state() {
        $response            = [];
        $response['status']  = 0;
        $response['error']   = 0;
        $response['data']    = [];
        $response['message'] = "";
        $postData            = filter_input_array(INPUT_POST);
        $errorCounter        = 0;
        if (!isset($postData['term_id']) || empty($postData['term_id'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['nonce']) || empty($postData['nonce'])) {
            $response['message'] = esc_html__("Unable to create folder, Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['post_type']) || empty($postData['post_type'])) {
            $response['message'] = esc_html__("Unable to create folder, Your request is not valid", 'folders');
            $errorCounter++;
        } else {
            if (!wp_verify_nonce($postData['nonce'], 'wcp_folder_nonce_'.esc_attr($postData['post_type']))) {
                $response['message'] = esc_html__("Your request is not valid", 'folders');
                $errorCounter++;
            }
        }

        if ($errorCounter == 0) {
            $response['status'] = 1;
            $term_id            = self::sanitize_options($postData['term_id']);
            $post_type          = self::sanitize_options($postData['post_type']);
            $is_active          = isset($postData['is_active']) ? $postData['is_active'] : 0;
            $is_active          = self::sanitize_options($is_active);
            $is_active          = ($is_active == 1) ? 1 : 0;
            $option = get_option("folders_".$post_type.$term_id);
            if ($option === false) {
                add_option("folders_".$post_type.$term_id, $is_active);
            } else {
                update_option("folders_".$post_type.$term_id, $is_active);
            }
        }

        echo wp_json_encode($response);
        wp_die();
    }

    public function change_all_status()
    {
        $response            = [];
        $response['status']  = 0;
        $response['error']   = 0;
        $response['data']    = [];
        $response['message'] = "";
        $postData            = filter_input_array(INPUT_POST);
        $errorCounter        = 0;
        if (!isset($postData['type']) || empty($postData['type'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['nonce']) || empty($postData['nonce'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else {
            $type  = self::sanitize_options($postData['type']);
            $nonce = self::sanitize_options($postData['nonce']);
            if (!wp_verify_nonce($nonce, 'wcp_folder_nonce_'.$type)) {
                $response['message'] = esc_html__("Your request is not valid", 'folders');
                $errorCounter++;
            }
        }

        if ($errorCounter == 0) {
            if (isset($postData['folders']) || !empty($postData['folders'])) {
                $status  = isset($postData['status']) ? $postData['status'] : 0;
                $status  = self::sanitize_options($status);
                $folders = self::sanitize_options($postData['folders']);
                $folders = trim($folders, ",");
                $folders = explode(",", $folders);
                foreach ($folders as $folder) {
                    global $wpdb;
                    $wpdb->update($this->folder_table, ["is_active" => esc_sql($status)], ["id" => esc_sql($folder)]);
                }
            }

            $response['status'] = 1;
        }

        echo wp_json_encode($response);
        wp_die();

    }//end wcp_change_all_status()

    function lock_unlock_all_folders() {
        $response            = [];
        $response['status']  = 0;
        $response['error']   = 0;
        $response['data']    = [];
        $response['message'] = "";
        $postData            = filter_input_array(INPUT_POST);
        $errorCounter        = 0;
        if (!isset($postData['post_type']) || empty($postData['post_type'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['nonce']) || empty($postData['nonce'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else {
            $nonce     = self::sanitize_options($postData['nonce']);
            $post_type = self::sanitize_options($postData['post_type']);
            if (!wp_verify_nonce($nonce, 'wcp_folder_nonce_'.$post_type)) {
                $response['message'] = esc_html__("Your request is not valid", 'folders');
                $errorCounter++;
            }
        }

        if ($errorCounter == 0) {
            $lock_folder = (isset($postData['lock_folders']) && $postData['lock_folders'] == 1) ? 1 : 0;
            $foldersData = (isset($postData['folders']) && is_array($postData['folders'])) ? $postData['folders'] : [];
            $post_type   = self::sanitize_options($postData['post_type']);
            $taxonomy    = self::get_custom_post_type($post_type);
            global $wpdb;
            $foldersInfo = [];
            if(empty($foldersData)) {
                global $wpdb;
                $condition = ($lock_folder)?0:1;
                $wpdb->update($this->folder_table, ["is_locked" => $lock_folder], ["is_locked" => $condition]);
            } else {
                foreach($foldersData as $folder) {
                    $lock_folder = 1;
                    $wpdb->update($this->folder_table, ["is_locked" => $lock_folder], ["id" => esc_sql($folder)]);
                }
            }

            $data = [];
            $data['is_locked']  = $lock_folder;
            $data['folders']    = $foldersInfo;
            $response['data']   = $data;
            $response['status'] = 1;
        }//end if

        echo wp_json_encode($response);
        die;
    }

    function remove_multiple_folder()
    {
        $response            = [];
        $response['status']  = 0;
        $response['error']   = 0;
        $response['data']    = [];
        $response['message'] = "";
        $postData            = filter_input_array(INPUT_POST);
        $errorCounter        = 0;
        $error = "";
        if (!isset($postData['term_id']) || empty($postData['term_id'])) {
            $error = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['type']) || empty($postData['type'])) {
            $error = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['nonce']) || empty($postData['nonce'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else {
            $type = self::sanitize_options($postData['type']);
            if (!wp_verify_nonce($postData['nonce'], 'wcp_folder_nonce_'.$type)) {
                $error = esc_html__("Your request is not valid", 'folders');
                $errorCounter++;
            }
        }

        if ($errorCounter == 0) {
            $term_ids = $postData['term_id'];
            $response['term_ids'] = [];
            if (!empty($term_ids)) {
                if (is_array($term_ids) && count($term_ids) > 0) {
                    global $wpdb;

                    foreach ($term_ids as $term_id) {
                        $term_id = esc_sql($term_id);
                        $this->remove_children_data($term_id);

                        $wpdb->delete($this->folder_table, ["id" => $term_id]);

                        $wpdb->delete($this->folder_item_table, ['folder_id' => $term_id]);

                        $response['term_ids'][] = $term_id;
                    }
                }
            }

            $is_active          = 1;
            $folders            = -1;
            $response['status'] = 1;

            $response['folders']       = $folders;
            $response['is_key_active'] = $is_active;
        } else {
            $response['error']   = 1;
            $response['message'] = $error;
        }//end if

        echo wp_json_encode($response);
        wp_die();
    }

    function copy_folders() {
        $response           = [];
        $response['status'] = 0;
        $response['error']  = 0;
        $response['data']   = [];
        $response['parent_id'] = 0;
        $response['message']   = "";
        $postData     = filter_input_array(INPUT_POST);
        $errorCounter = 0;

        if (!isset($postData['post_type']) || empty($postData['post_type'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['copy_from']) || empty($postData['copy_from'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['copy_to'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['nonce']) || empty($postData['nonce'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else {
            $nonce     = self::sanitize_options($postData['nonce']);
            $post_type = self::sanitize_options($postData['post_type']);
            if (!wp_verify_nonce($nonce, 'wcp_folder_nonce_'.$post_type)) {
                $response['message'] = esc_html__("Your request is not valid", 'folders');
                $errorCounter++;
            }
        }

        $folders = [];

        if ($errorCounter == 0) {
            $copy_from = sanitize_text_field($postData['copy_from']);
            $copy_to   = sanitize_text_field($postData['copy_to']);

            global $wpdb;

            if($copy_from == "custom" ) {
                if(isset($postData['folders']) && is_array($postData['folders']) && count($postData['folders'])) {
                    $folderItems = $postData['folders'];
                    foreach($folderItems as $folder) {
                        $copy_from = $folder;

                        $query = "SELECT * FROM {$this->folder_table} WHERE id = %d";
                        $query = $wpdb->prepare($query, [esc_sql($copy_from)]);
                        $folderData = $wpdb->get_row($query, ARRAY_A);

                        if (!empty($folderData)) {
                            $user_id = get_current_user_id();
                            $slug = self::create_slug_from_string($folderData['title']) . "-" . time() . "-" . $user_id;
                            $folder = $folderData;
                            $folder['parent_id'] = esc_sql($copy_to);
                            $folder['title_slug'] = $slug;
                            $folder['folder_order'] = 0;
                            $folder['created_on'] = gmdate("Y-m-d H:i:s");
                            $folder['created_by'] = $user_id;
                            unset($folder['id']);
                            unset($folder['updated_by']);
                            unset($folder['updated_on']);

                            $status = $wpdb->insert($this->folder_table, $folder);
                            if ($status) {
                                $folder_id = $wpdb->insert_id;

                                $query = "SELECT * FROM {$this->folder_item_table} WHERE folder_id = %d";
                                $query = $wpdb->prepare($query, [esc_sql($copy_from)]);
                                $results = $wpdb->get_results($query);
                                if (!empty($results)) {
                                    foreach ($results as $result) {
                                        $arg = [
                                            'folder_id' => $folder_id,
                                            'folder_item' => esc_sql($result->folder_item),
                                            'custom_type' => esc_sql($result->custom_type),
                                        ];
                                        $wpdb->insert($this->folder_item_table, $arg);
                                    }
                                }

                                $response['status'] = 1;
                                $response['folder_id'] = $folder_id;

                                $folder = [
                                    'is_high' => $folder['is_high'],
                                    'is_locked' => $folder['is_locked'],
                                    'is_active' => $folder['is_active'],
                                    'is_sticky' => $folder['is_sticky'],
                                    'nonce' => wp_create_nonce("wcp_folder_term_" . $folder_id),
                                    'folder_count' => 0,
                                    'parent_id' => esc_sql($copy_to),
                                    'slug' => $slug,
                                    'term_id' => $folder_id,
                                    'title' => esc_sql($folder['title'])
                                ];
                                $folders[] = $folder;

                                $response['data'] = $folders;
                                $response['parent_id'] = empty($parent) ? "#" : $parent;
                            }
                        }
                    }
                }
            } else {
                $query = "SELECT * FROM {$this->folder_table} WHERE id = %d";
                $query = $wpdb->prepare($query, [esc_sql($copy_from)]);
                $folderData = $wpdb->get_row($query, ARRAY_A);

                if (!empty($folderData)) {
                    $user_id = get_current_user_id();
                    $slug = self::create_slug_from_string($folderData['title']) . "-" . time() . "-" . $user_id;
                    $folder = $folderData;
                    $folder['parent_id'] = esc_sql($copy_to);
                    $folder['title_slug'] = $slug;
                    $folder['folder_order'] = 0;
                    $folder['created_on'] = gmdate("Y-m-d H:i:s");
                    $folder['created_by'] = $user_id;
                    unset($folder['id']);
                    unset($folder['updated_by']);
                    unset($folder['updated_on']);

                    $status = $wpdb->insert($this->folder_table, $folder);
                    if ($status) {
                        $folder_id = $wpdb->insert_id;

                        $query = "SELECT * FROM {$this->folder_item_table} WHERE folder_id = %d";
                        $query = $wpdb->prepare($query, [esc_sql($copy_from)]);
                        $results = $wpdb->get_results($query);
                        if (!empty($results)) {
                            foreach ($results as $result) {
                                $arg = [
                                    'folder_id' => $folder_id,
                                    'folder_item' => esc_sql($result->folder_item),
                                    'custom_type' => esc_sql($result->custom_type),
                                ];
                                $wpdb->insert($this->folder_item_table, $arg);
                            }
                        }

                        $response['status'] = 1;
                        $response['folder_id'] = $folder_id;

                        $folder = [
                            'is_high' => $folder['is_high'],
                            'is_locked' => $folder['is_locked'],
                            'is_active' => $folder['is_active'],
                            'is_sticky' => $folder['is_sticky'],
                            'nonce' => wp_create_nonce("wcp_folder_term_" . $folder_id),
                            'folder_count' => 0,
                            'parent_id' => esc_sql($copy_to),
                            'slug' => $slug,
                            'term_id' => $folder_id,
                            'title' => esc_sql($folder['title'])
                        ];
                        $folders[] = $folder;

                        $response['data'] = $folders;
                        $response['parent_id'] = empty($parent) ? "#" : $parent;
                    }
                }
            }
        }//end if

        echo wp_json_encode($response);
        die;
    }

    /**
     * Remove folder
     *
     * @since  1.0.0
     * @access public
     */
    public function remove_folder()
    {
        $response            = [];
        $response['status']  = 0;
        $response['error']   = 0;
        $response['data']    = [];
        $response['message'] = "";
        $postData            = filter_input_array(INPUT_POST);
        $errorCounter        = 0;
        if (!isset($postData['term_id']) || empty($postData['term_id'])) {
            $error = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['type']) || empty($postData['type'])) {
            $error = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['nonce']) || empty($postData['nonce'])) {
            $error = esc_html__("Unable to delete folder, Your request is not valid", 'folders');
            $errorCounter++;
        } else {
            $term_id = self::sanitize_options($postData['term_id']);
            if (!wp_verify_nonce($postData['nonce'], 'wcp_folder_term_'.$term_id)) {
                $error = esc_html__("Unable to delete folder, Your request is not valid", 'folders');
                $errorCounter++;
            }
        }

        if ($errorCounter == 0) {
            $term_id = self::sanitize_options($postData['term_id']);
            $this->remove_children_data($term_id);
            $response['status'] = 1;
            $is_active          = 1;
            $folders            = -1;

            global $wpdb;
            $table = $wpdb->prefix."prm_custom_folders";

            $wpdb->delete($table, ["id" => $term_id]);

            $table = $wpdb->prefix."prm_custom_folder_items";
            $wpdb->delete($table, ['folder_id' => $term_id]);

            $response['folders']       = $folders;
            $response['term_id']       = $term_id;
            $response['is_key_active'] = $is_active;
        } else {
            $response['error']   = 1;
            $response['message'] = $error;
        }

        echo wp_json_encode($response);
        wp_die();

    }//end wcp_remove_folder()

    function remove_children_data($parent_id) {
        global $wpdb;
        $table = $wpdb->prefix."prm_custom_folders";

        $query = "SELECT id FROM {$table} WHERE parent_id = %d";
        $query = $wpdb->prepare($query, [$parent_id]);
        $result = $wpdb->get_col($query);
        if(!empty($result)) {
            foreach($result as $row) {
                $this->remove_children_data($row);

                $wpdb->delete($table, ["id" => $row]);

                $table = $wpdb->prefix."prm_custom_folder_items";
                $wpdb->delete($table, ['folder_id' => $row]);
            }
        }
    }

    function lock_unlock_folder() {
        $response            = [];
        $response['status']  = 0;
        $response['error']   = 0;
        $response['data']    = [];
        $response['message'] = "";
        $postData            = filter_input_array(INPUT_POST);
        $errorCounter        = 0;
        if (!isset($postData['term_id']) || empty($postData['term_id'])) {
            $errorCounter++;
            $response['message'] = esc_html__("Your request is not valid", 'folders');
        } else if (!isset($postData['nonce']) || empty($postData['nonce'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else {
            $term_id = self::sanitize_options($postData['term_id']);
            if (!wp_verify_nonce($postData['nonce'], 'wcp_folder_term_'.$term_id)) {
                $response['message'] = esc_html__("Your request is not valid", 'folders');
                $errorCounter++;
            }
        }

        if ($errorCounter == 0) {
            $term_id = self::sanitize_options($postData['term_id']);

            global $wpdb;
            $table = $wpdb->prefix."prm_custom_folders";

            $query = "SELECT is_locked FROM {$table} WHERE id = %d";
            $query = $wpdb->prepare($query, [$term_id]);
            $status = $wpdb->get_var($query);
            $status = ($status) ? 0 : 1;

            $wpdb->update(
                $table,
                ["is_locked" => $status],
                ["id" => esc_sql($term_id)]
            );

            $response['marked'] = $status;
            $response['id']     = $term_id;
            $response['status'] = 1;
        }

        echo wp_json_encode($response);
        wp_die();
    }

    function change_folder_color() {
        $response            = [];
        $response['status']  = 0;
        $response['error']   = 0;
        $response['data']    = [];
        $response['message'] = "";
        $postData            = filter_input_array(INPUT_POST);
        $errorCounter        = 0;
        if (!isset($postData['term_id']) || empty($postData['term_id'])) {
            $errorCounter++;
            $response['message'] = esc_html__("Your request is not valid", 'folders');
        } else if (!isset($postData['nonce']) || empty($postData['nonce'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['color'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else {
            $term_id = self::sanitize_options($postData['term_id']);
            if (!wp_verify_nonce($postData['nonce'], 'wcp_folder_term_'.$term_id)) {
                $response['message'] = esc_html__("Your request is not valid", 'folders');
                $errorCounter++;
            }
        }

        if ($errorCounter == 0) {
            $term_id = self::sanitize_options($postData['term_id']);

            global $wpdb;
            $table = $wpdb->prefix."prm_custom_folders";

            $wpdb->update(
                $table,
                ["has_color" => esc_sql($postData['color'])],
                ["id" => esc_sql($term_id)]
            );

            $response['color'] = $postData['color'];
            $response['id']     = $term_id;
            $response['status'] = 1;
        }

        echo wp_json_encode($response);
        wp_die();
    }

    function mark_un_mark_folder() {
        $response            = [];
        $response['status']  = 0;
        $response['error']   = 0;
        $response['data']    = [];
        $response['message'] = "";
        $postData            = filter_input_array(INPUT_POST);
        $errorCounter        = 0;
        if (!isset($postData['term_id']) || empty($postData['term_id'])) {
            $errorCounter++;
            $response['message'] = esc_html__("Your request is not valid", 'folders');
        } else if (!isset($postData['nonce']) || empty($postData['nonce'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else {
            $term_id = self::sanitize_options($postData['term_id']);
            if (!wp_verify_nonce($postData['nonce'], 'wcp_folder_term_'.$term_id)) {
                $response['message'] = esc_html__("Your request is not valid", 'folders');
                $errorCounter++;
            }
        }

        if ($errorCounter == 0) {
            $term_id = self::sanitize_options($postData['term_id']);

            global $wpdb;
            $table = $wpdb->prefix."prm_custom_folders";

            $query = "SELECT is_high FROM {$table} WHERE id = %d";
            $query = $wpdb->prepare($query, [$term_id]);
            $status = $wpdb->get_var($query);
            $status = ($status) ? 0 : 1;

            $wpdb->update(
                $table,
                ["is_high" => $status],
                ["id" => esc_sql($term_id)]
            );

            $response['marked'] = $status;
            $response['id']     = $term_id;
            $response['status'] = 1;
        }

        echo wp_json_encode($response);
        wp_die();
    }

    function make_sticky_folder() {
        $response            = [];
        $response['status']  = 0;
        $response['error']   = 0;
        $response['data']    = [];
        $response['message'] = "";
        $postData            = filter_input_array(INPUT_POST);
        $errorCounter        = 0;
        if (!isset($postData['term_id']) || empty($postData['term_id'])) {
            $errorCounter++;
            $response['message'] = esc_html__("Your request is not valid", 'folders');
        } else if (!isset($postData['nonce']) || empty($postData['nonce'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else {
            $term_id = self::sanitize_options($postData['term_id']);
            if (!wp_verify_nonce($postData['nonce'], 'wcp_folder_term_'.$term_id)) {
                $response['message'] = esc_html__("Your request is not valid", 'folders');
                $errorCounter++;
            }
        }

        if ($errorCounter == 0) {
            $term_id = self::sanitize_options($postData['term_id']);

            global $wpdb;
            $table = $wpdb->prefix."prm_custom_folders";

            $query = "SELECT is_sticky, id FROM {$table} WHERE id = %d";
            $query = $wpdb->prepare($query, [$term_id]);
            $result = $wpdb->get_row($query);

            if(!empty($result)) {
                $status = intval(isset($result->is_sticky)?$result->is_sticky:0);
                $status = ($status) ? 0 : 1;

                global $wpdb;
                $table = $wpdb->prefix."prm_custom_folders";

                $wpdb->update(
                    $table,
                    ["is_sticky" => $status],
                    ["id" => esc_sql($term_id)]
                );

                $response['is_folder_sticky'] = $status;
            }

            $response['id']     = $term_id;
            $response['status'] = 1;
        }

        echo wp_json_encode($response);
        wp_die();
    }

    /**
     * Update folder data
     *
     * @since  1.0.0
     * @access public
     */
    public function update_folder()
    {
        $response            = [];
        $response['status']  = 0;
        $response['error']   = 0;
        $response['data']    = [];
        $response['message'] = "";
        $postData            = filter_input_array(INPUT_POST);
        $errorCounter        = 0;
        if (!current_user_can("manage_categories")) {
            $error = esc_html__("You have not permission to update folder", 'folders');
            $errorCounter++;
        } else if (!isset($postData['term_id']) || empty($postData['term_id'])) {
            $error = esc_html__("Unable to rename folder, Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['name']) || empty($postData['name'])) {
            $error = esc_html__("Folder name can no be empty", 'folders');
            $errorCounter++;
        } else if (!isset($postData['type']) || empty($postData['type'])) {
            $error = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['nonce']) || empty($postData['nonce'])) {
            $error = esc_html__("Unable to rename folder, Your request is not valid", 'folders');
            $errorCounter++;
        } else {
            $term_id = self::sanitize_options($postData['term_id']);
            if (!wp_verify_nonce($postData['nonce'], 'wcp_folder_term_'.$term_id)) {
                $error = esc_html__("Unable to rename folder, Your request is not valid", 'folders');
                $errorCounter++;
            }
        }//end if

        if ($errorCounter == 0) {
            $term_name   = self::sanitize_options($postData['name']);
            $term_id     = self::sanitize_options($postData['term_id']);
            $user_id     = get_current_user_id();
            $slug        = self::create_slug_from_string($term_name)."-".time()."-".$user_id;

            global $wpdb;
            $table = $wpdb->prefix."prm_custom_folders";

            $wpdb->update($table,
                [
                    "title" => esc_sql($term_name),
                    "title_slug" => esc_sql($slug),
                ],
                [
                    "id" => esc_sql($term_id)
                ]
            );
            $term_nonce         = wp_create_nonce('wcp_folder_term_'.$term_id);
            $response['id']     = $term_id;
            $response['slug']   = $slug;
            $response['status'] = 1;
            $response['term_title'] = $term_name;
            $response['nonce']      = $term_nonce;

        } else {
            $response['error']   = 1;
            $response['message'] = $error;
        }//end if

        echo wp_json_encode($response);
        wp_die();

    }//end wcp_update_folder()

    function save_folder_order() {
        $response            = [];
        $response['status']  = 0;
        $response['error']   = 0;
        $response['data']    = [];
        $response['message'] = "";
        $postData            = filter_input_array(INPUT_POST);
        $errorCounter        = 0;
        if (!isset($postData['term_ids']) || empty($postData['term_ids'])) {
            $errorCounter++;
            $response['message'] = esc_html__("Your request is not valid", 'folders');
        } else if (!isset($postData['type']) || empty($postData['type'])) {
            $errorCounter++;
            $response['message'] = esc_html__("Your request is not valid", 'folders');
        } else if (!isset($postData['nonce']) || empty($postData['nonce'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else {
            $type = self::sanitize_options($postData['type']);
            if (!wp_verify_nonce($postData['nonce'], 'wcp_folder_nonce_'.$type)) {
                $response['message'] = esc_html__("Your request is not valid", 'folders');
                $errorCounter++;
            }
        }

        if ($errorCounter == 0) {
            $termIds   = self::sanitize_options(($postData['term_ids']));
            $type      = self::sanitize_options($postData['type']);
            $termIds   = trim($termIds, ",");
            $termArray = explode(",", $termIds);
            $order     = 1;

            global $wpdb;
            $table = $wpdb->prefix."prm_custom_folders";

            foreach ($termArray as $term) {
                if (!empty($term)) {
                    $wpdb->update(
                        $table,
                        ["folder_order" => $order],
                        ["id" => esc_sql($term)]
                    );
                    $order++;
                }
            }

            $term_id     = self::sanitize_options($postData['term_id']);
            $parent_id   = self::sanitize_options($postData['parent_id']);

            $wpdb->update(
                $table,
                ["parent_id" => esc_sql($parent_id)],
                ["id" => esc_sql($term_id)]
            );

            $wpdb->update(
                $table,
                ["is_active" => 1],
                ["id" => esc_sql($parent_id)]
            );

            $response['status'] = 1;
        }//end if

        echo wp_json_encode($response);
        wp_die();
    }

    function save_folder_state() {
        $response            = [];
        $response['status']  = 0;
        $response['error']   = 0;
        $response['data']    = [];
        $response['message'] = "";
        $postData            = filter_input_array(INPUT_POST);
        $errorCounter        = 0;
        if (!isset($postData['term_id']) || empty($postData['term_id'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['nonce']) || empty($postData['nonce'])) {
            $response['message'] = esc_html__("Unable to create folder, Your request is not valid", 'folders');
            $errorCounter++;
        } else {
            $term_id = self::sanitize_options($postData['term_id']);
            if (!wp_verify_nonce($postData['nonce'], 'wcp_folder_term_'.$term_id)) {
                $response['message'] = esc_html__("Your request is not valid", 'folders');
                $errorCounter++;
            }
        }

        if ($errorCounter == 0) {
            $response['status'] = 1;
            $response['status'] = 1;
            $term_id            = self::sanitize_options($postData['term_id']);
            $is_active          = isset($postData['is_active']) ? $postData['is_active'] : 0;
            $is_active          = self::sanitize_options($is_active);

            global $wpdb;
            $table = $wpdb->prefix."prm_custom_folders";

            $wpdb->update($table, ['is_active' => esc_sql($is_active)], ["id" => esc_sql($term_id)]);
        }

        echo wp_json_encode($response);
        wp_die();
    }

    function save_folder_last_status() {
        $postData = filter_input_array(INPUT_POST);
        $error    = 0;
        if (!isset($postData['post_id']) || empty($postData['post_id'])) {
            $error = 1;
        } else if (!isset($postData['post_type']) || empty($postData['type'])) {
            $error = 1;
        } else if (!isset($postData['nonce']) || empty($postData['nonce'])) {
            $error = 1;
        }

        if ($error == 0) {
            $post_type = isset($postData['post_type']) ? $postData['post_type'] : "";
            $post_type = $this->filter_string_polyfill($post_type);
            $post_id   = isset($postData['post_id']) ? $postData['post_id'] : "";
            $post_id   = $this->filter_string_polyfill($post_id);
            if (!empty($post_type) && !empty($post_id)) {
                delete_option("last_folder_status_for".$post_type);
                add_option("last_folder_status_for".$post_type, $post_id);
            }
        }

        echo !esc_attr($error);
        wp_die();
    }

    /**
     * Will check fol other folders
     *
     * @since  1.0.0
     * @access public
     */
    public function check_for_other_folders()
    {
        $response            = [];
        $response['status']  = 0;
        $response['error']   = 0;
        $response['data']    = [];
        $response['message'] = "";
        $postData            = filter_input_array(INPUT_POST);
        $errorCounter        = 0;
        if (!isset($postData['post_id']) || empty($postData['post_id'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['type']) || empty($postData['type'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['nonce']) || empty($postData['nonce'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!wp_verify_nonce($postData['nonce'], 'wcp_folder_nonce_'.$postData['type'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        }

        if ($errorCounter == 0) {
            $folderUndoSettings = [];
            $type    = self::sanitize_options($postData['type']);
            $post_id = self::sanitize_options($postData['post_id']);
            $active_folder = self::sanitize_options($postData['active_folder']);

            $post_id = explode(",", $post_id);

            global $wpdb;
            if(!empty($active_folder)) {
                foreach ($post_id as $id) {
                    $query = "SELECT COUNT(folder_id) as total_folders FROM {$this->folder_item_table} WHERE folder_item = %s AND folder_id != %d";
                    $query = $wpdb->prepare($query, [esc_sql($id), esc_sql($active_folder)]);
                    $result = $wpdb->get_var($query);
                    if(!empty($result)) {
                        $response['status']          = -1;
                        $response['data']['post_id'] = $postData['post_id'];
                        echo wp_json_encode($response);
                        wp_die();
                    }
                }
            }

            $this->remove_post_folder();
        }//end if

        echo wp_json_encode($response);
        wp_die();

    }//end premio_check_for_other_folders()

    /**
     * Get folders by order
     *
     * @since  1.0.0
     * @access public
     */
    public function folders_by_order()
    {
        $response            = [];
        $response['status']  = 0;
        $response['error']   = 0;
        $response['data']    = [];
        $response['message'] = "";
        $postData            = filter_input_array(INPUT_POST);
        $errorCounter        = 0;

        if (!isset($postData['order']) || empty($postData['order'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['type']) || empty($postData['type'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['nonce']) || empty($postData['nonce'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else {
            $type  = self::sanitize_options($postData['type']);
            $nonce = self::sanitize_options($postData['nonce']);
            if (!wp_verify_nonce($nonce, 'wcp_folder_nonce_'.$type)) {
                $response['message'] = esc_html__("Your request is not valid", 'folders');
                $errorCounter++;
            }
        }//end if

        if ($errorCounter == 0) {
            $response['status'] = 1;

            $order_field = $postData['order'];

            $order_by = "";
            $order    = "ASC";

            if ($order_field == "a-z" || $order_field == "z-a") {
                $order_by = 'P.title';
                if ($order_field == "z-a") {
                    $order = "DESC";
                }
            } else if ($order_field == "n-o" || $order_field == "o-n") {
                $order_by = 'P.id';
                if ($order_field == "o-n") {
                    $order = "ASC";
                } else {
                    $order = "DESC";
                }
            }

            if (empty($order_by)) {
                $order = "P.folder_order";
            }

            if($order_by != "P.folder_order") {
                global $wpdb;
                $query = "UPDATE {$this->folder_table} SET folder_order = 0";
                $wpdb->query($query);
            }


            $post_type = self::get_custom_post_type($postData['type']);
            // Do not change: Free/Pro Class name change
            $sticky_open = get_option("premio_folder_sticky_status_".$postData['type']);
            $sticky_open = ($sticky_open == 1) ? 1 : 0;
            $tree_data     = WCP_Pro_Tree::get_full_custom_tree_data($post_type, $order_by, $order, $sticky_open);

            $response['data'] = $tree_data['string'];
            $taxonomies       = [];
            $response['terms'] = $taxonomies;
        }//end if

        echo wp_json_encode($response);
        die;

    }//end wcp_folders_by_order()


    function remove_post_folder() {
        $response            = [];
        $response['status']  = 0;
        $response['error']   = 0;
        $response['data']    = [];
        $response['message'] = "";
        $postData            = filter_input_array(INPUT_POST);
        $errorCounter        = 0;
        if (!isset($postData['post_id']) || empty($postData['post_id'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['type']) || empty($postData['type'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!current_user_can("manage_categories")) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        }

        if ($errorCounter == 0) {
            $folderUndoSettings = [];
            $type    = self::sanitize_options($postData['type']);
            $post_id = self::sanitize_options($postData['post_id']);

            $post_id = explode(",", $post_id);

            global $wpdb;
            $item_table = $wpdb->prefix."prm_custom_folder_items";
            $post_type = self::get_custom_post_type($type);
            foreach ($post_id as $post) {
                $args = [
                    'folder_item' => esc_sql($post),
                    'custom_type' => esc_sql($post_type)
                ];
                if (isset($postData['remove_from']) && $postData['remove_from'] == "current" && isset($postData['active_folder']) && is_numeric($postData['active_folder'])) {
                    $args['folder_id'] = esc_sql($postData['active_folder']);
                }
                $wpdb->delete($item_table, $args);
            }

//            delete_transient("folder_item_undo_settings");
//            set_transient("folder_undo_settings", $folderUndoSettings, DAY_IN_SECONDS);
            $response['status'] = 1;
        }//end if

        echo wp_json_encode($response);
        wp_die();
    }


    function filter_plugins($plugins) {
        if(isset($_GET['folders4plugins_folder']) && !empty($_GET['folders4plugins_folder'])) {
            $folder = sanitize_text_field($_GET['folders4plugins_folder']);
            global $wpdb;
            if($folder == -1) {
                $table = $wpdb->prefix . "prm_custom_folder_items";
                $query = "SELECT DISTINCT(folder_item) FROM {$table} WHERE custom_type = %s";
                $query = $wpdb->prepare($query, ["folders4plugins_folder"]);
                $records = $wpdb->get_results($query);
                if (empty($records)) {
                    return $plugins;
                } else {
                    foreach ($records as $record) {
                        if (isset($plugins[$record->folder_item])) {
                            unset($plugins[$record->folder_item]);
                        }
                    }
                    return $plugins;
                }
            } else {
                $table = $wpdb->prefix . "prm_custom_folders";
                $query = "SELECT id FROM {$table} WHERE title_slug = %s";
                $query = $wpdb->prepare($query, [$folder]);
                $folder_id = $wpdb->get_var($query);
                if (!empty($folder_id)) {
                    $table = $wpdb->prefix . "prm_custom_folder_items";
                    $query = "SELECT folder_item FROM {$table} WHERE folder_id = %d";
                    $query = $wpdb->prepare($query, [$folder_id]);
                    $records = $wpdb->get_results($query);
                    if (empty($records)) {
                        return [];
                    } else {
                        $filter = [];
                        foreach ($records as $record) {
                            if (isset($plugins[$record->folder_item])) {
                                $filter[] = $record->folder_item;
                            }
                        }
                        foreach($plugins as $key=>$plugin) {
                            if(!in_array($key, $filter)) {
                                unset($plugins[$key]);
                            }
                        }
                    }
                }
            }
        }
        return $plugins;
    }


    /**
     * Set multiple folders for posts
     *
     * @since  1.0.0
     * @access public
     */
    function get_folders_default_list()
    {
        $postData = filter_input_array(INPUT_POST);

        $post_type = self::sanitize_options($postData['type']);

        $post_type = $post_type."_folder";

        $ttpsts = $this->get_total_plugins($post_type);

        $empty_items = self::get_total_empty_posts($post_type);

        $taxonomies = $this->get_folder_details($post_type);

        $response = [
            'status'      => 1,
            'total_items' => $ttpsts,
            'empty_items' => $empty_items,
            'taxonomies'  => $taxonomies,
        ];
        echo wp_json_encode($response);
        die;

    }


    function get_total_plugins($post_type) {
        $plugins = get_plugins();
        if(!empty($plugins) && is_array($plugins)) {
            return count($plugins);
        }
        return 0;
    }


    function get_total_empty_posts($post_type) {
        global $wpdb;
        $table = $wpdb->prefix."prm_custom_folder_items";
        $query = "SELECT DISTINCT(folder_item)
                FROM {$table}
                WHERE custom_type = %s";
        $query = $wpdb->prepare($query, [$post_type]);
        $records = $wpdb->get_results($query);
        $plugin = 0;
        if(!empty($records)) {
            $plugins = get_plugins();
            foreach($records as $record) {
                if(isset($plugins[$record->folder_item])) {
                    $plugin++;
                } else {
                    $arg = [
                        'custom_type' => esc_sql($post_type),
                        'folder_item' => esc_sql($record->folder_item)
                      ];
                    $wpdb->delete($table, $arg);
                }
            }
        }

        return $this->get_total_plugins($post_type) - $plugin;
    }

    /**
     * Set multiple folders for posts
     *
     * @since  1.0.0
     * @access public
     */
    public function change_multiple_post_folder()
    {
        $response            = [];
        $response['status']  = 0;
        $response['error']   = 0;
        $response['data']    = [];
        $response['message'] = "";
        $postData            = filter_input_array(INPUT_POST);
        $errorCounter        = 0;
        if (!isset($postData['post_ids']) || empty($postData['post_ids'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['folder_id']) || empty($postData['folder_id'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['type']) || empty($postData['type'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['nonce']) || empty($postData['nonce'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        }else {
            if (!wp_verify_nonce($postData['nonce'], 'wcp_folder_nonce_'.$postData['type'])) {
                $response['message'] = esc_html__("Your request is not valid", 'folders');
                $errorCounter++;
            }
        }//end if

        if ($errorCounter == 0) {
            $folderUndoSettings = [];
            $postID    = self::sanitize_options($postData['post_ids']);
            $postID    = trim($postID, ",");
            $folderID  = self::sanitize_options($postData['folder_id']);
            $type      = self::sanitize_options($postData['type']);
            $postArray = explode(",", $postID);

            $taxonomy = "";
            if (isset($postData['taxonomy'])) {
                $taxonomy = self::sanitize_options($postData['taxonomy']);
            }

            if (is_array($postArray)) {
                global $wpdb;
                $item_table = $wpdb->prefix."prm_custom_folder_items";
                $post_type = self::get_custom_post_type($type);
                foreach ($postArray as $post) {
                    if (!empty($taxonomy)) {
                        $args = [
                            'folder_item' => esc_sql($post),
                            'custom_type' => esc_sql($post_type)
                        ];
                        $wpdb->delete($item_table, $args);
                    }

                    $args = [
                        'folder_item' => esc_sql($post),
                        'custom_type' => esc_sql($post_type),
                        'folder_id' => esc_sql($folderID)
                    ];
                    $wpdb->delete($item_table, $args);
                    $wpdb->insert($item_table, $args);
                }
            }

            $response['status'] = 1;
//            delete_transient("folder_undo_settings");
//            delete_transient("premio_folders_without_trash");
//            set_transient("folder_undo_settings", $folderUndoSettings, DAY_IN_SECONDS);

        }//end if

        echo wp_json_encode($response);
        wp_die();

    }//end change_multiple_post_folder()

    /**
     * Add New folder
     *
     * @since  2.8.7
     * @access public
     */
    public function add_new_folder()
    {
        $response            = [];
        $response['status']  = 0;
        $response['error']   = 0;
        $response['login']   = 1;
        $response['data']    = [];
        $response['message'] = "";
        $response['message2'] = "";
        $postData     = filter_input_array(INPUT_POST);
        $errorCounter = 0;
        $error        = esc_html__("Your request is not valid", 'folders');
        if (!current_user_can("manage_categories")) {
            $error = esc_html__("You have not permission to add folder", 'folders');
            $errorCounter++;
        } else if (!isset($postData['name']) || empty($postData['name'])) {
            $error = esc_html__("Folder name can no be empty", 'folders');
            $errorCounter++;
        } else if (!isset($postData['type']) || empty($postData['type'])) {
            $error = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else if (!isset($postData['nonce']) || empty($postData['nonce'])) {
            $response['login'] = 0;
            $error = esc_html__("Unable to create folder, Your request is not valid", 'folders');
            $errorCounter++;
        } else {
            $type = self::sanitize_options($postData['type']);
            if (!wp_verify_nonce($postData['nonce'], 'wcp_folder_nonce_'.$type)) {
                $response['login'] = 0;
                $error = esc_html__("Unable to create folder, Your request is not valid", 'folders');
                $errorCounter++;
            }
        }//end if

        if ($errorCounter == 0) {
            global $wpdb;
            $parent      = isset($postData['parent_id']) && !empty($postData['parent_id']) ? $postData['parent_id'] : 0;
            $parent      = self::sanitize_options($parent);
            $type        = self::sanitize_options($postData['type']);
            $folder_type = self::get_custom_post_type($type);
            $term_name   = self::sanitize_options($postData['name']);
            $user_id     = get_current_user_id();

            $status = $this->check_for_folder_title($term_name, $parent, $folder_type, $user_id);
            if($status) {
                $response['error']   = 1;
                $response['message'] = esc_html__("Folder name already exists", 'folders');
                echo wp_json_encode($response);
                wp_die();
            } else {
                $slug   = self::create_slug_from_string($term_name)."-".time()."-".$user_id;
                $args = [
                    'title'       => esc_sql($term_name),
                    'title_slug'  => esc_sql($slug),
                    'parent_id'   => esc_sql($parent),
                    'custom_type' => esc_sql($folder_type),
                    'folder_order' => 0,
                    'is_high'   => 0,
                    'is_locked' => 0,
                    'is_active' => 0,
                    'is_sticky' => 0,
                    'created_by'  => $user_id,
                    'created_on' => gmdate("Y-m-d H:i:s")
                ];
                $folder_id = $wpdb->insert($this->folder_table, $args);
                if($folder_id) {
                    $folder_id = $wpdb->insert_id;
                    /* Change current folder orders */
                    $query = "SELECT id, folder_order FROM ".$this->folder_table." WHERE parent_id = %d ORDER BY folder_order ASC";
                    $query = $wpdb->prepare($query, [esc_sql($parent)]);
                    $records = $wpdb->get_results($query);
                    if(!empty($records)) {
                        foreach($records as $key => $record) {
                            $wpdb->update($this->folder_table, ["folder_order" => ($key+1)], ["id" => $record->id]);
                        }
                    }

                    /**
                     * Checking for Duplicating folders
                     * */
                    $is_high    = 0;
                    $is_locked  = 0;
                    $is_active  = 0;
                    $is_sticky  = 0;
                    $is_duplicate = (isset($postData['is_duplicate'])&&$postData['is_duplicate'])?true:false;
                    if($is_duplicate) {
                        $duplicate_from = (isset($postData['duplicate_from'])&&$postData['duplicate_from'])?$postData['duplicate_from']:"";
                        if(!empty($duplicate_from) && is_numeric($duplicate_from)) {
                            $query = "SELECT * FROM {$this->folder_table} WHERE id = %d";
                            $query = $wpdb->prepare($query, [$duplicate_from]);
                            $folderData = $wpdb->get_row($query);
                            if(!empty($folderData)) {
                                $is_high    = $folderData->is_high;
                                $is_locked  = $folderData->is_locked;
                                $is_active  = $folderData->is_active;
                                $is_sticky  = $folderData->is_sticky;

                                $args = [
                                    'is_high'   => $is_high,
                                    'is_locked' => $is_locked,
                                    'is_active' => $is_active,
                                    'is_sticky' => $is_sticky
                                ];
                                $wpdb->update($this->folder_table, $args, ["id" => $folder_id]);

                                $query = "SELECT * FROM {$this->folder_item_table} WHERE folder_id = %d";
                                $query = $wpdb->prepare($query, [$duplicate_from]);
                                $results = $wpdb->get_results($query);
                                if(!empty($results)) {
                                    foreach($results as $result) {
                                        $arg = [
                                            'folder_id' => $folder_id,
                                            'folder_item' => esc_sql($result->folder_item),
                                            'custom_type' => esc_sql($result->custom_type),
                                        ];
                                        $wpdb->insert($this->folder_item_table, $arg);
                                    }
                                }
                            }
                        }
                    }

                    $response['status'] = 1;
                    $response['folder_id'] = $folder_id;

                    $folder = [
                        'is_high'   => $is_high,
                        'is_locked' => $is_locked,
                        'is_active' => $is_active,
                        'is_sticky' => $is_sticky,
                        'nonce'     => wp_create_nonce("wcp_folder_term_".$folder_id),
                        'folder_count' => 0,
                        'parent_id' => esc_sql($parent),
                        'slug'      => $slug,
                        'term_id'   => $folder_id,
                        'title'     => esc_sql($term_name)
                    ];
                    $folders = [];
                    $folders[] = $folder;

                    $response['data'] = $folders;
                    $response['parent_id'] = empty($parent)?"#":$parent;
                }
            }

        } else {
            $response['error']   = 1;
            $response['message'] = $error;
        }//end if

        echo wp_json_encode($response);
        wp_die();

    }//end wcp_add_new_folder()


    public function check_for_folder_title($title, $parent_id, $custom_type, $created_by) {
        global $wpdb;
        $query = "SELECT id FROM ".$this->folder_table." WHERE title = %s AND parent_id = %d AND custom_type = %s AND created_by = %d";
        $query = $wpdb->prepare($query, [$title, $parent_id, $custom_type, $created_by]);
        return $wpdb->get_var($query);
    }

    public function enqueue_styles() {
        if(self::is_active_for_plugin()) {
            wp_enqueue_style('wcp-folders-fa', plugin_dir_url(dirname(__FILE__)) . 'assets/css/folder-icon.css', [], WCP_PRO_FOLDER_VERSION);
            wp_enqueue_style('wcp-folders-admin', plugin_dir_url(dirname(__FILE__)) . 'assets/css/design.min.css', [], WCP_PRO_FOLDER_VERSION);
            wp_enqueue_style('wcp-folders-jstree', plugin_dir_url(dirname(__FILE__)) . 'assets/css/jstree.min.css', [], WCP_PRO_FOLDER_VERSION);
            wp_enqueue_style('wcp-overlayscrollbars', plugin_dir_url(dirname(__FILE__)) . 'assets/css/overlayscrollbars.min.css', [], WCP_PRO_FOLDER_VERSION);
            wp_enqueue_style('wcp-spectrum', plugin_dir_url(dirname(__FILE__)).'assets/css/spectrum.min.css', [], WCP_PRO_FOLDER_VERSION);
            wp_enqueue_style('wcp-folders-css', plugin_dir_url(dirname(__FILE__)) . 'assets/css/folders.css', [], WCP_PRO_FOLDER_VERSION);

            $width = get_option("wcp_dynamic_width_for_".$this->type);
            $width = esc_attr($width);
            $width = intval($width);
            $width = empty($width)||!is_numeric($width) ? 280 : $width;
            if ($width > 1200) {
                $width = 280;
            }
            $css = "";
            $width          = intval($width);
            $display_status = "wcp_dynamic_display_status_".$this->type;
            $display_status = get_option($display_status);
            if ($display_status != "hide") {
                if (!empty($width) && is_numeric($width)) {
                    $css .= ".wcp-content {width:{$width}px}";
                    if (function_exists('is_rtl') && is_rtl()) {
                        $css .= "html[dir='rtl'] body.wp-admin #wpcontent {padding-right:".($width + 20)."px}";
                        $css .= "html[dir='rtl'] body.wp-admin #e-admin-top-bar-root.e-admin-top-bar--active {width: calc(100% - 160px - ".($width)."px)}";
                        $css .= "html[dir='rtl'] body.wp-admin #wpcontent {padding-left:0px}";
                    } else {
                        $css .= "body.wp-admin #wpcontent {padding-left:".($width + 20)."px}";
                        $css .= "body.wp-admin #e-admin-top-bar-root.e-admin-top-bar--active {width: calc(100% - 160px - ".($width)."px)}";
                    }
                }
            } else {
                if (function_exists('is_rtl') && is_rtl()) {
                    $css .= "html[dir='rtl']  body.wp-admin #wpcontent {padding-right:20px}";
                    $css .= "html[dir='rtl'] body.wp-admin #wpcontent {padding-left:0px}";
                } else {
                    $css .= "body.wp-admin #wpcontent {padding-left:20px}";
                }
            }

            if (!empty($width) && is_numeric($width)) {
                if ($width > 1200) {
                    $width = 280;
                }

                $width = intval($width);
                $css  .= ".wcp-content {width: {$width}px}";


            }

            wp_register_style('wcp-css-handle', false);
            wp_enqueue_style('wcp-css-handle');
            wp_add_inline_style('wcp-css-handle', $css);

            $css_text = "";
            $customize_folders = get_option('customize_folders');
            $customize_folders = (empty($customize_folders) || !is_array($customize_folders))?[]:$customize_folders;
            if (!isset($customize_folders['new_folder_color']) || empty($customize_folders['new_folder_color'])) {
                $customize_folders['new_folder_color'] = "#FA166B";
            }

            $css_text .= ".media-frame a.add-new-folder { background-color: ".esc_attr($customize_folders['new_folder_color'])."; border-color: ".esc_attr($customize_folders['new_folder_color'])."}";
            $css_text .= ".wcp-hide-show-buttons .toggle-buttons { background-color: ".esc_attr($customize_folders['new_folder_color'])."; }";
            $css_text .= ".folders-toggle-button span { background-color: ".esc_attr($customize_folders['new_folder_color'])."; }";
            $css_text .= ".ui-resizable-handle.ui-resizable-e:before, .ui-resizable-handle.ui-resizable-w:before {border-color: ".esc_attr($customize_folders['new_folder_color'])." !important}";

            if (!isset($customize_folders['folder_bg_color']) || empty($customize_folders['folder_bg_color'])) {
                $customize_folders['folder_bg_color'] = "#FA166B";
            }

            $rgbColor  = self::hexToRgb($customize_folders['folder_bg_color']);
            $css_text .= "body:not(.no-hover-css) #custom-scroll-menu .jstree-hovered:not(.jstree-clicked), body:not(.no-hover-css) #custom-scroll-menu .jstree-hovered:not(.jstree-clicked):hover, .dynamic-menu a:hover, .folder-setting-menu li a:hover { background: rgba(".$rgbColor['r'].",".$rgbColor['g'].",".$rgbColor['b'].", 0.08) !important; color: #333333;}";
            $css_text .= "body:not(.no-hover-css) #custom-scroll-menu .jstree-clicked, body:not(.no-hover-css) #custom-scroll-menu .jstree-clicked:not(.jstree-clicked):focus, #custom-scroll-menu .jstree-clicked, #custom-scroll-menu .jstree-clicked:hover { background: ".$customize_folders['folder_bg_color']." !important; color: #ffffff !important; }";
            $css_text .= "#custom-scroll-menu .jstree-hovered.wcp-drop-hover, #custom-scroll-menu .jstree-hovered.wcp-drop-hover:hover, #custom-scroll-menu .jstree-clicked.wcp-drop-hover, #custom-scroll-menu .jstree-clicked.wcp-drop-hover:hover, body #custom-scroll-menu  *.drag-in > a:hover { background: ".$customize_folders['folder_bg_color']." !important; color: #ffffff !important; }";
            $css_text .= ".drag-bot > a { border-bottom: solid 2px ".$customize_folders['folder_bg_color']."}";
            $css_text .= ".drag-up > a { border-top: solid 2px ".$customize_folders['folder_bg_color']."}";
            $css_text .= "body:not(.no-hover-css) #custom-scroll-menu *.drag-in > a.jstree-hovered, body:not(.no-hover-css) #custom-scroll-menu *.drag-in > a.jstree-hovered:hover {background: ".$customize_folders['folder_bg_color']." !important; color: #fff !important;}";
            $css_text .= ".orange-bg > span, .jstree-clicked, .header-posts a.active-item, .un-categorised-items.active-item, .sticky-folders ul li a.active-item { background-color: ".esc_attr($customize_folders['folder_bg_color'])." !important; color: #ffffff !important; }";
            $css_text .= "body:not(.no-hover-css) .wcp-container .route .title:hover, body:not(.no-hover-css) .header-posts a:hover, body:not(.no-hover-css) .un-categorised-items:hover, body:not(.no-hover-css) .sticky-folders ul li a:hover { background: rgba(".esc_attr($rgbColor['r'].",".$rgbColor['g'].",".$rgbColor['b'].", 0.08")."); color: #333333;}";
            // $css_text .= "body:not(.no-hover-css) .wcp-container .route .title:hover, .header-posts a:hover, .un-categorised-items.active-item, .un-categorised-items:hover, .sticky-folders ul li a:hover {background: rgba(" . esc_attr($rgbColor['r'] . "," . $rgbColor['g'] . "," . $rgbColor['b'] . ", 0.08") . "); color:#444444;}";
            $css_text .= ".wcp-drop-hover {background-color: ".esc_attr($customize_folders['folder_bg_color'])." !important; color: #ffffff; }";
            $css_text .= "#custom-menu .route .nav-icon .wcp-icon {color: ".esc_attr($customize_folders['folder_bg_color'])." !important;}";
            $css_text .= ".mCS-3d.mCSB_scrollTools .mCSB_dragger .mCSB_dragger_bar {background-color: ".esc_attr($customize_folders['folder_bg_color'])." !important;}";
            $css_text .= ".os-theme-dark>.os-scrollbar>.os-scrollbar-track>.os-scrollbar-handle {background-color: ".esc_attr($customize_folders['folder_bg_color'])." !important;}";
            $css_text .= "body:not(.no-hover-css) .jstree-hovered {background: rgba(".esc_attr($rgbColor['r'].",".$rgbColor['g'].",".$rgbColor['b'].", 0.08)")."}";
            $css_text .= ".jstree-default .jstree-clicked {background:".esc_attr($customize_folders['folder_bg_color'])."}";
            $css_text .= ".folders-action-menu > ul > li > a:not(.disabled):hover, .folders-action-menu > ul > li > label:not(.disabled):hover {color:".esc_attr($customize_folders['folder_bg_color'])."}";
            $css_text .= ".jstree-closed > i.jstree-icon, .jstree-open > i.jstree-icon {color:".esc_attr($customize_folders['folder_bg_color'])."}";
            if (!isset($customize_folders['bulk_organize_button_color']) || empty($customize_folders['bulk_organize_button_color'])) {
                $customize_folders['bulk_organize_button_color'] = "#FA166B";
            }

            $css_text   .= "button.button.organize-button { background-color: ".esc_attr($customize_folders['bulk_organize_button_color'])."; border-color: ".esc_attr($customize_folders['bulk_organize_button_color'])."; }";
            $css_text   .= "button.button.organize-button:hover { background-color: ".esc_attr($customize_folders['bulk_organize_button_color'])."; border-color: ".esc_attr($customize_folders['bulk_organize_button_color'])."; }";
            $css_text   .= "#custom-scroll-menu .jstree-hovered:not(.jstree-clicked) .pfolder-folder-close, .dynamic-menu a:hover span, .dynamic-menu a:hover i {color: ".esc_attr($customize_folders['folder_bg_color'])."}";
            $font_family = "";
            if (isset($customize_folders['folder_font']) && !empty($customize_folders['folder_font'])) {
                $folder_fonts = self::get_font_list();
                $font_family  = $customize_folders['folder_font'];
                if (isset($folder_fonts[$font_family])) {
                    if ($font_family == "System Stack") {
                        $font_family = "-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen-Sans,Ubuntu,Cantarell,Helvetica Neue,sans-serif";
                    }

                    $css_text .= ".wcp-container, .folder-popup-form, .dynamic-menu { font-family: ".esc_attr($font_family)." !important; }";
                    if ($font_family == "-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen-Sans,Ubuntu,Cantarell,Helvetica Neue,sans-serif") {
                        $font_family = "System Stack";
                    }
                }

                if ($folder_fonts[$font_family] == "Default") {
                    $font_family = "";
                }
            }

            if (isset($customize_folders['folder_size']) && !empty($customize_folders['folder_size'])) {
                if ($customize_folders['folder_size'] == "custom") {
                    $customize_folders['folder_size'] = ! isset($customize_folders['folder_custom_font_size']) || empty($customize_folders['folder_custom_font_size']) ? "16" : $customize_folders['folder_custom_font_size'];
                }

                $css_text .= ".wcp-container .route span.title-text, .header-posts a, .un-categorised-items a, .sticky-title, .sticky-folders > ul > li > a, .jstree-default .jstree-anchor { font-size: ".esc_attr($customize_folders['folder_size'])."px; }";
            }

            if (!empty($font_family)) {
                wp_enqueue_style('custom-google-fonts', 'https://fonts.googleapis.com/css?family='.urlencode($font_family), false);
            }
            wp_add_inline_style('wcp-css-handle', $css_text);
        }
    }

    public static function get_plugins_hierarchical($taxonomy) {

        self::check_for_db_tables();

        global $wpdb;

        $table = $wpdb->prefix."prm_custom_folders";

        $hierarchical_terms = [];
        $query = "SELECT id, title, title_slug FROM {$table} WHERE parent_id = 0 AND custom_type = %s ORDER BY folder_order ASC";
        $query = $wpdb->prepare($query, [$taxonomy]);
        $results = $wpdb->get_results($query);
        foreach($results as $result) {
            $result->term_name      = $result->title;
            $hierarchical_terms[] = $result;
            $hierarchical_terms   = self::get_custom_child_folders($taxonomy, $hierarchical_terms, $result->id, "-");
        }

        return $hierarchical_terms;
    }

    public static function get_custom_child_folders($taxonomy, $hierarchical_terms, $parent_id, $separator) {
        global $wpdb;

        $table = $wpdb->prefix."prm_custom_folders";

        $query = "SELECT id, title, title_slug FROM {$table} WHERE parent_id = %d AND custom_type = %s ORDER BY folder_order ASC";
        $query = $wpdb->prepare($query, [$parent_id, $taxonomy]);
        $results = $wpdb->get_results($query);
        foreach($results as $result) {
            $result->title    = $separator." ".$result->title;
            $hierarchical_terms[] = $result;
            $hierarchical_terms   = self::get_custom_child_folders($taxonomy, $hierarchical_terms, $result->id, $separator."-");
        }

        return $hierarchical_terms;
    }

    public function enqueue_script() {
        if(self::is_active_for_plugin()) {
            remove_filter("terms_clauses", "TO_apply_order_filter");

            // Free/Pro Version change
            wp_dequeue_script("jquery-jstree");
            // CMS Tree Page View Conflict
            wp_enqueue_script('wcp-folders-overlayscrollbars', plugin_dir_url(dirname(__FILE__)).'assets/js/jquery.overlayscrollbars.min.js', [], WCP_PRO_FOLDER_VERSION, true);
            wp_enqueue_script('wcp-overlayscrollbars', plugin_dir_url(dirname(__FILE__)).'assets/js/overlayscrollbars.min.js', [], WCP_PRO_FOLDER_VERSION, true);
            wp_enqueue_script('wcp-folders-jstree', plugin_dir_url(dirname(__FILE__)).'assets/js/jstree.min.js', ['jquery'], WCP_PRO_FOLDER_VERSION, true);
            wp_enqueue_script('wcp-jquery-touch', plugin_dir_url(dirname(__FILE__)).'assets/js/jquery.ui.touch-punch.min.js', ['jquery'], WCP_PRO_FOLDER_VERSION, true);
            wp_enqueue_script('folders-spectrum', plugin_dir_url(dirname(__FILE__)).'assets/js/spectrum.min.js', ['jquery'], WCP_PRO_FOLDER_VERSION, true);
            wp_enqueue_script('wcp-folders-custom', plugin_dir_url(dirname(__FILE__)).'assets/js/plugins.js', ['jquery', 'jquery-ui-resizable', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable', 'backbone'], WCP_PRO_FOLDER_VERSION, true);

            $customize_folders = get_option('customize_folders');
            $post_type = "folders4plugins";
            $custom_type = self::get_custom_post_type($post_type);
            $lang = $this->js_strings();
            $register_url = $this->getRegisterKeyURL();
            $is_rtl = 0;
            if (function_exists('is_rtl') && is_rtl()) {
                $is_rtl = 1;
            }
            $can_manage_folder = current_user_can("manage_categories") ? 1 : 0;
            $width = get_option("wcp_dynamic_width_for_".$this->type);
            $width = intval($width);
            $width = empty($width)||!is_numeric($width) ? 280 : $width;
            if ($width > 1200) {
                $width = 280;
            }
            $use_shortcuts   = !isset($customize_folders['use_shortcuts']) ? "yes" : $customize_folders['use_shortcuts'];
            $folder_settings = $this->get_folder_details($custom_type);
            $current_url = admin_url("plugins.php?1=1");
            $page_url = admin_url("plugins.php?".$custom_type."=");
            if(!empty($folder_settings)) {
                foreach($folder_settings as $key => $folder) {
                    $folder_settings[$key]['has_color'] = $folder['has_color'];
                }
            }

            $selected_taxonomy = 0;
            global $wpdb;
            if(isset($_GET[$custom_type]) && !empty($_GET[$custom_type])) {
                $selected_taxonomy = sanitize_text_field($_GET[$custom_type]);
                $query = "SELECT id FROM {$this->folder_table} WHERE title_slug = %s";
                $query = $wpdb->prepare($query, [esc_sql($selected_taxonomy)]);
                $selected_taxonomy = $wpdb->get_var($query);
            }
            $plugin_status = (isset($_GET['plugin_status']) && !empty($_GET['plugin_status']))?esc_attr($_GET['plugin_status']):"";
            wp_localize_script(
                'wcp-folders-custom',
                'wcp_settings',
                [
                    'ajax_url'          => admin_url('admin-ajax.php'),
                    'admin_url'         => admin_url(''),
                    'post_type'         => $post_type,
                    'custom_type'       => $custom_type,
                    'current_url'       => $current_url,
                    'page_url'          => $page_url,
                    'lang'              => $lang,
                    'register_url'      => $register_url,
                    'isRTL'             => $is_rtl,
                    'can_manage_folder' => $can_manage_folder,
                    'folder_width'      => $width,
                    'use_shortcuts'     => $use_shortcuts,
                    'folder_settings'   => $folder_settings,
                    'nonce'             => wp_create_nonce('wcp_folder_nonce_'.$this->type),
                    'selected_taxonomy' => $selected_taxonomy,
                    'user_access'       => $this->get_folders_user_role(),
                    'plugin_status'     => $plugin_status,
                    'selected_colors'   => $this->selected_colors()
                ]
            );
        }
    }

    public function get_folder_details($post_type) {
        $folder_settings = [];
        global $wpdb;
        $table_name = $wpdb->prefix.'prm_custom_folders';
        $item_table = $wpdb->prefix.'prm_custom_folder_items';
        $query = "SELECT P.*, COUNT(I.id) as total_child, COUNT(I.id) as folder_count FROM ".$table_name." AS P
            LEFT JOIN ".$item_table." AS I ON P.id = I.folder_id
            WHERE P.custom_type = %s
            GROUP BY P.id";
        $query = $wpdb->prepare($query, [$post_type]);
        $records = $wpdb->get_results($query);
        foreach($records as $record) {
            $folder = [];
            $folder['title'] = $record->title;
            $folder['folder_id'] = intval($record->id);
            $folder['term_id'] = intval($record->id);
            $folder['folder_count'] = intval($record->folder_count);
            $folder['is_active'] = intval($record->is_active);
            $folder['is_high'] = intval($record->is_high);
            $folder['is_locked'] = intval($record->is_locked);
            $folder['is_sticky'] = intval($record->is_sticky);
            $folder['has_color'] = $record->has_color;
            $folder['is_deleted'] = 0;
            $folder['nonce'] = wp_create_nonce("wcp_folder_term_".$record->id);
            $folder['slug'] = $record->title_slug;
            $folder_settings[] = $folder;
        }
        return $folder_settings;
    }


    public function admin_footer() {
        if(self::is_active_for_plugin()) {

            self::check_for_db_tables();
            $folder_type = "folders4plugins";
            $post_type = WCP_Pro_Folders::get_custom_post_type($folder_type);

            $isForPlugins = true;
            $userRole      = $this->get_folders_user_role();
            $ttpsts = $this->get_total_plugins($post_type);
            $ttemp = $this->get_total_empty_posts($post_type);

            $hasValidKey   = $this->check_has_valid_key();
            $activateURL   = $this->getRegisterKeyURL();
            $form_html     = WCP_Pro_Forms::get_form_html($hasValidKey, $activateURL, $userRole);

            $sticky_open = get_option("premio_folder_sticky_status_".$this->type);
            $sticky_open = ($sticky_open == 1) ? 1 : 0;
            $tree_data     = WCP_Pro_Tree::get_full_custom_tree_data($post_type, "P.folder_order", "ASC", $sticky_open);
            $terms_data    = $tree_data['string'];
            $sticky_string = $tree_data['sticky_string'];

            $dynamic_folders = new Premio_Pro_Folders_Dynamic_Folders();

            include_once dirname(dirname(__FILE__)).WCP_DS."/templates".WCP_DS."admin".WCP_DS."admin-content.php";
        }
    }

    public static function is_active_for_plugin() {
        if(self::$isEnabled) {
            global $current_screen;
            if(isset($current_screen->base) && $current_screen->base == "plugins") {
                return true;
            }
        }
        return false;
    }

    public static function check_for_db_tables() {
        global $wpdb;


        $table_name = $wpdb->prefix.'prm_custom_folders';
        if ($wpdb->get_var("show tables like '{$table_name}'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE {$table_name} (
                id BIGINT(20) NOT NULL AUTO_INCREMENT,
                title TINYTEXT NULL,
                title_slug TINYTEXT NULL,
                folder_order INT(8) NULL,
                is_high TINYINT(4) NULL,
                is_locked TINYINT(4) NULL,
                is_active TINYINT(4) NULL,
                is_sticky TINYINT(4) NULL,
                parent_id BIGINT(20) NULL,
                custom_type CHAR(100) NULL,
                created_by BIGINT(20) NULL,
                updated_by BIGINT(20) NULL,
                created_on DATETIME NULL,
                updated_on DATETIME NULL,
                PRIMARY KEY (id)
            ) " . $charset_collate . ";";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

        /* Add Color for Folders */
        $field_check = $wpdb->get_var("SHOW COLUMNS FROM {$table_name} LIKE 'has_color'");
        if ('has_color' != $field_check) {
            $wpdb->query("ALTER TABLE {$table_name} ADD has_color VARCHAR(50) NULL DEFAULT NULL AFTER is_sticky");
        }

        $table_name = $wpdb->prefix.'prm_custom_folder_items';
        if ($wpdb->get_var("show tables like '{$table_name}'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE {$table_name} (
                id BIGINT(20) NOT NULL AUTO_INCREMENT,
                folder_id BIGINT(20) NULL,
                folder_item CHAR(100) NULL,   
                custom_type CHAR(100) NULL, 
                PRIMARY KEY (id)
            ) " . $charset_collate . ";";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
}


$WCP_Folders_for_Plugins = new WCP_Folders_for_Plugins();
