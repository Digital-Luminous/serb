<?php

/**
 * Render ACF block as a Gutenberg block.
 *
 * @param array $block
 * @return void
 */
function protherics_render_acf_block( $block ) {
    $slug = str_replace( 'acf/', '', $block['name'] );
    // include a template part from within the "/block" folder
    if ( file_exists( get_template_directory() . "/template-parts/blocks/{$slug}.php" ) ) {
        include( get_template_directory() . "/template-parts/blocks/{$slug}.php" );
    }
}


/**
 * Add custom block category (Gutenberg editor).
 */
add_filter( 'block_categories', function( $categories, $post ) {
    $categories[] = array(
        'slug' => 'protherics-blocks',
        'title' => __( 'Protherics - customs blocks', 'protherics' ),
        'icon' => ''
    );
    return $categories;
}, 10, 2 );

function protherics_acf_gutenberg_blocks() {
    if( function_exists( 'acf_register_block' ) ) {

        acf_register_block(
            array(
                'name' => 'hero',
                'title' => __( 'Hero', 'protherics' ),
                'description' => __( 'Hero section', 'protherics' ),
                'render_callback' => 'protherics_render_acf_block',
                'category' => 'protherics-blocks',
                'icon' => 'welcome-widgets-menus',
                'keywords' => array(),
            )
        );

        acf_register_block(
            array(
                'name' => 'two-col',
                'title' => __( 'Two columns', 'protherics' ),
                'description' => __( 'Two colums section', 'protherics' ),
                'render_callback' => 'protherics_render_acf_block',
                'category' => 'protherics-blocks',
                'icon' => 'columns',
                'keywords' => array(),
            )
        );

        acf_register_block(
            array(
                'name' => 'img-text',
                'title' => __( 'Image with text', 'protherics' ),
                'description' => __( 'Image with text section', 'protherics' ),
                'render_callback' => 'protherics_render_acf_block',
                'category' => 'protherics-blocks',
                'icon' => 'image-flip-horizontal',
                'keywords' => array(),
            )
        );

        acf_register_block(
            array(
                'name' => 'latest-insights',
                'title' => __( 'Latest insights', 'protherics' ),
                'description' => __( 'Latest insights section', 'protherics' ),
                'render_callback' => 'protherics_render_acf_block',
                'category' => 'protherics-blocks',
                'icon' => 'image-flip-horizontal',
                'keywords' => array(),
            )
        );

        acf_register_block(
            array(
                'name' => 'latest-news-social',
                'title' => __( 'Latest news with social', 'protherics' ),
                'description' => __( 'Latest news with social section', 'protherics' ),
                'render_callback' => 'protherics_render_acf_block',
                'category' => 'protherics-blocks',
                'icon' => 'image-flip-horizontal',
                'keywords' => array(),
            )
        );

        acf_register_block(
            array(
                'name' => 'hero-text',
                'title' => __( 'Hero with text', 'protherics' ),
                'description' => __( 'Hero with text section', 'protherics' ),
                'render_callback' => 'protherics_render_acf_block',
                'category' => 'protherics-blocks',
                'icon' => 'image-flip-horizontal',
                'keywords' => array(),
            )
        );

        acf_register_block(
            array(
                'name' => 'text',
                'title' => __( 'Text', 'protherics' ),
                'description' => __( 'Text section', 'protherics' ),
                'render_callback' => 'protherics_render_acf_block',
                'category' => 'protherics-blocks',
                'icon' => 'image-flip-horizontal',
                'keywords' => array(),
            )
        );

        acf_register_block(
            array(
                'name' => 'banner-slider',
                'title' => __( 'Banner', 'protherics' ),
                'description' => __( 'Banner section', 'protherics' ),
                'render_callback' => 'protherics_render_acf_block',
                'category' => 'protherics-blocks',
                'icon' => 'image-flip-horizontal',
                'keywords' => array(),
            )
        );

        acf_register_block(
            array(
                'name' => 'banner-slider-light',
                'title' => __( 'Banner light', 'protherics' ),
                'description' => __( 'Banner light section', 'protherics' ),
                'render_callback' => 'protherics_render_acf_block',
                'category' => 'protherics-blocks',
                'icon' => 'image-flip-horizontal',
                'keywords' => array(),
            )
        );

        acf_register_block(
            array(
                'name' => 'team-members',
                'title' => __( 'Members', 'protherics' ),
                'description' => __( 'Members section', 'protherics' ),
                'render_callback' => 'protherics_render_acf_block',
                'category' => 'protherics-blocks',
                'icon' => 'image-flip-horizontal',
                'keywords' => array(),
            )
        );

        acf_register_block(
            array(
                'name' => 'table',
                'title' => __( 'Table', 'protherics' ),
                'description' => __( 'Table section', 'protherics' ),
                'render_callback' => 'protherics_render_acf_block',
                'category' => 'protherics-blocks',
                'icon' => 'image-flip-horizontal',
                'keywords' => array(),
            )
        );

        acf_register_block(
            array(
                'name' => 'table-insights',
                'title' => __( 'Table insights', 'protherics' ),
                'description' => __( 'Table section', 'protherics' ),
                'render_callback' => 'protherics_render_acf_block',
                'category' => 'protherics-blocks',
                'icon' => 'image-flip-horizontal',
                'keywords' => array(),
            )
        );

        acf_register_block(
            array(
                'name' => 'cta',
                'title' => __( 'CTA', 'protherics' ),
                'description' => __( 'CTA section', 'protherics' ),
                'render_callback' => 'protherics_render_acf_block',
                'category' => 'protherics-blocks',
                'icon' => 'image-flip-horizontal',
                'keywords' => array(),
            )
        );

        acf_register_block(
            array(
                'name' => 'icons-text',
                'title' => __( 'Icons with text', 'protherics' ),
                'description' => __( 'Icons with text section', 'protherics' ),
                'render_callback' => 'protherics_render_acf_block',
                'category' => 'protherics-blocks',
                'icon' => 'image-flip-horizontal',
                'keywords' => array(),
            )
        );

        acf_register_block(
            array(
                'name' => 'accordion-text',
                'title' => __( 'Accordion with text', 'protherics' ),
                'description' => __( 'Accordion with text section', 'protherics' ),
                'render_callback' => 'protherics_render_acf_block',
                'category' => 'protherics-blocks',
                'icon' => 'image-flip-horizontal',
                'keywords' => array(),
            )
        );

        acf_register_block(
            array(
                'name' => 'timeline-news',
                'title' => __( 'Timeline news', 'protherics' ),
                'description' => __( 'Timeline news section', 'protherics' ),
                'render_callback' => 'protherics_render_acf_block',
                'category' => 'protherics-blocks',
                'icon' => 'image-flip-horizontal',
                'keywords' => array(),
            )
        );

        acf_register_block(
            array(
                'name' => 'insights-listing',
                'title' => __( 'Insights listing', 'protherics' ),
                'description' => __( 'Insights listing section', 'protherics' ),
                'render_callback' => 'protherics_render_acf_block',
                'category' => 'protherics-blocks',
                'icon' => 'image-flip-horizontal',
                'keywords' => array(),
            )
        );

        acf_register_block(
            array(
                'name' => 'locations',
                'title' => __( 'Locations', 'protherics' ),
                'description' => __( 'Locations section', 'protherics' ),
                'render_callback' => 'protherics_render_acf_block',
                'category' => 'protherics-blocks',
                'icon' => 'image-flip-horizontal',
                'keywords' => array(),
            )
        );

        acf_register_block(
            array(
                'name' => 'products',
                'title' => __( 'Products', 'protherics' ),
                'description' => __( 'Products section', 'protherics' ),
                'render_callback' => 'protherics_render_acf_block',
                'category' => 'protherics-blocks',
                'icon' => 'image-flip-horizontal',
                'keywords' => array(),
            )
        );

        acf_register_block(
            array(
                'name' => 'three-columns',
                'title' => __( 'Three columns', 'protherics' ),
                'description' => __( 'Three columns section', 'protherics' ),
                'render_callback' => 'protherics_render_acf_block',
                'category' => 'protherics-blocks',
                'icon' => 'image-flip-horizontal',
                'keywords' => array(),
            )
        );

        acf_register_block(
            array(
                'name' => 'timeline',
                'title' => __( 'Timeline', 'protherics' ),
                'description' => __( 'Timeline section', 'protherics' ),
                'render_callback' => 'protherics_render_acf_block',
                'category' => 'protherics-blocks',
                'icon' => 'image-flip-horizontal',
                'keywords' => array(),
            )
        );

    }
}
add_action( 'acf/init', 'protherics_acf_gutenberg_blocks' );

