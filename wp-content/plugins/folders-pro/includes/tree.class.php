<?php
/**
 * Class Folders Tree
 *
 * @author  : Premio <contact@premio.io>
 * @license : GPL2
 * */

if (! defined('ABSPATH')) {
    exit;
}

// Free/Pro Class name Change
class WCP_Pro_Tree
{


    /**
     * Define the core functionality to shoe taxonomies
     *
     * @since 1.0.0
     */
    public function __construct()
    {

    }//end __construct()


    /**
     * Get tree data into taxonomies (Root Folder)
     *
     * @since  1.0.0
     * @access public
     * @return $categories
     */
    public static function get_full_tree_data($post_type, $order_by="", $order="", $sticky_open=0, $user_id=false)
    {
        $isAjax = (defined('DOING_AJAX') && DOING_AJAX) ? 1 : 0;
        $type   = folders_sanitize_text($post_type, 'get');
        if ((isset($type) && !empty($type)) || ! $isAjax) {
            update_option("selected_".$post_type."_folder", "");
        }

        if ($user_id) {
            $user_id    = get_current_user_id();
            $user_meta  = get_userdata($user_id);
            $user_roles = $user_meta->roles;
            $user_roles = !is_array($user_roles) ? [] : $user_roles;
            if (in_array("administrator", $user_roles)) {
                $user_id = false;
            }
        }

        return self::get_folder_category_data($post_type, 0, 0, $order_by, $order, $sticky_open, $user_id);

    }//end get_full_tree_data()


    /**
     * Get tree data into taxonomies (Root Folder)
     *
     * @since  1.0.0
     * @access public
     * @return $categories
     */
    public static function get_full_custom_tree_data($post_type, $order_by="", $order="", $sticky_open=0, $user_id=false)
    {
        $isAjax = (defined('DOING_AJAX') && DOING_AJAX) ? 1 : 0;
        $type   = folders_sanitize_text($post_type, 'get');
        if ((isset($type) && !empty($type)) || ! $isAjax) {
            update_option("selected_".$post_type."_folder", "");
        }

        if ($user_id) {
            $user_id    = get_current_user_id();
            $user_meta  = get_userdata($user_id);
            $user_roles = $user_meta->roles;
            $user_roles = !is_array($user_roles) ? [] : $user_roles;
            if (in_array("administrator", $user_roles)) {
                $user_id = false;
            }
        }

        return self::get_custom_folder_data($post_type, 0, 0, $order_by, $order, $sticky_open, $user_id);

    }//end get_full_tree_data()


    /**
     * Get tree data into taxonomies (Child Folder)
     *
     * @since  1.0.0
     * @access public
     * @return $categories
     */
    public static function get_custom_folder_data($post_type, $parent=0, $parentStatus=0, $order_by="", $order="", $sticky_open=0, $user_id=false)
    {

        global $wpdb;
        $table_name = $wpdb->prefix.'prm_custom_folders';
        $item_table = $wpdb->prefix.'prm_custom_folder_items';
        $query = "SELECT P.*, COUNT(I.id) as total_child, COUNT(I.id) as trash_count FROM ".$table_name." AS P
            LEFT JOIN ".$item_table." AS I ON P.id = I.folder_id
            WHERE P.custom_type = %s AND P.parent_id = %d
            GROUP BY P.id";
        if(!empty($order_by) && !empty($order_by)) {
            $query .= " ORDER BY {$order_by} {$order}";
        }
        $query = $wpdb->prepare($query, [$post_type, $parent]);
        $records = $wpdb->get_results($query);

        $string        = "";
        $sticky_string = "";
        $child         = 0;

        if(!empty($records)) {
            $custom_order = 1;
            foreach($records as $key=>$record) {
                $return = self::get_custom_folder_data($post_type, $record->id, $record->is_active, $order_by, $order, $sticky_open, $user_id);

                $nonce     = wp_create_nonce('wcp_folder_term_'.$record->id);
                $is_active = intval($record->is_active);
                $is_sticky = intval($record->is_sticky);
                $class     = "";
                if ($is_active == 1 || ($is_sticky && $sticky_open)) {
                    $class .= " jstree-open";
                }
                $count = 0;

                if(isset($record->title_slug)) {
                    $string .= "<li id='{$record->id}' class='{$class}' data-slug='{$record->title_slug}' data-nonce='{$nonce}' data-folder='{$record->id}' data-child='{$record->total_child}' data-count='{$count}' data-parent='{$parent}'>
                                {$record->title}
                                <ul>{$return['string']}</ul>
                            </li>";
                }

                if($order_by != "P.folder_order") {
                    global $wpdb;
                    $table = $wpdb->prefix."prm_custom_folders";
                    $wpdb->update($table, ["folder_order" => $custom_order], ["id" => $record->id]);
                    $custom_order++;
                }

                $sticky_string .= $return['sticky_string'];
            }
        }

        return [
            'string'        => $string,
            'sticky_string' => $sticky_string,
            'child'         => $child,
        ];

    }//end get_folder_category_data()


