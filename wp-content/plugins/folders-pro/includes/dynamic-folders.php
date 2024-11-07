<?php
/**
 * Class Dynamic Folders
 *
 * @author  : Premio <contact@premio.io>
 * @license : GPL2
 * */

if (! defined('ABSPATH')) {
    exit;
}

class Premio_Pro_Folders_Dynamic_Folders
{

    /**
     * The Name of this plugin.
     *
     * @var    string    $post_type    Post type
     * @since  1.0.0
     * @access public
     */
    var $post_type;

    /**
     * The Name of this plugin.
     *
     * @var    string    $taxonomy    Taxonomy of Post type
     * @since  1.0.0
     * @access public
     */
    var $taxonomy;

    /**
     * The Name of this plugin.
     *
     * @var    boolean    $has_dynamic_folders    Dynamic folder status
     * @since  1.0.0
     * @access public
     */
    var $has_dynamic_folders = 0;


    /**
     * Define the core functionality of the plugin.
     *
     * Check for Dynamic folders functionality status
     * Load the posts as per type
     * the public-facing side of the site.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $customize_folders         = get_option('customize_folders');
        $this->has_dynamic_folders = (!isset($customize_folders['dynamic_folders']) || (isset($customize_folders['dynamic_folders']) && $customize_folders['dynamic_folders'] == "on")) ? 1 : 0;

        if ($this->has_dynamic_folders) {
            if (isset($customize_folders['dynamic_folders_for_admin_only']) && $customize_folders['dynamic_folders_for_admin_only'] == "on") {
                if (function_exists("wp_get_current_user")) {
                    $user       = wp_get_current_user();
                    $user_roles = (array) $user->roles;
                    $user_roles = !is_array($user_roles) ? [] : $user_roles;
                    if (!in_array("administrator", $user_roles)) {
                        $this->has_dynamic_folders = 0;
                    }
                }
            }
        }

        if ($this->has_dynamic_folders) {
            add_action('pre_get_posts', [$this, 'pre_get_posts']);
        }

    }//end __construct()


    /**
     * Filter the posts as per date, author ot type
     *
     * @since  1.0.0
     * @access public
     * @return $query
     */
    public function pre_get_posts($query)
    {
        $post_type      = isset($_REQUEST['post_type']) ? sanitize_text_field($_REQUEST['post_type']) : "";
        $dynamic_type   = isset($_REQUEST['ajax_action']) && ($_REQUEST['ajax_action'] == "premio_dynamic_folders") ? 1 : 0;
        $dynamic_folder = isset($_REQUEST['dynamic_folder']) && !empty($_REQUEST['dynamic_folder']) ? $_REQUEST['dynamic_folder'] : 0;
        if (!empty($post_type) && !empty($dynamic_type) && $dynamic_folder) {
            $dynamic_folder       = str_replace("_anchor", "", $dynamic_folder);
            $dynamic_folder_array = explode("-", $dynamic_folder);
            $filter_type          = isset($dynamic_folder_array[0]) ? $dynamic_folder_array[0] : "";
            $filter_value         = isset($dynamic_folder_array[1]) ? $dynamic_folder_array[1] : "";
            if (!empty($filter_type) && !empty($filter_value)) {
                if ($filter_type == "author") {
                    if ($filter_value != "all") {
                        $query->query_vars['author'] = $filter_value;
                    }
                } else if ($filter_type == "dates") {
                    if ($filter_value != "all") {
                    }
                } else if ($filter_type == "year") {
                    $query->query_vars['year'] = $filter_value;
                } else if ($filter_type == "month") {
                    $query->query_vars['year'] = $filter_value;
                    if (isset($dynamic_folder_array[2])) {
                        $query->query_vars['monthnum'] = $dynamic_folder_array[2];
                    }
                } else if ($filter_type == "day") {
                    $query->query_vars['year'] = $filter_value;
                    if (isset($dynamic_folder_array[2])) {
                        $query->query_vars['monthnum'] = $dynamic_folder_array[2];
                    }

                    if (isset($dynamic_folder_array[3])) {
                        $query->query_vars['day'] = $dynamic_folder_array[3];
                    }
                } else if ($filter_type == "pages_parent") {
                    if ($filter_value == "all") {
                        $results = $this->get_root_hierarchy_array();
                        if (!empty($results)) {
                            $post_ids = [];
                            foreach ($results as $row) {
                                $post_ids[] = $row->ID;
                            }

                            $query->query_vars['post__in'] = $post_ids;
                        } else {
                            $query->query_vars['post__in'] = [0];
                        }
                    } else {
                        $results = $this->get_child_pages_array($filter_value);
                        if (!empty($results)) {
                            $post_ids = [];
                            foreach ($results as $row) {
                                $post_ids[] = $row->ID;
                            }

                            $query->query_vars['post__in'] = $post_ids;
                        } else {
                            $query->query_vars['post__in'] = [0];
                        }
                    }//end if
                } else if ($filter_type == "post_category") {
                    if ($filter_value != "all") {
                        $category__in   = [];
                        $category__in[] = $filter_value;
                        $query->query_vars['category__in'] = $category__in;
                    }
                } else if ($filter_type == "extensions") {
                    if ($filter_value != "all") {
                        $extension = $this->get_file_ext_dynamic_folders("attachment", "", "a");
                        if (isset($extension[$filter_value])) {
                            $query->query_vars['post_mime_type'] = $extension[$filter_value];
                        }
                    }
                }//end if
            }//end if
        }//end if

        return $query;

    }//end pre_get_posts()


