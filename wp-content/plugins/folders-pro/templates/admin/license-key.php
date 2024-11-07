<?php
/**
 * Folders License Key
 *
 * @author  : Premio <contact@premio.io>
 * @license : GPL2
 * */

if (! defined('ABSPATH')) {
    exit;
}
$licenseKey   = get_option("wcp_folder_license_key");
$licenseData  = [];
$activeStatus = 0;
delete_transient("folder_license_key_data");
$message = "";
$class_name = "";
$topMessage    = "";
$hidePlans = get_option("hide_folders_plans");
$m          = filter_input(INPUT_GET, 'm');
if (!empty($licenseKey)) {
    $licenseData = $this->get_license_key_data($licenseKey);
    if (!empty($licenseData)) {
        if ($licenseData['license'] == "valid") {
            $activeStatus = 1;
            if ($licenseData['expires'] == "lifetime") {
                $topMessage = esc_html__("You have a lifetime license", "folders");
            } else {
                $topMessage = sprintf(esc_html__("Your license will expire on %s", "folders"), gmdate("d M, Y", strtotime($licenseData['expires'])));
            }
        } else if ($licenseData['license'] == "expired") {
            $class_name = "error";
            $message = sprintf(esc_html__("Your license has been expired on %s", "folders"), gmdate("d M, Y", strtotime($licenseData['expires'])));
        } else if ($licenseData['license'] == "site_inactive") {
            delete_option("wcp_folder_license_key");
            $licenseKey = "";
        }
    } else {
        delete_option("wcp_folder_license_key");
        $licenseKey = "";
    }
}
$encLicenseKey = $licenseKey;
if (!empty($licenseKey)) {
    $encLicenseKey = substr_replace($licenseKey, "**************", 6, 20);
}

