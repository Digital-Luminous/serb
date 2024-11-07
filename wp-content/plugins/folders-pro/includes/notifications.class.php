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

/**
 * Class Folders_Notifications
 *
 * This class is responsible for managing notifications related to folders.
 */
class Folders_Notifications
{
    /**
     * Class constructor.
     *
     * This method is called when an instance of the class is created. It is used to initialize the object.
     *
     * @return void
     */

    public $default_settings = null;


    /**
     * Class constructor.
     *
     * Initializes the object and sets up various actions and filters.
     *
     * @return void
     */
    public function __construct() {
        add_filter("check_for_folders_notification_settings", [$this, "notification_setting"], 10, 1);

        add_action('init', [$this, 'create_mail_log_table']);
        add_action("wp_ajax_wcp_send_test_email", [$this, 'send_test_email']);

        //add(publish) post/page
        add_action('transition_post_status', [$this,'my_custom_first_publish_hook'], 10, 3);
        //add plugin
        add_action('activated_plugin', [$this,'hookPluginActivate'], 50, 2);
        //add attachment
        add_action('add_attachment', [$this,'hookAttachmentAdd'], 50, 5);

        //trash post/page/attachment
        add_action('wp_trash_post', [$this,'hookPostTrash'], 50, 5);
        //delete plugin
        add_action('deactivated_plugin', [$this,'hookPluginDeactivate'], 50, 2);
        //delete
        add_action('before_delete_post', [$this,'my_deleted_post']);
        add_action('delete_attachment', [$this,'my_custom_attachment_deleted_hook']);

        //update post
        add_action('save_post', [$this,'my_custom_post_update_hook'], 10, 3);
        //update attchment
        add_action('edit_attachment', [$this,'my_custom_attachment_edit_hook']);

        //set cron job for send email
        add_filter('cron_schedules',[$this,'folders_cron_schedules']);
        add_action('init',[$this,'set_cron_job']);
        add_action("send_folders_email_notifications", [$this,"send_mail_notification"]);

        add_action("wp_ajax_folders_search_for_users", [$this, "folders_search_for_users"]);

        add_action('untrashed_post', [$this , 'custom_post_restored_hook']);
    }

    /**
     * Search for users in the WordPress users table based on search criteria.
     *
     * @return void
     */
    public function folders_search_for_users()
    {
        $response            = [];
        $response['status']  = 0;
        $response['error']   = 0;
        $response['data']    = [];
        $response['pagination'] = [];
        $response['message'] = "";
        $postData            = filter_input_array(INPUT_POST);
        $errorCounter        = 0;
        if (!isset($postData['nonce']) || empty($postData['nonce'])) {
            $response['message'] = esc_html__("Your request is not valid", 'folders');
            $errorCounter++;
        } else {
            $nonce = sanitize_text_field($postData['nonce']);
            if (!wp_verify_nonce($nonce, 'search_folder_user')) {
                $response['message'] = esc_html__("Your request is not valid", 'folders');
                $errorCounter++;
            }
        }

        if ($errorCounter == 0) {
            $search = sanitize_text_field($postData['search']);
            global $wpdb;
            $userTable = $wpdb->users;

            $perPage = 20;
            $currentPage = sanitize_text_field($postData['paged']);
            $currentPage = (is_numeric($currentPage) && $currentPage > 0) ? intval($currentPage) : 1;
            $start = ($currentPage - 1) * $perPage;

            if (!empty($search)) {
                $query = "SELECT SQL_CALC_FOUND_ROWS users.ID, display_name, user_nicename
                            FROM " . $userTable . " as users
                            WHERE 1=1 AND (user_login LIKE '%" . esc_sql($search) . "%'
                                    OR user_email LIKE '%" . esc_sql($search) . "%'
                                    OR display_name LIKE '%" . esc_sql($search) . "%'
                                    OR user_nicename LIKE '%" . esc_sql($search) . "%')
                            ORDER BY user_login ASC
                            LIMIT {$start}, " . $perPage;
            } else {
                $query = "SELECT SQL_CALC_FOUND_ROWS users.ID, display_name, user_nicename
                            FROM " . $userTable . " as users
                            ORDER BY user_login ASC
                            LIMIT {$start}, " . $perPage;
            }


            $users = $wpdb->get_results($query);
            if(!empty($users)) {
                $response['status'] = 1;
                foreach ($users as $user) {
                    $response['data'][] = [
                        "id" => $user->ID,
                        'display_name' => $user->display_name,
                        'user_nicename' => $user->user_nicename,
                    ];
                }
            }
        }
        echo wp_json_encode($response);
        die;
    }

