<?php
/**
 * Admin form folder settings
 *
 * @author  : Premio <contact@premio.io>
 * @license : GPL2
 * */

if (! defined('ABSPATH')) {
    exit;
}
?>
<style>
    <?php
    $string = "";
    global $typenow;
    $width = get_option("wcp_dynamic_width_for_".$typenow);
    $width = intval($width);
    if ($width == null || empty($width) || $width > 1200) {
        $width = 280;
    }

    $width            = ($width - 40);
    $customizeFolders = get_option('customize_folders');
    $customizeFolders = (empty($customizeFolders)||!is_array($customizeFolders))?[]:$customizeFolders;
    $dynamicFolders   = (!isset($customizeFolders['dynamic_folders']) || (isset($customizeFolders['dynamic_folders']) && $customizeFolders['dynamic_folders'] == "on")) ? 1 : 0;

    if ($dynamicFolders) {
        if (isset($customizeFolders['dynamic_folders_for_admin_only']) && $customizeFolders['dynamic_folders_for_admin_only'] == "on") {
            $userID    = get_current_user_id();
            $userMeta  = get_userdata($userID);
            $userRoles = $userMeta->roles;
            $userRoles = !is_array($userRoles) ? [] : $userRoles;
            if (!in_array("administrator", $userRoles)) {
                $dynamicFolders = 0;
            }
        }
    }
    ?>
</style>
<style>
<?php
$fontFamily = "";
if (isset($customizeFolders['folder_font']) && !empty($customizeFolders['folder_font'])) {
    $fontFamily  = $customizeFolders['folder_font'];
    $folderFonts = self::get_font_list();
    if (isset($folderFonts[$fontFamily])) {
        if ($fontFamily == "System Stack") {
            $fontFamily = "-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen-Sans,Ubuntu,Cantarell,Helvetica Neue,sans-serif";
        }
        ?>
        .wcp-container, .folder-popup-form, .dynamic-menu { font-family: <?php echo esc_attr($fontFamily) ?>; }
        <?php
    }

    if ($fontFamily == "-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen-Sans,Ubuntu,Cantarell,Helvetica Neue,sans-serif") {
        $fontFamily = "System Stack";
    }

    if ($folderFonts[$fontFamily] == "Default") {
        $fontFamily = "";
    }
}

if (!isset($customizeFolders['new_folder_color']) || empty($customizeFolders['new_folder_color'])) {
    $customizeFolders['new_folder_color'] = "#f51366";
}

if (!isset($customizeFolders['folder_bg_color']) || empty($customizeFolders['folder_bg_color'])) {
    $customizeFolders['folder_bg_color'] = "#f51366";
}

if (!isset($customizeFolders['bulk_organize_button_color']) || empty($customizeFolders['bulk_organize_button_color'])) {
    $customizeFolders['bulk_organize_button_color'] = "#f51366";
}

if (!isset($customizeFolders['default_icon_color']) || empty($customizeFolders['default_icon_color'])) {
    $customizeFolders['default_icon_color'] = "#334155";
}

