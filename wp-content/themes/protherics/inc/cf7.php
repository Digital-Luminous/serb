<?php

function custom_add_form_tag_productlist() {
    wpcf7_add_form_tag( array( 'productlist', 'productlist*' ), 'custom_productlist_form_tag_handler', true );
    wpcf7_add_form_tag( array( 'locationlist', 'locationlist*' ), 'custom_locationlist_form_tag_handler', true );
}
add_action( 'wpcf7_init', 'custom_add_form_tag_productlist' );

function custom_productlist_form_tag_handler( $tag ) {

    $tag = new WPCF7_FormTag( $tag );

    if ( empty( $tag->name ) ) {
        return '';
    }

    $customlist = '';

    $query = new WP_Query(array(
        'post_type' => 'products',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby'       => 'title',
        'order'         => 'ASC',
    ));

    while ($query->have_posts()) {
        $query->the_post();
        $post_title = get_the_title();
        $customlist .= sprintf( '<option value="%1$s">%2$s</option>', esc_html( $post_title ), esc_html( $post_title ) );
    }

    wp_reset_query();

    if (get_current_blog_id() === 1 || str_contains(get_site_url(), 'veriton')) {
        $customlist = sprintf( '<select class="c-form__hidden-select js-hidden-select wpcf7-form-control wpcf7-select wpcf7-validates-as-required" data-type="search" data-search-placeholder="' . __('Type to search products', 'protherics') . '" name="%1$s" id="%2$s" aria-required="true" aria-invalid="false"><option value="">' . __( '---', 'protherics' ) . '</option>%3$s</select>', $tag->name, $tag->name . '-options', $customlist );
    } else if (get_current_blog_id() === 2) {
        $customlist = sprintf( '<select class="c-form__hidden-select js-hidden-select wpcf7-form-control wpcf7-select wpcf7-validates-as-required" data-type="search" data-search-placeholder="' . __('Tapez pour rechercher des produits', 'protherics') . '" name="%1$s" id="%2$s" aria-required="true" aria-invalid="false"><option value="">' . __( '---', 'protherics' ) . '</option>%3$s</select>', $tag->name, $tag->name . '-options', $customlist );
    } else {
        $current_language = apply_filters( 'wpml_current_language', null );

        if ( $current_language === 'nl' ) {
            $customlist = sprintf( '<select class="c-form__hidden-select js-hidden-select wpcf7-form-control wpcf7-select wpcf7-validates-as-required" data-type="search" data-search-placeholder="' . __('Typ om naar producten te zoeken', 'protherics') . '" name="%1$s" id="%2$s" aria-required="true" aria-invalid="false"><option value="">' . __( '---', 'protherics' ) . '</option>%3$s</select>', $tag->name, $tag->name . '-options', $customlist );
        } else {
            $customlist = sprintf( '<select class="c-form__hidden-select js-hidden-select wpcf7-form-control wpcf7-select wpcf7-validates-as-required" data-type="search" data-search-placeholder="' . __('Tapez pour rechercher des produits', 'protherics') . '" name="%1$s" id="%2$s" aria-required="true" aria-invalid="false"><option value="">' . __( '---', 'protherics' ) . '</option>%3$s</select>', $tag->name, $tag->name . '-options', $customlist );
        }
    }

    return $customlist;
}

function custom_locationlist_form_tag_handler( $tag ) {

    $tag = new WPCF7_FormTag( $tag );

    if ( empty( $tag->name ) ) {
        return '';
    }

    $customlist = '';

    // $query = new WP_Query(array(
    //     'post_type' => 'products',
    //     'post_status' => 'publish',
    //     'posts_per_page' => -1,
    //     'orderby'       => 'title',
    //     'order'         => 'ASC',
    // ));

    $terms = get_terms( array(
        'taxonomy' => 'product-locations',
        'hide_empty' => false,
    ) );

    foreach ( $terms as $item ) {
        // $query->the_post();
        // $post_title = get_the_title();
        $term_name = $item->name;
        $customlist .= sprintf( '<option value="%1$s">%2$s</option>', esc_html( $term_name ), esc_html( $term_name ) );
    }

    wp_reset_query();

    $customlist = sprintf( '<select class="c-form__hidden-select js-hidden-select wpcf7-form-control wpcf7-select wpcf7-validates-as-required" data-type="normal" name="%1$s" id="%2$s" aria-required="true" aria-invalid="false"><option value="">' . __( 'Location', 'protherics' ) . '*</option>%3$s</select>', $tag->name, $tag->name . '-options', $customlist );

    return $customlist;
}

