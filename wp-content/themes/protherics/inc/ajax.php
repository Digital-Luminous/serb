<?php

function protherics_insights_ajax() {
    $args = $_REQUEST;
    $search = $args['search'] ? $args['search'] : false;
    $paged = $args['page'] ? $args['page'] : false;

    $posts = array();

    $posts_per_page = 6;

    $args_query = array(
        'post_type' => 'insights',
        'posts_per_page' => $posts_per_page,
    );

    if ( $paged ) {
        $args_query['paged'] = $paged;
    }
    if ( $search ) {
        $args_query['s'] = $search;
    }

    $query = new WP_Query( $args_query );

    $posts['info'] = array(
        'max_pages' => $query->max_num_pages,
        'posts_per_page' => $posts_per_page,
        'all_posts' => $query->found_posts,
        'current_page_posts' => $query->post_count
    );

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $post_id = get_the_ID();
            $post_author = get_field( 'author', $post_id );
            if ( ! $post_author ) {
                $post_author = get_the_author();
            }

            $terms = wp_get_post_terms( $post_id, 'subjects', array(
                'fields' => 'all',
                'hide_empty' => false,
                'number' => 2,
            ) );

            $posts['posts'][] = array(
                'title' => get_the_title( $post_id ),
                'excerpt' => get_the_excerpt( $post_id ),
                'author' => $post_author,
                'label' => __( 'Read more', 'protherics' ),
                'img' => get_the_post_thumbnail_url( $post_id ),
                'date' => get_the_date( 'd F Y', $post_id ),
                'terms' => $terms,
				'link' => get_the_permalink( $post_id )
            );
        }
    }

    wp_send_json( $posts );
}
add_action( 'wp_ajax_nopriv_get_insights', 'protherics_insights_ajax' );
add_action( 'wp_ajax_get_insights', 'protherics_insights_ajax' );
