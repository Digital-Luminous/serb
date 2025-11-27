<?php
/**
 * Primary menu walker
 */

class Protherics_Walker_Nav_Menu extends Walker_Nav_Menu {

    public $parent_i = 0;

    // depth = 0 -> otvaranje prvog nivoa (children top level elemenata)
    // depth = 1 -> otvaranje trećeg nivoa (children drugog nivoa)
    function start_lvl( &$output, $depth = 0, $args = array() ) {
        // Za prvi nivo (depth 0) već si ručno otvorio <ul class="c-main-nav-sublist">,
        // zato ovde ne radimo ništa.
        if ( $depth === 0 ) {
            return;
        }

        // Treći nivo (depth 2) se otvara kada WP javi start_lvl sa depth=1
        if ( $depth === 1 ) {
            $output .= '<ul class="c-main-nav-sub__sublist">';
        }
    }

    function start_el( &$output, $item, $depth=0, $args = array(), $id = 0 ) {
        global $wp, $post;
        $path = get_template_directory_uri();
        $current_url = home_url( $wp->request );
        $classes = empty( $item->classes ) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        // filters in region
        $classes = apply_filters( 'nav_menu_item_classes', $classes, $item, $depth );
        $args    = apply_filters( 'nav_menu_item_args', $args, $item, $depth );

        $object = $item->object;
        $type   = $item->type;
        $title  = $item->title;

        $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
        $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
        $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
        $description = $item->description;
        $permalink   = $item->url;

        $active = ( in_array( 'current-menu-ancestor', $classes) 
            || in_array( 'current-menu-item', $classes ) 
            || in_array( 'current_page_item', $classes ) 
            || in_array( 'current_page_parrent', $classes ) 
            || $permalink == $current_url 
        ) ? 'is-active ' : '';

        $desc = get_field( 'description', $item->ID );

        // TOP LEVEL (depth 0)
        if ( $depth == 0 ) {

            if ( in_array( 'menu-item-has-children', $item->classes ) ) {
                $classes[] = 'c-main-nav-list__item--has-children';
            }

            $output .= '<li class="c-main-nav-list__item ' . $active . implode( ' ', $classes ) . '">';

            if ( in_array( 'menu-item-has-children', $item->classes ) ) {
                $output .= '<button class="c-main-nav-list__button c-main-nav-list__link t-size-22 t-size-16--desktop ui-font-weight--bold ui-color--white-1 js-menu-btn">';
                $output .= $title;
                $output .= '</button>';

                $output .= '<div class="c-main-nav-list__submenu ui-bg--light-grey-01">';
                $output .= '<div class="l-inner">';
                $output .= '<div class="c-main-nav-submenu">';
                $output .= '<header class="c-main-nav-submenu__header">';
                $output .= '<p class="c-main-nav-list__title t-size-32--desktop ui-color--white-1 ui-font-weight--semibold">';
                $output .= $title;
                $output .= '</p>';
                $output .= '</header>';
                $output .= '<div class="c-main-nav-submenu__container">';
                $output .= '<div class="c-main-nav-submenu__description">';
                $output .= '<p class="c-main-nav-list__text ui-color--white-1 t-size-20--desktop">';
                $output .= $desc;
                $output .= '</p>';
                $output .= '</div>';
                $output .= '<div class="c-main-nav-submenu__sublist">';

                // OVDE ručno otvaraš UL za drugi nivo
                $output .= '<ul class="c-main-nav-sublist">';

            } else {
                $output .= '<a href="' . $permalink . '" class="c-main-nav-list__link t-size-22 t-size-16--desktop ui-font-weight--bold ui-color--white-1">';
                $output .= $title;
                $output .= '</a>';
            }

        // SECOND LEVEL (depth 1) – stavke unutar .c-main-nav-sublist
        } else if ( $depth == 1 ) {

            // Ako ovaj item ima još jedan nivo ispod sebe
            if ( in_array( 'menu-item-has-children', $item->classes ) ) {
                $classes[] = 'c-main-nav-sublist__item--has-children';
            }

            $output .= '<li class="c-main-nav-sublist__item ' . implode( ' ', $classes ) . '">';

            // Link drugog nivoa (uvek postoji, čak i ako ima children)
            $output .= '<a href="' . $permalink . '" class="c-main-nav-sublist__link ui-color--white-1">';
            $output .= $title;
            $output .= '</a>';
            // PAŽNJA: ovde NAMERNO ne zatvaramo </li>
            // Ako item ima decu, start_lvl(depth=1) će otvoriti <ul> unutar ovog <li>,
            // a end_el(depth=1) će ga zatvoriti.

        // THIRD LEVEL (depth 2) – klasična lista unutar drugog nivoa
        } else if ( $depth == 2 ) {

            $output .= '<li class="c-main-nav-sub-sublist__item ' . implode( ' ', $classes ) . '">';
            $output .= '<a href="' . $permalink . '" class="c-main-nav-sublist__link ui-color--white-1">';
            $output .= $title;
            $output .= '</a>';
            $output .= '</li>';
        }
    }

    function end_el(&$output, $item, $depth=0, $args=array()) {

        // TOP LEVEL
        if ( $depth == 0 ) {
            if ( in_array( 'menu-item-has-children', $item->classes ) ) {
                $output .= '</ul>'; // .c-main-nav-sublist
                $output .= '</div>'; // .c-main-nav-submenu__sublist
                $output .= '</div>'; // .c-main-nav-submenu__container
                $output .= '</div>'; // .c-main-nav-submenu
                $output .= '</div>'; // .l-inner

                $output .= '<div class="l-inner">';
                $output .= '<button class="c-close-button ui-color--white-1 js-close-submenu">';
                $output .= '<svg class="c-close-button__icon" width="1em" height="1em" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><title>Close</title><g fill="none" fill-rule="evenodd"><circle cx="20" cy="20" r="19.5" stroke="currentColor"></circle><path fill="currentColor" d="M10 20h20v1H10z"></path></g></svg>';
                $output .= '<span class="c-close-button__label ui-color--white-1 t-size-20">';
                $output .= 'Close menu';
                $output .='</span>';
                $output .= '</button>';
                $output .= '</div>';

                $output .= '</div>'; // .c-main-nav-list__submenu
                $output .= '</li>';   // zatvaranje top-level <li>

            } else {
                $output .= '</li>';
            }

        // SECOND LEVEL – sada ovde zatvaramo li
        } elseif ( $depth == 1 ) {
            $output .= '</li>';
        }

        // depth 2 već zatvaramo u start_el, tako da ne treba ništa
    }

    public function end_lvl( &$output, $depth = 0, $args = null ) {
        // Ni ovde ne diramo prvi nivo (depth 0),
        // jer si već ručno zatvorio UL u end_el kod depth 0.
        if ( $depth === 0 ) {
            return;
        }

        // Zatvaranje UL za treći nivo
        if ( $depth === 1 ) {
            $output .= '</ul>';
        }
    }
}