    /**
     * Get the posts by all categories
     *
     * @since  1.0.0
     * @access public
     * @return $string
     */
    public function get_post_category_dynamic_folders($post_type, $taxonomy)
    {
        if ($post_type != 'post') {
            return "";
        }

        $string = "";

        if (!$this->has_dynamic_folders) {
            return $string;
        }

        $class = "";
        if (get_option("folders_".$post_type."post_category-all") == 1) {
            $class = " jstree-open";
        }

        $string .= "<li class='".esc_attr($class)."' id='post_category-all' data-slug='dates-all' >".esc_html__("All Categories", "folders");
        $string .= "<ul>";
        $string .= $this->get_category_list(0, $post_type);
        $string .= "</ul>";
        $string .= "</li>";
        return $string;

    }//end get_post_category_dynamic_folders()


    /**
     * Get the categories list
     *
     * @since  1.0.0
     * @access public
     * @return $string
     */
    public function get_category_list($cat, $post_type)
    {
        $next   = get_categories('hide_empty=true&orderby=name&order=ASC&parent='.$cat);
        $string = "";
        if (!$this->has_dynamic_folders) {
            return $string;
        }

        if ($next) :
            foreach ($next as $cat) :
                $class = "";
                if (get_option("folders_".$post_type."post_category-".$cat->term_id) == 1) {
                    $class = " jstree-open";
                }

                $string .= "<li class='".esc_attr($class)."' id='post_category-{$cat->term_id}'>".$cat->name;
                $string .= "<ul>";
                $string .= $this->get_category_list($cat->term_id, $post_type);
                $string .= "</ul>";
                $string .= "</li>";
            endforeach;
        endif;

        return $string;

    }//end get_category_list()