    /**
     * Retrieves the notification settings.
     *
     * @param array $current_settings The current notification settings.
     *
     * @return array The default notification settings.
     */
    public function notification_setting($current_settings)
    {
        if(!is_null($this->default_settings)) {
            return $this->default_settings;
        }
        $folders_settings = get_option("folders_settings");
        $folders_settings = !is_array($folders_settings)?[]:$folders_settings;
        $post_setting = apply_filters("check_for_folders_post_args", ["show_in_menu" => 1]);
        $post_types = get_post_types( $post_setting, 'objects' );
        $default_post_type = [];
        if(!empty($post_types)) {
            foreach($post_types as $post_type => $setting) {
                if(in_array($post_type, $folders_settings)) {
                    $default_post_type[$post_type] = $setting->label;
                }
            }
        }
        if(in_array("folders4plugins",$folders_settings)) {
            $default_post_type['plugin'] = "Plugins";
        }
        $current_user = wp_get_current_user();
        $user_email = $current_user->user_email;
        $default_settings = [
            'allow_notification' => 'off',
            'notification_email' => [$user_email],
            'mail_setting'       => [
                'on_item_insert'     => [
                    'status'         => 'off',
                    'default'        => $default_post_type,
                    'post_type'      => [],
                    'title'          => esc_html__("Send Notifications when users add any of the following new items", "folders"),
                    'email'          => [
                        'subject'    => "New {post_type} added by {user_name}, {email} - Folders",
                        'content'    => "Activity: {post_type} added\nWhere: {post_type}\nTitle: {post_title}\nPost Status: {post_status}\n{activity_link}"
                    ],
                    'help'           => "Username: {user_name}\nEmail: {email}\nPost type: {post_type}\nPost title: {post_title}\nPost Status: {post_status}\n{activity_link}"
                ],
                'on_item_edit'       => [
                    'status'         => 'off',
                    'post_type'      => [],
                    'default'        => $default_post_type,
                    'title'          => esc_html__("Send Notifications when users making edits to any of the following items", "folders"),
                    'email'          => [
                        'subject'    => "{post_type} edited by {user_name}, {email} - Folders",
                        'content'    => "Activity: {post_type} edited\nWhere: {post_type}\nTitle: {post_title}\n{activity_link}"
                    ],
                    'help'           => "Username: {user_name}\nEmail: {email}\nPost type: {post_type}\nPost title: {post_title}\n{activity_link}"
                ],
                'on_item_remove'     => [
                    'status'         => 'off',
                    'post_type'      => [],
                    'default'        => $default_post_type,
                    'title'          => esc_html__("Send Notifications when users delete/deactivate any of the following items", "folders"),
                    'email'          => [
                        'subject'    => "{post_type} deleted by {user_name}, {email} - Folders",
                        'content'    => "Activity: {post_type} deleted\nWhere: {post_type}\nTitle: {post_title}"
                    ],
                    'help'           => "Username: {user_name}\nEmail: {email}\nPost type: {post_type}\nPost title: {post_title}"
                ],
                'on_creating_folder' => [
                    'status'         => 'off',
                    'post_type'      => [],
                    'default'        => $default_post_type,
                    'title'          => esc_html__("Send Notifications when users add a new folder", "folders"),
                    'email'          => [
                        'subject'    => "New folder added by {user_name}, {email} in {post_type} - Folders",
                        'content'    => "Activity: {post_type} folder added\nWhere: {post_type}\nFolder name: {folder_name}\n{activity_link}"
                    ],
                    'help'           => "Username: {user_name}\nEmail: {email}\nPost type: {post_type}\nFolder name: {folder_name}\n{activity_link}"
                ],
                'on_removing_folder' => [
                    'status'         => 'off',
                    'post_type'      => [],
                    'default'        => $default_post_type,
                    'title'          => esc_html__("Send Notifications when users delete folder", "folders"),
                    'email'          => [
                        'subject'    => "Folder deleted by {user_name}, {email} in {post_type} - Folders",
                        'content'    => "Activity: {post_type} folder deleted\nWhere: {post_type}\nFolder name: {folder_name}"
                    ],
                    'help'           => "Username: {user_name}\nEmail: {email}\nPost type: {post_type}\nFolder name: {folder_name}"
                ],
                'on_moving_folder' => [
                    'status'         => 'off',
                    'post_type'      => [],
                    'default'        => $default_post_type,
                    'title'          => esc_html__("Send Notifications when users move folder", "folders"),
                    'email'          => [
                        'subject'    => "Folder moved by {user_name}, {email} in {post_type} - Folders",
                        'content'    => "Activity: {post_type} folder moved\nWhere: {post_type}\nFolder name: {folder_name}\n{activity_link}"
                    ],
                    'help'           => "Username: {user_name}\nEmail: {email}\nPost type: {post_type}\nFolder name: {folder_name}\n{activity_link}"
                ],
                'on_item_move'       => [
                    'status'         => 'off',
                    'post_type'      => [],
                    'default'        => $default_post_type,
                    'title'          => esc_html__("Send Notifications when users move any of the following items to folder", "folders"),
                    'email'          => [
                        'subject'    => "{post_type} moved by {user_name}, {email} - Folders",
                        'content'    => "Activity: {post_type} moved\nWhere: {post_type}\nTitle: {post_title}\n{activity_link}"
                    ],
                    'help'           => "Username: {user_name}\nEmail: {email}\nPost type: {post_type}\nPost title: {post_title}\nFolder name: {folder_name}\n{activity_link}"
                ],
                'remove_users' => [
                    'status'         => 'off',
                    'users'          => [],
                    'default'        => [],
                    'title'          => esc_html__("Don't Send Notifications when these users make changes", "folders")
                ],
            ]
        ];
        $current_settings = !is_array($current_settings)?[]:$current_settings;

        $this->default_settings = $this->set_default_value($current_settings, $default_settings);
        return $this->default_settings;
    }