    /**
     * Get tree data into taxonomies (Child Folder)
     *
     * @since  1.0.0
     * @access public
     * @return $categories
     */
    public static function get_folder_category_data($post_type, $parent=0, $parentStatus=0, $order_by="", $order="", $sticky_open=0, $user_id=false)
    {
        $arg = [
            'hide_empty'            => false,
            'parent'                => $parent,
            'hierarchical'          => false,
            'update_count_callback' => '_update_generic_term_count',
        ];
        if (!empty($order_by) && !empty($order)) {
            $arg['orderby'] = $order_by;
            $arg['order']   = $order;

            if ($user_id) {
                $arg['meta_query'] = [
                    [
                        'key'   => 'created_by',
                        'type'  => '=',
                        'value' => $user_id,
                    ],
                ];
            }
        } else {
            $arg['orderby'] = 'meta_value_num';
            $arg['order']   = 'ASC';
            if ($user_id) {
                $arg['meta_query'] = [
                    [
                        'key'  => 'wcp_custom_order',
                        'type' => 'NUMERIC',
                    ],
                    [
                        'key'   => 'created_by',
                        'type'  => '=',
                        'value' => $user_id,
                    ],
                ];
            } else {
                $arg['meta_query'] = [
                    [
                        'key'  => 'wcp_custom_order',
                        'type' => 'NUMERIC',
                    ],
                ];
            }
        }//end if

        $arg['taxonomy'] = $post_type;

        $terms = get_terms($arg);

        $string        = "";
        $sticky_string = "";
        $child         = 0;
        $isAjax        = (defined('DOING_AJAX') && DOING_AJAX) ? 1 : 0;
        if (!empty($terms)) {
            $child = count($terms);
            foreach ($terms as $key => $term) {
                if (!empty($order_by) && !empty($order)) {
                    update_term_meta($term->term_id, "wcp_custom_order", ($key + 1));
                }

                $folder_info    = get_term_meta($term->term_id, "folder_info", true);
                $folder_info = shortcode_atts([
                    'is_sticky' => 0,
                    'is_high'   => 0,
                    'is_locked' => 0,
                    'is_active' => 0,
                ], $folder_info);

                $status = intval($folder_info['is_active']);


                $return = self::get_folder_category_data($post_type, $term->term_id, $status, $order_by, $order, $sticky_open, $user_id);
                $type   = folders_sanitize_text($post_type, 'get');
                if ($post_type == "attachment") {
                    if (isset($type) && $type == $term->slug) {
                        update_option("selected_".$post_type."_folder", $term->term_id);
                    }
                } else {
                    if (isset($type) && $type == $term->slug) {
                        update_option("selected_".$post_type."_folder", $term->term_id);
                    }
                }

                $count = ($term->trash_count != 0) ? $term->trash_count : 0;

                // Free/Pro URL Change
                $nonce     = wp_create_nonce('wcp_folder_term_'.$term->term_id);
                $is_active = intval($folder_info['is_active']);
                $is_sticky = intval($folder_info['is_sticky']);
                $class     = "";
                if ($is_active == 1 || ($is_sticky && $sticky_open)) {
                    $class .= " jstree-open";
                }

                $string .= "<li id='{$term->term_id}' class='{$class}' data-slug='{$term->slug}' data-nonce='{$nonce}' data-folder='{$term->term_id}' data-child='{$child}' data-count='{$count}' data-parent='{$parent}'>
                                {$term->name}
                                <ul>{$return['string']}</ul>
                            </li>";

                $sticky_string .= $return['sticky_string'];
            }//end foreach
        }//end if

        return [
            'string'        => $string,
            'sticky_string' => $sticky_string,
            'child'         => $child,
        ];

    }//end get_folder_category_data()


    /**
     * Get option data into taxonomies (Parent Folder)
     *
     * @since  1.0.0
     * @access public
     * @return $categories
     */
    public static function get_option_data_for_select($post_type)
    {
        $string  = "<option value='0'>Parent Folder</option>";
        $string .= self::get_folder_option_data($post_type, 0, '');
        return $string;

    }//end get_option_data_for_select()


    /**
     * Get option data into taxonomies (Child Folder)
     *
     * @since  1.0.0
     * @access public
     * @return $categories
     */
    public static function get_folder_option_data($post_type, $parent=0, $space="", $folder_by_user=0)
    {
        $args = [
            'hide_empty'   => false,
            'parent'       => $parent,
            'orderby'      => 'meta_value_num',
            'order'        => 'ASC',
            'hierarchical' => false,
        ];

        if ($folder_by_user) {
            $args['meta_query'] = [
                [
                    'key'  => 'wcp_custom_order',
                    'type' => 'NUMERIC',
                ],
                [
                    'key'   => 'created_by',
                    'type'  => '=',
                    'value' => $folder_by_user,
                ],
            ];
        } else {
            $args['meta_query'] = [
                [
                    'key'  => 'wcp_custom_order',
                    'type' => 'NUMERIC',
                ],
            ];
        }

        $args['taxonomy'] = $post_type;
        $terms = get_terms($args);

        $selected_term = get_option("selected_".$post_type."_folder");

        $string = "";
        if (!empty($terms)) {
            foreach ($terms as $term) {
                $selected = ($selected_term == $term->term_id) ? "selected" : "";
                $string  .= "<option {$selected} value='{$term->term_id}'>{$space}{$term->name}</option>";
                $string  .= self::get_folder_option_data($post_type, $term->term_id, trim($space)."- ");
            }
        }

        return $string;

    }//end get_folder_option_data()


}//end class
