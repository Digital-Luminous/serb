<?php

function protherics_breadcrumbs() {
    // Set variables for later use
    $here_text = __( '' );
    $home_link = home_url( '/' );
    $home_text = get_the_title( get_option('page_on_front') ) ?? __( 'Home', 'protherics' );
    $link_before = '<li class="c-breadcrumbs__item">';
    $link_after = '</li>';
    $link_attr = ' class="c-breadcrumbs__link"';
    $link = $link_before . '<a' . $link_attr . ' href="%1$s">%2$s</a>';
    $delimiter = '<span class="c-breadcrumbs__divider">&gt;</span>';
    $before = $link_before . '<span class="c-breadcrumbs__text">';
    $after = '</span>' . $link_after;
    $page_addon = '';
    $breadcrumb_trail = '';
    $category_links = '';
    /**
     * Set our own $wp_the_query variable. Do not use the global variable version due to
     * reliability
     */
    $wp_the_query = $GLOBALS['wp_the_query'];
    $queried_object = $wp_the_query->get_queried_object();
    $page_for_posts = get_option( 'page_for_posts' );
    // Handle single post requests which includes single pages, posts and attatchments
    if ( is_singular() ) {
        /**
         * Set our own $post variable. Do not use the global variable version due to
         * reliability. We will set $post_object variable to $GLOBALS['wp_the_query']
         */
        $post_object = sanitize_post( $queried_object );
        // Set variables
        $title = apply_filters( 'the_title', $post_object->post_title );
        $parent = $post_object->post_parent;
        $post_type = $post_object->post_type;
        $post_status = $post_object->post_status;
        $post_id = $post_object->ID;
        $post_link = $before . $title . $after;
        $parent_string = '';
        $post_type_link = '';
        if ( 'post' === $post_type ) {
            $category_links = sprintf( $link, get_permalink( $page_for_posts ), get_the_title( $page_for_posts ) ) . $delimiter . $link_after;
        }
        if ( !in_array( $post_type, ['post', 'page', 'attachment'] ) ) {
            $post_type_object = get_post_type_object( $post_type );
                $archive_link = esc_url( get_post_type_archive_link( $post_type ) );
                $post_type_link = sprintf( $link, $archive_link, $post_type_object->labels->name );
        }
        // Get post parents if $parent !== 0
        if ( 0 !== $parent ) {
            $parent_links = [];
            while ( $parent ) {
                $post_parent = get_post( $parent );
                $post_parent_status = $post_parent->post_status;
                if ( $post_parent_status == 'private' ) {
                    // $parent_links[] = sprintf( $link, esc_url( '' ), str_replace( 'Private: ', '', get_the_title( $post_parent->ID ) ) );
                    $parent_links[] = $before . str_replace( 'Private: ', '', get_the_title( $post_parent->ID ) ) . $after;
                } else {
                    $parent_links[] = sprintf( $link, esc_url( get_permalink( $post_parent->ID ) ), get_the_title( $post_parent->ID ) );
                }
                $parent = $post_parent->post_parent;
            }
            $parent_links = array_reverse( $parent_links );
            $parent_string = implode( $delimiter, $parent_links );
        }
        // Lets build the breadcrumb trail
        if ( $parent_string ) {
            $breadcrumb_trail = $parent_string . $link_before . $delimiter . $link_after . $post_link;
        } else {
            $breadcrumb_trail = $post_link;
        }
        if ( $post_type_link ) {
            $breadcrumb_trail = $post_type_link . $link_before . $delimiter . $link_after . $breadcrumb_trail;
        }

        if ( $category_links ) {
            $breadcrumb_trail = $category_links . $breadcrumb_trail;
        }

    }
    // Handle archives which includes category-, tag-, taxonomy-, date-, custom post type archives and author archives
    if ( is_archive() ) {
        if ( is_category() || is_tag() || is_tax() ) {
            // Set the variables for this section
            $term_object = get_term( $queried_object );
            $taxonomy = $term_object->taxonomy;
            $term_id = $term_object->term_id;
            $term_name = $term_object->name;
            $term_parent = $term_object->parent;
            $taxonomy_object = get_taxonomy( $taxonomy );
            $current_term_link = $before . $taxonomy_object->labels->singular_name . ': ' . $term_name . $after;
            $parent_term_string = '';
            if ( 0 !== $term_parent ) {
                // Get all the current term ancestors
                $parent_term_links = [];
                while ( $term_parent ) {
                    $term = get_term( $term_parent, $taxonomy );
                    $parent_term_links[] = sprintf( $link, esc_url( get_term_link( $term ) ), $term->name );
                    $term_parent = $term->parent;
                }
                $parent_term_links = array_reverse( $parent_term_links );
                $parent_term_string = implode( $delimiter, $parent_term_links );
            }

            $breadcrumb_trail .= sprintf( $link, get_permalink( $page_for_posts ), get_the_title( $page_for_posts ) ) . $delimiter . $link_after;

            if ( $parent_term_string ) {
                $breadcrumb_trail .= $parent_term_string . $delimiter . $current_term_link;
            } else {
                $breadcrumb_trail .= $current_term_link;
            }
        } elseif ( is_author() ) {
            $breadcrumb_trail = __( 'Author archive for ', 'protherics' ) . $before . $queried_object->data->display_name . $after;
        } elseif ( is_date() ) {
            // Set default variables
            $year = $wp_the_query->query_vars['year'];
            $monthnum = $wp_the_query->query_vars['monthnum'];
            $day = $wp_the_query->query_vars['day'];
            // Get the month name if $monthnum has a value
            if ( $monthnum ) {
                $date_time = DateTime::createFromFormat( '!m', $monthnum );
                $month_name = $date_time->format( 'F' );
            }
            if ( is_year() ) {
                $breadcrumb_trail = $before . $year . $after;
            } elseif ( is_month() ) {
                $year_link = sprintf( $link, esc_url( get_year_link( $year ) ), $year );
                $breadcrumb_trail = $year_link . $delimiter . $before . $month_name . $after;
            } elseif ( is_day() ) {
                $year_link = sprintf( $link, esc_url( get_year_link( $year ) ), $year );
                $month_link = sprintf( $link, esc_url( get_month_link( $year, $monthnum ) ), $month_name );
                $breadcrumb_trail = $year_link . $delimiter . $month_link . $delimiter . $before . $day . $after;
            }
        } elseif ( is_post_type_archive() ) {
            $post_type = $wp_the_query->query_vars['post_type'];
            $post_type_object = get_post_type_object( $post_type );
            $breadcrumb_trail = $before . $post_type_object->labels->singular_name . $after;
        }
    }
    // Handle the search page
    if ( is_search() ) {
        $breadcrumb_trail = __( 'Search query for: ', 'protherics' ) . $before . get_search_query() . $after;
    }
    // Handle 404's
    if ( is_404() ) {
        $breadcrumb_trail = $before . __( 'Error 404', 'protherics' ) . $after;
    }
    // Handle paged posts
    if ( $queried_object->ID == $page_for_posts ) {
        $breadcrumb_trail .= $before . get_the_title( $queried_object ) . $after;
    }
    $breadcrumb_output_link = '';
    $breadcrumb_output_link .= '<section class="l-section l-breadcrumbs s-regular-bottom">';
    $breadcrumb_output_link .= '<div class="l-inner">';
    $breadcrumb_output_link .= '<ul class="c-breadcrumbs t-size-14 t-size-16--desktop ui-color--black-1">';

    if (  ( is_home() || is_front_page() ) && $queried_object->ID != $page_for_posts ) {
        $breadcrumb_output_link .= '';
    } else {
        $breadcrumb_output_link .= '<li class="c-breadcrumbs__item"><a href="' . $home_link . '" class="c-breadcrumbs__link ui-link t-typo-19 ui-color--accent">' . $home_text . '</a>' . '</li>' . $link_before . $delimiter . $link_after;
        $breadcrumb_output_link .= $breadcrumb_trail . $link_after;
        $breadcrumb_output_link .= $page_addon;
    }
    $breadcrumb_output_link .= '</ul>';
    $breadcrumb_output_link .= '</div>';
    $breadcrumb_output_link .= '</section>';
    return $breadcrumb_output_link;
}