/**
 * List of the blocks allowed
 * Only custom blocks (AFC) are allowed
 */
function protherics_allowed_block_types( $allowed_blocks, $post ) {
    $blocks = array();
    if ( $post->post->post_type == 'insights' || $post->post->post_type == 'news' ) {
        $blocks = array(
            'core/heading',
            'core/paragraph',
            'core/image',
            'core/embed',
            'core/quote',
			'core/gallery',
            'core/list',
            'acf/table-insights',
        );
    } else if ( $post->post->post_type == 'members' ) {
        $blocks = array(
            'core/heading',
            'core/paragraph',
        );
    } else {
        $blocks = array(
            'acf/two-col',
            'acf/hero',
            'acf/img-text',
            'acf/latest-insights',
            'acf/latest-news-social',
            'acf/hero-text',
            'acf/text',
            'acf/banner-slider',
            'acf/banner-slider-light',
            'acf/team-members',
            'acf/table',
            'acf/cta',
            'acf/icons-text',
            'acf/accordion-text',
            'acf/timeline-news',
            'acf/insights-listing',
            'acf/locations',
            'acf/products',
            'core/shortcode',
            'acf/three-columns',
            'acf/timeline',
        );
    }
    return $blocks;
}
add_filter( 'allowed_block_types_all', 'protherics_allowed_block_types', 10, 2 );
