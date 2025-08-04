<?php

add_action( 'rest_api_init', function () {
    register_rest_route( 'members/v1', '/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'protherics_rest_api_members',
    ) );

    register_rest_route( 'news/v1', '/all', array(
        'methods' => 'GET',
        'callback' => 'protherics_rest_api_news',
    ) );

    register_rest_route( 'locations/v1', '/all', array(
        'methods' => 'GET',
        'callback' => 'protherics_rest_api_locations',
    ) );

    register_rest_route( 'products/v1', '/all', array(
        'methods' => 'GET',
        'callback' => 'protherics_rest_api_products',
    ) );

    register_rest_route( 'twitter/v1', '/all', array(
        'methods' => 'GET',
        'callback' => 'rest_api_twitter',
    ) );

    register_rest_route( 'redirect/v1', '/all', array(
        'methods' => 'GET',
        'callback' => 'protherics_rest_api_redirect',
    ) );

} );

function protherics_rest_api_members( $data ) {
    $post_id = isset( $data['id'] ) ? $data['id'] : false;
    $post_data = array();

    if ( $post_id ) {
        $args = array(
            'post_type' => 'members',
            'p' => $post_id
        );
        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();

                $post_data[] = array(
                    'id' => $post_id,
                    'name' => get_the_title( $post_id ),
                    'job' => get_field( 'job', $post_id ),
                    'excerpt' => get_the_excerpt( $post_id ),
                    'content' => get_the_content( $post_id ),
                    'img' => get_the_post_thumbnail_url( $post_id ),
                );
            }
        }
    }
    return $post_data;
}

function protherics_rest_api_news() {
    $posts = array();
    $args = array(
        'post_type' => 'news',
        'posts_per_page' => -1,
    );
    $query = new WP_Query( $args );

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();

            $post_id = get_the_ID();
            $date = get_field( 'date', $post_id );

            if ( $date ) {
                $year = date_format( date_create( $date ), 'Y' );
                $posts[$year][] = array (
                    'date' => $date,
                    'title' => get_the_title( $post_id ),
                    'desc' => get_the_excerpt( $post_id ),
                    'link' => get_the_permalink( $post_id ),
                    'label' => __( 'Read release', 'protherics' ),
                    'regions' => get_field( 'show_in_region', $post_id ),
                );
            }
        }
    }
    return $posts;
}

function protherics_rest_api_locations() {
    $posts = array();
    $args = array(
        'post_type' => 'locations',
        'posts_per_page' => -1,
    );
    $query = new WP_Query( $args );

    $button_label = get_field( 'locations_button_label', 'option' );

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $post_id = get_the_ID();
            $bg_color = get_field( 'background_color', $post_id );
            $country = get_field( 'country', $post_id );
            $contact_url = get_field( 'contact_url', $post_id );
            $contact = get_field( 'contact_details', $post_id );
            $contact_button = get_field( 'hide_contact_button', $post_id );
            $contact_arr = array();
            if ( $contact ) {
                foreach ( $contact as $item ) {
                    $contact_arr[] = $item['phone_number'];
                }
            }
            $address = get_field( 'address_details', $post_id );
            $address_arr = array();
            if ( $address ) {
                foreach ( $address as $item ) {
                    $address_arr[] = $item['text'];
                }
            }
            $terms = wp_get_post_terms( $post_id, 'regions', array( 'fields' => 'names' ) );
            $posts[] = array(
                "continent" => $terms[0],
                "bgColor" => $bg_color , 
                "country" => $country,
                "addressDetails" => $address_arr,
                "contactDetails" => $contact_arr,
                "contactUrl" => $contact_url,
                'contactLabel' => $button_label,
                'hideButton' => $contact_button,
            );
        }
    }

    return $posts;
}