    /**
     * Get the media extensions
     *
     * @since  1.0.0
     * @access public
     * @return $string
     */
    public function get_file_ext_dynamic_folders($post_type, $taxonomy, $return="s")
    {

        if (!$this->has_dynamic_folders) {
            if ($return == "a") {
                return [];
            }

            return "";
        }

        global $wpdb;

        if ('attachment' == $post_type) {
            $results = $wpdb->get_results("SELECT DISTINCT(post_mime_type) FROM {$wpdb->posts} WHERE post_mime_type != '' ORDER BY post_mime_type ASC");
        } else {
            if ($return == "a") {
                return [];
            }

            return "";
        }

        $extList = [];
        if (!empty($results)) {
            foreach ($results as $row) {
                if (!empty($row->post_mime_type)) {
                    $mime_type           = strtolower($row->post_mime_type);
                    $mime_type           = explode("/", $mime_type);
                    $mime_type           = array_pop($mime_type);
                    $extList[$mime_type] = $row->post_mime_type;
                }
            }
        }

        if ($return == "a") {
            return $extList;
        } else {
            $string = "";
            $class  = "";
            if (get_option("folders_".$post_type."extensions-all") == 1) {
                $class = " jstree-open";
            }

            $string .= "<li class='".esc_attr($class)."' id='extensions-all' data-slug='extensions-all' >".esc_html__("All Extensions", "folders");
            $string .= "<ul>";
            foreach ($extList as $key => $value) {
                $string .= "<li id='extensions-".$key."'>";
                $string .= ".".$key;
                $string .= "</li>";
            }

            $string .= "</ul>";
            $string .= "</li>";
            return $string;
        }//end if

    }//end get_file_ext_dynamic_folders()


    /**
     * Get posts by date, month, year
     *
     * @since  1.0.0
     * @access public
     * @return $string
     */
    public function get_date_dynamic_folders($post_type, $taxonomy, $return="s")
    {
        global $wpdb;

        if (!$this->has_dynamic_folders) {
            if ($return == "a") {
                return [];
            }

            return "";
        }

        $cache_key = [];
        $years     = [];
        $folders   = [];

        if ('attachment' == $post_type) {
            $results = $wpdb->get_results("SELECT post_date FROM {$wpdb->posts} WHERE post_type = 'attachment' ORDER BY post_date ASC");
        } else {
            $results = $wpdb->get_results($wpdb->prepare("SELECT post_date FROM {$wpdb->posts} WHERE post_type = %s AND post_status NOT IN ('trash', 'auto-draft') ORDER BY post_date ASC", $post_type));
        }

        foreach ($results as $row) {
            // Skip blank dates
            if ('0000-00-00 00:00:00' == $row->post_date) {
                continue;
            }

            $timezone = wp_timezone();

            $date = new DateTime($row->post_date, $timezone);

            $year  = $date->format('Y');
            $month = $date->format('m');
            $day   = $date->format('d');

            // $dates[ $year ][ $month ][ $day ] = array();
            if (! isset($years[$year])) {
                $years[$year] = [
                    'year'   => $year,
                    'name'   => $year,
                    'months' => [],
                ];
            }

            if (! isset($years[$year]['months'][$month])) {
                $years[$year]['months'][$month] = [
                    'month' => $month,
                    'name'  => $date->format('F'),
                    'days'  => [],
                ];
            }

            if (! isset($years[$year]['months'][$month]['days'][$day])) {
                $years[$year]['months'][$month]['days'][$day] = [
                    'day'  => $day,
                    'name' => $date->format('j'),
                ];
            }
        }//end foreach

        $string = "";

        if (!empty($results)) {
            $class = "";
            if (get_option("folders_".$post_type."dates-all") == 1) {
                $class = " jstree-open";
            }

            $string   .= "<li id='dates-all' class='".esc_attr($class)."' data-slug='dates-all' >".esc_html__("All Dates", "folders");
            $string   .= "<ul>";
            $folders[] = [
                'name'  => esc_html__("All Dates", "folders"),
                'value' => 'dates-all',
            ];
            // Create our folders
            foreach ($years as $year) {
                $year_id = $year['year'];
                $class   = "";
                if (get_option("folders_".$post_type."year-".$year['year']) == 1) {
                    $class = " jstree-open";
                }

                $string   .= "<li class='".esc_attr($class)."' id='year-".$year['year']."'>";
                $string   .= $year['name'];
                $folders[] = [
                    'name'  => "- ".$year['name'],
                    'value' => 'year-'.$year['year'],
                ];
                if (isset($year['months']) && !empty($year['months'])) {
                    $string .= "<ul>";
                    foreach ($year['months'] as $month) {
                        $month_id = $month['month'];
                        $class    = "";
                        if (get_option("folders_".$post_type."month-".$year_id."-".$month['month']) == 1) {
                            $class = " jstree-open";
                        }

                        $string   .= "<li id='month-".$year_id."-".$month['month']."' class='".esc_attr($class)."'>";
                        $string   .= $month['name'];
                        $folders[] = [
                            'name'  => "-- ".$month['name'],
                            'value' => 'month-'.$year_id."-".$month['month'],
                        ];
                        if (isset($month['days']) && !empty($month['days'])) {
                            $string .= "<ul>";
                            foreach ($month['days'] as $days) {
                                $string   .= "<li id='day-".$year_id."-".$month_id."-".$days['day']."'>";
                                $string   .= $days['name'];
                                $string   .= "</li>";
                                $folders[] = [
                                    'name'  => "--- ".$days['name'],
                                    'value' => 'day-'.$year_id."-".$month_id."-".$days['day'],
                                ];
                            }

                            $string .= "</ul>";
                        }

                        $string .= "</li>";
                    }//end foreach

                    $string .= "</ul>";
                }//end if

                $string .= "</li>";
            }//end foreach

            $string .= "</ul>";
            $string .= "</li>";
        }//end if

        if ($return == "s") {
            return $string;
        } else {
            return $folders;
        }

    }//end get_date_dynamic_folders()