$rgbColor = self::hexToRgb($customizeFolders['folder_bg_color']);
?>
.add-new-folder { background-color: <?php echo esc_attr($customizeFolders['new_folder_color']) ?>; border-color: <?php echo esc_attr($customizeFolders['new_folder_color']) ?> }
.jstree-closed > i.jstree-icon, .jstree-open > i.jstree-icon { color: <?php echo esc_attr($customizeFolders['folder_bg_color']) ?>; }
.wcp-hide-show-buttons .toggle-buttons { background-color: <?php echo esc_attr($customizeFolders['new_folder_color']) ?>; }
.folders-toggle-button span { background-color: <?php echo esc_attr($customizeFolders['new_folder_color']) ?>; }
.ui-resizable-handle.ui-resizable-e:before, .ui-resizable-handle.ui-resizable-w:before {border-color: <?php echo esc_attr($customizeFolders['new_folder_color']) ?>;}
button.button.organize-button { background-color: <?php echo esc_attr($customizeFolders['bulk_organize_button_color']) ?>; border-color: <?php echo esc_attr($customizeFolders['bulk_organize_button_color']) ?>; }
button.button.organize-button:hover { background-color: <?php echo esc_attr($customizeFolders['bulk_organize_button_color']) ?>; border-color: <?php echo esc_attr($customizeFolders['bulk_organize_button_color']) ?>; }
body:not(.no-hover-css) #custom-scroll-menu .jstree-hovered:not(.jstree-clicked), body:not(.no-hover-css) #custom-scroll-menu .jstree-hovered:not(.jstree-clicked):hover, .dynamic-menu a.active, .dynamic-menu a:hover, .folder-setting-menu li a:hover { background: rgba(<?php echo esc_attr($rgbColor['r'].",".$rgbColor['g'].",".$rgbColor['b'].", 0.08") ?>) !important; color: #333333;}
.dynamic-menu li.color-folder:hover {background: rgba(<?php echo esc_attr($rgbColor['r'].",".$rgbColor['g'].",".$rgbColor['b'].", 0.08") ?>) !important; color: #333333;}
.dynamic-menu li.color-folder:hover a {background: transparent !important; color: #333333;}

body:not(.no-hover-css) #custom-scroll-menu .jstree-anchor:not(.jstree-clicked):focus { background: rgba(<?php echo esc_attr($rgbColor['r'].",".$rgbColor['g'].",".$rgbColor['b'].", 0.08") ?>) !important; color: #333333; }
body:not(.no-hover-css) #custom-scroll-menu .jstree-clicked, body:not(.no-hover-css) #custom-scroll-menu .jstree-clicked:not(.jstree-clicked):focus, #custom-scroll-menu .jstree-clicked, #custom-scroll-menu .jstree-clicked:hover { background: <?php echo esc_attr($customizeFolders['folder_bg_color']) ?> !important; color: #ffffff !important; }
.custom-scroll-menu.hor-scroll .horizontal-scroll-menu .jstree-clicked .folder-actions { background: <?php echo esc_attr($customizeFolders['folder_bg_color']) ?> !important; color: #ffffff !important; }
#custom-scroll-menu .jstree-hovered.wcp-drop-hover, #custom-scroll-menu .jstree-hovered.wcp-drop-hover:hover, #custom-scroll-menu .jstree-clicked.wcp-drop-hover, #custom-scroll-menu .jstree-clicked.wcp-drop-hover:hover, body #custom-scroll-menu  *.drag-in > a:hover { background: <?php echo esc_attr($customizeFolders['folder_bg_color']) ?> !important; color: #ffffff !important; }
body #custom-scroll-menu  *.drag-in > a:hover > .pfolder-folder-close { color: #fff !important; }
.drag-bot > a {
    border-bottom: solid 2px <?php echo esc_attr($customizeFolders['folder_bg_color']) ?>;
}
.drag-up > a {
    border-top: solid 2px <?php echo esc_attr($customizeFolders['folder_bg_color']) ?>;
}
#custom-scroll-menu .jstree-hovered:not(.jstree-clicked) .pfolder-folder-close {
    color: <?php echo esc_attr($customizeFolders['folder_bg_color']) ?>;
}
.folders-action-menu > ul > li > a:not(.disabled):hover, .folders-action-menu > ul > li > label:not(.disabled):hover {
    color: <?php echo esc_attr($customizeFolders['folder_bg_color']) ?>;
}
.dynamic-menu a.active span i, .dynamic-menu a:hover span i, .dynamic-menu a.active span.dashicons, .dynamic-menu a:hover span.dashicons { color: <?php echo esc_attr($customizeFolders['folder_bg_color']) ?> }
body:not(.no-hover-css) #custom-scroll-menu *.drag-in > a.jstree-hovered, body:not(.no-hover-css) #custom-scroll-menu *.drag-in > a.jstree-hovered:hover {
    background: <?php echo esc_attr($customizeFolders['folder_bg_color']) ?> !important;
    color: #fff !important;
}
.orange-bg > span ,.wcp-container .route.active-item > h3.title, .header-posts a.active-item, .un-categorised-items.active-item, .sticky-folders ul li a.active-item { background-color: <?php echo esc_attr($customizeFolders['folder_bg_color']) ?> !important; color: #ffffff; }
body:not(.no-hover-css) .wcp-container .route .title:hover, body:not(.no-hover-css) .header-posts a:hover, body:not(.no-hover-css) .un-categorised-items:hover, body:not(.no-hover-css) .sticky-folders ul li a:hover {background: rgba(<?php echo esc_attr($rgbColor['r'].",".$rgbColor['g'].",".$rgbColor['b'].", 0.08") ?>);}
.wcp-drop-hover {
    background: <?php echo esc_attr($customizeFolders['folder_bg_color']) ?> !important;
}
#custom-menu .route .nav-icon .wcp-icon {color: <?php echo esc_attr($customizeFolders['folder_bg_color']) ?> !important;}
.mCS-3d.mCSB_scrollTools .mCSB_dragger .mCSB_dragger_bar { background: <?php echo esc_attr($customizeFolders['folder_bg_color']) ?> !important; }
.os-theme-dark>.os-scrollbar>.os-scrollbar-track>.os-scrollbar-handle { background: <?php echo esc_attr($customizeFolders['folder_bg_color']) ?> !important; }
.jstree-node.drag-in > a.jstree-anchor.jstree-hovered { background-color: <?php echo esc_attr($customizeFolders['folder_bg_color']) ?> !important; color: #ffffff; }
.pfolder-folder-close {color: <?php echo esc_attr($customizeFolders['default_icon_color']) ?>}
<?php
if (isset($customizeFolders['folder_size']) && !empty($customizeFolders['folder_size'])) {
    if ($customizeFolders['folder_size'] == "custom") {
        $customizeFolders['folder_size'] = ! isset($customizeFolders['folder_custom_font_size']) || empty($customizeFolders['folder_custom_font_size']) ? "16" : $customizeFolders['folder_custom_font_size'];
    }
    ?>
    .wcp-container .route span.title-text, .header-posts a, .un-categorised-items a, .sticky-title, .sticky-folders > ul > li > a, .jstree-default .jstree-anchor { font-size: <?php echo esc_attr($customizeFolders['folder_size']) ?>px; }
    <?php
}
?>
</style>
<?php if (!empty($fontFamily)) {
    wp_enqueue_style('custom-google-fonts', 'https://fonts.googleapis.com/css?family='.urlencode($fontFamily), false);
} ?>
<div id="media-css">

</div>
<?php
$optionName = $typenow."_parent_status";
$status     = get_option($optionName);
global $typenow;
$title = ucfirst($typenow);
if ($typenow == "page") {
    $title = "Pages";
} else if ($typenow == "post") {
    $title = "Posts";
} else if ($typenow == "attachment") {
    $title = "Files";
} else {
    $postType  = $typenow;
    $postTypes = get_post_types([ "name" => $postType], 'objects');
    if (!empty($postTypes) && is_array($postTypes) && isset($postTypes[$postType]) && isset($postTypes[$postType]->label)) {
        $title = $postTypes[$postType]->label;
    }
}

if(!isset($isForPlugins)) {
    $displayStatus = "wcp_dynamic_display_status_" . $typenow;
} else {
    $displayStatus = "wcp_dynamic_display_status_" . $this->type;
}
$displayStatus = get_option($displayStatus);
$className     = isset($displayStatus) && $displayStatus == "hide" ? "hide-folders-area" : "";
$activeClass   = (isset($displayStatus) && $displayStatus == "hide") ? "" : "active";
$activeClass2  = (isset($displayStatus) && $displayStatus == "hide") ? "active" : "";

// Do not change here, Free/Pro Class name
$postType       = WCP_Pro_Folders::get_custom_post_type($typenow);
$active         = "";
$activeAllClass = "";
if (!empty($postType)) {
    if (isset($_REQUEST[$postType]) && $_REQUEST[$postType] == -1) {
        $active = "active-item";
    }

    if (!isset($_REQUEST[$postType]) || $_REQUEST[$postType] == "") {
        $activeAllClass = "active-item";
    }

    if(isset($_REQUEST['dynamic_folder'])) {
        $activeAllClass = "";
    }
}
$horClass = (!isset($customizeFolders['enable_horizontal_scroll']) || $customizeFolders['enable_horizontal_scroll'] == "on") ? "hor-scroll" : "";
if(!isset($isForPlugins)) {
?>
<div id="wcp-content" class="<?php echo esc_attr(isset($displayStatus) && $displayStatus == "hide" ? "hide-folders-area" : "")  ?>" >
    <div id="wcp-content-resize" class="user-<?php echo esc_attr($userRole) ?>">
        <div class="wcp-content">
            <div class="wcp-hide-show-buttons">
                <div class="toggle-buttons hide-folders <?php echo esc_attr($activeClass)  ?>"><span class="dashicons dashicons-arrow-left"></span></div>
                <div class="toggle-buttons show-folders <?php echo esc_attr($activeClass2) ?>"><span class="dashicons dashicons-arrow-right"></span></div>
            </div>
            <div class='wcp-container'>
                <div class="sticky-wcp-custom-form">
                    <?php echo $form_html ?>
                    <div class="top-settings">
                        <div class="folder-search-form">
                            <div class="form-search-input">
                                <input type="text" value="" id="folder-search" autocomplete="off" />
                                <span><i class="pfolder-search"></i></span>
                            </div>
                        </div>
                        <div class="folder-separator"></div>
                        <div class="header-posts">
                            <a href="javascript:;" class="all-posts <?php echo esc_attr($activeAllClass) ?>"><?php echo esc_html__("All ", 'folders').esc_attr($title) ?> <span class="total-count"><?php echo $ttpsts ?></span></a>
                        </div>
                        <div class="un-categorised-items <?php echo esc_attr($active) ?>">
                            <a href="javascript:;" class="un-categorized-posts"><?php echo esc_html__("Unassigned ", 'folders').esc_attr($title) ?> <span class="total-count total-empty"><?php echo $ttemp ?></span> </a>
                        </div>

                        <div class="sticky-folders">
                            <div class="sticky-title"><?php esc_html_e("Sticky Folders", "folders") ?> <span class="pull-right"><i class="pfolder-pin"></i></span></div>
                            <ul>
                                <?php
                                // echo $sticky_string ?>
                            </ul>
                        </div>
                        <div class="folder-separator-2"></div>
                        <?php $folder_status = get_option("premio_folder_sticky_status_".$typenow) ?>
                        <?php if($userRole == "admin") { ?>
                            <div class="sr-only">
                                <input type="file" name="upload_media_folder[]" disabled id="upload_media_folder" class="upload-media-action"  multiple directory="" webkitdirectory="" mozdirectory="" />
                            </div>
                        <?php } ?>
                        <div class="folders-action-menu">
                            <ul>
                                <?php if($userRole == "admin" || $userRole == "view-edit") { ?>
                                    <li style="flex: 0 0 22px;"><a href="javascript:;" class="no-bg"><input type="checkbox" id="menu-checkbox" ></a></li>
                                <?php } else { ?>
                                    <li style="flex: 0 0 22px;"><a href="javascript:;" class="no-bg"><input type="checkbox" disabled /></a></li>
                                <?php } ?>
                                <?php if($userRole == "admin") { ?>
                                    <li><label for="upload_media_folder" class="folder-tooltip upload-media-action disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Upload Folder", "folders"); ?>" ><span class="dashicons dashicons-cloud-upload"></span></label></li>
                                <?php } else { ?>
                                    <li><label class="folder-tooltip disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Upload Folder", "folders"); ?>" ><span class="dashicons dashicons-cloud-upload"></span></label></li>
                                <?php } ?>
                                <?php if($userRole == "admin" || $userRole == "view-edit") { ?>
                                    <li><a class="folder-tooltip cut-folder-action disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Cut", "folders"); ?>"><span class="pfolder-cut"></span></a></li>
                                    <li><a class="folder-tooltip copy-folder-action disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Copy", "folders"); ?>"><span class="pfolder-copy"></span></a></li>
                                    <li><a class="folder-tooltip paste-folder-action disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Paste", "folders"); ?>"><span class="pfolder-paste"></span></a></li>
                                    <!--<li><a class="folder-tooltip undo-folder-action disabled" href="javascript:;" data-folder-tooltip="<?php /*esc_html_e("Undo Changes", "folders"); */?>"><span class="pfolder-undo"></span></a></li>-->
                                    <li class="folder-settings-btn1">
                                        <a class="lock-unlock-all-folders folder-tooltip open-folders" href="#" data-folder-tooltip="<?php esc_html_e("Lock all folders", "folders"); ?>"><span class="dashicons dashicons-lock"></span></a>
                                        <!--<div class="folder-setting-menu">
                                            <ul>
                                                <li><a href="javascript:;" id="lock-all-folder"><span class="dashicons dashicons-lock"></span> <?php /*esc_html_e("Lock all folders", "folders"); */?></a></li>
                                                <li><a href="javascript:;" id="unlock-all-folder"><span class="dashicons dashicons-unlock"></span> <?php /*esc_html_e("Unlock all folders", "folders"); */?></a></li>
                                            </ul>
                                        </div>-->
                                    </li>
                                    <li><a class="folder-tooltip delete-folder-action disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Delete", "folders"); ?>"><span class="pfolder-remove"></span></a></li>
                                <?php } else { ?>
                                    <li><a class="folder-tooltip disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Cut", "folders"); ?>"><span class="pfolder-cut"></span></a></li>
                                    <li><a class="folder-tooltip disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Copy", "folders"); ?>"><span class="pfolder-copy"></span></a></li>
                                    <li><a class="folder-tooltip disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Paste", "folders"); ?>"><span class="pfolder-paste"></span></a></li>
                                    <li><a class="folder-tooltip disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Lock all folders", "folders"); ?>"><span class="dashicons dashicons-lock"></span></a></li>
                                    <li><a class="folder-tooltip disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Delete", "folders"); ?>"><span class="pfolder-remove"></span></a></li>
                                <?php } ?>
                            </ul>
                        </div>
                        <div class="folder-separator-2"></div>
                    </div>
                </div>
                <div id="custom-scroll-menu" class="custom-scroll-menu <?php echo esc_attr($horClass) ?>">
                    <div class="horizontal-scroll-menu">
                        <div class="ajax-preloader">
                            <div class="cssload-container">
                                <div class="cssload-tube-tunnel"></div>
                            </div>
                        </div>

                        <?php if ($dynamicFolders) { ?>
                            <div class="dynamic-tree-data">
                                <?php
                                $class = "";
                                if (get_option("folders_".$typenow."dynamic-folders") == 1) {
                                    $class = " jstree-open";
                                }
                                ?>
                                <div class="dynamic-folders" id="dynamic-tree-folders">
                                    <ul class="dynamic-folders-list">
                                        <li id="dynamic-folders" class="<?php echo esc_attr($class) ?>">
                                            <?php esc_html_e("Dynamic Folders", "folders") ?>
                                            <span class="folder-tooltip" data-folder-tooltip="<?php esc_html_e("Use this to sort folders by metadata such as Authors, Dates, Categories, Dates, Extensions", "folders"); ?>"><span class="dashicons dashicons-editor-help"></span></span>
                                            <ul>
                                                <?php echo $authors ?>
                                                <?php echo $dates ?>
                                                <?php echo $extensions ?>
                                                <?php echo $post_categories ?>
                                                <?php echo $page_hierarchy ?>
                                                <?php if(!empty($otherCategories)) {
                                                    foreach($otherCategories as $category) {
                                                        echo $category;
                                                    }
                                                } ?>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="dynamic-tree-data-separator"></div>
                        <?php }//end if
                        ?>

                        <div class="js-tree-data">
                            <div id="js-tree-menu" class="<?php echo ($status == 1) ? "active" : "" ?>">
                                <div class="wcp-parent" id="title0"><i class="fa fa-folder-o"></i> All Folders</div>
                                <ul class='space first-space' id='space_0'>
                                    <?php echo $terms_data; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } else { ?>
    <div id="wcp-content" class="<?php echo esc_attr(isset($displayStatus) && $displayStatus == "hide" ? "hide-folders-area" : "")  ?>" >
        <div id="wcp-content-resize" class="user-<?php echo esc_attr($userRole) ?>">
            <div class="wcp-content">
                <div class="wcp-hide-show-buttons">
                    <div class="toggle-buttons hide-folders <?php echo esc_attr($activeClass)  ?>"><span class="dashicons dashicons-arrow-left"></span></div>
                    <div class="toggle-buttons show-folders <?php echo esc_attr($activeClass2) ?>"><span class="dashicons dashicons-arrow-right"></span></div>
                </div>
                <div class='wcp-container'>
                    <div class="sticky-wcp-custom-form">
                        <?php echo $form_html ?>
                        <div class="top-settings">
                            <div class="folder-search-form">
                                <div class="form-search-input">
                                    <input type="text" value="" id="folder-search" autocomplete="off" />
                                    <span><i class="pfolder-search"></i></span>
                                </div>
                            </div>
                            <div class="folder-separator"></div>
                            <div class="header-posts">
                                <a href="javascript:;" class="all-posts <?php echo esc_attr($activeAllClass) ?>"><?php echo esc_html__("All ", 'folders').esc_attr($title) ?> <span class="total-count"><?php echo $ttpsts ?></span></a>
                            </div>
                            <div class="un-categorised-items <?php echo esc_attr($active) ?>">
                                <a href="javascript:;" class="un-categorized-posts"><?php echo esc_html__("Unassigned ", 'folders').esc_attr($title) ?> <span class="total-count total-empty"><?php echo $ttemp ?></span> </a>
                            </div>

                            <div class="sticky-folders">
                                <div class="sticky-title"><?php esc_html_e("Sticky Folders", "folders") ?> <span class="pull-right"><i class="pfolder-pin"></i></span></div>
                                <ul>
                                    <?php
                                    // echo $sticky_string ?>
                                </ul>
                            </div>
                            <div class="folder-separator-2"></div>
                            <?php $folder_status = get_option("premio_folder_sticky_status_".$typenow) ?>
                            <?php if($userRole == "admin") { ?>
                                <div class="sr-only">
                                    <input type="file" name="upload_media_folder[]" disabled id="upload_media_folder" class="upload-media-action"  multiple directory="" webkitdirectory="" mozdirectory="" />
                                </div>
                            <?php } ?>
                            <div class="folders-action-menu">
                                <ul>
                                    <?php if($userRole == "admin" || $userRole == "view-edit") { ?>
                                        <li style="flex: 0 0 22px;"><a href="javascript:;" class="no-bg"><input type="checkbox" id="menu-checkbox" ></a></li>
                                    <?php } else { ?>
                                        <li style="flex: 0 0 22px;"><a href="javascript:;" class="no-bg"><input type="checkbox" disabled /></a></li>
                                    <?php } ?>
                                    <?php if($userRole == "admin") { ?>
                                        <li><label for="upload_media_folder" class="folder-tooltip upload-media-action disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Upload Folder", "folders"); ?>" ><span class="dashicons dashicons-cloud-upload"></span></label></li>
                                    <?php } else { ?>
                                        <li><label class="folder-tooltip disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Upload Folder", "folders"); ?>" ><span class="dashicons dashicons-cloud-upload"></span></label></li>
                                    <?php } ?>
                                    <?php if($userRole == "admin" || $userRole == "view-edit") { ?>
                                        <li><a class="folder-tooltip cut-folder-action disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Cut", "folders"); ?>"><span class="pfolder-cut"></span></a></li>
                                        <li><a class="folder-tooltip copy-folder-action disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Copy", "folders"); ?>"><span class="pfolder-copy"></span></a></li>
                                        <li><a class="folder-tooltip paste-folder-action disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Paste", "folders"); ?>"><span class="pfolder-paste"></span></a></li>
                                        <!--<li><a class="folder-tooltip undo-folder-action disabled" href="javascript:;" data-folder-tooltip="<?php /*esc_html_e("Undo Changes", "folders"); */?>"><span class="pfolder-undo"></span></a></li>-->
                                        <li class="folder-settings-btn1">
                                            <a class="lock-unlock-all-folders folder-tooltip open-folders" href="#" data-folder-tooltip="<?php esc_html_e("Lock all folders", "folders"); ?>"><span class="dashicons dashicons-lock"></span></a>
                                            <!--<div class="folder-setting-menu">
                                            <ul>
                                                <li><a href="javascript:;" id="lock-all-folder"><span class="dashicons dashicons-lock"></span> <?php /*esc_html_e("Lock all folders", "folders"); */?></a></li>
                                                <li><a href="javascript:;" id="unlock-all-folder"><span class="dashicons dashicons-unlock"></span> <?php /*esc_html_e("Unlock all folders", "folders"); */?></a></li>
                                            </ul>
                                        </div>-->
                                        </li>
                                        <li><a class="folder-tooltip delete-folder-action disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Delete", "folders"); ?>"><span class="pfolder-remove"></span></a></li>
                                    <?php } else { ?>
                                        <li><a class="folder-tooltip disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Cut", "folders"); ?>"><span class="pfolder-cut"></span></a></li>
                                        <li><a class="folder-tooltip disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Copy", "folders"); ?>"><span class="pfolder-copy"></span></a></li>
                                        <li><a class="folder-tooltip disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Paste", "folders"); ?>"><span class="pfolder-paste"></span></a></li>
                                        <li><a class="folder-tooltip disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Lock all folders", "folders"); ?>"><span class="dashicons dashicons-lock"></span></a></li>
                                        <li><a class="folder-tooltip disabled" href="javascript:;" data-folder-tooltip="<?php esc_html_e("Delete", "folders"); ?>"><span class="pfolder-remove"></span></a></li>
                                    <?php } ?>
                                </ul>
                            </div>
                            <div class="folder-separator-2"></div>
                        </div>
                    </div>
                    <div id="custom-scroll-menu" class="custom-scroll-menu <?php echo esc_attr($horClass) ?>">
                        <div class="horizontal-scroll-menu">
                            <div class="ajax-preloader">
                                <div class="cssload-container">
                                    <div class="cssload-tube-tunnel"></div>
                                </div>
                            </div>

                            <?php
                            if ($dynamicFolders) { ?>
                                <div class="dynamic-tree-data">
                                    <?php
                                    $class = "";
                                    if (get_option("folders_folders4pluginsdynamic-folders") == 1) {
                                        $class = " jstree-open";
                                    }
                                    ?>
                                    <div class="dynamic-folders" id="dynamic-tree-folders">
                                        <ul class="dynamic-folders-list">
                                            <li id="dynamic-folders" class="<?php echo esc_attr($class) ?>">
                                                <?php esc_html_e("Dynamic Folders", "folders") ?>
                                                <ul>
                                                    <?php
                                                    if(class_exists("WP_Plugins_List_Table")) {
                                                        $wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
                                                        $views = $wp_list_table->get_views();
                                                        if(!empty($views)) {
                                                            foreach($views as $key=>$view) {
                                                                echo "<li data-key='{$key}' id='folder-{$key}'>{$view}</li>";
                                                            }
                                                        }
                                                    } ?>
                                                </ul>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="dynamic-tree-data-separator"></div>
                            <?php }//end if
                            ?>

                            <div class="js-tree-data">
                                <div id="js-tree-menu" class="<?php echo ($status == 1) ? "active" : "" ?>">
                                    <ul class='space first-space' id='space_0'>
                                        <?php echo $terms_data; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php }