    /**
     * Sets default values for missing keys in the current settings array
     * using the provided default settings array recursively.
     *
     * @param array $current_settings The current notification settings.
     * @param array $default_settings The default notification settings.
     *
     * @return array The updated default notification settings.
     */
    function set_default_value($current_settings, $default_settings) {
        if(is_array($default_settings)) {
            foreach ($default_settings as $key => $value) {
                if(isset($current_settings[$key]) && is_array($current_settings[$key]) && $this->is_numeric_array($current_settings[$key])) {
                    $default_settings[$key] = $current_settings[$key];
                } else {
                    if (!is_array($value)) {
                        if (isset($current_settings[$key])) {
                            $default_settings[$key] = $current_settings[$key];
                        }
                    } else {
                        if (!isset($current_settings[$key])) {
                            $default_settings[$key] = $value;
                        } else if (!isset($default_settings[$key])) {
                            $default_settings[$key] = $current_settings[$key];
                        } else {
                            $default_settings[$key] = $this->set_default_value($current_settings[$key], $default_settings[$key]);
                        }
                    }
                }
            }
        } else {
            return $current_settings;
        }
        return $default_settings;
    }

    /**
     * Checks if an array is numeric.
     *
     * @param array $arr The array to be checked.
     *
     * @return bool Returns true if the array is numeric, false otherwise.
     */
    function is_numeric_array(array $arr) {
        if ($arr === []) {
            return true;
        }
        return array_keys($arr) === range(0, count($arr) - 1);
    }