    /**
     * Get author list
     *
     * @since  1.0.0
     * @access public
     * @return $string
     */
    public function get_author_dynamic_folders($post_type, $taxonomy, $return="s")
    {

        if (!$this->has_dynamic_folders) {
            if ($return == "a") {
                return [];
            }

            return "";
        }

        global $wpdb;

        $folders = [];

        // Fetch authors
        $results = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT u.ID, u.display_name FROM {$wpdb->posts} p INNER JOIN {$wpdb->users} u ON p.post_author = u.ID AND post_status NOT IN ('trash', 'auto-draft') WHERE post_type = %s ORDER BY u.display_name ASC", $post_type));

        $folders[] = [
            'name'  => esc_html__("All Authors", "folders"),
            'value' => "author-all",
        ];

        $class = "";
        if (get_option("folders_".$post_type."author-all") == 1) {
            $class = " jstree-open";
        }

        $string  = "";
        $string .= "<li id='author-all' class='".esc_attr($class)."' data-slug='parent-all' >".__("All Authors", "folders");

        if (!empty($results)) {
            $string .= "<ul>";
            foreach ($results as $row) {
                $class = "";
                if (get_option("folders_".$post_type."author-".$row->ID) == 1) {
                    $class = " jstree-open";
                }

                $string .= "<li class='".esc_attr($class)."' id='author-".$row->ID."' data-slug='parent-all' >".$row->display_name."</li>";

                $folders[] = [
                    'name'  => "- ".$row->display_name,
                    'value' => 'author-'.$row->ID,
                ];
            }

            $string .= "</ul>";
        }

        if ($return == "s") {
            return $string;
        } else {
            return $folders;
        }

    }//end get_author_dynamic_folders()