if (isset($m) && !empty($m)) {
    switch ($m) {
        case "error":
            $class_name = "error";
            $message = esc_html__("Your license key is not valid", 'folders');
            break;
        case "valid":
            $class_name = "success";
            $message = esc_html__("Your license key is activated successfully", 'folders');
            break;
        case "unactivated":
            $class_name = "success";
            $message = esc_html__("Your license key is deactivated successfully", 'folders');
            break;
        case "expired":
            $class_name = "error";
            $message = esc_html__("Your license has been expired", 'folders');
            break;
        case "invalid":
            $class_name = "error";
            $message = "Your license is invalid.<br/>Please get a <a href='https://premio.io/downloads/folders/' target='_blank'>valid license</a> to activate the product";
            break;
        case "no_activations":
            $class_name = "error";
            $message = "Your license was activated for another domain.<br/>Please visit your <a href='https://go.premio.io/' target='_blank'>Premio account</a>";
            break;
    }//end switch
}
?>
<div class="wrap">
    <div class="price-container">
        <div class="key-box">
            <div class="key-box-left">
                <?php esc_html_e("Folders", "folders") ?>
            </div>
            <div class="key-box-right">
                <?php if(!empty($licenseKey)) {
                    if($hidePlans === false) { ?>
                    <a class="get-license-key manage-plan-button" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" tabindex="-1">
                            <path d="M0 0h20v20H0z"></path>
                            <rect x="7" y="3" width="6" height="14" rx="1" stroke="#b78deb" class="stroke" stroke-width="1.67"></rect>
                            <path d="M13 7a1 1 0 011-1h3a1 1 0 011 1v9a1 1 0 01-1 1h-4V7zM7 7a1 1 0 00-1-1H3a1 1 0 00-1 1v9a1 1 0 001 1h4V7z" class="stroke" stroke="#b78deb" stroke-width="1.67"></path>
                        </svg>
                        <?php esc_html_e("Manage your plan", "folders"); ?>
                    </a>
                <?php } } else { ?>
                    <a class="get-license-key" href="https://go.premio.io/" target="_blank">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <path d="M11.9169 5.25008L14.8336 2.33341M16.5002 0.666748L14.8336 2.33341L16.5002 0.666748ZM8.4919 8.67508C8.92218 9.09964 9.26423 9.60511 9.49836 10.1624C9.73248 10.7197 9.85406 11.3178 9.85609 11.9223C9.85811 12.5267 9.74054 13.1257 9.51016 13.6845C9.27977 14.2434 8.94111 14.7511 8.51368 15.1785C8.08625 15.606 7.5785 15.9446 7.01965 16.175C6.4608 16.4054 5.8619 16.523 5.25742 16.5209C4.65295 16.5189 4.05485 16.3973 3.49755 16.1632C2.94026 15.9291 2.43478 15.587 2.01023 15.1568C1.17534 14.2923 0.713363 13.1346 0.723806 11.9328C0.734249 10.7311 1.21627 9.58153 2.06606 8.73175C2.91585 7.88196 4.0654 7.39994 5.26714 7.38949C6.46888 7.37905 7.62664 7.84102 8.49106 8.67592L8.4919 8.67508ZM8.4919 8.67508L11.9169 5.25008L8.4919 8.67508ZM11.9169 5.25008L14.4169 7.75008L17.3336 4.83342L14.8336 2.33341L11.9169 5.25008Z" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <?php esc_html_e("Get License Key", "folders"); ?>
                    </a>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php if(isset($hasBackButton)) { ?>
        <div class="back-button-box">
            <a href="<?php echo esc_url(admin_url("options-general.php?page=wcp_folders_settings")) ?>" class="go-back-button"><?php esc_html_e("Back", "folders"); ?></a>
        </div>
    <?php } ?>
    <div class="price-container">
        <div class="key-container">
            <div class="key-container-left">
                <div class="license-key-title">
                    <?php esc_html_e("Enter Your License Key", "folders"); ?>
                </div>
                <?php if(empty($licenseKey) && empty($topMessage)) { ?>
                    <div class="license-key-desc">
                        <?php esc_html_e("To receive updates, please enter your valid Software Licensing license key.", "folders"); ?>
                    </div>
                <?php } else { ?>
                    <div class="license-key-desc">
                        <?php echo esc_attr($topMessage); ?>
                    </div>
                <?php } ?>
                <div class="license-key-input">
                    <?php if(!empty($class_name)) { ?>
                        <div class="license-key-notice notice-<?php echo esc_attr($class_name) ?>">
                            <?php echo $message ?>
                        </div>
                    <?php } ?>
                    <form action="" id="license_key_form">
                        <?php if(empty($licenseKey)) { ?>
                            <input type="text" class="input-key" id="license_key" name="license_key" value="" placeholder="<?php esc_html_e("Type your key", "folders"); ?>">
                            <button type="submit" class="license-key-button">
                                <span class="btn-text"><?php esc_html_e("Activate", "folders"); ?></span>
                                <span class="btn-loader">
                                    <span class="lds-ellipsis">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </span>
                                </span>
                            </button>
                        <?php } else { ?>
                            <input type="text" class="input-key" id="license_key" name="license_key" readonly value="<?php echo esc_attr($encLicenseKey) ?>" placeholder="<?php esc_html_e("Type your key", "folders"); ?>">
                            <a href="javascript:;" class="license-deactivate-button" id="deactivate_key">
                                <span class="btn-text"><?php esc_html_e("Deactivate License", "folders"); ?></span>
                                <span class="btn-loader">
                                    <span class="lds-ellipsis">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </span>
                                </span>
                            </a>
                        <?php } ?>
                    </form>
                </div>
            </div>
            <div class="key-container-right">
                <img src="<?php echo esc_url(WCP_PRO_FOLDER_URL."assets/images/license-image.png") ?>" alt="<?php esc_html_e("License Key", "folders"); ?>">
            </div>
        </div>
    </div>
</div>
<?php
$redirectURL = admin_url("admin.php?page=wcp_folders_register");
if(isset($_GET['setting_page']) && $_GET['setting_page'] == "license-key") {
    $redirectURL = admin_url("options-general.php?page=wcp_folders_settings&setting_page=license-key");
}
?>
<script>
    (function (factory) {
        "use strict";
        if(typeof define === 'function' && define.amd) {
            define(['jquery'], factory);
        }
        else if(typeof module !== 'undefined' && module.exports) {
            module.exports = factory(require('jquery'));
        }
        else {
            factory(jQuery);
        }
    }(function ($, undefined) {
        $(document).ready(function(){
            $(document).on("submit", "#license_key_form", function(){
                var licenseKey = jQuery.trim($("#license_key").val());
                $(".license-key-button").addClass("form-loading").prop("disabled", true);
                $.ajax({
                    url:"<?php echo esc_url(admin_url("admin-ajax.php")) ?>",
                    data: {
                        key: licenseKey,
                        action: "wcp_folder_activate_key",
                        nonce: "<?php echo esc_attr(wp_create_nonce("activate_folder_key")) ?>",
                        mode: $("#debug_response").length
                    },
                    method: 'post',
                    success: function(res){
                        $(".license-key-button").addClass("form-loading").prop("disabled", false);
                        if(!$("#debug_response").length) {
                            if(res == "valid") {
                                window.location.href = window.location+'&&m=' + res;
                            } else {
                                window.location.href = window.location+'&m=' + res;
                            }
                        }
                    }
                });
                return false;
            });
            $(document).on("click", "#deactivate_key:not(.form-loading)", function(){
                var licenseKey = jQuery.trim($("#license_key").val());
                $(this).addClass("form-loading");
                $.ajax({
                    url:"<?php echo esc_url(admin_url("admin-ajax.php")) ?>",
                    data: {
                        key: licenseKey,
                        action: "wcp_folder_deactivate_key",
                        nonce: "<?php echo esc_attr(wp_create_nonce("deactivate_folder_key")) ?>",
                        mode: $("#debug_response").length
                    },
                    method: 'post',
                    success: function(res){
                        if(!$("#debug_response").length) {
                            window.location.href = window.location+'&m=' + res;
                        }
                    }
                });
                return false;
            });
        });
    }));

</script>
