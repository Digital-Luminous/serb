<?php
/**
 * Admin folders settings for user roles and users
 *
 * @author  : Premio <contact@premio.io>
 * @license : GPL2
 * */

if (! defined('ABSPATH')) {
    exit;
}
?>
<div class="folder-user-settings <?php echo esc_attr(($folders_by_user_roles == "on")?"active":"") ?>">
    <div class="user-role-tab">
        <ul>
            <li class="active"><a href="#settings-roles"><?php esc_html_e("Roles", "folders") ?></a></li>
            <li><a href="#settings-users"><?php esc_html_e("Users", "folders") ?></a></li>
        </ul>
    </div>
    <div class="folders-by-user">
        <div class="user-role-setting active" id="settings-roles">
            <table class="import-export-table">
                <tr>
                    <td>
                        <?php
                        global $wp_roles;
                        $folderRoles = [
                            'admin' => esc_html__("Admin", "folders"),
                            'view-edit' => esc_html__("View & Edit", "folders"),
                            'view-only' => esc_html__("View Only", "folders"),
                            'no-access' => esc_html__("No Access", "folders"),
                        ];
                        if(isset($wp_roles->roles) && count($wp_roles->roles)) {
                            $allRoles = $wp_roles->roles;
                            ?>
                            <div class="role-setting-search">
                                <input type="text" id="role-search" placeholder="<?php esc_html_e("Search by user roles", "folders"); ?>" />
                                <button type="button">
                                    <svg width="15" height="16" viewBox="0 0 15 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.18065 0.72168C11.14 0.72168 14.3606 3.94225 14.3606 7.9016C14.3606 9.7696 13.6437 11.4733 12.4706 12.752L14.7789 15.0555C14.995 15.2715 14.9957 15.621 14.7797 15.837C14.672 15.9462 14.5297 16 14.3881 16C14.2473 16 14.1058 15.9462 13.9974 15.8385L11.6612 13.5088C10.4322 14.493 8.87401 15.0822 7.18065 15.0822C3.2213 15.0822 0 11.8609 0 7.9016C0 3.94225 3.2213 0.72168 7.18065 0.72168ZM7.18065 1.82764C3.83106 1.82764 1.10596 4.552 1.10596 7.9016C1.10596 11.2512 3.83106 13.9763 7.18065 13.9763C10.5295 13.9763 13.2546 11.2512 13.2546 7.9016C13.2546 4.552 10.5295 1.82764 7.18065 1.82764Z" fill="#ABABAB"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="role-settings default">
                                <div class="role-setting-left"><?php esc_html_e("Roles", "folders"); ?></div>
                                <div class="role-setting-right"><?php esc_html_e("Permissions", "folders"); ?></div>
                            </div>
                            <?php
                            $roleSettings = get_option("folders_role_access_settings");
                            $roleSettings = is_array($roleSettings)?$roleSettings:[];
                            foreach($allRoles as $key=>$role) {
                                $defaultRole = "no-access";
                                $userAccess = 4;
                                if(isset($role['capabilities']['manage_categories']) && $role['capabilities']['manage_categories']) {
                                    $defaultRole = "admin";
                                    $userAccess = 1;
                                } else if((isset($role['capabilities']['edit_posts']) && $role['capabilities']['edit_posts']) || (isset($role['capabilities']['edit_pages']) && $role['capabilities']['edit_pages'])) {
                                    $defaultRole = "view-edit";
                                    $userAccess = 2;
                                } else if(isset($role['capabilities']['upload_files']) && $role['capabilities']['upload_files']) {
                                    $defaultRole = "view-only";
                                    $userAccess = 3;
                                }
                                if(isset($roleSettings[$key])) {
                                    $currentRole = $roleSettings[$key];
                                } else {
                                    $currentRole = $defaultRole;
                                }
                                ?>
                                <div class="role-settings active" data-role="<?php echo esc_attr($key) ?>" data-nonce="<?php echo esc_attr(wp_create_nonce("change_folders_role_".$key)) ?>" >
                                    <div class="role-setting-left">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12.5708 2.09956L19.2082 4.32652C19.9512 4.57461 20.4535 5.25711 20.4575 6.02198L20.4998 12.6626C20.5129 14.6758 19.779 16.6282 18.435 18.1579C17.8169 18.8601 17.0246 19.4631 16.0128 20.0025L12.4449 21.9097C12.3332 21.9686 12.2103 21.999 12.0865 22C11.9627 22.0009 11.8389 21.9715 11.7281 21.9137L8.12702 20.0505C7.10417 19.52 6.30482 18.9258 5.68064 18.2335C4.3145 16.7194 3.55542 14.7758 3.54233 12.7597L3.50002 6.12397C3.49602 5.35811 3.98932 4.67071 4.72827 4.41281L11.3405 2.10643C11.7332 1.96718 12.1711 1.96424 12.5708 2.09956Z" fill="#E6386C"/>
                                            <path d="M12.1255 12.4737C14.2948 12.4737 16.1255 12.8262 16.1255 14.1862C16.1255 15.5467 14.2828 15.8867 12.1255 15.8867C9.95667 15.8867 8.12549 15.5342 8.12549 14.1742C8.12549 12.8137 9.96818 12.4737 12.1255 12.4737ZM12.1255 5.88672C13.595 5.88672 14.7725 7.06373 14.7725 8.53225C14.7725 10.0008 13.595 11.1783 12.1255 11.1783C10.6564 11.1783 9.4785 10.0008 9.4785 8.53225C9.4785 7.06373 10.6564 5.88672 12.1255 5.88672Z" fill="white"/>
                                        </svg>
                                        <span class="role-title"><?php echo esc_attr($role['name']) ?></span>
                                    </div>
                                    <div class="role-setting-right">
                                        <?php if($key != "administrator") { ?>
                                            <div class="user-folder-access">
                                                <div class="access-title">
                                                    <span class="access-role-title"><?php echo esc_attr($folderRoles[$currentRole]) ?></span>
                                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M4 6L8 10L12 6" stroke-width="1.33" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </div>
                                                <div class="user-access-list" data-role="<?php echo esc_attr($userAccess) ?>">
                                                    <ul>
                                                        <?php $count = 1;
                                                        foreach($folderRoles as $key=>$role_type) { ?>
                                                            <li data-id="<?php echo esc_attr($count); $count++; ?>" data-role="<?php echo esc_attr($key) ?>" class="change-folders-role-access <?php echo esc_attr(($key == $currentRole)?"active":"")  ?>">
                                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path d="M13.3332 4L5.99984 11.3333L2.6665 8" stroke="#E6386C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                </svg>
                                                                <span><?php echo esc_attr($role_type) ?></span>
                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        <?php } else { ?>
                                            <div class="access-title">
                                                <span class="access-role-title"><?php esc_html_e("Admin", "folders") ?></span>
                                                <span class="dashicons dashicons-lock"></span>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        <div class="user-role-setting" id="settings-users">
            <table class="import-export-table">
                <tr>
                    <td>
                        <div class="role-setting-search" id="search-user">
                            <input type="text" id="user-search" placeholder="<?php esc_html_e("Search by user", "folders"); ?>" />
                            <button type="button">
                                <svg width="15" height="16" viewBox="0 0 15 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M7.18065 0.72168C11.14 0.72168 14.3606 3.94225 14.3606 7.9016C14.3606 9.7696 13.6437 11.4733 12.4706 12.752L14.7789 15.0555C14.995 15.2715 14.9957 15.621 14.7797 15.837C14.672 15.9462 14.5297 16 14.3881 16C14.2473 16 14.1058 15.9462 13.9974 15.8385L11.6612 13.5088C10.4322 14.493 8.87401 15.0822 7.18065 15.0822C3.2213 15.0822 0 11.8609 0 7.9016C0 3.94225 3.2213 0.72168 7.18065 0.72168ZM7.18065 1.82764C3.83106 1.82764 1.10596 4.552 1.10596 7.9016C1.10596 11.2512 3.83106 13.9763 7.18065 13.9763C10.5295 13.9763 13.2546 11.2512 13.2546 7.9016C13.2546 4.552 10.5295 1.82764 7.18065 1.82764Z" fill="#ABABAB"/>
                                </svg>
                                <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
                            </button>
                        </div>
                        <div class="role-settings default" id="user-role-settings" style="display: none">
                            <div class="role-setting-left"><?php esc_html_e("Users", "folders"); ?></div>
                            <div class="role-setting-right"><?php esc_html_e("Permissions", "folders"); ?></div>
                        </div>
                        <div id="user-search-list">

                        </div>
                        <div class="no-user-record">
                            <?php echo sprintf(esc_html__("Please search for a user to edit Folders access for specific users, or %1\$s", "folders"), "<a href='#' class='load-all-users'>".esc_html__("load all users", "folders")."</a>") ?>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