    /**
     * Get active plugins list
     *
     * @since  1.0.0
     * @access public
     * @return $string
     */
    public function get_active_plugin_dynamic_folders($post_type,$taxonomy, $return="s")
    {
        echo "return param : ".esc_attr($return);
        if (!$this->has_dynamic_folders) {
            if ($return == "a") {
                return [];
            }

            return "";
        }

        global $wpdb;

        $folders = [];

        // Fetch authors
        $active_plugin = get_option('active_plugins');
        $plugins=get_plugins();
        $results=array();
        foreach ($active_plugin as $p){
            if(isset($plugins[$p])){
                array_push($results, $plugins[$p]);
            }
        }

        $folders[] = [
            'name'  => esc_html__("Active", "folders"),
            'value' => "plugin-active",
        ];

        $class = "";
        if (get_option("folders_".$post_type."author-all") == 1) {
            $class = " jstree-open";
        }

        $string  = "";
        $string .= "<li class='" . esc_attr($class) . " active-plugins' id='plugin-active' data-slug='active-plugin-all' >";
        $string .= "<a href='#' data-selectedtype='plugin' data-select='active' data-selected='active-plugin-all'>" .__("Active", "folders") . "</a>";

        $string .= "</li>";



        if ($return == "s") {
            return $string;
        } else {
            return $folders;
        }

    }//end get_active_plugin_dynamic_folders()


    /**
     * Get inactive plugins list
     *
     * @since  1.0.0
     * @access public
     * @return $string
     */
    public function get_inactive_plugin_dynamic_folders($post_type,$taxonomy, $return="s")
    {
        if (!$this->has_dynamic_folders) {
            if ($return == "a") {
                return [];
            }

            return "";
        }

        global $wpdb;

        $folders = [];

        $plugins=get_plugins();
        $results=array();
        foreach ($plugins as $key => $value){
            if(is_plugin_inactive($key)) {
                array_push($results, $plugins[$key]);
            }
        }

        $folders[] = [
            'name'  => esc_html__("Inactive", "folders"),
            'value' => "plugin-inactive",
        ];

        $class = "";
        if (get_option("folders_".$post_type."author-all") == 1) {
            $class = " jstree-open";
        }

        global $totals, $status;

        $string  = "";
        $string .= "<li class='" . esc_attr($class) . " inactive-plugins' id='plugin-inactive' data-slug='inactive-plugin' >";
        $string .= "<a href='#' data-selectedtype='plugin' data-select='inactive' data-selected='inactive-plugin'>" .__("Inactive", "folders") . "</a>";

        $string .= "</li>";


        if ($return == "s") {
            return $string;
        } else {
            return $folders;
        }

    }//end get_inactive_plugin_dynamic_folders()


    /**
     * Get update required plugins list
     *
     * @since  1.0.0
     * @access public
     * @return $string
     */
    public function get_update_plugin_dynamic_folders($post_type,$taxonomy, $return="s")
    {
        if (!$this->has_dynamic_folders) {
            if ($return == "a") {
                return [];
            }

            return "";
        }

        global $wpdb;

        $folders = [];

        $results= [];
        $update_plugins = get_site_transient( 'update_plugins' );
        $plugins=get_plugins();
        foreach ($update_plugins->response as $key=>$value){
            if(isset($plugins[$key])){
                array_push($results, $plugins[$key]);
            }
        }

        $folders[] = [
            'name'  => esc_html__("Updates Available", "folders"),
            'value' => "plugin-update",
        ];

        $class = "";
        if (get_option("folders_".$post_type."author-all") == 1) {
            $class = " jstree-open";
        }

        $string  = "";
        $string .= "<li class='" . esc_attr($class) . " upgrade-plugins' id='plugin-upgrade' data-slug='upgrade-plugin' >";
        $string .= "<a href='#' data-selectedtype='plugin' data-select='upgrade' data-selected='upgrade-plugin'>" .__("Updates available", "folders") . "</a>";

        $string .= "</li>";


        if ($return == "s") {
            return $string;
        } else {
            return $folders;
        }

    }//end get_inactive_plugin_dynamic_folders()