include_once "modals.php";
$hide_folder_color_pop_up = get_option("hide_folder_color_pop_up");
if($hide_folder_color_pop_up != "yes" && WCP_PRO_FOLDER_VERSION == "3.0") {
    $customize_folders = get_option("customize_folders");
    if (isset($customize_folders['show_folder_in_settings']) && $customize_folders['show_folder_in_settings'] == "yes") {
        $setting_url = admin_url("options-general.php?page=wcp_folders_settings&setting_page=customize-folders&focus=icon-color");
    } else {
        $setting_url = admin_url("admin.php?page=wcp_folders_settings&setting_page=customize-folders&focus=icon-color");
    }
    ?>
    <div class="folder-popup-form color-popup-options" id="color-pop-up-options" style="display: block">
        <div class="popup-form-content">
            <div class="popup-form-data">
                <div class="close-popup-button">
                    <a class="" href="javascript:;"><span></span></a>
                </div>
                <div class="folder-popup-top">
                    <img src="<?php echo esc_url(WCP_PRO_FOLDER_URL."assets/images/color-popup.png") ?>" />
                </div>
                <div class="folder-popup-bottom">
                    <div class="folder-color-title">
                        <?php esc_html_e("🎨 Set custom colors to folders icon", "folders") ?>
                    </div>
                    <div class="folder-color-desc">
                        <?php esc_html_e("You can now change the icon color for each folder from the menu and change the default icon color from the Folders settings.", "folders") ?>
                    </div>
                </div>
                <div class="folder-form-buttons">
                    <a href="javascript:;" class="form-cancel-btn"><?php esc_html_e("Cancel", "folders") ?></a>
                    <a href="<?php echo esc_url($setting_url) ?>" class="form-submit-btn customize-folder-color"><?php esc_html_e("Customise", "folders") ?></a>
                </div>
            </div>
        </div>
        <script>
            jQuery(document).ready(function(){
                jQuery(document).on("click", ".color-popup-options .form-cancel-btn, .color-popup-options .close-popup-button, #color-pop-up-options", function(e){
                    jQuery.ajax({
                        url: "<?php echo esc_url(admin_url("admin-ajax.php")) ?>",
                        data: {
                            action: 'hide_folder_color_pop_up',
                            nonce: '<?php echo esc_attr(wp_create_nonce('hide_folder_color_pop_up')) ?>'
                        },
                        type: 'post',
                        success: function(){

                        }
                    });
                });
            });
        </script>
    </div>
<?php } ?>