    /**
     * Sends a test email.
     *
     * @return array The response after sending the test email.
     */
    public function send_test_email() {
        $response = [
            'status' => 0,
            'message' => ""
        ];
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : "";
        $nonce = sanitize_text_field($nonce);

        $action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : "";
        $action = sanitize_text_field($action);

        $email = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : "";
        $email = sanitize_text_field($email);

        if ($action == "wcp_send_test_email" && wp_verify_nonce($nonce, 'wp_test_mail')) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response['message'] = esc_html__("Invalid email format");
            } else {
                $subject = esc_html__("Test Email");
                $message = esc_html__("This is test email from Folders");
                $headers = array('Content-Type: text/html; charset=UTF-8');
                $status = wp_mail( $email, $subject, $message, $headers );
                if($status) {
                    $response['message'] = esc_html__("Message sent successfully");
                    $response['status'] = 1;
                } else {
                    $response['message'] = esc_html__("Could not send email");
                }
            }
        } else {
            $response['message'] = esc_html__("Invalid Request, Please try again");
        }
        echo wp_json_encode($response);
        die;
    }

    /**
     * Check if email notification should be sent and send the email if necessary.
     *
     * @param int $postId The ID of the post.
     * @param int $folderId The ID of the folder.
     * @param string $post_type The type of the post.
     * @param string $folder_title The title of the folder.
     * @param string $post_title The title of the post.
     * @param string $post_status The status of the post.
     * @param string $action The action being performed.
     *
     * @return void
     */
    public static function check_for_email($postId, $folderId, $post_type, $folder_title, $post_title, $post_status, $action) {

        $user_id     = get_current_user_id();
        $notification_setting = get_option('folders_notification_settings');
        $notification_setting = apply_filters('check_for_folders_notification_settings', $notification_setting);
        $plugin_name = "";
        if($post_type != "plugin") {
            $post_type_detail = get_post_type_object($post_type);
            $post_type_label = $post_type_detail->labels->singular_name;
        } else {
            $post_type_label = "Plugin";
        }
        if(isset($notification_setting['allow_notification']) && $notification_setting['allow_notification'] == "on") {
            if (isset($notification_setting['mail_setting']['remove_users']['status']) && $notification_setting['mail_setting']['remove_users']['status'] == "on") {
                if(!empty($notification_setting['mail_setting']['remove_users']['users'])) {

                    if (in_array($user_id, $notification_setting['mail_setting']['remove_users']['users'])) {
                        return;
                    }
                }
            }

            if(isset($notification_setting['mail_setting'][$action]['status']) && $notification_setting['mail_setting'][$action]['status'] == "on") {
                if(isset($notification_setting['mail_setting'][$action]['post_type'])) {
                    if (in_array($post_type, $notification_setting['mail_setting'][$action]['post_type'])) {

                        global $current_user;
                        $subject = $notification_setting['mail_setting'][$action]['email']['subject'];
                        $subject = str_replace(["{post_type}","{user_name}","{email}","{post_title}","{post_status}","{folder_name}"],[$post_type_label,$current_user->display_name,$current_user->user_email,$post_title,$post_status,$folder_title],$subject);

                        $content_get = $notification_setting['mail_setting'][$action]['email']['content'];
                        $activity_link =  isset($_SERVER['HTTP_REFERER'])?esc_url($_SERVER['HTTP_REFERER']):"";
                        if(!empty($activity_link)) {
                            $activity_link = "<a href='".$activity_link."'>".esc_html__('Click here to see the changes', "folders")."</a>";
                        }
                        if($action == "on_item_insert" || $action == "on_item_edit") {
                            $link = admin_url("post.php?post=".$postId."&action=edit");
                            $activity_link = "<a href='".$link."'>".esc_html__('Click here to see the changes', "folders")."</a>";
                        } elseif($action == "on_item_remove") {
                            if($post_type == "attachment") {
                                $activity_link = "<a href='".esc_url(admin_url( 'upload.php'))."'>".esc_html__('Click here to see the changes', "folders")."</a>";
                            } else if($post_type == "post") {
                                $activity_link = "<a href='".esc_url(admin_url( 'edit.php'))."'>".esc_html__('Click here to see the changes', "folders")."</a>";
                            } else {
                                $activity_link = "<a href='".esc_url(admin_url( 'edit.php?post_type='.$post_type))."'>".esc_html__('Click here to see the changes', "folders")."</a>";
                            }
                        }
                        if(!empty($post_title) && !empty($postId)) {
                            if($post_type == "attachment" && $action == "on_item_remove") {
                                $post_title = "<a href='".esc_url(admin_url( 'upload.php'))."' >".$post_title."</a>";
                            } else if ($post_type != "attachment" && $action == "on_item_remove") {
                                $post_title = "<a href='".esc_url(admin_url( 'edit.php?post_type='.$post_type ))."' >".$post_title."</a>";
                            } else if ($post_type == "attachment" && ($action == "on_item_insert" || $action == "on_item_edit")) {
                                $imageURL = wp_get_attachment_image_url($postId, 'full');
                                $post_title = "<a href='".esc_url($imageURL)."' >".$post_title."</a>";
                            } else {
                                $post_title = "<a href='".esc_url(get_permalink($postId))."' >".$post_title."</a>";
                            }
                        }
                        $content = str_replace(["{post_type}","{user_name}","{email}","{post_title}","{post_status}","{folder_name}","{activity_link}"],[$post_type_label,$current_user->display_name,$current_user->user_email,$post_title,$post_status,$folder_title,$activity_link],$content_get);

                        global $wpdb;

                        if($post_type == 'plugin') {
                            $plugin_name = $post_title;
                        }
                        $table_name = $wpdb->prefix . "fldr_email_logs";
                        $insert = [
                            'user_id'      => $user_id,
                            'post_id'      => $postId,
                            'folder_id'    => $folderId,
                            'plugin_name'  => $plugin_name,
                            'subject'      => $subject,
                            'message'      => $content,
                            'is_mail_sent' => 0,
                            'action_date'  => gmdate("Y-m-d H:i:s")

                        ];
                        $wpdb->insert($table_name, $insert);
                    }
                }
            }
        }
    }


    /**
     * Creates a mail log table if it does not exist in the database.
     *
     * @return void
     */
    public function create_mail_log_table(){
        global $wpdb;
        $table_name = $wpdb->prefix . "fldr_email_logs";
        if ($wpdb->get_var("show tables like '{$table_name}'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE {$table_name} (
                id BIGINT(20) NOT NULL AUTO_INCREMENT,
                user_id BIGINT(20) NULL,
                post_id BIGINT(20) NULL,
                folder_id BIGINT(20) NULL,
                plugin_name VARCHAR(255) NULL,
                subject LONGTEXT NULL,
                message LONGTEXT NULL,
                is_mail_sent TINYTEXT NULL,
                action_date DATETIME NULL,
                PRIMARY KEY (id)
            ) " . $charset_collate . ";";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    /**
     * Sets a cron job to send email notifications every minute if it is not already scheduled.
     *
     * @return void
     */
    public function set_cron_job() {
        if (!wp_next_scheduled ( 'send_folders_email_notifications' )) {
            wp_schedule_event(time(), 'every_10_minutes', 'send_folders_email_notifications');
        }
    }

    /**
     * Sends email notifications to users based on the mail log table in the database.
     *
     * @return void
     */
    public function send_mail_notification() {
        global $wpdb;
        $table_name = $wpdb->prefix . "fldr_email_logs";
        $current_time = gmdate("Y-m-d H:i:s");

        $query = "SELECT user_id, count(id) as total_records 
                    FROM {$table_name} 
                    WHERE is_mail_sent = 0 AND action_date <= '{$current_time}'
                    GROUP BY user_id";
        $results = $wpdb->get_results($query);
        if(count($results) > 0) {
            foreach($results as $result) {
                $query = "SELECT * FROM {$table_name} 
                    WHERE is_mail_sent = 0 
                      AND action_date <= '{$current_time}'
                      AND user_id <= '{$result->user_id}'
                    ORDER BY action_date ASC";
                $records = $wpdb->get_results($query);
                $mail_content = "";
                $mail_subject = "";
                if(count($records) == 1) {
                    $record = $records[0];
                    $mail_subject = $record->subject;
                    $mail_content = nl2br($record->message);
                } else if(count($records)) {
                    $user = get_user_by("id", $result->user_id);
                    $mail_subject = "New activity by ".$user->display_name." - Folders";
                    foreach($records as $record) {
                        $mail_content .= nl2br($record->message)."<br/><br/>";
                    }
                }
                if(count($records)) {

                    $query = "UPDATE {$table_name} 
                            SET is_mail_sent = 1 
                            WHERE user_id = %d AND action_date <= '%s'";
                    $query = $wpdb->prepare($query, [$result->user_id, $current_time]);
                    $wpdb->query($query);

                    $message = nl2br($message);
                    $headers = "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/ht  ml; charset=UTF-8\r\n";
                    $headers .= 'From: no-reply@' . $_SERVER['HTTP_HOST'] . PHP_EOL;
                    $headers .= 'X-Mailer: PHP/' . phpversion();
                    $notification_setting = get_option('folders_notification_settings');
                    if (isset($notification_setting['notification_email']) && is_array($notification_setting['notification_email']) && !empty($notification_setting['notification_email'])) {
                        foreach ($notification_setting['notification_email'] as $key => $notification_email) {
                            wp_mail($notification_email, $mail_subject, $mail_content, $headers);
                        }
                    }
                }
            }
        }
    }

    /**
     * Registers a custom cron schedule if it does not exist.
     *
     * @param array $schedules The existing cron schedules.
     * @return array The updated cron schedules array.
     */
    public function folders_cron_schedules($schedules) {
        if(!isset($schedules['every_10_minutes'])) {
            $schedules['every_10_minutes'] = array(
                'interval' => 60*10,
                'display' => __('Every 10 minute')
            );
        }
        return $schedules;
    }

    //Hook Functions

    /**
     * Executes when a post is published for the first time.
     * Checks for email and sets a transient if the new status is "publish".
     *
     * @param string $new_status The new post status.
     * @param string $old_status The old post status.
     * @param WP_Post $post The post object.
     * @return void
     */
    public function my_custom_first_publish_hook($new_status, $old_status, $post) {
        if('publish' === $new_status && 'publish' !== $old_status) {
            self::check_for_email($post->ID,0,$post->post_type, "", $post->post_title,$post->post_status,"on_item_insert");
            set_transient("folders_post_published",1);
        }
    }

    /**
     * Handle attachment add hook.
     *
     * @param int $id The ID of the attachment.
     * @return void
     */
    public function hookAttachmentAdd($id = 0) {
        $data = get_post($id);
        $title = "";
        if ($data) {
            $id = $data->ID;
            $title = $data->post_title;
        }
        self::check_for_email($id,0,$data->post_type, "", $title,$data->post_status,"on_item_insert");
    }

    /**
     * Hook function for the post trash action.
     *
     * @param int $id The ID of the post being trashed.
     * @return void
     */
    public function hookPostTrash($id= 0) {
        $data = get_post($id);
        $title = "";
        if ($data) {
            $id = $data->ID;
            $title = $data->post_title;
        }
        self::check_for_email($id,0,$data->post_type, "", $title,$data->post_status,"on_item_remove");
    }

    /**
     * Handles the plugin deactivation hook.
     *
     * @param string $plugin The file path of the plugin to be deactivated.
     * @param string $network_activation Optional. Specifies whether the plugin is being deactivated for a network. Default empty string.
     *
     * @return void
     */
    public function hookPluginDeactivate($plugin = '', $network_activation = '') {
        $filename = WP_PLUGIN_DIR . '/' . $plugin;
        $info = get_plugin_data($filename);
        self::check_for_email(0,0,'plugin', "", $info['Name'],'',"on_item_remove");
    }

    /**
     * Executes custom actions when a post is updated.
     *
     * @param int $post_id The ID of the post being updated.
     * @param WP_Post $post The updated post object.
     * @param bool $update Whether this is an update or a new post.
     *
     * @return void
     */
    public function my_custom_post_update_hook($post_id, $post, $update) {
        // Check if this is not an autosave
        $isPublished = get_transient("folders_post_published");
        if (!$update || wp_is_post_autosave($post_id) || wp_is_post_revision($post_id) || $isPublished) {
            delete_transient("folders_post_published");
            return;
        }
        if ( isset( $_POST['action'] ) && $_POST['action'] == 'editpost' ) {
            self::check_for_email($post_id, 0, $post->post_type, "", $post->post_title, '', "on_item_edit");
            delete_transient("folders_post_published");
        }
    }

    function custom_post_restored_hook($post_id) {
        $post = get_post($post_id);
        self::check_for_email($post_id, 0, $post->post_type, "", $post->post_title, '', "on_item_edit");
    }


    /**
     * Performs necessary actions when a custom attachment is edited.
     *
     * @param int $id The ID of the attachment being edited.
     * @return void
     */
    public function my_custom_attachment_edit_hook($id) {
        $post = get_post($id);
        if ( $post->post_status === 'inherit' && $post->post_status !== 'trash' ) {
            if (get_post_type($id) === 'attachment') {
                $data = get_post($id);
                $title = "";
                if ($data) {
                    $id = $data->ID;
                    $title = $data->post_title;
                }
                self::check_for_email($id, 0, $data->post_type, "", $title, $data->post_status, "on_item_edit");
            }
        }
    }

    /**
     * Executes specific actions when a plugin is activated.
     *
     * @param string $plugin The path to the plugin file.
     * @param string $network_activation Whether the plugin is being network activated or not.
     * @return void
     */
    public function hookPluginActivate($plugin = '', $network_activation = '')
    {
        $filename = WP_PLUGIN_DIR . '/' . $plugin;
        $info = get_plugin_data($filename);
        self::check_for_email(0,0,'plugin', "", $info['Name'],'',"on_item_insert");
    }

    /**
     * Calls check_for_email with the data of a deleted post.
     *
     * @param int $id The ID of the deleted post.
     * @return void
     */
    public function my_deleted_post($id) {
        if ( (isset( $_POST['action'] ) && ($_POST['action'] == 'trash' || $_POST['action'] == 'delete-post')) || (isset( $_GET['action'] ) && $_GET['action'] == 'delete') ) {
            $data = get_post($id);
            $title = "";
            if ($data) {
                $id = $data->ID;
                $title = $data->post_title;
            }
            self::check_for_email($id, 0, $data->post_type, "", $title, $data->post_status, "on_item_remove");
        }
    }

    /**
     * Executes custom logic when a attachment is deleted.
     *
     * @param int $id The ID of the attachment being deleted.
     * @return void
     */
    public function my_custom_attachment_deleted_hook($id) {
        if ( (isset( $_POST['action'] ) && ($_POST['action'] == 'trash' || $_POST['action'] == 'delete-post') ) || (isset( $_GET['action'] ) && $_GET['action'] == 'delete') ) {
            if (get_post_type($id) == 'attachment') {
                $data = get_post($id);
                $title = "";
                if ($data) {
                    $id = $data->ID;
                    $title = $data->post_title;
                }
                self::check_for_email($id, 0, $data->post_type, "", $title, $data->post_status, "on_item_remove");
            }
        }
    }


}
if(class_exists("Folders_Notifications")) {
    $Folders_Notifications = new Folders_Notifications();
}