    public function  get_common_taxonomy_dynamic_folder($post_type, $taxonomy,$title,$slug,$main_slug,$return="s") {

        if (!$this->has_dynamic_folders) {
            if ($return == "a") {
                return [];
            }

            return "";
        }

        $args = array(
            'orderby' => 'name',
            'taxonomy' => $taxonomy,
            'hierarchical' => 1,
            'hide_empty' => 0,
            'parent' => 0
        );
        $new_categories = get_categories($args);


        $folders[] = [
            'name' => esc_attr($title),
            'value' => esc_attr($slug)."-all",
        ];

        $class = "";
        if (get_option("folders_" . $post_type . esc_attr($slug)."-all") == 1) {
            $class = " jstree-open";
        }

        $string = "";

        $string .= "<li id='".esc_attr($slug)."-all' class='" . esc_attr($class) . "' data-slug='".esc_attr($slug)."-all' >" . esc_attr($title);

        if (!empty($new_categories)) {

            $string .= "<ul class='main-".esc_attr($main_slug)."'>";
            foreach ($new_categories as $row) {
                $class = "";
                if (get_option("folders_" . $post_type . esc_attr($slug)."-" . $row->term_id) == 1) {
                    $class = " jstree-open";
                }

                $string .= "<li class='" . esc_attr($class) . " main-".esc_attr($slug)."' id='".esc_attr($slug)."-" . $row->term_id . "' data-slug='".esc_attr($slug)."-all' >";
                $string .= "<a href='#' data-select='".esc_attr($slug)."' data-selected='" . $row->slug . "'>" . $row->name . "</a>";

                $count_children = count(get_term_children($row->term_id, $taxonomy));
                if ($count_children > 0) {
                    $string .= $this->get_common_child_category($post_type,$taxonomy, "", $row->term_id,$title,$slug,$main_slug);
                }

                $string .= "</li>";
                $folders[] = [
                    'name' => "- " . $row->name,
                    'value' => esc_attr($slug).'-' . $row->term_id,
                ];
            }

            $string .= "</ul>";
            $string .= "</li>";
        }


        if ($return == "s") {
            return $string;
        } else {
            return $folders;
        }
    }
    public function get_common_child_category ($post_type,$taxonomy,$string , $term_id,$title,$slug,$main_slug) {

        $args = [
            'orderby' => 'name',
            'taxonomy' => $taxonomy,
            'hierarchical' => 1,
            'hide_empty' => 0,
            'parent'    => $term_id
        ];

        $child_categories = get_terms( $args );

        if (!empty($child_categories)) {

            $string .= "<ul class='child-".esc_attr($main_slug)."'>";
            foreach ($child_categories as $row) {
                $class = "";
                if (get_option("folders_".$post_type."category-".$row->term_id) == 1) {
                    $class = " jstree-open";
                }

                $string .= "<li class='".esc_attr($class)." main-".esc_attr($slug)."' id='".esc_attr($slug)."-".$row->term_id."' data-slug='".esc_attr($slug)."-all' >";
                $string .= "<a href='#' data-select='".esc_attr($slug)."' data-selected='".$row->slug."'>".$row->name."</a>";

                $count_children = count (get_term_children( $row->term_id, $taxonomy ));
                if($count_children > 0) {
                    $string .= $this->get_common_child_category($post_type,$taxonomy,"" , $row->term_id,$title,$slug,$main_slug);
                }

                $string .= "</li>";
                $folders[] = [
                    'name'  => "- ".$row->name,
                    'value' => esc_attr($slug).'-'.$row->term_id,
                ];
            }

            $string .= "</ul>";

        } else {
            return $string;
        }
        return $string;

    }


    /**
     * Get wordpress timezone
     *
     * @since  1.0.0
     * @access public
     * @return $timezone
     */
    public static function timezone_identifier()
    {
        $timezone = wp_timezone_string();
        if (empty($timezone)) {
            return "UTC";
        }

        return $timezone;

    }//end timezone_identifier()


