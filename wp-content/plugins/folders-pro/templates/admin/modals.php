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
<div id="folder-add-update-content">
    <div class="folder-popup-form" id="add-update-folder">
        <div class="popup-form-content">
            <div class="popup-form-data">
                <div class="close-popup-button">
                    <a class="" href="javascript:;"><span></span></a>
                </div>
                <form action="" method="post" id="save-folder-form">
                    <div id="add-update-folder-title" class="add-update-folder-title">
                        <?php esc_html_e("Add a new folder", "folders") ?>
                    </div>
                    <div class="add-folder-note">
                        <?php esc_html_e("Enter your folder's name (or create more than one folder by separating the name with a comma)", "folders") ?>
                    </div>
                    <div class="folder-form-input">
                        <div class="folder-group">
                            <input id="add-update-folder-name" autocomplete="off" required="required">
                            <span class="highlight"></span><span class="folder-bar"></span>
                            <label for="add-update-folder-name"><?php esc_html_e("Folder name", "folders") ?></label>
                        </div>
                    </div>
                    <div class="folder-form-errors">
                        <span class="dashicons dashicons-info"></span> <?php esc_html_e("Please enter folder name", "folders") ?>
                    </div>
                    <?php if (!$hasValidKey) { ?>
                        <div class="folder-form-buttons hide-it pro-message" id="pro-notice">
                            <span class="pro-tip">
                                <?php esc_html_e("Pro tip", "folders") ?>
                            </span>
                            <div class="pro-notice">
                                <?php printf(esc_html__("%sActivate your license key%s to create subfolders (with 20+ amazing features) & premium support 🎉", "folders"), '<a class="inline-button" target="_blank" href="'.esc_url($this->getRegisterKeyURL()).'">', "</a>"); ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="folder-form-buttons">
                        <a href="javascript:;" class="form-cancel-btn"><?php esc_html_e("Cancel", "folders") ?></a>
                        <button type="submit" class="form-submit-btn" id="save-folder-data" style="width: 160px"><?php esc_html_e("Submit", "folders") ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="folder-popup-form" id="update-folder-item">
        <div class="popup-form-content">
            <div class="popup-form-data">
                <div class="close-popup-button">
                    <a class="" href="javascript:;"><span></span></a>
                </div>
                <form action="" method="post" id="update-folder-form">
                    <div id="update-folder-title" class="add-update-folder-title">
                        <?php esc_html_e("Rename folder", "folders") ?>
                    </div>
                    <div class="folder-form-input">
                        <div class="folder-group">
                            <input id="update-folder-item-name" autocomplete="off" required="required">
                            <span class="highlight"></span><span class="folder-bar"></span>
                            <label for="update-folder-item-name"><?php esc_html_e("Folder name", "folders") ?></label>
                        </div>
                    </div>
                    <div class="folder-form-errors">
                        <span class="dashicons dashicons-info"></span> <?php esc_html_e("Please enter folder name", "folders") ?>
                    </div>
                    <div class="folder-form-buttons">
                        <a href="javascript:;" class="form-cancel-btn"><?php esc_html_e("Cancel", "folders") ?></a>
                        <button type="submit" class="form-submit-btn" id="update-folder-data" style="width: 160px"><?php esc_html_e("Submit", "folders") ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="folder-popup-form" id="confirm-remove-folder">
        <div class="popup-form-content">
            <div class="popup-form-data">
                <div class="close-popup-button">
                    <a class="" href="javascript:;"><span></span></a>
                </div>
                <div class="add-update-folder-title" id="remove-folder-message">
                    <?php esc_html_e("Are you sure you want to delete the selected folder?", "folders") ?>
                </div>
                <div class="folder-form-message" id="remove-folder-notice">
                    <?php esc_html_e("Items in the folder will not be deleted.", "folders") ?>
                </div>
                <div class="folder-form-buttons">
                    <a href="javascript:;" class="form-cancel-btn"><?php esc_html_e("No, Keep it", "folders") ?></a>
                    <a href="javascript:;" class="form-submit-btn" id="remove-folder-item"><?php esc_html_e("Yes, Delete it!", "folders") ?></a>
                </div>
            </div>
        </div>
    </div>

    <div class="folder-popup-form" id="no-more-folder-credit">
        <div class="popup-form-content">
            <div class="popup-form-data">
                <div class="close-popup-button">
                    <a class="" href="javascript:;"><span></span></a>
                </div>
                <div class="add-update-folder-title" id="folder-limitation-message">

                </div>
                <div class="folder-form-message">
                    <?php esc_html_e("Unlock unlimited amount of folders by activating license key.", "folders") ?>
                </div>
                <div class="folder-form-buttons">
                    <a href="javascript:;" class="form-cancel-btn"><?php esc_html_e("Cancel", "folders") ?></a>
                    <a href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" target="_blank" class="form-submit-btn"><?php esc_html_e("Activate License Key", "folders") ?></a>
                </div>
            </div>
        </div>
    </div>

    <div class="folder-popup-form" id="error-folder-popup">
        <div class="popup-form-content">
            <div class="popup-form-data">
                <div class="close-popup-button">
                    <a class="" href="javascript:;"><span></span></a>
                </div>
                <div class="add-update-folder-title" id="error-folder-popup-message">

                </div>
                <div class="folder-form-buttons">
                    <a href="javascript:;" class="form-cancel-btn"><?php esc_html_e("Close", "folders") ?></a>
                </div>
            </div>
        </div>
    </div>

    <div class="folder-popup-form" id="sub-folder-popup">
        <div class="popup-form-content">
            <div class="popup-form-data">
                <div class="close-popup-button">
                    <a class="" href="javascript:;"><span></span></a>
                </div>
                <div class="add-update-folder-title">
                    <?php esc_html_e("Sub-folders is a pro feature", "folders") ?>
                </div>
                <div class="folder-form-message" style="padding: 25px 10px;" >
                    <?php esc_html_e("Hey, it looks like you want to create sub-folders on Folders. Sub-folders is a premium feature. Activate your license key to create, access and organize your files with sub-folders.", "folders") ?>
                </div>
                <div class="folder-form-buttons">
                    <a href="javascript:;" class="form-cancel-btn"><?php esc_html_e("Cancel", "folders") ?></a>
                    <a href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" target="_blank" class="form-submit-btn"><?php esc_html_e("Activate License Key", "folders") ?></a>
                </div>
            </div>
        </div>
    </div>

    <div class="folder-popup-form" id="confirm-your-change">
        <div class="popup-form-content">
            <div class="popup-form-data">
                <div class="close-popup-button">
                    <a class="" href="javascript:;"><span></span></a>
                </div>
                <div class="add-update-folder-title">
                    <?php esc_html_e("Confirm your change", "folders") ?>
                </div>
                <div class="folder-form-message" style="padding: 25px 10px;" >
                    <?php esc_html_e("Hey, it looks like you want to move the file to \"Unassigned Files.\" Do you want to move the file from the current folder only or from all the folders where the file exists?", "folders") ?>
                </div>
                <div class="folder-form-buttons">
                    <input type="hidden" id="unassigned_folders" />
                    <a href="javascript:;" class="form-cancel-btn remove-from-all-folders" id="remove-from-all-folders"><?php esc_html_e("From all folders", "folders") ?></a>
                    <a href="javascript:;" class="form-submit-btn remove-from-current-folder" id="remove-from-current-folder"><?php esc_html_e("Just from this folder", "folders") ?></a>
                </div>
            </div>
        </div>
    </div>

    <div class="folder-popup-form" id="add-sub-folder-popup">
        <div class="popup-form-content">
            <div class="popup-form-data">
                <div class="close-popup-button">
                    <a class="" href="javascript:;"><span></span></a>
                </div>
                <div class="add-update-folder-title">
                    <?php esc_html_e("Add a new folder", "folders") ?>
                </div>
                <div class="folder-form-input">
                    <div class="folder-group">
                        <input id="update-folder-item-name" autocomplete="off" required="required" readonly>
                        <span class="highlight"></span><span class="folder-bar"></span>
                        <label for="update-folder-item-name"><?php esc_html_e("Folder name", "folders") ?></label>
                    </div>
                </div>
                <div class="folder-form-buttons">
                    <span class="pro-tip">
                        <?php esc_html_e("Pro tip", "folders") ?>
                    </span>
                    <div class="pro-notice">
                        <?php printf(esc_html__("%sActivate your license key%s to create subfolders (with 20+ amazing features) & premium support 🎉", "folders"), '<a class="inline-button" target="_blank" href="'.esc_url($this->getRegisterKeyURL()).'">', "</a>"); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="folder-popup-form" id="bulk-move-folder">
        <form action="" method="post" id="bulk-folder-form">
            <div class="popup-form-content">
                <div class="popup-form-data">
                    <div class="close-popup-button">
                        <a class="" href="javascript:;"><span></span></a>
                    </div>
                    <div class="popup-folder-title">
                        <?php esc_html_e("Select Folder", "folders") ?>
                    </div>
                    <div class="select-box">
                        <select id="bulk-select">
                            <option value=""><?php esc_html_e("Select Folder", "folders") ?></option>
                        </select>
                    </div>
                    <div class="folder-form-buttons">
                        <a href="javascript:;" class="form-cancel-btn"><?php esc_html_e("Cancel", "folders") ?></a>
                        <button type="submit" class="form-submit-btn" id="move-to-folder" style="width: 200px"><?php esc_html_e("Move to Folder", "folders") ?></button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="folders-undo-notification" id="do-undo">
        <div class="folders-undo-body">
            <a href="javascript:;" class="close-undo-box"><span></span></a>
            <div class="folders-undo-header"><?php esc_html_e("Action performed successfully", "folders") ?></div>
            <div class="folders-undo-body"><?php printf(esc_html__("Your action has been successfully completed. Click the %sUndo%s button to reverse the action", "folders"), "<b>", "</b>"); ?></div>
            <div class="folders-undo-footer"><button class="undo-button" type="button"><?php esc_html_e("Undo", "folders") ?></button></div>
        </div>
    </div>

    <div class="folders-undo-notification" id="undo-done">
        <div class="folders-undo-body success" style="padding: 0">
            <a href="javascript:;" class="close-undo-box"><span></span></a>
            <div class="folders-undo-header" style="color: #014737; padding: 0"><?php esc_html_e("Action reversed successfully", "folders") ?></div>
        </div>
    </div>

    <div class="folders-undo-notification" id="copy-message">
        <div class="folders-undo-body">
            <a href="javascript:;" class="close-undo-box"><span></span></a>
            <div class="folders-undo-header"><?php esc_html_e("Selection successfully copied", "folders") ?></div>
            <div class="folders-undo-body"><?php esc_html_e("Folder successfully copied in your clipboard. Navigate to your desired location and click 'Paste' button to paste the folder", "folders"); ?></div>
        </div>
    </div>

    <div class="folders-undo-notification" id="cut-message">
        <div class="folders-undo-body">
            <a href="javascript:;" class="close-undo-box"><span></span></a>
            <div class="folders-undo-header"><?php esc_html_e("Selection successfully selected for moving", "folders") ?></div>
            <div class="folders-undo-body"><?php esc_html_e('Navigate to your desired location and click "Paste" button to move the folder', "folders"); ?></div>
        </div>
    </div>

    <div class="folders-undo-notification success" id="paste-message">
        <div class="folders-undo-body">
            <a href="javascript:;" class="close-undo-box"><span></span></a>
            <div class="folders-undo-header"><?php esc_html_e("Selection successfully selected for moving", "folders") ?></div>
            <div class="folders-undo-body"><?php esc_html_e("Clipboard content has been pasted successfully.", "folders"); ?></div>
            <div class="folders-undo-footer"><button class="undo-button copy-paste-action " type="button"><?php esc_html_e("Undo", "folders") ?></button></div>
        </div>
    </div>

    <div class="folders-undo-notification success" id="folder-upload-message">
        <div class="folders-undo-body">
            <a href="javascript:;" class="close-undo-box"><span></span></a>
            <div class="folders-undo-header"><?php esc_html_e("Folder successfully uploaded", "folders") ?></div>
            <div class="folders-undo-body"><?php esc_html_e("Your folder has successfully been uploaded.", "folders"); ?></div>
        </div>
    </div>

    <div class="folder-popup-form" id="wp-config-update-notice">
        <form action="" method="post" id="bulk-folder-form">
            <div class="popup-form-content">
                <div class="popup-form-data">
                    <div class="close-popup-button">
                        <a class="" href="javascript:;"><span></span></a>
                    </div>
                    <div class="popup-folder-title">
                        <?php esc_html_e("Something went wrong", "folders") ?>
                    </div>
                    <div class="folder-form-message" style="padding: 25px 10px;" >
                        <?php esc_html_e('We couldn’t write to the file automatically. Please add the line manually to your wp-config.php. You need to modify your wp-config.php file and just before the line that says "That’s all, stop editing!", add this line:', "folders") ?><code>define( 'MEDIA_TRASH', true );</code>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="folder-popup-form" id="lock-all-folder-popup">
        <div class="popup-form-content">
            <div class="popup-form-data">
                <div class="close-popup-button">
                    <a class="" href="javascript:;"><span></span></a>
                </div>
                <div class="add-update-folder-title">
                    <?php esc_html_e("Are you sure?", "folders") ?>
                </div>
                <div class="folder-form-message" style="padding: 25px 10px;" >
                    <?php printf(esc_html__("You're about to %s. Are you sure?", "folders"), "<b>".esc_html__("lock all folders", "folders")."</b>") ?>
                </div>
                <div class="folder-form-buttons">
                    <input type="hidden" id="unassigned_folders" />
                    <a href="javascript:;" class="form-cancel-btn "><?php esc_html_e("Nevermind", "folders") ?></a>
                    <a href="javascript:;" class="form-submit-btn lock-all-folder"><?php esc_html_e("Lock all folders", "folders") ?></a>
                </div>
            </div>
        </div>
    </div>

    <div class="folder-popup-form" id="unlock-all-folder-popup">
        <div class="popup-form-content">
            <div class="popup-form-data">
                <div class="close-popup-button">
                    <a class="" href="javascript:;"><span></span></a>
                </div>
                <div class="add-update-folder-title">
                    <?php esc_html_e("Are you sure?", "folders") ?>
                </div>
                <div class="folder-form-message" style="padding: 25px 10px;" >
                    <?php printf(esc_html__("You're about to %s. Are you sure?", "folders"), "<b>".esc_html__("unlock all folders", "folders")."</b>") ?>
                </div>
                <div class="folder-form-buttons">
                    <input type="hidden" id="unassigned_folders" />
                    <a href="javascript:;" class="form-cancel-btn "><?php esc_html_e("Nevermind", "folders") ?></a>
                    <a href="javascript:;" class="form-submit-btn unlock-all-folder"><?php esc_html_e("Unlock all folders", "folders") ?></a>
                </div>
            </div>
        </div>
    </div>

    <div class="folder-popup-form" id="keyboard-shortcut">
        <div class="popup-form-content">
            <div class="popup-content" style="position: relative;">
                <div class="close-popup-button">
                    <a class="" href="javascript:;"><span></span></a>
                </div>
                <div class="import-plugin-title" style="font-weight: bold; padding: 0 0 20px 0; font-size: 16px;"><?php esc_html_e("Keyboard shortcuts (Ctrl+K)", 'folders'); ?></div>
                <div class="plugin-import-table">
                    <table class="keyboard-shortcut">
                        <tr>
                            <th><?php esc_html_e("Create New Folder", "folders") ?></th>
                            <td><span class="key-button">Alt</span><span class="plus-button">+</span><span class="key-button">N</span> </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e("Rename Folder", "folders") ?></th>
                            <td><span class="key-button">F2</span></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e("Copy Folder", "folders") ?></th>
                            <td><span class="key-button">Ctrl</span><span class="plus-button">+</span><span class="key-button">C</span> </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e("Cut Folder", "folders") ?></th>
                            <td><span class="key-button">Ctrl</span><span class="plus-button">+</span><span class="key-button">X</span> </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e("Paste Folder", "folders") ?></th>
                            <td><span class="key-button">Ctrl</span><span class="plus-button">+</span><span class="key-button">V</span> </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e("Duplicate Folder", "folders") ?></th>
                            <td><span class="key-button">Ctrl</span><span class="plus-button">+</span><span class="key-button">D</span> </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e("Delete Folder", "folders") ?></th>
                            <td><span class="key-button">Delete</span></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e("Next Folder", "folders") ?></th>
                            <td><span class="key-button"><span class="dashicons dashicons-arrow-down-alt"></span></span> </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e("Previous Folder", "folders") ?></th>
                            <td><span class="key-button"><span class="dashicons dashicons-arrow-up-alt"></span></span> </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e("Expand Folder", "folders") ?></th>
                            <td><span class="key-button"><span class="dashicons dashicons-arrow-right-alt"></span></span> </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e("Collapse Folder", "folders") ?></th>
                            <td><span class="key-button"><span class="dashicons dashicons-arrow-left-alt"></span></span> </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e("Re-order folders to upwards", "folders") ?></th>
                            <td><span class="key-button">Ctrl</span><span class="plus-button">+</span><span class="key-button"><span class="dashicons dashicons-arrow-up-alt"></span></span></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e("Re-order folders to downwards", "folders") ?></th>
                            <td><span class="key-button">Ctrl</span><span class="plus-button">+</span><span class="key-button"><span class="dashicons dashicons-arrow-down-alt"></span></span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
