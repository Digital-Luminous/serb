<?php
 
function protherics_subject_taxonomy() {
  $labels = array(
    'name' => _x( 'Subjects', 'taxonomy general name' ),
    'singular_name' => _x( 'Subject', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Subjects' ),
    'all_items' => __( 'All Subjects' ),
    'parent_item' => __( 'Parent Subject' ),
    'parent_item_colon' => __( 'Parent Subject:' ),
    'edit_item' => __( 'Edit Subject' ), 
    'update_item' => __( 'Update Subject' ),
    'add_new_item' => __( 'Add New Subject' ),
    'new_item_name' => __( 'New Subject Name' ),
    'menu_name' => __( 'Subjects' ),
  );    
 
  register_taxonomy( 'subjects', array( 'insights' ), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_in_rest' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'subject' ),
  ));
 
}
add_action( 'init', 'protherics_subject_taxonomy', 0 );

function protherics_region_taxonomy() {
  $labels = array(
    'name' => _x( 'Region', 'taxonomy general name' ),
    'singular_name' => _x( 'Region', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Region' ),
    'all_items' => __( 'All Region' ),
    'parent_item' => __( 'Parent Region' ),
    'parent_item_colon' => __( 'Parent Region:' ),
    'edit_item' => __( 'Edit Region' ), 
    'update_item' => __( 'Update Region' ),
    'add_new_item' => __( 'Add New Region' ),
    'new_item_name' => __( 'New Region Name' ),
    'menu_name' => __( 'Regions' ),
  );    
 
  register_taxonomy( 'regions', array( 'locations' ), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_in_rest' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'region' ),
  ));
 
}
add_action( 'init', 'protherics_region_taxonomy', 0 );

function protherics_disease_area_taxonomy() {
  $labels = array(
    'name' => _x( 'Disease area', 'taxonomy general name' ),
    'singular_name' => _x( 'Disease area', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search disease area' ),
    'all_items' => __( 'All disease area' ),
    'parent_item' => __( 'Parent disease area' ),
    'parent_item_colon' => __( 'Parent disease area:' ),
    'edit_item' => __( 'Edit disease area' ), 
    'update_item' => __( 'Update disease area' ),
    'add_new_item' => __( 'Add New disease area' ),
    'new_item_name' => __( 'New disease area Name' ),
    'menu_name' => __( 'Disease area' ),
  );    
 
  register_taxonomy( 'disease-area', array( 'products' ), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_in_rest' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'disease-area' ),
  ));
 
}
add_action( 'init', 'protherics_disease_area_taxonomy', 0 );

function protherics_product_location_taxonomy() {
  $labels = array(
    'name' => _x( 'Locations', 'taxonomy general name' ),
    'singular_name' => _x( 'Location', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search location' ),
    'all_items' => __( 'All locations' ),
    'parent_item' => __( 'Parent location' ),
    'parent_item_colon' => __( 'Parent location:' ),
    'edit_item' => __( 'Edit location' ), 
    'update_item' => __( 'Update location' ),
    'add_new_item' => __( 'Add New location' ),
    'new_item_name' => __( 'New location Name' ),
    'menu_name' => __( 'Location' ),
  );    
 
  register_taxonomy( 'product-locations', array( 'products' ), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_in_rest' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'product-locations' ),
  ));
 
}
add_action( 'init', 'protherics_product_location_taxonomy', 0 );