    /**
     * Get pages hierarchy list
     *
     * @since  1.0.0
     * @access public
     * @return $string
     */
    public function get_page_hierarchy_dynamic_folders($post_type, $taxonomy)
    {
        if ($post_type != 'page') {
            return ;
        }

        $string = "";

        $class = "";
        if (get_option("folders_".$post_type."pages_parent-all") == 1) {
            $class = " jstree-open";
        }

        $string .= "<li id='pages_parent-all' class='".esc_attr($class)."' data-slug='dates-all' >".esc_html__("All Hierarchy", "folders");
        $string .= "<ul>";
        $string .= $this->get_root_hierarchy();
        $string .= "</ul>";
        $string .= "</li>";
        return $string;

    }//end get_page_hierarchy_dynamic_folders()


    /**
     * Get pages hierarchy list
     *
     * @since  1.0.0
     * @access public
     * @return $results
     */
    public function get_root_hierarchy_array()
    {
        global $wpdb;
        $post_table = $wpdb->posts;

        $query   = "SELECT DISTINCT(P.ID), P.post_title 
            FROM {$post_table} AS P 
            INNER JOIN {$post_table} AS PC ON PC.post_parent = P.ID 
            WHERE PC.post_parent != 0 AND P.post_type = 'page' AND PC.post_type = 'page' AND P.post_parent = 0 AND PC.post_status != 'trash' AND P.post_status != 'trash'";
        $results = $wpdb->get_results($query);

        return $results;

    }//end get_root_hierarchy_array()


    /**
     * Get root pages hierarchy list
     *
     * @since  1.0.0
     * @access public
     * @return $string
     */
    public function get_root_hierarchy()
    {

        $results = $this->get_root_hierarchy_array();

        $string = "";
        foreach ($results as $row) {
            $string .= "<li id='pages_parent-{$row->ID}' >".$row->post_title;
            $string .= "<ul>";
            $string .= $this->get_page_hierarchy($row->ID);
            $string .= "</ul>";
            $string .= "</li>";
        }

        return $string;

    }//end get_root_hierarchy()


    /**
     * Get chile pages list
     *
     * @since  1.0.0
     * @access public
     * @return $results
     */
    public function get_child_pages_array($parent_id=0)
    {
        global $wpdb;
        $post_table = $wpdb->posts;

        $query = "SELECT DISTINCT(P.ID), P.post_title 
            FROM {$post_table} AS P 
            WHERE P.post_parent = {$parent_id} AND P.post_type = 'page' AND P.post_status != 'trash'";

        $results = $wpdb->get_results($query);
        return $results;

    }//end get_child_pages_array()


    /**
     * Get pages hierarchy list
     *
     * @since  1.0.0
     * @access public
     * @return $results
     */
    public function get_page_hierarchy_array($parent_id=0)
    {
        global $wpdb;
        $post_table = $wpdb->posts;

        $query = "SELECT DISTINCT(P.ID), P.post_title 
            FROM {$post_table} AS P 
            INNER JOIN {$post_table} AS PC ON PC.post_parent = P.ID 
            WHERE P.post_parent = {$parent_id} AND P.post_type = 'page' AND PC.post_type = 'page'";

        $results = $wpdb->get_results($query);
        return $results;

    }//end get_page_hierarchy_array()


    /**
     * Get page hierarchy list
     *
     * @since  1.0.0
     * @access public
     * @return $string
     */
    public function get_page_hierarchy($parent_id=0)
    {

        $results = $this->get_page_hierarchy_array($parent_id);
        $string  = "";
        foreach ($results as $row) {
            $string .= "<li id='pages_parent-{$row->ID}' >".$row->post_title;
            $string .= "<ul>";
            $string .= $this->get_page_hierarchy($row->ID);
            $string .= "</ul>";
            $string .= "</li>";
        }

        return $string;

    }//end get_page_hierarchy()


}//end class

$Premio_Pro_Folders_Dynamic_Folders = new Premio_Pro_Folders_Dynamic_Folders();