function protherics_rest_api_products() {
    $posts = array();


    $terms_disease = get_terms( array(
        'taxonomy' => 'disease-area',
        'hide_empty' => false,
    ) );

    $query_args = array(
        'post_type' => 'products',
        'posts_per_page' => -1,
    );

    foreach ( $terms_disease as $item ) {
        $cat_posts = array();

        $query_args['tax_query'] = array(
            array(
                'taxonomy' => 'disease-area',
                'field' => 'term_id',
                'terms' => $item->term_id,
            )
        );
        $query = new WP_Query( $query_args );

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id = get_the_ID();

                $trademark = get_field( 'trademark', $post_id );
                $product_composition = get_field( 'product_composition', $post_id );
                $btn = get_field( 'button', $post_id );

                $terms_disease_post = get_the_terms( $post_id, 'disease-area' );
                foreach( $terms_disease_post as $item_cat ) {
                    $item_cat->color = get_field( 'color', $item_cat );
                }
                $terms_locations_post = get_the_terms( $post_id, 'product-locations' );

                $post_img_id = get_post_thumbnail_id( $post_id );
                $post_img_alt = get_post_meta( $post_img_id, '_wp_attachment_image_alt', true );

                $trademark_hide = get_field( 'hide_trademark', $post_id );

                $trademark_array = array(
                    'title' => isset( $trademark['title'] ) && $trademark['title'] ? $trademark['title'] : false,
                    'content' => isset( $trademark['popup'] ) && $trademark['popup'] ? $trademark['popup'] : false,
                );

                if ( $trademark_hide ) {
                    $trademark_array = array(
                        'title' => false,
                        'content' => false,
                    );
                }

                $cat_posts[] = array(
                    'id' => $post_id,
                    'title' => get_the_title( $post_id ),
                    'img' => get_the_post_thumbnail_url( $post_id ),
                    'btnLabel' => get_field( 'button_label', $post_id ),
                    'imgAlt' => $post_img_alt,
                    'trademark' => $trademark_array,
                    'productComposition' => $product_composition,
                    'buttonUrls' => $btn,
                    'diseadeArea' => $terms_disease_post ? $terms_disease_post : array(),
                    'locations' => $terms_locations_post ? $terms_locations_post : array(),
                );
            }
        }
        $posts[$item->name]['color'] = get_field( 'color', $item );
        $posts[$item->name]['products'] = $cat_posts;
        wp_reset_postdata();

    }

    return $posts;
}

function rest_api_twitter() {
	$tweets = array();
	if ( class_exists( 'TwitterWP' ) ) {
		$app = array(
			'consumer_key'        => 'ma1hTYEQ8sxrSjU4NbK8q3gBe',
			'consumer_secret'     => '4Rwfp8sj7DOd8Yzqt62YDMXXRvizzVrhK9BcrR0fC66uaJvn18',
			'access_token'        => '1546778980757475333-9XdnBc4h1qF5gOn5gW1oovhxamUEGE',
			'access_token_secret' => 'leZP1J6QX55tkQ0vzsVIYHutmRvqxAQvOHw6YswpL2LLD',
		);

		// initiate your app
		$tw = TwitterWP::start( $app );
		$user = '@SERB_Pharma';
		$user_id = '1546778980757475333';
		// bail here if the user doesn't exist
		if ( !$tw->user_exists( $user_id ) ) {
            return;
		}
		$tweets_fetch = $tw->get_tweets( $user, 1 );
		if ( $tweets_fetch ) {
			foreach ( $tweets_fetch as $tweet ) {
				if ( $tweet->created_at ) {
					$date_format = date ( "d/m/Y", strtotime( $tweet->created_at ) );
					$array_tweet = (array)$tweet;
					$array_tweet['format_date'] = $date_format;
					$tweets[] = $array_tweet;
				}
			}
		}
	}
	return $tweets;
}

function protherics_rest_api_redirect() {
    $links = array();
    $links_acf = get_field( 'redirect_links', 'option' );

    if ( $links_acf ) {
        foreach ( $links_acf as $link ) {
            if ( isset( $link['link'] ) && $link['link'] ) {
                $links[] = array(
                    'url' => $link['link']
                );
            }
        }
    }

    return $links;
}