add_filter('wpcf7_autop_or_not', '__return_false');


function protherics_dynamic_emails( $contact_form, $abort, $submission ) {

    $form_id = get_field( 'cf_contact_form_id', 'option' );

    $rules = get_field( 'cf_rules', 'option' );

    $current_form_id = $contact_form->id();

    if ( $form_id == $current_form_id && $rules ) {

        $step_1 = array();
        $step_2 = array();
        $step_3 = array();

        $cc_emails = array();

        $submission = WPCF7_Submission::get_instance();
        $posted_data = $submission->get_posted_data();

        if ( isset( $posted_data['adminemail'][0] ) ) {
            foreach ( $rules as $item ) {
                if ( $posted_data['adminemail'][0] == $item['enquiry_type'] ) {
                    $step_1[] = $item;
                }
            }
        }
        if(count( $step_1 ) > 0) {
            if(is_main_site() ) {
                if ( isset( $posted_data['country_select'] ) ) {
                    foreach ( $step_1 as $item ) {
                        if ( $item['geography'] == '' ) {
                            $step_2[] = $item;
                        } else {
                            foreach ( $item['geography'] as $geo ) {
                                if ( $posted_data['country_select'] == $geo->name || $geo == null ) {
                                    $step_2[] = $item;
                                }
                            }
                        }
                        // if ( $posted_data['country_select'] == $item['geography']->name || $item['geography'] == null ) {
                        //     $step_2[] = $item;
                        // }
                    }
                }
            } else {
                foreach ( $step_1 as $item ) {
                    if ( $item['geography'] == '' ) {
                        $step_2[] = $item;
                    } else {
                        foreach ( $item['geography'] as $geo ) {
                            $step_2[] = $item;
                        }
                    }
                }
            }
        }
       
        

        if ( isset( $posted_data['product_select'] ) && count( $step_2 ) > 0 ) {
            foreach ( $step_2 as $item ) {
                if ( $item['product'] == '' ) {
                    $step_3[] = $item;
                } else {
                    foreach ( $item['product'] as $product ) {
                        if ( $posted_data['product_select'] == $product->post_title ) {
                            $step_3[] = $item;
                        }
                    }
                }
                // if ( $posted_data['product_select'] == $item['product']->post_title || $item['product'] == null ) {
                //     $step_3[] = $item;
                //     if ( isset( $item['email'] ) && $item['email'] ) {
                //         $cc_emails[] = $item['email'];
                //     }
                // }
            }
        }

        foreach ( $step_3 as $item ) {
            if ( isset( $item['email'] ) && $item['email'] ) {
                $cc_emails[] = $item['email'];
            }
        }

        if ( count( $cc_emails ) > 0 ) {
            $properties = $contact_form->get_properties();
            $emails = $properties['mail']['recipient'];
            // $mail = $contact_form->prop('mail');
            // $mail['additional_headers'] = "Cc: $cc_emails";
            // $contact_form->set_properties( array(
            //     "mail" => $mail
            // ) );

            // return $instance;
            foreach ( $cc_emails as $email ) {
                $emails .= ', ' . $email;
            }
            $properties['mail']['recipient'] = $emails;
            $contact_form->set_properties( $properties );
        }
    }



    return $contact_form;

}
add_filter( 'wpcf7_before_send_mail', 'protherics_dynamic_emails', 10, 3 );

add_filter( 'wpcf7_validate_productlist*', 'custom_email_confirmation_validation_filter', 5, 2 );
add_filter( 'wpcf7_validate_locationlist*', 'custom_email_confirmation_validation_filter', 5, 2 );

function custom_email_confirmation_validation_filter( $result, $tag ) {
    $name = $tag->name;
    $value = isset( $_POST[$name] ) ? $_POST[$name] : '';
    if ( empty( $value ) ) {
        $result->invalidate( $tag, wpcf7_get_message('invalid_required') );
    }
    return $result;
}
