<?php
/**
 * Class Folders Forms
 *
 * @author  : Premio <contact@premio.io>
 * @license : GPL2
 * */

if (! defined('ABSPATH')) {
    exit;
}

class WCP_Pro_Forms
{


    /**
     * Define the core functionality of the plugin.
     *
     * @since 1.0.0
     */
    public function __construct()
    {

    }//end __construct()


    /**
     * Return form HTML data
     *
     * @since  1.0.0
     * @access public
     * @return $html
     */
    public static function get_form_html($hasValidKey, $activateURL, $userRole = "admin")
    {
        ob_start();
        $customizeFolders = get_option('customize_folders');
        $showInPage       = !isset($customizeFolders['use_shortcuts']) ? "yes" : $customizeFolders['use_shortcuts'];
        ?>

        <div class="wcp-custom-form">
            <div class="form-title">
                <div class="plugin-title">
                    <?php esc_html_e("Folders", 'folders'); ?>
                    <span class="folder-loader-ajax">
                        <svg id="successAnimation" fill="#F51366" class="animated" xmlns="http://www.w3.org/2000/svg" width="70" height="70" viewBox="0 0 70 70">
                            <path id="successAnimationResult" fill="#D8D8D8" d="M35,60 C21.1928813,60 10,48.8071187 10,35 C10,21.1928813 21.1928813,10 35,10 C48.8071187,10 60,21.1928813 60,35 C60,48.8071187 48.8071187,60 35,60 Z M23.6332378,33.2260427 L22.3667622,34.7739573 L34.1433655,44.40936 L47.776114,27.6305926 L46.223886,26.3694074 L33.8566345,41.59064 L23.6332378,33.2260427 Z"></path>
                            <circle id="successAnimationCircle" cx="35" cy="35" r="24" stroke="#979797" stroke-width="2" stroke-linecap="round" fill="transparent"></circle>
                            <polyline id="successAnimationCheck" stroke="#979797" stroke-width="2" points="23 34 34 43 47 27" fill="transparent"></polyline>
                        </svg>
                    </span>
                </div>
                <div class="plugin-button">
                    <?php if ($showInPage == "yes") { ?>
                        <a href="#" class="view-shortcodes folder-tooltip" data-folder-tooltip="<?php esc_html_e("Press Ctrl+K to view keyboard shortcuts", 'folders'); ?>"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg></a>
                    <?php } ?>
                    <?php if($userRole == "admin") { ?>
                        <a href="javascript:;" class="add-new-folder" id="add-new-folder">
                            <span class="create_new_folder"><i class="pfolder-add-folder"></i></span> <span><?php esc_html_e("New Folder", 'folders') ?></span>
                        </a>
                    <?php } ?>
                </div>
                <div class="clear"></div>
            </div>
            <div class="form-options">
                <ul>
                    <li>
                        <a href="javascript:;" class="expand-collapse folder-tooltip" id="expand-collapse-list" data-folder-tooltip="<?php esc_html_e("Expand", 'folders'); ?>">
                            <span class="icon pfolder-arrow-down"></span> <span class="text"><?php esc_html_e("Expand", 'folders'); ?></span>
                        </a>
                    </li>
                    <li>
                        <div class="form-options">
                            <ul>
                                <li class="last folder-order">
                                    <?php if($userRole == "admin" || $userRole == "view-edit") { ?>
                                        <a data-folder-tooltip="Sort Folders" href="javascript:;" id="sort-order-list" class="sort-folder-order folder-tooltip">
                                            <span class="icon pfolder-arrow-sort"></span> <span class="text"><?php esc_html_e("Sort", 'folders'); ?></span>
                                        </a>
                                        <div class="folder-sort-menu <?php echo ($hasValidKey) ? "" : "is-pro" ?>">
                                            <ul>
                                                <li><a data-sort="a-z" href="#"><?php esc_html_e("A → Z", 'folders') ?></a></li>
                                                <li><a data-sort="z-a" href="#"><?php esc_html_e("Z → A", 'folders') ?></a></li>
                                                <?php if ($hasValidKey) { ?>
                                                    <li><a data-sort="n-o" href="#"><?php esc_html_e("Sort by newest", 'folders') ?></a></li>
                                                    <li><a data-sort="o-n" href="#"><?php esc_html_e("Sort by oldest", 'folders') ?></a></li>
                                                <?php } else { ?>
                                                    <li><a data-sort="n-o" target="_blank" class="pro-feature" href="<?php echo esc_url($activateURL) ?>"><?php esc_html_e("Sort by newest", 'folders') ?> <span><?php esc_html_e("(Activate)", 'folders') ?></span></a></li>
                                                    <li><a data-sort="o-n" target="_blank" class="pro-feature" href="<?php echo esc_url($activateURL) ?>"><?php esc_html_e("Sort by oldest", 'folders') ?> <span><?php esc_html_e("(Activate)", 'folders') ?></span></a></li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    <?php }?>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
                <?php if (!($hasValidKey)) { ?>
                    <div class="upgrade-message">
                        <span class="upgrade-message"><a target="_blank" class="pink" href="<?php echo esc_url($activateURL) ?>"><?php esc_html_e("Activate your license key", 'folders') ?> <span class="dashicons dashicons-arrow-right-alt"></span></a></span>
                    </div>
                <?php } ?>
            </div>
            <div class="form-loader">
                <div class="form-loader-count"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();

    }//end get_form_html()


}//end class

