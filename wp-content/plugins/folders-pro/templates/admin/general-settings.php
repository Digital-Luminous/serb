<?php
if (! defined('ABSPATH')) {
    exit;
}
?>
<!-- do not change here, Free/Pro URL Change -->
<link rel='stylesheet' href='<?php echo esc_url(WCP_PRO_FOLDER_URL) ?>assets/css/settings.css?ver=<?php echo esc_attr(WCP_PRO_FOLDER_VERSION) ?>' type='text/css' media='all' />
<link rel='stylesheet' href='<?php echo esc_url(WCP_PRO_FOLDER_URL) ?>assets/css/folder-icon.css?ver=<?php echo esc_attr(WCP_PRO_FOLDER_VERSION) ?>' type='text/css' media='all' />
<link rel='stylesheet' href='<?php echo esc_url(WCP_PRO_FOLDER_URL) ?>assets/css/spectrum.min.css?ver=<?php echo esc_attr(WCP_PRO_FOLDER_VERSION) ?>' type='text/css' media='all' />
<?php if ($setting_page == "folder-settings") { ?>
    <link rel='stylesheet' href='<?php echo esc_url(WCP_PRO_FOLDER_URL) ?>assets/css/select2.min.css?ver=<?php echo esc_attr(WCP_PRO_FOLDER_VERSION) ?>' type='text/css' media='all' />
    <script type="text/javascript" src='<?php echo esc_url(WCP_PRO_FOLDER_URL) ?>assets/js/select2.min.js?ver=<?php echo esc_attr(WCP_PRO_FOLDER_VERSION) ?>'  />
<?php } ?>
<script src="<?php echo esc_url(WCP_PRO_FOLDER_URL) ?>assets/js/spectrum.min.js"></script>
<style>
    <?php if (function_exists('is_rtl') && is_rtl()) { ?>
    #setting-form {
        float: right;
    }
    <?php } ?>
</style>
<script>
    (function (factory) {
        "use strict";
        if (typeof define === 'function' && define.amd) {
            define(['jquery'], factory);
        }
        else if(typeof module !== 'undefined' && module.exports) {
            module.exports = factory(require('jquery'));
        }
        else {
            factory(jQuery);
        }
    }(function ($, undefined) {
        var selectedItem;
        var importTitle = "<?php esc_html_e("Import folders from %plugin%", "folders"); ?>";
        var importDesc = "<?php esc_html_e("Are you sure you'd like to import %1\$d folders from %plugin%?", "folders"); ?>";
        var removeTitle = "<?php esc_html_e("Are you sure?", "folders"); ?>";
        var removeDesc = "<?php esc_html_e("You're about to delete %plugin%'s folders. Are you sure you'd like to proceed?", "folders"); ?>";
        var customSearchAJAX = null;
        var totalColors = 0;
        $(document).ready(function(){
            <?php if ($setting_page == "folder-settings") { ?>
            $(".select2-box").select2();
            $("#folders_post_type").select2();
            $("#folders_post_type").on("change", function(){
                $(".folders-select").addClass("hide-option");
                $(this).find("option:selected").each(function(){
                   $("#folders-for-"+$(this).attr("value")).removeClass("hide-option");
                });
            });
            <?php } ?>
            <?php if(isset($_GET['focus']) && $_GET['focus'] == "icon-color") {
                $hide_folder_color_pop_up = get_option("hide_folder_color_pop_up");
                if(!($hide_folder_color_pop_up)) {
                    add_option("hide_folder_color_pop_up", "yes");
                } else {
                    update_option("hide_folder_color_pop_up", "yes");
                }
                ?>
                $(".default-icon-color").addClass("add-focus-animate");
            <?php } ?>
            $(document).on("click",".form-cancel-btn, .close-popup-button, .folder-popup-form",function(){
                if($(this).hasClass("cancel-folders") || $(this).hasClass("remove-folders-box") || $(this).hasClass("close-remove-folders")) {
                    $("#remove_folders_when_removed").prop("checked", false);
                    setFoldersRemoveStatus("off");
                }
                if($(this).hasClass("delete-button")) {
                    setFoldersRemoveStatus("on");
                }
                $(".folder-popup-form").hide();
            });
            $(document).on("click", ".change-folder-user-status", function(){
                var userStatus = $(this).is(":checked")?"on":"off";
                $.ajax({
                    url: "<?php echo esc_url(admin_url("admin-ajax.php")) ?>",
                    data: {
                        'action': 'wcp_update_user_folder_status',
                        'status': userStatus,
                        'nonce': "<?php echo esc_attr(wp_create_nonce("wcp_update_user_folder_status")) ?>"
                    },
                    type: 'post',
                    success: function (res) {

                    }
                });
            });
            $(document).on("click", "#dynamic_folders_for_admin_only", function(){
                var userStatus = $(this).is(":checked")?"on":"off";
                $.ajax({
                    url: "<?php echo esc_url(admin_url("admin-ajax.php")) ?>",
                    data: {
                        'action': 'wcp_update_dynamic_folder_status',
                        'status': userStatus,
                        'nonce': "<?php echo esc_attr(wp_create_nonce("dynamic_folders_for_admin_only")) ?>"
                    },
                    type: 'post',
                    success: function (res) {

                    }
                });
            });
            $(document).on("click", "#folders_by_user_roles", function(){
                var userStatus = $(this).is(":checked")?"on":"off";
                if($(this).is(":checked")) {
                    $(".folder-user-settings").addClass("active");
                } else {
                    $(".folder-user-settings").removeClass("active");
                }
                $.ajax({
                    url: "<?php echo esc_url(admin_url("admin-ajax.php")) ?>",
                    data: {
                        'action': 'wcp_update_folder_user_settings',
                        'status': userStatus,
                        'nonce': "<?php echo esc_attr(wp_create_nonce("folders_by_user_roles")) ?>"
                    },
                    type: 'post',
                    success: function (res) {

                    }
                });
            });
            $(document).on("click", ".user-folder-access .access-title", function(e){
                e.stopPropagation();
                if($(this).closest(".user-folder-access").hasClass("active")) {
                    $(".user-folder-access").removeClass("active");
                } else {
                    $(".user-folder-access").removeClass("active");
                    $(this).closest(".user-folder-access").addClass("active");
                }
            });
            $(document).on("click", "body,html", function(){
                $(".user-folder-access").removeClass("active");
            });
            $(document).on("click", ".user-role-tab a", function(e){
                e.preventDefault();
                var thisID = $(this).attr("href");
                if(!$(this).closest("li").hasClass("active") && $(thisID).length) {
                    $(".user-role-tab li.active").removeClass("active");
                    $(this).closest("li").addClass("active");
                    $(".user-role-setting").removeClass("active");
                    $(thisID).addClass("active");
                }
            });

            $(document).on("keyup", "#role-search", function(){
                $("#settings-roles tr td .no-user-record").remove();
                if($.trim($(this).val()) != "") {
                    $(".role-settings[data-role]").removeClass("active");
                    var thisValue = ($.trim($(this).val())).toLowerCase();
                    $(".role-settings[data-role]").each(function(){
                        var dataRole = ($(this).data("role")).toLowerCase();
                        if(dataRole.indexOf(thisValue) !== -1) {
                            $(this).addClass("active");
                        }
                    });

                    if($(".role-settings.active[data-role]").length == 0) {
                        $("#settings-roles tr td").append("<span class='no-user-record'><?php esc_html_e("no results found", "folders"); ?></span>");
                    }
                } else {
                    $(".role-settings[data-role]").addClass("active");
                }
            });

            $(document).on("keyup", "#user-search", function(){
                if($.trim($(this).val()) != "" && $.trim($(this).val()).length > 0) {
                    getUserData($.trim($("#user-search").val()), 1);
                } else {
                    if(customSearchAJAX != null) {
                        customSearchAJAX.abort();
                    }
                    customSearchAJAX = null;
                    $("#user-role-settings").hide();
                    $("#user-search-list").html("");
                    $(".no-user-record").show();
                }
            });

            $(document).on("click", ".load-all-users", function(e){
                e.preventDefault();
                getUserData("", 1);
            });

            $(document).on("click", ".user-pagination a", function(e){
                e.preventDefault();
                var newPage = $(this).text();
                newPage = parseInt(newPage.replaceAll(",",""));
                $("body,html").animate({
                    scrollTop: $("#setting-form").offset().top - 45
                }, 100);
                if($(this).hasClass("next") || $(this).hasClass("prev")) {
                    var currentPage = $(".user-pagination span.current").text();
                    currentPage = parseInt(currentPage.replaceAll(",",""));
                    if($(this).hasClass("next")) {
                        getUserData($.trim($("#user-search").val()), (currentPage+1));
                    } else {
                        getUserData($.trim($("#user-search").val()), (currentPage-1));
                    }
                } else {
                    getUserData($.trim($("#user-search").val()), newPage);
                }
            });

            function getUserData(searchText, pageNo) {
                customSearchAJAX = $.ajax({
                    url: "<?php echo esc_url(admin_url("admin-ajax.php")) ?>",
                    data: {
                        action: 'wcp_search_folder_user',
                        nonce: "<?php echo esc_attr(wp_create_nonce("search_folder_user")) ?>",
                        search: searchText,
                        paged: pageNo
                    },
                    type: 'post',
                    dataType: 'json',
                    success: function(res) {
                        $("#user-search-list").html("");
                        if(res.status) {
                            $("#user-role-settings").show();
                            for(i=0; i<res.data.length; i++) {
                                $("#user-search-list").append(res.data[i]['form_data']);
                            }
                            if(res.pagination) {
                                $("#user-search-list").append("<div class='user-pagination'>"+res.pagination+"</a>");
                            }
                        } else {
                            $("#user-search-list").html("<span class='no-user-record'><?php esc_html_e("no results found", "folders"); ?></span>");
                        }
                    },
                    beforeSend: function() {
                        if(customSearchAJAX != null) {
                            customSearchAJAX.abort();
                        }
                        $(".no-user-record").hide();
                        $("#search-user button").addClass('is-loading');
                        $("#user-search-list").html('<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>');
                    },
                    complete: function() {
                        $("#search-user button").removeClass('is-loading');
                    }
                });
            }

            $(document).on("click", ".change-folders-role-access:not(.active)", function(e){
                e.stopPropagation();
                if(!$(this).hasClass("active")) {
                    var dataId = parseInt($(this).data("id"));
                    var dataAccess = parseInt($(this).closest(".user-access-list").data("role"));
                    if(dataAccess <= dataId) {
                        var dataRole = $(this).data("role")
                        var dataUserRole = $(this).closest(".role-settings").data("role");
                        var dataNonce = $(this).closest(".role-settings").data("nonce");
                        $(this).closest(".user-access-list").find(".active").removeClass("active");
                        $(this).addClass("active");
                        $(this).closest(".role-settings").find(".access-role-title").text($(this).find("span").text());
                        $(this).closest(".user-folder-access").removeClass("active");
                        $.ajax({
                            url: "<?php echo esc_url(admin_url("admin-ajax.php")) ?>",
                            data: {
                                action: 'wcp_change_folders_role_access',
                                nonce: dataNonce,
                                role: dataUserRole,
                                access: dataRole
                            },
                            type: 'post',
                            dataType: 'json',
                            success: function (res) {

                            }
                        });
                    } else {
                        $(this).closest(".user-folder-access").removeClass("active");
                        $("#folders-user-role-access").addClass("active").show();
                    }
                }
            });

            $(document).on("click", ".change-folders-user-access:not(.active)", function(e){
                e.stopPropagation();
                if(!$(this).hasClass("active")) {
                    var dataId = parseInt($(this).data("id"));
                    var dataAccess = parseInt($(this).closest(".user-access-list").data("role"));
                    var userId = $(this).closest(".role-settings").data("id");
                    var dataNonce = $(this).closest(".role-settings").data("nonce");
                    var dataRole = $(this).data("role");
                    if(dataAccess <= dataId) {

                        $(this).closest(".user-access-list").find(".active").removeClass("active");
                        $(this).addClass("active");
                        $(this).closest(".role-settings").find(".access-role-title").text($(this).find("span").text());
                        $(this).closest(".user-folder-access").removeClass("active")
                        $.ajax({
                            url: "<?php echo esc_url(admin_url("admin-ajax.php")) ?>",
                            data: {
                                action: 'wcp_change_folders_user_access',
                                nonce: dataNonce,
                                user_id: userId,
                                access: dataRole
                            },
                            type: 'post',
                            dataType: 'json',
                            cache: false,
                            success: function (res) {
                                if(res.refresh) {
                                    var currentPage = 1;
                                    if($(".user-pagination span.current").length) {
                                        var currentPage = $(".user-pagination span.current").text();
                                        currentPage = parseInt(currentPage.replaceAll(",",""));
                                    }
                                    getUserData($.trim($("#user-search").val()), currentPage);
                                }
                            }
                        });
                    } else {
                        $("#change-user-id").val(userId);
                        $("#change-user-nonce").val(dataNonce);
                        $("#change-user-role").val(dataRole);
                        var buttonText = $(this).closest(".user-access-list").data("button");
                        $(this).closest(".user-folder-access").removeClass("active")
                        $("#change-user-permission").text(buttonText);
                        $("#folders-user-access").addClass("active").show();
                    }
                }
            });

            $(document).on("click","#change-user-permission", function(e){
                e.stopPropagation();
                $.ajax({
                    url: "<?php echo esc_url(admin_url("admin-ajax.php")) ?>",
                    data: {
                        action: 'wcp_change_folders_user_permission',
                        nonce: $("#change-user-nonce").val(),
                        user_id: $("#change-user-id").val(),
                        access: $("#change-user-role").val()
                    },
                    type: 'post',
                    dataType: 'json',
                    beforeSend: function() {
                        $("#folders-user-access").removeClass("active").hide();
                        $("#search-user button").addClass('is-loading');
                        $("#user-search-list").html('<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>');
                    },
                    success: function (res) {
                        if(res.status) {
                            var currentPage = 1;
                            if($(".user-pagination span.current").length) {
                                currentPage = $(".user-pagination span.current").text();
                                currentPage = parseInt(currentPage.replaceAll(",",""));
                            }
                            getUserData($.trim($("#user-search").val()), currentPage);
                        }
                    }
                });
            });

            $(document).on("click",".import-folders-button", function(e){
                $("#import-folders-popup").show();
            });
            $(document).on("click",".view-shortcodes", function(e){
                e.preventDefault();
                $("#keyboard-shortcut").show();
            });
            $(document).on("click",".popup-form-content", function(e){
                e.stopPropagation();
                e.stopImmediatePropagation();
            });
            $(document).on("click",".folder-select",function(){
                if($(this).is(":checked")) {
                    $(this).closest("tr").find(".hide-show-option").removeClass("hide-option");
                } else {
                    $(this).closest("tr").find(".hide-show-option").addClass("hide-option");
                }
            });
            $(document).on("click", ".accordion-header", function(){
                if($(this).hasClass("active")) {
                    $(this).closest(".accordion").find(".accordion-content").slideUp();
                    $(this).removeClass("active");
                } else {
                    $(this).closest(".accordion").find(".accordion-content").slideDown();
                    $(this).addClass("active");
                }
            });
            $(document).on("change", ".hide-show-option", function(){
                if($(this).val() == "folders-pro") {
                    $(this).find("option").prop("selected", false);
                    $(this).find("option:first").prop("selected", true);
                    window.open("<?php echo esc_url($this->getRegisterKeyURL()) ?>", "_blank");
                }
            });
            $(document).on("change", "#folder_font", function(){
                if($(this).val() == "folders-pro") {
                    $(this).val("").trigger("change");
                    window.open("<?php echo esc_url($this->getRegisterKeyURL()) ?>", "_blank");
                }
            });
            $(document).on("change", "#folder_size", function(){
                if($(this).val() == "folders-pro" || $(this).val() == "folders-pro-item" || $(this).val() == "folders-pro-item") {
                    $(this).val("16").trigger("change");
                    window.open("<?php echo esc_url($this->getRegisterKeyURL()) ?>", "_blank");
                }
            });
            $(".accordion-header:first").trigger("click");
            $(document).on("change","#folder_font, #folder_size, #folder_custom_font_size", function(){
                setCSSProperties();
            });
            $(document).on("click", "input[name='customize_folders[show_media_details]']", function(){
                if($("#show_media_details").is(":checked")) {
                    $(".media-setting-box").addClass("active");
                } else {
                    $(".media-setting-box").removeClass("active");
                }
            });
            setCSSProperties();
            <?php if ($setting_page == "customize-folders") { ?>
            $('.color-field').spectrum({
                chooseText: "Submit",
                preferredFormat: "hex",
                showInput: true,
                cancelText: "Cancel",
                show: function(color) {
                    $(this).closest(".color-checkbox").find("input.sr-only").prop("checked", true);
                    $(this).closest(".color-checkbox").find("input.sr-only").trigger("change");
                },
                move: function (color) {
                    $(this).val(color.toHexString());
                    setCSSProperties();
                },
                change: function (color) {
                    $(this).val(color.toHexString());
                    setCSSProperties();
                }
            });
            if($(".color-field").length) {
                $(".folder-colors .color-field").spectrum("disable");
            }

            totalColors = $(".folder-colors .color-field").length;

            $(document).on("click", ".edit-default-colors", function(e){
                e.preventDefault();
                $(".folder-colors .new-folder-color-option").remove();
                $(".folder-colors .color-field").spectrum("enable");
                $(".folder-color-box").addClass("active");
                checkforColors();
            });

            $(document).on("click", ".cancel-default-colors", function(e){
                e.preventDefault();
                $(".folder-colors .color-field").spectrum("disable");
                $(".folder-color-box").removeClass("active");
                $(".folder-colors .new-folder-color-option").remove();
                $(".folder-colors .folder-color.hide-color").removeClass("hide-color");
            });

            $(document).on("click", ".save-default-colors", function(e){
                e.preventDefault();
                $(".folder-colors .new-folder-color-option").removeClass("new-folder-color-option");
                $(".folder-colors .folder-color.hide-color").remove();
                if($(".folder-colors .folder-color:not(.new-folder-color)").length) {
                    $(".folder-colors .folder-color:not(.new-folder-color)").each(function(){
                        var eleId = $(this).data("id");
                        var inputColor = $(this).find("input.color-field").val();
                        if(!inputColor) {
                            inputColor = $(this).find("input").spectrum('get').toHexString();
                        }
                        if($("#default-color-"+eleId).length) {
                            var isChecked = $("#default-color-"+eleId+" input").is(":checked");
                            $("#default-color-"+eleId+" input").val(inputColor);
                            $("#default-color-"+eleId+" label").css("background", inputColor);
                        } else {
                            $(".folder-default-colors").append('<div class="folder-default-color" id="default-color-'+eleId+'" data-id="'+eleId+'" > <input id="icon-color-'+eleId+'" name="customize_folders[default_icon_color]" class="sr-only" value="'+inputColor+'" type="radio"> <label for="icon-color-'+eleId+'" style="background: '+inputColor+'"></label> </div>');
                        }
                    });
                }
                $(".folder-default-colors .folder-default-color").each(function(){
                    var eleId = $(this).data("id");
                    if(!$("#selected-color-"+eleId).length) {
                        $(this).remove();
                    }
                });
                $(".folder-colors .color-field").spectrum("disable");
                $(".folder-color-box").removeClass("active");
                setCSSProperties();
            });

            $(document).on("change", "input[name='customize_folders[default_icon_color]']:checked", function(){
                setCSSProperties();
            });

            $(document).on("submit", "#setting-form", function(){
                if($(".folder-color-box").hasClass("active")) {
                    $(".save-default-colors").trigger("click");
                }
                $(".folder-colors .color-field").spectrum("enable");
                $(".folder-colors .folder-color.hide-color").remove();
                return true;
            });

            $(document).on("click", ".folder-color", function(e){
                e.stopImmediatePropagation();
                e.stopPropagation();
                $(".folder-color-box .folder-color").removeClass("default-color");
                $(this).addClass("default-color");
                $("#default_icon_color").val($(this).find("input.color-field").val());
            });

            function checkforColors() {
                <?php if(!$hasValidKey) { ?>
                    return;
                <?php } ?>
                $(".folder-color.new-folder-color").remove();
                if($(".folder-colors .folder-color:not(.hide-color)").length < 9) {
                    $(".folder-colors").append('<div class="folder-color new-folder-color"> <input name="new_folder_color" class="new-color-field sr-only" value="#ffffff" /> </div>');
                }
                $('.new-color-field').spectrum({
                    chooseText: "Submit",
                    preferredFormat: "hex",
                    showInput: true,
                    cancelText: "Cancel",
                    change: function(color) {
                        var newColor = color.toHexString();
                        if(newColor != "#ffffff") {
                            $('.new-folder-color').before("<div class='folder-color new-folder-color-option' id='selected-color-"+totalColors+"' data-id='"+totalColors+"'><input name='customize_folders[folder_colors][]' class='new-custom-color sr-only' value='"+newColor+"'> <button class='remove-color' type='button'><span class='dashicons dashicons-no-alt'></span></button></div>");
                            totalColors++;
                            $('.new-custom-color').spectrum({
                                chooseText: "Submit",
                                preferredFormat: "hex",
                                showInput: true,
                                cancelText: "Cancel",
                            });
                            $(".new-folder-color").remove();
                            checkforColors();
                        }
                    }
                });
            }
            checkforColors();
            $(document).on("click", ".remove-color", function(){
                $(this).closest(".folder-color").addClass("hide-color");
                if($(".folder-colors .new-folder-color").length == 0) {
                    checkforColors();
                }
            })
            $(document).on("click", ".color-field", function(){
                if($(this).is(":checked")) {
                    $(this).spectrum("show");
                }
            });
            $(document).on("click", ".custom-color-picker .color-box", function(){

            });
            <?php }//end if
            ?>
            $(document).on("click", "input[name='customize_folders[remove_folders_when_removed]']", function(e){
                if($(this).is(":checked")) {
                    $("#remove-folders-data-box").show();
                    //setFoldersRemoveStatus("on");
                } else {
                    setFoldersRemoveStatus("off");
                }
            })
            $(document).on("click", ".import-folder-data", function(e){
                selectedItem = $(this).closest("tr").data("plugin");
                if(!$(this).hasClass("in-popup")) {
                    var pluginName = $(this).closest("tr").find(".plugin-name").html();
                    var pluginFolders = parseInt($(this).closest("tr").data("folders"));
                    var popupTitle = importTitle.replace("%plugin%", pluginName);
                    $(".import-folder-title").html(popupTitle);
                    var popupDesc = importDesc.replace("%plugin%", "<b>" + pluginName + "</b>");
                    popupDesc = popupDesc.replace("%d", "<b>" + pluginFolders + "</b>");
                    $(".import-folder-note").html(popupDesc);
                    $("#import-plugin-data").show();
                } else {
                    importPluginData();
                }
            });
            $(document).on("click", ".remove-folders-data", function(e){
                e.preventDefault();
                $("#remove-confirmation-box").show();
                $("#delete-input").focus();
            });
            $(document).on("keyup", "#delete-input", function(){
                if($.trim($(this).val()) != "") {
                    var inputVal = $.trim($(this).val()).toLowerCase();
                    if (inputVal == "delete") {
                        $("#remove-folders-data-button").prop("disabled", false);
                        $(".delete-confirmation-message").html("<?php esc_html_e('This will delete all existing folders & settings', 'folders'); ?>");
                    } else {
                        $("#remove-folders-data-button").prop("disabled", true);
                        if (inputVal != "") {
                            var textLen = inputVal.length;
                            var curStr = ("delete").substring(0, textLen);
                            if (curStr != inputVal) {
                                $(".delete-confirmation-message").html("<?php esc_html_e('Please type DELETE and click on the "Delete" button to confirm', 'folders'); ?>");
                            } else {
                                $(".delete-confirmation-message").html("");
                            }
                        } else {
                            $(".delete-confirmation-message").html("");
                        }
                    }
                } else {
                    $(".delete-confirmation-message").html("");
                }
            });
            $(document).on("submit", "#remove_folders_data", function(e){
                e.preventDefault();
                if($.trim($("#delete-input").val()).toLowerCase() == "delete") {
                    $.ajax({
                        url: "<?php echo esc_url(admin_url("admin-ajax.php")) ?>",
                        data: {
                            action: 'wcp_remove_all_folders_data',
                            nonce: $("#remove-folder-nonce").val()
                        },
                        type: 'post',
                        success: function(res) {
                            <?php
                            $redirectURL = $this->getFolderSettingsURL();
                            if (!empty($redirectURL)) {
                                $page        = isset($_POST['tab_page']) ? sanitize_text_field($_POST['tab_page']) : "";
                                $type        = folders_sanitize_text('setting_page', 'get');
                                $type        = empty($type) ? "" : "&setting_page=".$type;
                                $redirectURL = $redirectURL.$type;
                                if (!empty($page)) {
                                    $redirectURL .= "&setting_page=".$page;
                                }
                            }

                            $redirectURL = $redirectURL."&note=2";
                            ?>
                            window.location = "<?php echo esc_url($redirectURL) ?>";
                        }
                    });
                }
                return false;
            });
            $(document).on("change", "#delete-input", function(){
                if($.trim($(this).val()).toLowerCase() == "delete") {
                    $("#remove-folders-data-button").prop("disabled", false);
                } else {
                    $("#remove-folders-data-button").prop("disabled", true);
                }
            });
            /*$(document).on("click", "#remove-folders-data-button", function(e){
                e.preventDefault();
                $(".folder-popup-form").hide();
                $("#remove-confirmation-box").show();
            });*/
            $(document).on("click", "#import-folder-button", function(e){
                importPluginData();
            });
            $(document).on("click", ".remove-folder-data", function(e){
                selectedItem = $(this).closest("tr").data("plugin");
                var pluginName = $(this).closest("tr").find(".plugin-name").html();
                var pluginFolders = parseInt($(this).closest("tr").data("folders"));
                var popupTitle = removeTitle.replace("%plugin%", pluginName);
                $(".remove-folder-title").html(popupTitle);
                var popupDesc = removeDesc.replace("%plugin%", "<b>" + pluginName + "</b>");
                popupDesc = popupDesc.replace("%d", "<b>" + pluginFolders + "</b>");
                $(".remove-folder-note").html(popupDesc);
                $("#remove-plugin-data").show();
            });
            $(document).on("click", "#remove-folder-button", function(){
                removePluginData();
            });
            setTooltipPosition();

            $(document).on("click", ".checkbox-color", function(){
                setCSSProperties();
            });

            $(document).on("change", ".checkbox-color", function(){
                setCSSProperties();
            });

            $(document).on("click", "#use_folder_undo", function(){
                if($(this).is(":checked")) {
                    $(".timeout-settings").addClass("active");
                } else {
                    $(".timeout-settings").removeClass("active");
                }
            });

            $("#license_key_form").on("submit", function(){
                licenseKey = jQuery.trim($("#license_key").val());
                jQuery.ajax({
                    url:"<?php echo esc_url(admin_url("admin-ajax.php")) ?>",
                    data: "key="+licenseKey+"&action=wcp_folder_activate_key&nonce=<?php echo esc_attr(wp_create_nonce("activate_folder_key")) ?>",
                    method: 'post',
                    success: function(res){
                        window.location = '<?php echo esc_url($settingURL) ?>&setting_page=license-key&m='+res;
                    }
                });
                return false;
            });
            $("#deactivate_key").on("click", function(){
                licenseKey = jQuery.trim($("#license_key").val());
                jQuery.ajax({
                    url:"<?php echo esc_url(admin_url("admin-ajax.php")) ?>",
                    data: "key="+licenseKey+"&action=wcp_folder_deactivate_key&nonce=<?php echo esc_attr(wp_create_nonce("deactivate_folder_key")) ?>",
                    method: 'post',
                    success: function(res){
                        window.location = '<?php echo esc_url($settingURL) ?>&setting_page=license-key&m='+res;
                    }
                });
                return false;
            });
        });

        function setFoldersRemoveStatus(status) {
            $.ajax({
                url: "<?php echo esc_url(admin_url("admin-ajax.php")) ?>",
                data: {
                    'action': 'wcp_update_folders_uninstall_status',
                    'status': status,
                    'nonce': "<?php echo esc_attr(wp_create_nonce("wcp_folders_uninstall_status")) ?>"
                },
                type: 'post',
                success: function (res) {

                }
            });
        }

        var totalAttachments = 0;

        function importPluginData() {
            $("#import-folder-button").addClass("button");
            $("#import-folder-button").prop("disabled", true);
            $(".import-folder-data").prop("disabled", true);
            $(".other-plugins-"+selectedItem+" .import-folder-data .spinner").addClass("active");
            totalAttachments = 0;
            importPluginDataByPage(1);
        }

        function importPluginDataByPage(pageNo) {
            $.ajax({
                url: "<?php echo esc_url(admin_url("admin-ajax.php")) ?>",
                data: {
                    'plugin': $(".other-plugins-"+selectedItem).data("plugin"),
                    'nonce': $(".other-plugins-"+selectedItem).data("nonce"),
                    'action': 'wcp_import_plugin_folders_data',
                    'paged' : pageNo,
                    'attached': totalAttachments
                },
                type: 'post',
                success: function(res){
                    var response = $.parseJSON(res);
                    if(response.status == -1) {
                        totalAttachments = 0;
                        $(".import-folder-data").prop("disabled", false);
                        $(".other-plugins-"+selectedItem+" .import-folder-data .spinner").removeClass("active");
                        $("#import-third-party-plugin-data").hide();
                        $("#no-more-folder-credit").show();
                        $("#import-folder-button").removeClass("button");
                        $("#import-folder-button").prop("disabled", false);
                    } else if(response.status) {
                        $(".other-plugins-"+response.data.plugin+" .import-message").html(response.message).addClass("success-import");

                        if(parseInt(response.data.pages) > parseInt(response.data.current)) {
                            totalAttachments = response.data.attachments;
                            importPluginDataByPage(parseInt(response.data.current)+1);
                        } else {
                            totalAttachments = 0;
                            $(".other-plugins-"+response.data.plugin+" .import-folder-data").remove();
                            $(".import-folder-data").prop("disabled", false);
                        }
                    } else {
                        $(".other-plugins-"+response.data.plugin+" .import-message").html(response.message).addClass("error-import");
                        $(".other-plugins-"+response.data.plugin+" .import-folder-data").remove();
                        $(".import-folder-data").prop("disabled", false);
                        totalAttachments = 0;
                    }
                    $("#import-folder-button").prop("disabled", false);
                    $("#import-plugin-data").hide();
                }
            });
        }

        function removePluginData() {
            $(".other-plugins-"+selectedItem+" .remove-folder-data .spinner").addClass("active");
            $.ajax({
                url: "<?php echo esc_url(admin_url("admin-ajax.php")) ?>",
                data: {
                    'plugin': $(".other-plugins-"+selectedItem).data("plugin"),
                    'nonce': $(".other-plugins-"+selectedItem).data("nonce"),
                    'action': 'wcp_remove_plugin_folders_data'
                },
                type: 'post',
                success: function(res){
                    var response = $.parseJSON(res);
                    $("#remove-plugin-data").hide();
                    if(response.status) {
                        $(".other-plugins-"+response.data.plugin).remove();
                    } else {
                        $(".other-plugins-"+response.data.plugin+" .import-message").html(response.message).addClass("error-import");
                        $(".other-plugins-"+response.data.plugin+" .remove-folder-data .spinner").removeClass("active");
                    }

                    if($("#import-folders-popup .plugin-import-table tr").length == 0) {
                        $("#import-folders-popup").hide();
                        $(".has-other-plugins").remove();
                        $(".no-more-plugins").addClass("active");
                    }
                }
            });
        }

        function setCSSProperties() {
            if($("#new_folder_color").length && $("#new_folder_color").val() != "") {
                $("#add-new-folder").css("border-color", $("#new_folder_color").val());
                $("#add-new-folder").css("background-color", $("#new_folder_color").val());
            } else  if ($("input[name='customize_folders[new_folder_color]']:checked").length) {
                $("#add-new-folder").css("border-color", $("input[name='customize_folders[new_folder_color]']:checked").val());
                $("#add-new-folder").css("background-color", $("input[name='customize_folders[new_folder_color]']:checked").val());
            }

            if($("#bulk_organize_button_color").length && $("#bulk_organize_button_color").val() != "") {
                $(".organize-button").css("border-color", $("#bulk_organize_button_color").val());
                $(".organize-button").css("background-color", $("#bulk_organize_button_color").val());
                $(".organize-button").css("color", "#ffffff");
            } else if ($("input[name='customize_folders[bulk_organize_button_color]']:checked").length) {
                $(".organize-button").css("border-color", $("input[name='customize_folders[bulk_organize_button_color]']:checked").val());
                $(".organize-button").css("background-color", $("input[name='customize_folders[bulk_organize_button_color]']:checked").val());
                $(".organize-button").css("color", "#ffffff");
            }

            if($("#dropdown_color").length && $("#dropdown_color").val() != "") {
                $(".media-select").css("border-color", $("#dropdown_color").val());
                $(".media-select").css("color", $("#dropdown_color").val());
            } else if ($("input[name='customize_folders[dropdown_color]']:checked").length) {
                $(".media-select").css("border-color", $("input[name='customize_folders[dropdown_color]']:checked").val());
                $(".media-select").css("color", $("input[name='customize_folders[dropdown_color]']:checked").val());
            }

            if($("#folder_bg_color").length && $("#folder_bg_color").val() != "") {
                $(".all-posts.active-item").css("border-color", $("#folder_bg_color").val());
                $(".all-posts.active-item").css("background-color", $("#folder_bg_color").val());
                $(".all-posts.active-item").css("color", "#ffffff");
            } else if ($("input[name='customize_folders[folder_bg_color]']:checked").length) {
                $(".all-posts.active-item").css("border-color", $("input[name='customize_folders[folder_bg_color]']:checked").val());
                $(".all-posts.active-item").css("background-color", $("input[name='customize_folders[folder_bg_color]']:checked").val());
                $(".all-posts.active-item").css("color", "#ffffff");
            }
            $("#custom-css").html("");
            if($("#folder_font").val() != "") {
                font_val = $("#folder_font").val();
                if(font_val == "System Stack") {
                    font_val = "-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen-Sans,Ubuntu,Cantarell,Helvetica Neue,sans-serif"
                } else {
                    $('head').append('<link href="https://fonts.googleapis.com/css?family=' + font_val + ':400,600,700" rel="stylesheet" type="text/css" class="chaty-google-font">');
                }
                $('.preview-box').css('font-family', font_val);
            } else {
                $('.preview-box').css('style', "");
            }
            if($("#folder_size").val() != "") {
                if($("#folder_size").val() == "custom") {
                    $("#custom_font_size").addClass("active");
                    $(".folder-list li a span, .header-posts a, .un-categorised-items a").css("font-size", $("#folder_custom_font_size").val()+"px");
                } else {
                    $(".folder-list li a span, .header-posts a, .un-categorised-items a").css("font-size", $("#folder_size").val()+"px");
                    $("#custom_font_size").removeClass("active");
                }
            } else {
                $(".folder-list li a span, .header-posts a, .un-categorised-items a").css("font-size", "14px");
            }

            var folderColor = "#334155";
            if($("input[name='customize_folders[default_icon_color]']:checked").length) {
                folderColor = $("input[name='customize_folders[default_icon_color]']:checked").val();
            }
            $(".folder-list i").css("color", folderColor);
        }

        $(window).on("scroll", function(){
            setTooltipPosition();
        }).on("resize", function(){
            setTooltipPosition();
        });

        function setTooltipPosition() {
            if($(".html-tooltip:not(.no-position):not(.dynamic)").length) {
                $(".html-tooltip:not(.no-position):not(.dynamic)").each(function(){
                    if($(this).offset().top - $(window).scrollTop() > 540) {
                        $(this).addClass("top").removeClass("side").removeClass("bottom");
                        $(this).find(".tooltip-text").attr("style","");
                        $(this).find(".tooltip-text").removeClass("hide-arrow");
                    } else if($(window).height() - ($(this).offset().top - $(window).scrollTop()) > 460) {
                        $(this).addClass("bottom").removeClass("top").removeClass("side");
                        $(this).find(".tooltip-text").attr("style","");
                        $(this).find(".tooltip-text").removeClass("hide-arrow");
                    } else {
                        $(this).addClass("side").removeClass("top").removeClass("bottom");
                        if($(this).find(".tooltip-text").length) {
                            $(this).find(".tooltip-text").attr("style","");
                            $(this).find(".tooltip-text").removeClass("hide-arrow");

                            if($(this).find(".tooltip-text").offset().top - $(window).scrollTop() - 50 < 0) {
                                $(this).find(".tooltip-text").css("margin-top", Math.abs($(this).find(".tooltip-text").offset().top - $(window).scrollTop() - 50)+"px");
                                $(this).find(".tooltip-text").addClass("hide-arrow");
                            } else {
                                $(this).find(".tooltip-text").attr("style","");
                                if(($(this).find(".tooltip-text").offset().top + parseInt($(this).find(".tooltip-text").outerHeight()) - $(window).scrollTop() - $(window).height()) > 0) {
                                    $(this).find(".tooltip-text").css("margin-top", ((-1)*Math.abs($(this).find(".tooltip-text").offset().top + parseInt($(this).find(".tooltip-text").outerHeight()) - $(window).scrollTop() - $(window).height()) - 10)+"px");
                                    $(this).find(".tooltip-text").addClass("hide-arrow");
                                }
                            }
                        }
                    }
                });
            }
            if($(".html-tooltip.dynamic:not(.no-position)").length) {
                $(".html-tooltip.dynamic:not(.no-position)").each(function(){
                    if($(this).offset().top - $(window).scrollTop() > 540) {
                        $(this).addClass("top").removeClass("side").removeClass("bottom");
                        $(this).find(".tooltip-text").attr("style","");
                        $(this).find(".tooltip-text").removeClass("hide-arrow");
                    } else if($(window).height() - ($(this).offset().top - $(window).scrollTop()) > 490) {
                        $(this).addClass("bottom").removeClass("top").removeClass("side");
                        $(this).find(".tooltip-text").attr("style","");
                        $(this).find(".tooltip-text").removeClass("hide-arrow");
                    } else {
                        $(this).addClass("side").removeClass("top").removeClass("bottom");
                        if($(this).find(".tooltip-text").length) {
                            $(this).find(".tooltip-text").attr("style","");
                            $(this).find(".tooltip-text").removeClass("hide-arrow");

                            if($(this).find(".tooltip-text").offset().top - $(window).scrollTop() - 50 < 0) {
                                $(this).find(".tooltip-text").css("margin-top", Math.abs($(this).find(".tooltip-text").offset().top - $(window).scrollTop() - 50)+"px");
                                $(this).find(".tooltip-text").addClass("hide-arrow");
                            } else {
                                $(this).find(".tooltip-text").attr("style","");
                                if(($(this).find(".tooltip-text").offset().top + parseInt($(this).find(".tooltip-text").outerHeight()) - $(window).scrollTop() - $(window).height()) > 0) {
                                    $(this).find(".tooltip-text").css("margin-top", ((-1)*Math.abs($(this).find(".tooltip-text").offset().top + parseInt($(this).find(".tooltip-text").outerHeight()) - $(window).scrollTop() - $(window).height()) - 10)+"px");
                                    $(this).find(".tooltip-text").addClass("hide-arrow");
                                }
                            }
                        }
                    }
                });
            }
        }
    }));
</script>
<div id="custom-css">

</div>
<?php
// Check website is hosted on wp.org or not
$wp_status = get_option("is_web_hosted_on_wp");
if ($wp_status === false) {
    $site_url = site_url("/");
    $domain   = wp_parse_url($site_url);
    $domain   = wp_parse_url($site_url);

    $options = [
        'http' => ['ignore_errors' => true],
    ];

    $webLink = $domain['host'];

    $context  = stream_context_create($options);
    $response = @file_get_contents(
        'https://public-api.wordpress.com/rest/v1/sites/'.$webLink,
        false,
        $context
    );
    if (!empty($response)) {
        $response = json_decode($response, true);

        if (!empty($response) && is_array($response)) {
            if (isset($response['ID']) && !empty($response['ID'])) {
                add_option("is_web_hosted_on_wp", "yes");
                $wp_status = "yes";
            }
        }
    }
}//end if

if ($wp_status === false) {
    if (!function_exists("get_current_user_id")) {
        add_option("is_web_hosted_on_wp", "yes");
        $wp_status = "yes";
    }
}

if ($wp_status === false) {
    add_option("is_web_hosted_on_wp", "no");
}

$show_media_popup = false;
if ($wp_status == "yes") {
    $popup_shown = get_option("is_wp_media_popup_shown");
    if ($popup_shown === false) {
        $show_media_popup = true;
        add_option("is_wp_media_popup_shown", 1);
    } else {
        if (!is_numeric($popup_shown)) {
            $popup_shown = 1;
        }

        $popup_shown++;
        if ($popup_shown < 4) {
            $show_media_popup = true;
        }

        update_option("is_wp_media_popup_shown", $popup_shown);
    }
}

// var_dump($show_media_popup); die;
?>
<div class="wrap">
    <h1><?php esc_html_e('Folders Settings', 'folders'); ?></h1>
    <?php
    settings_fields('folders_settings');
    settings_fields('default_folders');
    settings_fields('customize_folders');
    $options           = get_option('folders_settings');
    $default_folders   = get_option('default_folders');
    $customize_folders = get_option('customize_folders');
    $folders_settings  = get_option('folders_user_role_settings');
    $default_folders   = (empty($default_folders) || !is_array($default_folders)) ? [] : $default_folders;
    do_settings_sections(__FILE__);
    delete_transient("premio_folders_without_trash");
    ?>
    <?php if (isset($_GET['note']) && $_GET['note'] == 1) { ?>
        <div class="folder-notification notice notice-success is-dismissible">
            <div class="folder-notification-title"><?php esc_html_e("Changes Saved", "folders") ?></div>
            <div class="folder-notification-note"><?php esc_html_e("Your changes have been saved.", "folders") ?></div>
        </div>
    <?php } if (isset($_GET['note']) && $_GET['note'] == 2) {?>
        <div class="folder-notification notice notice-error is-dismissible">
            <div class="folder-notification-title"><?php esc_html_e("Folders Deleted", "folders") ?></div>
            <div class="folder-notification-note"><?php esc_html_e("All folders has been successfully deleted.", "folders") ?></div>
        </div>
    <?php } ?>
    <?php if ($setting_page != "license-key") { ?>
    <form action="options.php" method="post" id="setting-form">
        <input type="hidden" name="tab_page" value="<?php echo esc_attr($setting_page) ?>">
        <?php } ?>
        <div class="folders-tabs">
            <div class="folder-tab-menu">
                <ul>
                    <li><a class="<?php echo esc_attr(($setting_page == "folder-settings") ? "active" : "") ?>" href="<?php echo esc_url($settingURL."&setting_page=folder-settings") ?>"><?php esc_html_e('Folders Settings', 'folders'); ?></a></li>
                    <li><a class="<?php echo esc_attr(($setting_page == "folders-by-user") ? "active" : "") ?>" href="<?php echo esc_url($settingURL."&setting_page=folders-by-user") ?>"><?php esc_html_e('User Restrictions', 'folders'); ?></a></li>
                    <li><a class="<?php echo esc_attr(($setting_page == "customize-folders") ? "active" : "") ?>" href="<?php echo esc_url($settingURL."&setting_page=customize-folders") ?>"><?php esc_html_e('Customize Folders', 'folders'); ?></a></li>
                    <li><a class="<?php echo esc_attr(($setting_page == "folders-import") ? "active" : "") ?>" href="<?php echo esc_url($settingURL."&setting_page=folders-import") ?>"><?php esc_html_e('Tools', 'folders'); ?></a></li>
                    <?php if ($isInSettings) {
                        $licenseKey    = get_option("wcp_folder_license_key");
                        $hidePlans = get_option("hide_folders_plans");
                        if(!empty($licenseKey)) { ?>
                            <li><a class="<?php echo esc_attr(($setting_page == "license-key") ? "active" : "") ?>" href="<?php echo esc_url($settingURL."&setting_page=license-key&show-manage-plan=1") ?>"><?php esc_html_e('Manage Your Plan', 'folders'); ?></a></li>
                        <?php } else if($hidePlans !== false) { ?>
                            <li><a class="<?php echo esc_attr(($setting_page == "license-key") ? "active" : "") ?>" href="<?php echo esc_url($settingURL."&setting_page=license-key&hideFolder=1") ?>"><?php esc_html_e('License Key', 'folders'); ?></a></li>
                        <?php } else { ?>
                            <li><a class="<?php echo esc_attr(($setting_page == "license-key") ? "active" : "") ?>" href="<?php echo esc_url($settingURL."&setting_page=license-key") ?>"><?php esc_html_e('License Key', 'folders'); ?></a></li>
                        <?php }
                    }?>
                </ul>
            </div>
            <div class="folder-tab-content">
                <div class="tab-content <?php echo esc_attr(($setting_page=="folder-settings")?"active":"") ?>" id="folder-settings">
                    <div class="accordion-content no-bp">
                        <div class="accordion-left">
                            <table class="form-table">
                                <tboby>
                                    <?php
                                    $post_setting = apply_filters("check_for_folders_post_args", ["show_in_menu" => 1]);
                                    $post_types = get_post_types( $post_setting, 'objects' );
                                    $post_array = array("page", "post", "attachment");
                                    ?>
                                    <tr>
                                        <td><label class="folder-label" for="folders_post_type"><?php esc_html_e("Use folders with", "folders") ?></label></td>
                                        <td>
                                            <select id="folders_post_type" multiple name="folders_settings[]">
                                                <?php
                                                foreach ( $post_types as $post_type ) {
                                                    if ( ! $post_type->show_ui) continue;
                                                    $is_checked = !in_array( $post_type->name, $options )?"hide-option":"";
                                                    $selected_id = (isset($default_folders[$post_type->name]))?$default_folders[$post_type->name]:"all";
                                                    $is_exists = WCP_Pro_Folders::check_for_setting($post_type->name, "default_folders");
                                                    $is_customized = WCP_Pro_Folders::check_for_setting($post_type->name, "folders_settings");
                                                    $selected = in_array( $post_type->name, $options )?"selected":"";
                                                    if(in_array($post_type->name, $post_array) || $hasValidKey){
                                                        echo "<option ".esc_attr($selected)." value='".esc_attr($post_type->name)."' >".esc_attr($post_type->label)."</option>";
                                                    } else {
                                                        echo "<option disabled value='".esc_attr($post_type->name)."' >".esc_attr($post_type->label)." (Activate Key)</option>";
                                                    }
                                                }
                                                if(!$hasValidKey && (empty($is_exists) || $is_exists === false)) {
                                                    echo "<option class='pro-select-item' value='folders-pro'>" . esc_html__("Plugins", "folders"). " (Pro) 🔑</option>";
                                                } else {
                                                    $selected = in_array( "folders4plugins", $options )?"selected":"";
                                                    echo "<option " . esc_attr( $selected ) . " value='folders4plugins'>" . esc_html__("Plugins", "folders") . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </td>
                                        <td>
                                            <?php
                                            foreach ( $post_types as $post_type ) {
                                                if ( ! $post_type->show_ui) continue;
                                                $is_checked = !in_array( $post_type->name, $options )?"hide-option":"";
                                                $selected_id = (isset($default_folders[$post_type->name]))?$default_folders[$post_type->name]:"all";
                                                $is_exists = WCP_Pro_Folders::check_for_setting($post_type->name, "default_folders");
                                                $is_customized = WCP_Pro_Folders::check_for_setting($post_type->name, "folders_settings");
                                                $checked = in_array( $post_type->name, $options )?"checked":"";
                                                if(in_array($post_type->name, $post_array) || $is_customized === true || $hasValidKey){ ?>
                                                    <table class="folders-select hide-show-option <?php echo esc_attr($is_checked) ?>" id="folders-for-<?php echo esc_attr($post_type->name); ?>">
                                                        <tr>
                                                            <td><label class="folder-label" for="folders_for_<?php echo esc_attr($post_type->name); ?>"><?php esc_html_e("Default folder", "folders") ?></label></td>
                                                            <td>
                                                                <select class="" id="folders_for_<?php echo esc_attr($post_type->name); ?>" name="default_folders[<?php echo esc_attr($post_type->name); ?>]" ?>
                                                                    <option value=""><?php printf(esc_html__("All %1\$s Folder"), esc_attr($post_type->label)) ?></option>
                                                                    <option value="-1" <?php echo ($selected_id == -1)?"selected":"" ?>><?php printf(esc_html__("Unassigned %1\$s"), esc_attr($post_type->label)) ?></option>
                                                                    <?php
                                                                    if(isset($terms_data[$post_type->name]) && !empty($terms_data[$post_type->name])) {
                                                                        foreach ($terms_data[$post_type->name] as $term) {
                                                                            if(!$hasValidKey && (empty($is_exists) || $is_exists === false)) {
                                                                                echo "<option class='pro-select-item' value='folders-pro'>" . esc_attr( $term->name ). " (Pro) 🔑</option>";
                                                                            } else {
                                                                                $selected = ( $selected_id == $term->slug ) ? "selected" : "";
                                                                                echo "<option " . esc_attr( $selected ) . " value='" . esc_attr( $term->slug ) . "'>" . esc_attr( $term->name ) . "</option>";
                                                                            }
                                                                        }
                                                                    } ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                <?php }
                                            }
                                            $is_checked = !in_array( "folders4plugins", $options )?"hide-option":"";
                                            $selected_id = (isset($default_folders["folders4plugins"]))?$default_folders["folders4plugins"]:"all";
                                            $is_exists = WCP_Pro_Folders::check_for_setting("folders4plugins", "default_folders");
                                            $is_customized = WCP_Pro_Folders::check_for_setting("folders4plugins", "folders_settings");
                                            $checked = in_array( "folders4plugins", $options )?"checked":"";
                                            ?>
                                            <table class="folders-select hide-show-option <?php echo esc_attr($is_checked) ?>" id="folders-for-folders4plugins">
                                                <tr>
                                                    <td><label class="folder-label" for="folders_for_folders4plugins"><?php esc_html_e("Default folder", "folders") ?></label></td>
                                                    <td>
                                                        <select class="" id="folders_for_folders4plugins" name="default_folders[folders4plugins]" ?>
                                                            <option value=""><?php printf(esc_html__("All %1\$s Folder"), esc_html__("Plugins", "folders")) ?></option>
                                                            <option value="-1" <?php echo ($selected_id == -1)?"selected":"" ?>><?php printf(esc_html__("Unassigned %1\$s"), esc_html__("Plugins", "folders")) ?></option>
                                                            <?php
                                                            if(isset($terms_data["folders4plugins"]) && !empty($terms_data["folders4plugins"])) {
                                                                foreach ($terms_data["folders4plugins"] as $term) {
                                                                    if(!$hasValidKey && (empty($is_exists) || $is_exists === false)) {
                                                                        echo "<option class='pro-select-item' value='folders-pro'>" . esc_attr( $term->title ). " (Pro) 🔑</option>";
                                                                    } else {
                                                                        $selected = ( $selected_id == $term->title_slug ) ? "selected" : "";
                                                                        echo "<option " . esc_attr( $selected ) . " value='" . esc_attr( $term->title_slug ) . "'>" . esc_attr( $term->title ) . "</option>";
                                                                    }
                                                                }
                                                            } ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </tboby>
                            </table>
                            <table class="form-table">
                                    <?php
                                    $show_in_page = !isset($customize_folders['use_shortcuts'])?"yes":$customize_folders['use_shortcuts'];
                                    ?>
                                    <tr>
                                        <td class="no-padding">
                                            <input type="hidden" name="customize_folders[use_shortcuts]" value="no">
                                            <label for="use_shortcuts" class="custom-checkbox">
                                                <input id="use_shortcuts" class="sr-only" <?php checked($show_in_page, "yes") ?> type="checkbox" name="customize_folders[use_shortcuts]" value="yes">
                                                <span></span>
                                            </label>
                                        </td>
                                        <td colspan="3">
                                            <label for="use_shortcuts" ><?php esc_html_e( 'Use keyboard shortcuts to navigate faster', 'folders'); ?> <a href="#" class="view-shortcodes">(<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg> <?php esc_html_e( 'View shortcuts', 'folders'); ?>)</a>
                                        </td>
                                    </tr>
                                    <input type="hidden" name="customize_folders[dynamic_folders]" value="off" />
                                    <?php $dynamic_folders = !isset($customize_folders['dynamic_folders'])?"on":$customize_folders['dynamic_folders']; ?>
                                    <?php if(!$hasValidKey) { ?>
                                        <tr>
                                            <td style="padding: 15px 10px 15px 0px" colspan="4">
                                                <?php $dynamic_folders = "off"; ?>
                                                <a class="upgrade-box-link" target="_blank" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" >
                                                    <label for="" class="custom-checkbox send-user-to-pro">
                                                        <input disabled type="checkbox" class="sr-only" name="customize_folders[dynamic_folders]" id="dynamic_folders" value="on" <?php checked($dynamic_folders, "on") ?>>
                                                        <span></span>
                                                    </label>
                                                    <label for="" class="send-user-to-pro">
                                                        <?php esc_html_e("Dynamic Folders", "folders"); ?>
                                                        <button type="button" class="upgrade-link" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>"><?php esc_html_e("Activate your license key", 'folders') ?></button>
                                                        <span class="html-tooltip dynamic">
                                                            <span class="dashicons dashicons-editor-help"></span>
                                                            <span class="tooltip-text top" style="">
                                                                <?php esc_html_e("Automatically filter posts/pages/custom posts/media files based on author, date, file types & more", "folders") ?>
                                                                <img src="<?php echo esc_url(WCP_PRO_FOLDER_URL."assets/images/dynamic-folders.gif") ?>">
                                                            </span>
                                                        </span>
                                                        <span class="recommanded"><?php esc_html_e("Recommended", "folders"); ?></span>
                                                    </label>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } else { ?>
                                        <tr>
                                            <td width="20" class="no-padding">
                                                <label for="dynamic_folders" class="custom-checkbox">
                                                    <input class="sr-only" type="checkbox" id="dynamic_folders" name="customize_folders[dynamic_folders]" value="on" <?php checked($dynamic_folders, "on") ?>/>
                                                    <span></span>
                                                </label>
                                            </td>
                                            <td colspan="3">
                                                <label for="dynamic_folders" >
                                                    <?php esc_html_e( 'Dynamic Folders', 'folders'); ?>
                                                    <span class="html-tooltip dynamic">
                                                        <span class="dashicons dashicons-editor-help"></span>
                                                        <span class="tooltip-text top" style="">
                                                            <?php esc_html_e("Automatically filter posts/pages/custom posts/media files based on author, date, file types & more", "folders") ?>
                                                            <img src="<?php echo esc_url(WCP_PRO_FOLDER_URL."assets/images/dynamic-folders.gif") ?>">
                                                        </span>
                                                    </span>
                                                    <span class="recommanded"><?php esc_html_e("Recommended", "folders"); ?></span>
                                                </label>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <?php
                                    $show_in_page = !isset($customize_folders['use_folder_undo'])?"yes":$customize_folders['use_folder_undo'];
                                    ?>
                                    <tr>
                                        <td class="no-padding">
                                            <input type="hidden" name="customize_folders[use_folder_undo]" value="no">
                                            <label for="use_folder_undo" class="custom-checkbox">
                                                <input id="use_folder_undo" class="sr-only" <?php checked($show_in_page, "yes") ?> type="checkbox" name="customize_folders[use_folder_undo]" value="yes">
                                                <span></span>
                                            </label>
                                        </td>
                                        <td colspan="3">
                                            <label for="use_folder_undo" ><?php esc_html_e( 'Use folders with Undo action after performing tasks', 'folders'); ?>
                                                <span class="html-tooltip dynamic">
                                                        <span class="dashicons dashicons-editor-help"></span>
                                                        <span class="tooltip-text top" style="">
                                                            <?php esc_html_e("Undo any action on Folders. Undo move, copy, rename etc actions with this feature", "folders") ?>
                                                            <img src="<?php echo esc_url(WCP_PRO_FOLDER_URL."assets/images/undo-feature-folders.gif") ?>">
                                                        </span>
                                                    </span>
                                                <span class="recommanded"><?php esc_html_e("Recommended", "folders"); ?></span>
                                            </label>
                                        </td>
                                    </tr>
                                    <?php
                                    $default_timeout = !isset($customize_folders['default_timeout'])?"5":$customize_folders['default_timeout'];
                                    ?>
                                    <tr class="timeout-settings <?php echo ($show_in_page == "yes")?"active":"" ?>">
                                        <td style="padding: 10px 0;" colspan="4">
                                            <label for="default_timeout" ><?php esc_html_e( 'Default timeout', 'folders'); ?></label>
                                            <div class="seconds-box">
                                                <input type="number" class="seconds-input" name="customize_folders[default_timeout]" value="<?php echo esc_attr($default_timeout) ?>" />
                                            </div>
                                        </td>
                                    </tr>
                                    <script>
                                        function isNumeric(event) {
                                            console.log(event.keyCode);

                                            // Allow: backspace, delete, tab, escape, enter, and .
                                            if (event.keyCode === 8 || event.keyCode === 46 || event.keyCode === 9 || event.keyCode === 27 ||
                                                event.keyCode === 13 || (event.keyCode === 190 && event.key !== '.')) {
                                                return true;
                                            }

                                            // Ensure that it is a number and stop the keypress
                                            if (event.keyCode < 48 || event.keyCode > 57) {
                                                event.preventDefault();
                                                return false;
                                            }
                                            return true;
                                        }

                                        function changeValue() {
                                            if(document.getElementById("enable_media_trash_changed").value == 0) {
                                                document.getElementById("enable_media_trash_changed").value = 1;
                                            } else {
                                                document.getElementById("enable_media_trash_changed").value = 0;
                                            }
                                        }

                                    </script>
                                    <?php
                                    $enable_media_trash = !isset($customize_folders['enable_media_trash'])?"off":$customize_folders['enable_media_trash'];
                                    ?>
                                    <input type="hidden" name="customize_folders[enable_media_trash]" value="off" />
                                    <input type="hidden" name="customize_folders[enable_media_trash_changed]" id="enable_media_trash_changed" value="0">
                                    <?php if(!$hasValidKey) { ?>
                                        <tr>
                                            <td style="padding: 15px 10px 15px 0px" colspan="4">
                                                <?php $replace_media_title = "off"; ?>
                                                <a class="upgrade-box-link" target="_blank" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" >
                                                    <label for="" class="custom-checkbox send-user-to-pro">
                                                        <input disabled type="checkbox" class="sr-only" name="customize_folders[enable_media_trash]" id="replace_media_title" value="on" <?php checked($replace_media_title, "on") ?>>
                                                        <span></span>
                                                    </label>
                                                    <label for="" class="send-user-to-pro">
                                                        <?php esc_html_e("Move files to trash by default before deleting", "folders"); ?>
                                                        <span class="folder-tooltip" data-title="<?php esc_html_e("When enabled, files will be moved to trash to prevent mistakes, and then you can delete permanently from the trash", "folders") ?>"><span class="dashicons dashicons-editor-help"></span></span>
                                                    </label>
                                                    <button type="button" class="upgrade-link" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>"><?php esc_html_e("Activate your license key", 'folders') ?></button>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } else {
                                        ?>
                                        <tr>
                                            <td width="20" class="no-padding">
                                                <label for="enable_media_trash" class="custom-checkbox">
                                                    <input class="sr-only" type="checkbox" id="enable_media_trash" name="customize_folders[enable_media_trash]" value="on" <?php checked($enable_media_trash, "on") ?>  onclick="changeValue()"/>
                                                    <span></span>
                                                </label>
                                            </td>
                                            <td colspan="3">
                                                <label for="enable_media_trash" >
                                                    <?php esc_html_e("Move files to trash by default before deleting", "folders"); ?>
                                                    <span class="folder-tooltip" data-title="<?php esc_html_e("When enabled, files will be moved to trash to prevent mistakes, and then you can delete permanently from the trash", "folders") ?>"><span class="dashicons dashicons-editor-help"></span></span>
                                                </label>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <?php
                                    $show_in_page = !isset($customize_folders['folders_enable_replace_media'])?"yes":$customize_folders['folders_enable_replace_media'];
                                    ?>
                                    <tr>
                                        <td class="no-padding">
                                            <input type="hidden" name="customize_folders[folders_enable_replace_media]" value="no">
                                            <label for="folders_enable_replace_media" class="custom-checkbox">
                                                <input id="folders_enable_replace_media" class="sr-only" <?php checked($show_in_page, "yes") ?> type="checkbox" name="customize_folders[folders_enable_replace_media]" value="yes">
                                                <span></span>
                                            </label>
                                        </td>
                                        <td colspan="3">
                                            <label for="folders_enable_replace_media" ><?php esc_html_e( 'Enable Replace Media', 'folders'); ?> <span class="folder-tooltip" data-title="<?php esc_html_e("The Replace Media feature will allow you to replace your media files throughout your website with the click of a button,  which means the file will be replaced for all your posts, pages, etc ", "folders") ?>"><span class="dashicons dashicons-editor-help"></span></span><span class="recommanded"><?php esc_html_e("Recommended", "folders"); ?></span></label>
                                        </td>
                                    </tr>
                                    <?php
                                    $show_in_page = !isset($customize_folders['folders_media_cleaning'])?"yes":$customize_folders['folders_media_cleaning'];
                                    ?>
                                    <tr>
                                        <td class="no-padding">
                                            <input type="hidden" name="customize_folders[folders_media_cleaning]" value="no">
                                            <label for="folders_folders_media_cleaning" class="custom-checkbox">
                                                <input id="folders_folders_media_cleaning" class="sr-only" <?php checked($show_in_page, "yes") ?> type="checkbox" name="customize_folders[folders_media_cleaning]" value="yes">
                                                <span></span>
                                            </label>
                                        </td>
                                        <td colspan="3">
                                            <label for="folders_folders_media_cleaning" ><?php esc_html_e( 'Use Media Cleaning to clear unused media files', 'folders'); ?> <span class="folder-tooltip" data-title="<?php esc_html_e("The Media Cleaning feature enables you to clean unused media files for your WordPress site and adds a Media Cleaning item under Media", "folders") ?>"><span class="dashicons dashicons-editor-help"></span></span></label>
                                        </td>
                                    </tr>

                                    <input type="hidden" name="customize_folders[show_media_details]" value="off" />
                                    <!-- Do not make changes here, Only for Free -->
                                    <?php $show_media_details = !isset($customize_folders['show_media_details'])?"on":$customize_folders['show_media_details']; ?>
                                    <?php if(!$hasValidKey) { ?>
                                        <tr>
                                            <td style="padding: 15px 10px 15px 0px" colspan="4">
                                                <?php $show_media_details = "off"; ?>
                                                <a class="upgrade-box-link" target="_blank" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" >
                                                    <label for="" class="custom-checkbox send-user-to-pro">
                                                        <input disabled type="checkbox" class="sr-only" name="customize_folders[show_media_details]" id="show_media_details" value="on" <?php checked($show_media_details, "on") ?>>
                                                        <span></span>
                                                    </label>
                                                    <label for="" class="send-user-to-pro">
                                                        <?php esc_html_e("Show media details on hover", "folders"); ?>
                                                        <button type="button" class="upgrade-link" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>"><?php esc_html_e("Activate your license key", 'folders') ?></button>
                                                        <span class="html-tooltip bottom">
                                                            <span class="dashicons dashicons-editor-help"></span>
                                                            <span class="tooltip-text top" style="">
                                                                <?php esc_html_e("Show useful metadata including title, size, type, date, dimension & more on hover.", "folders"); ?>
                                                                <img src="<?php echo esc_url(WCP_PRO_FOLDER_URL."assets/images/folders-media.gif") ?>">
                                                            </span>
                                                        </span>
                                                        <span class="recommanded"><?php esc_html_e("Recommended", "folders"); ?></span>
                                                    </label>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } else { ?>
                                        <tr>
                                            <td width="20" class="no-padding">
                                                <label for="show_media_details" class="custom-checkbox">
                                                    <input class="sr-only" type="checkbox" id="show_media_details" name="customize_folders[show_media_details]" value="on" <?php checked($show_media_details, "on") ?>/>
                                                    <span></span>
                                                </label>
                                            </td>
                                            <td colspan="3">
                                                <label for="show_media_details" >
                                                    <?php esc_html_e( 'Show media details on hover', 'folders'); ?>
                                                    <span class="html-tooltip bottom">
                                                        <span class="dashicons dashicons-editor-help"></span>
                                                        <span class="tooltip-text top" style="">
                                                            <?php esc_html_e("Show useful metadata including title, size, type, date, dimension & more on hover.", "folders"); ?>
                                                            <img src="<?php echo esc_url(WCP_PRO_FOLDER_URL."assets/images/folders-media.gif") ?>">
                                                        </span>
                                                    </span>
                                                    <span class="recommanded"><?php esc_html_e("Recommended", "folders"); ?></span>
                                                </label>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <tr class="media-setting-box <?php echo (!$hasValidKey)?"active":($show_media_details == "on"?"active":"") ?> <?php echo (!$hasValidKey)?"send-user-to-pro":"" ?>" >
                                        <td colspan="4">
                                            <input type="hidden" name="folders_settings1" value="<?php echo esc_attr(time()) ?>">
                                            <div>
                                                <div class="">
                                                    <?php
                                                    $media_settings = array(
                                                        'image_title' => array(
                                                            "title" => esc_html__("Title", "folders"),
                                                            "default" => "on",
                                                        ),
                                                        'image_alt_text' =>  array(
                                                            "title" => esc_html__("Alternative Text", "folders"),
                                                            "default" => "off",
                                                        ),
                                                        'image_file_url' =>  array(
                                                            "title" => esc_html__("File URL", "folders"),
                                                            "default" => "off",
                                                        ),
                                                        'image_dimensions' =>  array(
                                                            "title" => esc_html__("Dimensions", "folders"),
                                                            "default" => "on",
                                                        ),
                                                        'image_size' =>  array(
                                                            "title" => esc_html__("Size", "folders"),
                                                            "default" => "off",
                                                        ),
                                                        'image_file_name' =>  array(
                                                            "title" => esc_html__("Filename", "folders"),
                                                            "default" => "off",
                                                        ),
                                                        'image_type' =>  array(
                                                            "title" => esc_html__("Type", "folders"),
                                                            "default" => "on",
                                                        ),
                                                        'image_date' =>  array(
                                                            "title" => esc_html__("Date", "folders"),
                                                            "default" => "on",
                                                        ),
                                                        'image_uploaded_by' =>  array(
                                                            "title" => esc_html__("Uploaded by", "folders"),
                                                            "default" => "off",
                                                        )
                                                    );
                                                    $media_col_settings = isset($customize_folders['media_col_settings']) && is_array($customize_folders['media_col_settings'])?$customize_folders['media_col_settings']:array("image_title","image_dimensions","image_type","image_date");
                                                    ?>
                                                    <input type="hidden" name="customize_folders[media_col_settings][]" value="all">
                                                    <div>
                                                        <div class="normal-box">
                                                            <select <?php echo (!$hasValidKey)?"disabled":"" ?> multiple="multiple" name="customize_folders[media_col_settings][]" class="select2-box">
                                                                <?php foreach($media_settings as $key=>$media) {
                                                                    if($hasValidKey) {
                                                                        $selected = in_array($key, $media_col_settings) ? "on" : "";
                                                                    } else {
                                                                        $selected = $media['default'];
                                                                    }
                                                                    ?>
                                                                    <option <?php selected($selected, "on") ?> value="<?php echo esc_attr($key) ?>"><?php echo esc_attr($media['title']) ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                        <?php if(!$hasValidKey) { ?>
                                                            <a class="upgrade-box" target="_blank" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" >
                                                                <button type="button"><?php esc_html_e("Activate your License key", 'folders') ?></button>
                                                            </a>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                    $show_in_page = !isset($customize_folders['show_folder_in_settings'])?"no":$customize_folders['show_folder_in_settings'];
                                    ?>
                                    <tr>
                                        <td class="no-padding">
                                            <input type="hidden" name="customize_folders[show_folder_in_settings]" value="no">
                                            <label for="show_folder_in_settings" class="custom-checkbox">
                                                <input id="show_folder_in_settings" class="sr-only" <?php checked($show_in_page, "yes") ?> type="checkbox" name="customize_folders[show_folder_in_settings]" value="yes">
                                                <span></span>
                                            </label>
                                        </td>
                                        <td colspan="3">
                                            <label for="show_folder_in_settings" ><?php esc_html_e( 'Place the Folders settings page nested under "Settings"', 'folders'); ?></label>
                                            <span class="folder-tooltip" data-title="<?php esc_html_e("When this setting is enabled, you will be able to access the Folders settings page by clicking on 'Settings' in the WordPress dashboard and then selecting 'Folders' from the submenu.", "folders") ?>">
                                                <span class="dashicons dashicons-editor-help"></span>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php $val = get_option("folders_show_in_menu"); ?>
                                    <input type="hidden" name="folders_show_in_menu" value="off" />
                                    <tr>
                                        <td width="20" class="no-padding">
                                            <label for="folders_show_in_menu" class="custom-checkbox">
                                                <input class="sr-only" type="checkbox" id="folders_show_in_menu" name="folders_show_in_menu" value="on" <?php checked($val, "on") ?>/>
                                                <span></span>
                                            </label>
                                        </td>
                                        <td colspan="3">
                                            <label for="folders_show_in_menu" ><?php esc_html_e( 'Show the folders also in WordPress menu', 'folders'); ?></label>
                                            <span class="html-tooltip bottom">
                                                <span class="dashicons dashicons-editor-help"></span>
                                                <span class="tooltip-text top tooltip-image-height" style="">
                                                    <?php esc_html_e("Show folders separately on your WordPress sidebar for quick and easy access to folders of Media, Posts, Pages etc", "folders"); ?>
                                                    <img alt="folders" src="<?php echo esc_url(WCP_PRO_FOLDER_URL."assets/images/page-folders.png") ?>" />
                                                </span>
                                            </span>
                                        </td>
                                    </tr>
                                    <input type="hidden" name="customize_folders[replace_media_title]" value="off" />
                                    <?php $replace_media_title = !isset($customize_folders['replace_media_title'])?"no":$customize_folders['replace_media_title']; ?>
                                    <?php if(!$hasValidKey) { ?>
                                        <tr>
                                            <td style="padding: 15px 10px 15px 0px" colspan="4">
                                                <?php $replace_media_title = "off"; ?>
                                                <a class="upgrade-box-link" target="_blank" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" >
                                                    <label for="" class="custom-checkbox send-user-to-pro">
                                                        <input disabled type="checkbox" class="sr-only" name="customize_folders[replace_media_title]" id="replace_media_title" value="on" <?php checked($replace_media_title, "on") ?>>
                                                        <span></span>
                                                    </label>
                                                    <label for="" class="send-user-to-pro">
                                                        <?php esc_html_e("Auto Rename file based on title", "folders"); ?>
                                                        <span class="folder-tooltip" data-title="<?php esc_html_e("Replace the actual file name of media files with the title from the WordPress editor.", "folders") ?>"><span class="dashicons dashicons-editor-help"></span></span></label>
                                                    <button type="button" class="upgrade-link" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>"><?php esc_html_e("Activate your license key", 'folders') ?></button>
                                                    </label>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } else { ?>
                                        <tr>
                                            <td width="20" class="no-padding">
                                                <label for="replace_media_title" class="custom-checkbox">
                                                    <input class="sr-only" type="checkbox" id="replace_media_title" name="customize_folders[replace_media_title]" value="on" <?php checked($replace_media_title, "on") ?>/>
                                                    <span></span>
                                                </label>
                                            </td>
                                            <td colspan="3">
                                                <label for="replace_media_title" >
                                                    <?php esc_html_e( 'Auto Rename file based on title', 'folders'); ?>
                                                    <span class="folder-tooltip" data-title="<?php esc_html_e("Replace the actual file name of media files with the title from the WordPress editor.", "folders") ?>"><span class="dashicons dashicons-editor-help"></span></span></label>
                                                </label>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tboby>
                            </table>
                        </div>
                        <div class="accordion-right">
                            <div class="premio-help">
                                <a href="https://premio.io/help/folders/?utm_source=pluginspage" target="_blank">
                                    <div class="premio-help-btn">
                                        <img src="<?php echo esc_url(WCP_PRO_FOLDER_URL."assets/images/premio-help.png") ?>" alt="Premio Help" class="Premio Help" />
                                        <div class="need-help"><?php esc_html_e("Need Help?", "folders") ?></div>
                                        <div class="visit-our"><?php esc_html_e("Visit our", "folders") ?></div>
                                        <div class="knowledge-base"><?php esc_html_e("knowledge base", "folders") ?></div>
                                    </div>
                                </a>
                            </div>
                            <?php if($wp_status == "yes") { ?>
                                <div class="premio-help">
                                    <div class="premio-help-btn wp-folder-user">
                                        <div class="folder-help-icon"><span class="dashicons dashicons-wordpress"></span></div>
                                        <div class="need-help"><?php esc_html_e("WordPress.com User?", "folders") ?></div>
                                        <div class="visit-our"><a target="_blank" href="https://premio.io/help/folders/how-to-activate-folders-for-wordpress-com-media-library/"><?php esc_html_e("Enable Folders", "folders"); ?></a><?php esc_html_e(" for your Media Library", "folders"); ?></div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="clear"></div>
                        <div class="submit-button">
                            <?php submit_button(); ?>
                        </div>
                    </div>
                </div>
                <div class="tab-content <?php echo esc_attr(($setting_page == "customize-folders") ? "active" : "") ?>" id="customize-folders">
                    <div class="accordion-content">
                        <div class="accordion-left">
                            <table class="form-table">
                                <?php
                                $colors        = [
                                    "#FA166B",
                                    "#0073AA",
                                    "#484848",
                                ];
                                $color         = !isset($customize_folders['new_folder_color'])||empty($customize_folders['new_folder_color']) ? "#FA166B" : $customize_folders['new_folder_color'];
                                $setting_color = WCP_Pro_Folders::check_for_setting("new_folder_color", "customize_folders");
                                $custom_color  = in_array($color, $colors) ? false : true;
                                ?>
                                <tr>
                                    <td width="255px" class="no-padding">
                                        <?php if (!$hasValidKey) { ?>
                                            <a class="upgrade-box-link" target="_blank" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" >
                                                <label for="new_folder_color" ><b>"<?php esc_html_e("New Folder", "folders") ?>"</b> <?php esc_html_e("button color", "folders") ?> <button type="button" class="upgrade-link" ><?php esc_html_e("Activate your license key", 'folders') ?></button></label>
                                            </a>
                                        <?php } else { ?>
                                            <label for="new_folder_color" ><b>"<?php esc_html_e("New Folder", "folders") ?>"</b> <?php esc_html_e("button color", "folders") ?></label>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <ul class="color-list">
                                            <?php $field_name = "new_folder_color"; foreach ($colors as $key => $value) { ?>
                                                <li>
                                                    <label class="color-checkbox <?php echo ($color == $value) ? "active" : "" ?>" for="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>">
                                                        <input type="radio" id="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>" name="customize_folders[<?php echo esc_attr($field_name) ?>]" class="sr-only checkbox-color" value="<?php echo esc_attr($value) ?>" <?php checked($color, $value) ?> />
                                                        <span style="background: <?php echo esc_attr($value) ?>"></span>
                                                    </label>
                                                </li>
                                            <?php } $key = 3; ?>
                                            <?php if (!$hasValidKey) { ?>
                                                <?php if ($setting_color !== false && $setting_color != "#FA166B") { ?>
                                                    <li>
                                                        <label class="color-checkbox <?php echo ($color == $setting_color) ? "active" : "" ?>" for="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>">
                                                            <input type="radio" id="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>" name="customize_folders[<?php echo esc_attr($field_name) ?>]" class="sr-only checkbox-color" value="<?php echo esc_attr($setting_color) ?>" <?php checked($color, $setting_color) ?> />
                                                            <span style="background: <?php echo esc_attr($setting_color) ?>"></span>
                                                        </label>
                                                    </li>
                                                <?php } ?>
                                                <li>
                                                    <a class="upgrade-box-link d-block" target="_blank" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" >
                                                        <span class="color-box"><span class="gradient"></span> <span class="color-box-area"><?php esc_html_e("Custom", "folders") ?></span></span>
                                                        <span class="upgrade-link"><?php esc_html_e("Activate your license key", "folders") ?></span>
                                                    </a>
                                                </li>
                                            <?php } else { ?>
                                                <li>
                                                    <label class="color-checkbox custom-color-picker <?php echo ($custom_color) ? "active" : "" ?>" for="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>">
                                                        <input type="radio" id="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>" name="customize_folders[<?php echo esc_attr($field_name) ?>]" class="sr-only checkbox-color color-field" value="<?php echo ($custom_color) ? esc_attr($color) : "#FA166B" ?>" <?php echo ($custom_color) ? "checked" : "" ?> />
                                                        <span class="color-box"><span class="color-box-area"><?php esc_html_e("Custom", "folders"); ?></span></span>
                                                    </label>
                                                </li>
                                            <?php }//end if
                                            ?>
                                        </ul>
                                    </td>
                                    <td rowspan="4" >

                                    </td>
                                </tr>
                                <?php
                                $color         = !isset($customize_folders['bulk_organize_button_color'])||empty($customize_folders['bulk_organize_button_color']) ? "#FA166B" : $customize_folders['bulk_organize_button_color'];
                                $setting_color = WCP_Pro_Folders::check_for_setting("bulk_organize_button_color", "customize_folders");
                                $custom_color  = in_array($color, $colors) ? false : true;
                                ?>
                                <tr>
                                    <td class="no-padding">
                                        <?php if (!$hasValidKey) { ?>
                                            <a class="upgrade-box-link" target="_blank" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" >
                                                <label for="bulk_organize_button_color" ><?php printf(esc_html__("\"%1\$s\" button color", "folders"), "<b>".esc_html__("Bulk Organize", "folders")."</b>") ?> <button type="button" class="upgrade-link" ><?php esc_html_e("Activate your license key", 'folders') ?></button></label>
                                            </a>
                                        <?php } else { ?>
                                            <label for="bulk_organize_button_color" ><?php printf(esc_html__("\"%1\$s\" button color", "folders"), "<b>".esc_html__("Bulk Organize", "folders")."</b>") ?></label>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <ul class="color-list">
                                            <?php $field_name = "bulk_organize_button_color"; foreach ($colors as $key => $value) { ?>
                                                <li>
                                                    <label class="color-checkbox <?php echo ($color == $value) ? "active" : "" ?>" for="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>">
                                                        <input type="radio" id="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>" name="customize_folders[<?php echo esc_attr($field_name) ?>]" class="sr-only checkbox-color" value="<?php echo esc_attr($value) ?>" <?php checked($color, $value) ?> />
                                                        <span style="background: <?php echo esc_attr($value) ?>"></span>
                                                    </label>
                                                </li>
                                            <?php } $key = 3; ?>
                                            <?php if (!$hasValidKey) { ?>
                                                <?php if ($setting_color !== false && $setting_color != "#FA166B") { ?>
                                                    <li>
                                                        <label class="color-checkbox <?php echo ($color == $setting_color) ? "active" : "" ?>" for="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>">
                                                            <input type="radio" id="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>" name="customize_folders[<?php echo esc_attr($field_name) ?>]" class="sr-only checkbox-color" value="<?php echo esc_attr($setting_color) ?>" <?php checked($color, $setting_color) ?> />
                                                            <span style="background: <?php echo esc_attr($setting_color) ?>"></span>
                                                        </label>
                                                    </li>
                                                <?php } ?>
                                                <li>
                                                    <a class="upgrade-box-link d-block" target="_blank" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" >
                                                        <span class="color-box"><span class="gradient"></span> <span class="color-box-area"><?php esc_html_e("Custom", "folders") ?></span></span>
                                                        <span class="upgrade-link"><?php esc_html_e("Activate your license key", "folders") ?></span>
                                                    </a>
                                                </li>
                                            <?php } else { ?>
                                                <li>
                                                    <label class="color-checkbox custom-color-picker <?php echo ($custom_color) ? "active" : "" ?>" for="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>">
                                                        <input type="radio" id="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>" name="customize_folders[<?php echo esc_attr($field_name) ?>]" class="sr-only checkbox-color color-field" value="<?php echo ($custom_color) ? esc_attr($color) : "#FA166B" ?>" <?php echo ($custom_color) ? "checked" : "" ?> />
                                                        <span class="color-box"><span class="color-box-area"><?php esc_html_e("Custom", "folders"); ?></span></span>
                                                    </label>
                                                </li>
                                            <?php }//end if
                                            ?>
                                        </ul>
                                    </td>
                                </tr>
                                <?php
                                $color         = !isset($customize_folders['media_replace_button'])||empty($customize_folders['media_replace_button']) ? "#FA166B" : $customize_folders['media_replace_button'];
                                $setting_color = WCP_Pro_Folders::check_for_setting("media_replace_button", "customize_folders");
                                $custom_color  = in_array($color, $colors) ? false : true;
                                ?>
                                <tr>
                                    <td class="no-padding">
                                        <?php if (!$hasValidKey) { ?>
                                            <a class="upgrade-box-link" target="_blank" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" >
                                                <label for="media_replace_button" ><?php printf(esc_html__("\"%1\$s\" media library button", "folders"), "<b>".esc_html__("Replace File", "folders")."</b>") ?> <button type="button" class="upgrade-link" ><?php esc_html_e("Activate your license key", 'folders') ?></button></label>
                                            </a>
                                        <?php } else { ?>
                                            <label for="media_replace_button" ><?php printf(esc_html__("\"%1\$s\" media library button", "folders"), "<b>".esc_html__("Replace File", "folders")."</b>") ?></label>

                                        <?php } ?>
                                    </td>
                                    <td>
                                        <ul class="color-list">
                                            <?php $field_name = "media_replace_button"; foreach ($colors as $key => $value) { ?>
                                                <li>
                                                    <label class="color-checkbox <?php echo ($color == $value) ? "active" : "" ?>" for="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>">
                                                        <input type="radio" id="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>" name="customize_folders[<?php echo esc_attr($field_name) ?>]" class="sr-only checkbox-color" value="<?php echo esc_attr($value) ?>" <?php checked($color, $value) ?> />
                                                        <span style="background: <?php echo esc_attr($value) ?>"></span>
                                                    </label>
                                                </li>
                                            <?php } $key = 3; ?>
                                            <?php if (!$hasValidKey) { ?>
                                                <?php if ($setting_color !== false && $setting_color != "#FA166B") { ?>
                                                    <li>
                                                        <label class="color-checkbox <?php echo ($color == $setting_color) ? "active" : "" ?>" for="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>">
                                                            <input type="radio" id="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>" name="customize_folders[<?php echo esc_attr($field_name) ?>]" class="sr-only checkbox-color" value="<?php echo esc_attr($setting_color) ?>" <?php checked($color, $setting_color) ?> />
                                                            <span style="background: <?php echo esc_attr($setting_color) ?>"></span>
                                                        </label>
                                                    </li>
                                                <?php } ?>
                                                <li>
                                                    <a class="upgrade-box-link d-block" target="_blank" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" >
                                                        <span class="color-box"><span class="gradient"></span> <span class="color-box-area"><?php esc_html_e("Custom", "folders") ?></span></span>
                                                        <span class="upgrade-link"><?php esc_html_e("Activate your license key", "folders") ?></span>
                                                    </a>
                                                </li>
                                            <?php } else { ?>
                                                <li>
                                                    <label class="color-checkbox custom-color-picker <?php echo ($custom_color) ? "active" : "" ?>" for="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>">
                                                        <input type="radio" id="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>" name="customize_folders[<?php echo esc_attr($field_name) ?>]" class="sr-only checkbox-color color-field" value="<?php echo ($custom_color) ? esc_attr($color) : "#FA166B" ?>" <?php echo ($custom_color) ? "checked" : "" ?> />
                                                        <span class="color-box"><span class="color-box-area"><?php esc_html_e("Custom", "folders") ?></span></span>
                                                    </label>
                                                </li>
                                            <?php }//end if
                                            ?>
                                        </ul>
                                    </td>
                                </tr>
                                <?php
                                $color         = !isset($customize_folders['dropdown_color'])||empty($customize_folders['dropdown_color']) ? "#484848" : $customize_folders['dropdown_color'];
                                $setting_color = WCP_Pro_Folders::check_for_setting("dropdown_color", "customize_folders");
                                $custom_color  = in_array($color, $colors) ? false : true;
                                ?>
                                <tr>
                                    <td class="no-padding">
                                        <?php if (!$hasValidKey) { ?>
                                            <a class="upgrade-box-link" target="_blank" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" >
                                                <label for="dropdown_color" ><?php esc_html_e("Dropdown color", "folders") ?> <button type="button" class="upgrade-link" ><?php esc_html_e("Activate your license key", 'folders') ?></button></label>
                                            </a>
                                        <?php } else { ?>
                                            <label for="dropdown_color" ><?php esc_html_e("Dropdown color", "folders") ?></label>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <ul class="color-list">
                                            <?php $field_name = "dropdown_color"; foreach ($colors as $key => $value) { ?>
                                                <li>
                                                    <label class="color-checkbox <?php echo ($color == $value) ? "active" : "" ?>" for="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>">
                                                        <input type="radio" id="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>" name="customize_folders[<?php echo esc_attr($field_name) ?>]" class="sr-only checkbox-color" value="<?php echo esc_attr($value) ?>" <?php checked($color, $value) ?> />
                                                        <span style="background: <?php echo esc_attr($value) ?>"></span>
                                                    </label>
                                                </li>
                                            <?php } $key = 3; ?>
                                            <?php if (!$hasValidKey) { ?>
                                                <?php if ($setting_color !== false && $setting_color != "#FA166B") { ?>
                                                    <li>
                                                        <label class="color-checkbox <?php echo ($color == $setting_color) ? "active" : "" ?>" for="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>">
                                                            <input type="radio" id="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>" name="customize_folders[<?php echo esc_attr($field_name) ?>]" class="sr-only checkbox-color" value="<?php echo esc_attr($setting_color) ?>" <?php checked($color, $setting_color) ?> />
                                                            <span style="background: <?php echo esc_attr($setting_color) ?>"></span>
                                                        </label>
                                                    </li>
                                                <?php } ?>
                                                <li>
                                                    <a class="upgrade-box-link d-block" target="_blank" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" >
                                                        <span class="color-box"><span class="gradient"></span> <span class="color-box-area"><?php esc_html_e("Custom", "folders") ?></span></span>
                                                        <span class="upgrade-link"><?php esc_html_e("Activate your license key", "folders") ?></span>
                                                    </a>
                                                </li>
                                            <?php } else { ?>
                                                <li>
                                                    <label class="color-checkbox custom-color-picker <?php echo ($custom_color) ? "active" : "" ?>" for="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>">
                                                        <input type="radio" id="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>" name="customize_folders[<?php echo esc_attr($field_name) ?>]" class="sr-only checkbox-color color-field" value="<?php echo ($custom_color) ? esc_attr($color) : "#484848" ?>" <?php echo ($custom_color) ? "checked" : "" ?> />
                                                        <span class="color-box"><span class="color-box-area"><?php esc_html_e("Custom", "folders") ?></span></span>
                                                    </label>
                                                </li>
                                            <?php }//end if
                                            ?>
                                        </ul>
                                    </td>
                                </tr>
                                <?php
                                $color         = !isset($customize_folders['folder_bg_color'])||empty($customize_folders['folder_bg_color']) ? "#FA166B" : $customize_folders['folder_bg_color'];
                                $setting_color = WCP_Pro_Folders::check_for_setting("folder_bg_color", "customize_folders");
                                $custom_color  = in_array($color, $colors) ? false : true;
                                ?>
                                <tr>
                                    <td class="no-padding">
                                        <?php if (!$hasValidKey) { ?>
                                            <a class="upgrade-box-link" target="_blank" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" >
                                                <label for="folder_bg_color" ><?php esc_html_e("Folders background color", "folders") ?> <button type="button" class="upgrade-link" ><?php esc_html_e("Activate your license key", 'folders') ?></button></label>
                                            </a>
                                        <?php } else { ?>
                                            <label for="folder_bg_color" ><?php esc_html_e("Folders background color", "folders") ?></label>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <ul class="color-list">
                                            <?php $field_name = "folder_bg_color"; foreach ($colors as $key => $value) { ?>
                                                <li>
                                                    <label class="color-checkbox <?php echo ($color == $value) ? "active" : "" ?>" for="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>">
                                                        <input type="radio" id="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>" name="customize_folders[<?php echo esc_attr($field_name) ?>]" class="sr-only checkbox-color" value="<?php echo esc_attr($value) ?>" <?php checked($color, $value) ?> />
                                                        <span style="background: <?php echo esc_attr($value) ?>"></span>
                                                    </label>
                                                </li>
                                            <?php } $key = 3; ?>
                                            <?php if (!$hasValidKey) { ?>
                                                <?php if ($setting_color !== false && $setting_color != "#FA166B") { ?>
                                                    <li>
                                                        <label class="color-checkbox <?php echo ($color == $setting_color) ? "active" : "" ?>" for="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>">
                                                            <input type="radio" id="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>" name="customize_folders[<?php echo esc_attr($field_name) ?>]" class="sr-only checkbox-color" value="<?php echo esc_attr($setting_color) ?>" <?php checked($color, $setting_color) ?> />
                                                            <span style="background: <?php echo esc_attr($setting_color) ?>"></span>
                                                        </label>
                                                    </li>
                                                <?php } ?>
                                                <li>
                                                    <a class="upgrade-box-link d-block" target="_blank" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" >
                                                        <span class="color-box"><span class="gradient"></span> <span class="color-box-area"><?php esc_html_e("Custom", "folders") ?></span></span>
                                                        <span class="upgrade-link"><?php esc_html_e("Activate your license key", "folders") ?></span>
                                                    </a>
                                                </li>
                                            <?php } else { ?>
                                                <li>
                                                    <label class="color-checkbox custom-color-picker <?php echo ($custom_color) ? "active" : "" ?>" for="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>">
                                                        <input type="radio" id="<?php echo esc_attr($field_name)."-".esc_attr($key) ?>" name="customize_folders[<?php echo esc_attr($field_name) ?>]" class="sr-only checkbox-color color-field" value="<?php echo ($custom_color) ? esc_attr($color) : "#FA166B" ?>" <?php echo ($custom_color) ? "checked" : "" ?> />
                                                        <span class="color-box"><span class="color-box-area"><?php esc_html_e("Custom", "folders") ?></span></span>
                                                    </label>
                                                </li>
                                            <?php }//end if
                                            ?>
                                        </ul>
                                    </td>
                                </tr>
                                <tr class="default-icon-color">
                                    <td class="no-padding">
                                        <label for="enable_horizontal_scroll" >
                                            <?php esc_html_e("Default icon color", 'folders'); ?>
                                            <span class="folder-tooltip" data-title="<?php esc_html_e("You can set the default icon color for Folders from here. Each folder and subfolder can have a different color, which you can change in the folder settings.", "folders"); ?>"><span class="dashicons dashicons-editor-help"></span></span>
                                        </label>
                                    </td>
                                    <?php
                                    if($hasValidKey) {
                                        $defaultColors = [];
                                        if(!isset($customize_folders['folder_color_updated'])) {
                                            $defaultColors = ["#334155", "#86cd91", "#1E88E5", "#ff6060", "#49E670", "#ffdb5e", "#ff95ee"];
                                        }
                                        $colors = isset($customize_folders['folder_colors'])?$customize_folders['folder_colors']:$defaultColors;
                                        $default_icon_color = isset($customize_folders['default_icon_color'])?$customize_folders['default_icon_color']:"#334155";
                                        ?>
                                        <td colspan="2">
                                            <div class="folder-color-box">
                                                <div class="folder-colors">
                                                    <?php foreach($colors as $key=>$color) {
                                                        $is_active = ($color == $default_icon_color)?"default-color":"";
                                                        ?>
                                                        <div class="folder-color <?php echo esc_attr($is_active) ?>" id="selected-color-<?php echo esc_attr($key) ?>" data-id="<?php echo esc_attr($key) ?>">
                                                            <input name="customize_folders[folder_colors][]" class="color-field sr-only" value="<?php echo esc_attr($color) ?>" />
                                                            <button class="remove-color" type="button"><span class="dashicons dashicons-no-alt"></span></button>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <div class="folder-default-colors">
                                                    <?php foreach($colors as $key=>$color) {
                                                        ?>
                                                        <div class="folder-default-color" id="default-color-<?php echo esc_attr($key) ?>" data-id="<?php echo esc_attr($key) ?>">
                                                            <input <?php checked($color, $default_icon_color) ?> id="icon-color-<?php echo esc_attr($key) ?>" name="customize_folders[default_icon_color]" class="sr-only" value="<?php echo esc_attr($color) ?>" type="radio" />
                                                            <label for="icon-color-<?php echo esc_attr($key) ?>" style="background: <?php echo esc_attr($color) ?>"></label>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <div class="clear"></div>
                                                <div class="text-right">
                                                    <a href="#" class="edit-default-colors"><?php esc_html_e("Edit default colors", "folders") ?></a>
                                                    <a href="#" class="cancel-default-colors"><?php esc_html_e("Cancel", "folders") ?></a>
                                                    <a href="#" class="save-default-colors"><?php esc_html_e("Save", "folders") ?></a>
                                                </div>
                                            </div>
                                        </td>
                                    <?php } else {
                                        $default_icon_color = isset($customize_folders['default_icon_color'])?$customize_folders['default_icon_color']:"#334155";
                                        $colors = ["#334155", "#86cd91", "#1E88E5", "#ff6060"];
                                        ?>
                                        <td>
                                            <div class="folder-colors">
                                                <div class="folder-default-colors">
                                                    <?php foreach($colors as $key=>$color) {
                                                        ?>
                                                        <div class="folder-default-color" id="default-color-<?php echo esc_attr($key) ?>" data-id="<?php echo esc_attr($key) ?>">
                                                            <input <?php checked($color, $default_icon_color) ?> id="icon-color-<?php echo esc_attr($key) ?>" name="customize_folders[default_icon_color]" class="sr-only" value="<?php echo esc_attr($color) ?>" type="radio" />
                                                            <label for="icon-color-<?php echo esc_attr($key) ?>" style="background: <?php echo esc_attr($color) ?>"></label>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <div class="clear"></div>
                                            <a href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" class="custom-icon-color-link"><?php esc_html_e("Activate your License key", "folders"); ?></a>
                                        </td>
                                    <?php } ?>
                                    <!--<input type="hidden" name="customize_folders[folder_color_updated]" value="1">-->
                                </tr>
                                <?php
                                $font         = !isset($customize_folders['folder_font'])||empty($customize_folders['folder_font']) ? "" : $customize_folders['folder_font'];
                                $setting_font = WCP_Pro_Folders::check_for_setting("folder_font", "customize_folders");
                                $index        = 0;
                                ?>
                                <tr>
                                    <td class="no-padding">
                                        <label for="folder_font" >
                                            <?php if ($hasValidKey || ($setting_font !== false && $setting_font != "" && !in_array($setting_font, ["Arial", "Tahoma", "Verdana", "Helvetica", "Times New Roman", "Trebuchet MS", "Georgia"]))) {
                                                esc_html_e("Folders font", 'folders');
                                            } else { ?>
                                                <a class="upgrade-box-link" target="_blank" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" >
                                                    <?php esc_html_e('Folders font', 'folders') ?> <button type="button" class="upgrade-link" ><?php esc_html_e("Activate your License key", 'folders') ?></button>
                                                </a>
                                            <?php } ?>
                                        </label>
                                    </td>
                                    <td colspan="2">
                                        <select name="customize_folders[folder_font]" id="folder_font" >
                                            <?php $group = '';
                                            foreach ($fonts as $key => $value) :
                                                $title = $key;
                                                if ($index == 0) {
                                                    $key = "";
                                                }

                                                $index++;
                                                if ($value != $group) {
                                                    echo '<optgroup label="'.esc_attr($value).'">';
                                                    $group = $value;
                                                }

                                                if ($hasValidKey || (($setting_font !== false && $setting_font != "" && !in_array($setting_font, ["Arial", "Tahoma", "Verdana", "Helvetica", "Times New Roman", "Trebuchet MS", "Georgia"])) || $value != "Google Fonts" )) { ?>
                                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($font, $key); ?>><?php echo esc_attr($title); ?></option>
                                                <?php } else { ?>
                                                    <option class="pro-select-item" value="folders-pro"><?php echo esc_attr($title); ?> (Pro) 🔑</option>
                                                <?php } ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <?php
                                $size        = ! isset($customize_folders['folder_size']) || empty($customize_folders['folder_size']) ? "16" : $customize_folders['folder_size'];
                                $folder_size = WCP_Pro_Folders::check_for_setting("folder_size", "customize_folders");
                                ?>
                                <tr>
                                    <td class="no-padding">
                                        <label for="folder_size" >
                                            <?php if ($folder_size === false || intval($folder_size) === 16) { ?>
                                                <a class="upgrade-box-link" target="_blank" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" >
                                                    <?php esc_html_e('Folders size', 'folders') ?> <button type="button" class="upgrade-link" ><?php esc_html_e("Activate your License key", 'folders') ?></button>
                                                </a>
                                            <?php } else { ?>
                                                <?php esc_html_e("Folders size", 'folders'); ?>
                                            <?php } ?>
                                        </label>
                                    </td>
                                    <td colspan="2">
                                        <?php
                                        if (!$hasValidKey && ($folder_size === false || intval($folder_size) == 16)) {
                                            $sizes = [
                                                "folders-pro"       => "Small (Pro) 🔑",
                                                "16"                => "Medium",
                                                "folders-pro-item"  => "Large (Pro) 🔑",
                                                "folders-pro-field" => "Custom (Pro) 🔑",
                                            ];
                                            $size  = 16;
                                        } else {
                                            $sizes = [
                                                "12"     => "Small",
                                                "16"     => "Medium",
                                                "20"     => "Large",
                                                "custom" => "Custom",
                                            ];
                                        }
                                        ?>
                                        <select name="customize_folders[folder_size]" id="folder_size" >
                                            <?php
                                            foreach ($sizes as $key => $value) {
                                                $selected = ($key == $size) ? "selected" : "";
                                                echo "<option ".esc_attr($selected)." value='".esc_attr($key)."'>".esc_attr($value)."</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <?php $folder_custom_font_size = ! isset($customize_folders['folder_custom_font_size']) || empty($customize_folders['folder_custom_font_size']) ? "16" : $customize_folders['folder_custom_font_size']; ?>
                                <tr id="custom_font_size">
                                    <td>&nbsp;</td>
                                    <td>
                                        <div class="font-range-slider">
                                            <div class="font-slider">
                                                <div class="font-slider-left">A</div>
                                                <div class="font-slider-center">
                                                    <input class="none" type="range" min="10" max="24" value="<?php echo esc_attr($folder_custom_font_size) ?>" id="folder_custom_font_size" step="1" name="customize_folders[folder_custom_font_size]">
                                                </div>
                                                <div class="font-slider-right">A</div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php $enable_horizontal_scroll = ! isset($customize_folders['enable_horizontal_scroll']) || empty($customize_folders['enable_horizontal_scroll']) ? "on" : $customize_folders['enable_horizontal_scroll']; ?>
                                <tr>
                                    <td class="no-padding">
                                        <label for="enable_horizontal_scroll" >
                                            <?php esc_html_e("Enable horizontal scroll", 'folders'); ?> <span class="folder-tooltip" data-title="<?php esc_html_e("When a folder has too much text or you have many levels of sub-folders, a horizontal scroll bar will appear on the bottom to scroll & view Folder names", "folders") ?>"><span class="dashicons dashicons-editor-help"></span></span>
                                        </label>
                                    </td>
                                    <td colspan="2">
                                        <input type="hidden" name="customize_folders[enable_horizontal_scroll]" value="off" />
                                        <div class="inline-checkbox">
                                            <label class="folder-switch" for="enable_horizontal_scroll">
                                                <input type="checkbox" class="sr-only normal-input" name="customize_folders[enable_horizontal_scroll]" id="enable_horizontal_scroll" value="on" <?php checked($enable_horizontal_scroll, "on") ?>>
                                                <div class="folder-slider round"></div>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                                $show_in_page = isset($customize_folders['show_in_page']) ? $customize_folders['show_in_page'] : "hide";
                                $show_folder  = WCP_Pro_Folders::check_for_setting("show_in_page", "customize_folders");
                                if (empty($show_in_page)) {
                                    $show_in_page = "hide";
                                }
                                ?>
                                <tr>
                                    <td colspan="3" style="padding: 15px 20px 15px 0">
                                        <input type="hidden" name="customize_folders[show_in_page]" value="hide">
                                        <?php if (!$hasValidKey && ($show_folder === false || $show_folder === "hide")) { ?>
                                            <a class="upgrade-box-link" target="_blank" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" >
                                                <label for="" class="custom-checkbox send-user-to-pro">
                                                    <input disabled type="checkbox" class="sr-only" name="customize_folders[show_in_page]" id="show_in_page" value="on" <?php checked($show_in_page, "show") ?>>
                                                    <span></span>
                                                </label>
                                                <label for="" class="send-user-to-pro">
                                                    <?php esc_html_e("Show Folders in upper position", "folders"); ?>
                                                    <span class="folder-tooltip" data-title="<?php esc_html_e("The list of your folders will also appear at the top of the page, e.g. under 'Media library'.", "folders"); ?>">
                                                        <span class="dashicons dashicons-editor-help"></span>
                                                    </span>
                                                    <button type="button" class="upgrade-link" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>"><?php esc_html_e("Activate your License key", 'folders') ?></button>
                                                </label>
                                            </a>
                                        <?php } else { ?>
                                            <label class="custom-checkbox" for="show_folders">
                                                <input id="show_folders" class="sr-only" <?php checked($show_in_page, "show") ?> type="checkbox" name="customize_folders[show_in_page]" value="show">
                                                <span></span>
                                            </label>
                                            <label for="show_folders"><?php esc_html_e("Show Folders in upper position", "folders") ?></label>
                                            <span class="folder-tooltip" data-title="<?php esc_html_e("The list of your folders will also appear at the top of the page, e.g. under 'Media library'.", "folders"); ?>">
                                                <span class="dashicons dashicons-editor-help"></span>
                                            </span>
                                        <?php } ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="accordion-right">
                            <div class="preview-text">
                                <?php esc_html_e("Preview", "folders"); ?>
                                <div class="preview-text-info"><?php esc_html_e("See the full functionality on your media library, posts, pages, and custom posts", "folders") ?></div>
                            </div>
                            <div class="preview-inner-box">
                                <div class="preview-box">
                                    <div class="wcp-custom-form">
                                        <div class="form-title">
                                            <?php esc_html_e("Folders", "folders"); ?>
                                            <a href="javascript:;" class="add-new-folder" id="add-new-folder">
                                                <span class="create_new_folder"><i class="pfolder-add-folder"></i></span>
                                                <span><?php esc_html_e("New Folder", "folders"); ?></span>
                                            </a>
                                            <div class="clear"></div>
                                        </div>
                                        <div class="form-options">
                                            <ul>
                                                <li>
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" class="sr-only" >
                                                        <span></span>
                                                    </div>
                                                </li>
                                                <li>
                                                    <a href="javascript:;" id="inline-update"><span class="icon pfolder-edit-folder"><span class="path2"></span></span> <span class="text"><?php esc_html_e("Rename", "folders"); ?></span> </a>
                                                </li>
                                                <li>
                                                    <a href="javascript:;" id="inline-remove"><span class="icon pfolder-remove"></span> <span class="text"><?php esc_html_e("Delete", "folders"); ?></span> </a>
                                                </li>
                                                <li class="last">
                                                    <a href="javascript:;" id="expand-collapse-list" data-tooltip="<?php esc_html_e("Expand", "folders"); ?>"><span class="icon pfolder-arrow-down"></span></a>
                                                </li>
                                                <li class="last">
                                                    <a href="javascript:;" ><span class="icon pfolder-arrow-sort"></span></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="shadow-box">
                                        <div class="header-posts">
                                            <a href="javascript:;" class="all-posts active-item"><?php esc_html_e("All Files", "folders"); ?> <span class="total-count">215</span></a>
                                        </div>
                                        <div class="un-categorised-items  ui-droppable">
                                            <a href="javascript:;" class="un-categorized-posts"><?php esc_html_e("Unassigned Files", "folders"); ?> <span class="total-count total-empty">191</span> </a>
                                        </div>
                                        <div class="separator"></div>
                                        <ul class="folder-list">
                                            <li><a href="javascript:;"><i class="wcp-icon pfolder-folder-close"></i> <span><?php esc_html_e("Folder 1", "folders"); ?></span><span class="total-count">20</span><span class="clear"></span></a></li>
                                            <li><a href="javascript:;"><i class="wcp-icon pfolder-folder-close"></i> <span><?php esc_html_e("Folder 2", "folders"); ?></span><span class="total-count">13</span><span class="clear"></span></a></li>
                                            <li><a href="javascript:;"><i class="wcp-icon pfolder-folder-close"></i> <span><?php esc_html_e("Folder 3", "folders"); ?></span><span class="total-count">5</span><span class="clear"></span></a></li>
                                        </ul>
                                        <div class="separator"></div>
                                        <div class="media-buttons">
                                            <select class="media-select">
                                                <option><?php esc_html_e("All Files", "folders"); ?></option>
                                                <option><?php esc_html_e("Folder 1", "folders"); ?></option>
                                                <option><?php esc_html_e("Folder 2", "folders"); ?></option>
                                                <option><?php esc_html_e("Folder 3", "folders"); ?></option>
                                            </select>
                                            <button type="button" class="button organize-button"><?php esc_html_e("Bulk Organize", "folders"); ?></button>
                                            <div style="clear: both;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        <?php submit_button(); ?>
                    </div>
                </div>
                <div class="tab-content <?php echo esc_attr(($setting_page == "folders-import") ? "active" : "") ?>" id="folder-import">
                    <?php
                    $remove_folders_when_removed = !isset($customize_folders['remove_folders_when_removed']) ? "off" : $customize_folders['remove_folders_when_removed'];
                    ?>
                    <input type="hidden" name="customize_folders[remove_folders_when_removed]" value="off" />
                    <div class="folder-danger-zone">
                        <table class="import-export-table">
                            <?php if ($is_plugin_exists) { ?>
                                <tr class="has-other-plugins">
                                    <td>
                                        <span class="folder-info"><span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e("Import", "folders"); ?></span>
                                        <span class="folder-text"><span><?php esc_html_e("External folders found.", "folders"); ?></span> <?php esc_html_e("Click import to start importing external folders.", "folders"); ?></span>
                                    </td>
                                    <td class="last-td">
                                        <a href="#" class="import-folders-button"><?php esc_html_e("Import", "folders"); ?></a>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr class="no-more-plugins <?php echo (!$is_plugin_exists) ? "active" : "" ?>">
                                <td>
                                    <span class="folder-info"><span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e("Import", "folders"); ?></span>
                                    <span class="folder-text"><?php esc_html_e("Couldn't detect any external folders that can be imported. Please contact us if you have external folders that were not detected", "folders"); ?></span>
                                </td>
                                <td class="last-td">
                                    <a href="https://premio.io/contact/" target="_blank" class="contact-folders-button"><?php esc_html_e("Contact Us", "folders"); ?></a>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"><span class="danzer-title"><span class="dashicons dashicons-warning"></span> <?php esc_html_e("Danger Zone", "folders"); ?></span></td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="danger-info"><?php esc_html_e("Delete plugin data upon deletion", "folders"); ?></span>
                                    <span class="danger-data"><?php esc_html_e("Delete all folders when the plugin is removed. This feature will remove all existing folders created by the plugin upon deletion.", "folders"); ?> <b><?php esc_html_e("(Not recommended)", "folders"); ?></span></b>
                                </td>
                                <td class="last-td" >
                                    <div class="inline-checkbox">
                                        <label class="folder-switch" for="remove_folders_when_removed">
                                            <input type="checkbox" class="sr-only change-folder-status" name="customize_folders[remove_folders_when_removed]" id="remove_folders_when_removed" value="on" <?php checked($remove_folders_when_removed, "on") ?>>
                                            <div class="folder-slider round"></div>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="danger-info"><?php esc_html_e("Manual Data Removal", "folders"); ?></span>
                                    <span class="danger-data"><?php esc_html_e("Delete all folders data manually This feature will remove all existing folders created by the plugin. Use this feature with caution.", "folders"); ?>
                                </td>
                                <td class="last-td">
                                    <a href="#" class="remove-folders-data"><?php esc_html_e("Delete Now", "folders"); ?></a>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="tab-content <?php echo esc_attr(($setting_page == "license-key") ? "active" : "") ?>">
                    <?php if ($setting_page == "license-key") { ?>
                        <?php
                        $type = folders_sanitize_text('m', 'get');
                        if (isset($type) && !empty($type)) {
                            switch ($type) {
                                case "error": ?>
                                    <div class='folder-error-message'><?php esc_html_e("Your license key is not valid", 'folders') ?></div>
                                    <?php break;
                                case "valid": ?>
                                    <div class='folder-success-message'><?php esc_html_e("Your license key is activated successfully", 'folders') ?></div>
                                    <?php break;
                                case "unactivated": ?>
                                    <div class='folder-success-message'><?php esc_html_e("Your license key is deactivated successfully", 'folders') ?></div>
                                    <?php break;
                                case "expired": ?>
                                    <div class='folder-error-message'><?php esc_html_e("Your license has been expired", 'folders') ?></div>
                                    <?php break;
                                case "no_activations": ?>
                                    <div class='folder-error-message'><?php printf(esc_html__("Your license was activated for another domain, please visit your %1\$sPremio account%2\$s", 'folders'), '<a target="_blank" href="https://go.premio.io">', '</a>') ?></div>
                                    <?php break;
                            }
                        }
                        ?>
                        <div class="key-table">
                            <div class="license-key-footer">
                                <?php
                                $licenseKey    = get_option("wcp_folder_license_key");
                                $licenseData   = [];
                                $active_status = 0;
                                delete_transient("folder_license_key_data");
                                if (!empty($licenseKey)) {
                                    $licenseData = $this->get_license_key_data($licenseKey);
                                    if (!empty($licenseData)) {
                                        if ($licenseData['license'] == "valid") {
                                            $active_status = 1;
                                        } else if ($licenseData['license'] == "expired") {
                                            $active_status = 2;
                                        }
                                    } else {
                                        $licenseKey = "";
                                        delete_option("wcp_folder_license_key");
                                    }
                                }

                                if (!$active_status) {
                                    esc_html_e("To receive updates, please enter your valid license key.", 'folders');
                                } else if ($active_status == 1 && $licenseData['expires'] == "lifetime") {
                                    esc_html_e("You have a lifetime license", 'folders');
                                } else if ($active_status == 1) {
                                    printf(esc_html__("Your license will expire on %1\$s"), esc_attr(gmdate("d M, Y", strtotime($licenseData['expires']))));
                                } else if ($active_status == 2) {
                                    $url = WCP_PRO_FOLDER_API_URL.'/checkout/?edd_license_key='.$licenseKey."&download_id=".WCP_PRO_FOLDER_PR0DUCT_ID; ?>
                                    <span class='error-message'><?php printf(esc_html__("Your license has been expired on %1\$s", 'folders'), esc_attr(gmdate("d M, Y", strtotime($licenseData['expires'])))) ?></span><a target="_blank" href="<?php echo esc_url($url) ?>" class="button button-primary" ><?php esc_html_e("Renew Now", 'folders') ?></a> <?php
                                }

                                $encLicenseKey = $licenseKey;
                                if (!empty($licenseKey)) {
                                    $encLicenseKey = substr_replace($licenseKey, "**************", 6, 20);
                                }
                                ?>
                            </div>
                            <form action="" id="license_key_form">
                                <div class="input-field">
                                    <label for="license_key"><?php esc_html_e("License Key", 'folders') ?></label>
                                    <?php if (!empty($licenseKey)) { ?>
                                        <input class="license-key" readonly value="<?php echo esc_attr($encLicenseKey) ?>">
                                        <input type="hidden" class="license-key" id="license_key" name="license_key" value="">
                                    <?php } else { ?>
                                        <input class="license-key" id="license_key" name="license_key" value="<?php echo esc_attr($licenseKey) ?>">
                                    <?php } ?>
                                </div>
                                <div class="submit">
                                    <?php if (!empty($licenseKey)) { ?>
                                        <a href="javascript:;" class="button secondary-button" id="deactivate_key"><?php esc_html_e("Deactivate Key", 'folders') ?></a>
                                    <?php } else { ?>
                                        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_html_e("Activate Key", 'folders') ?>">
                                    <?php } ?>
                                </div>
                            </form>
                        </div>
                    <?php }//end if
                    ?>
                </div>
                <div class="tab-content <?php echo esc_attr(($setting_page == "folders-by-user") ? "active" : "") ?>" id="folders-by-user">
                    <input type="hidden" name="customize_folders[folders_by_users]" value="off" >
                    <input type="hidden" name="customize_folders[dynamic_folders_for_admin_only]" value="off" >
                    <input type="hidden" name="folders_user_role_settings[folders_by_user_roles]" value="off" >
                    <?php
                    $folders_by_users = !isset($customize_folders['folders_by_users']) ? "off" : $customize_folders['folders_by_users'];
                    $dynamic_folders_for_admin_only = !isset($customize_folders['dynamic_folders_for_admin_only']) ? "off" : $customize_folders['dynamic_folders_for_admin_only'];
                    $folders_by_user_roles = ($folders_settings == "on") ? "on" : "off";
                    ?>
                    <?php if ($setting_page == "folders-by-user") { ?>
                        <div class="folders-by-user">

                            <table class="import-export-table">
                                <?php if ($hasValidKey) { ?>
                                    <tr>
                                        <td>
                                            <span class="danger-info"><?php esc_html_e("Restrict users to their folders only", "folders"); ?></span>
                                            <span class="danger-data"><?php esc_html_e("Users will only be able to access their folders and media. Only Admin users will be able to view all folders", "folders"); ?>
                                        </td>
                                        <td class="last-td" >
                                            <div class="inline-checkbox">
                                                <label class="folder-switch" for="folders_by_users">
                                                    <input type="checkbox" class="sr-only change-folder-user-status" name="customize_folders[folders_by_users]" id="folders_by_users" value="on" <?php checked($folders_by_users, "on") ?>>
                                                    <div class="folder-slider round"></div>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } else { ?>
                                    <tr>
                                        <td>
                                            <span class="danger-info"><?php esc_html_e("Restrict users to their folders only", "folders"); ?></span>
                                            <span class="danger-data"><?php esc_html_e("Users will only be able to access their folders and media. Only Admin users will be able to view all folders", "folders"); ?>
                                        </td>
                                        <td class="last-td" >
                                            <a class="upgrade-box-link" target="_blank" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" >
                                                <span>
                                                    <label class="folder-switch send-user-to-pro" for="dynamic_folders_for_admin_only">
                                                        <input type="hidden">
                                                        <div class="folder-slider round"></div>
                                                    </label>
                                                </span>
                                                <button type="button" class="upgrade-link" ><?php esc_html_e("Activate your License key", 'folders') ?></button>
                                            </a>
                                        </td>
                                    </tr>
                                <?php }//end if
                                ?>
                                <?php if ($hasValidKey) { ?>
                                    <tr>
                                        <td>
                                            <span class="danger-info"><?php esc_html_e("Restrict access to dynamic folders", "folders"); ?></span>
                                            <span class="danger-data"><?php esc_html_e("Regular users will not access dynamic folders.", "folders"); ?></span>
                                            <span class="danger-data"><?php esc_html_e("Only Admin users will be able to view dynamic folders.", "folders"); ?></span>
                                        </td>
                                        <td class="last-td" >
                                            <div class="inline-checkbox">
                                                <label class="folder-switch" for="dynamic_folders_for_admin_only">
                                                    <input type="checkbox" class="sr-only" name="customize_folders[dynamic_folders_for_admin_only]" id="dynamic_folders_for_admin_only" value="on" <?php checked($dynamic_folders_for_admin_only, "on") ?>>
                                                    <div class="folder-slider round"></div>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } else { ?>
                                    <tr>
                                        <td>
                                            <span class="danger-info"><?php esc_html_e("Restrict access to dynamic folders", "folders"); ?></span>
                                            <span class="danger-data"><?php esc_html_e("Regular users will not access dynamic folders.", "folders"); ?></span>
                                            <span class="danger-data"><?php esc_html_e("Only Admin users will be able to view dynamic folders.", "folders"); ?></span>
                                        </td>
                                        <td class="last-td" >
                                            <a class="upgrade-box-link" target="_blank" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" >
                                                <span>
                                                    <label class="folder-switch send-user-to-pro" for="folders_by_users">
                                                        <input type="hidden">
                                                        <div class="folder-slider round"></div>
                                                    </label>
                                                </span>
                                                <button type="button" class="upgrade-link" ><?php esc_html_e("Activate your License key", 'folders') ?></button>
                                            </a>
                                        </td>
                                    </tr>
                                <?php }//end if ?>
                                <?php
                                $user = wp_get_current_user();
                                $roles = ( array ) $user->roles;
                                if(!empty($roles) && in_array('administrator', $roles)) { ?>
                                    <?php if ($hasValidKey) { ?>
                                        <tr>
                                            <td>
                                                <div class="folder-access">
                                                    <?php esc_html_e("Folders Access Management", "folders"); ?>
                                                    <div class="folder-html-tooltip">
                                                        <div class="html-tooltip-text">
                                                            <p><?php esc_html_e("Give specific users or general WordPress user roles granular access to Folders:", "folders") ?></p>
                                                            <ol>
                                                                <li><?php esc_html_e("Admin: Full access to modifying, creating and viewing folders", "folders") ?></li>
                                                                <li><?php esc_html_e("View & Edit: These users can only update and view currently existing folders", "folders") ?></li>
                                                                <li><?php esc_html_e("View only: These users can only view the folders", "folders") ?></li>
                                                                <li><?php esc_html_e("No Access: These users have no access to folders", "folders") ?></li>
                                                            </ol>
                                                        </div>
                                                        <span class="dashicons dashicons-editor-help"></span>
                                                    </div>
                                                </div>
                                                <span class="danger-data">
                                                    <?php esc_html_e("Set Folders permissions for user roles and specific users.", "folders"); ?>

                                                </span>
                                            </td>
                                            <td class="last-td" >
                                                <div class="inline-checkbox">
                                                    <label class="folder-switch" for="folders_by_user_roles">
                                                        <input type="checkbox" class="sr-only" name="folders_user_role_settings[folders_by_user_roles]" id="folders_by_user_roles" value="on" <?php checked($folders_by_user_roles, "on") ?>>
                                                        <div class="folder-slider round"></div>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } else { ?>
                                        <tr>
                                            <td>
                                                <div class="folder-access">
                                                    <?php esc_html_e("Folders Access Management", "folders"); ?>
                                                    <div class="folder-html-tooltip">
                                                        <div class="html-tooltip-text">
                                                            <?php esc_html_e("The user roles for Folders are as follows:", "folders") ?>
                                                            <ol>
                                                                <li><?php esc_html_e("Admin: Full access to modifying, creating and viewing folders", "folders") ?></li>
                                                                <li><?php esc_html_e("View & Edit: These users can only update and view currently existing folders", "folders") ?></li>
                                                                <li><?php esc_html_e("View only: These users can only view the folders", "folders") ?></li>
                                                                <li><?php esc_html_e("No Access: These users have no access to folders", "folders") ?></li>
                                                            </ol>
                                                        </div>
                                                        <span class="dashicons dashicons-editor-help"></span>
                                                    </div>
                                                </div>
                                                <span class="danger-data">
                                                    <?php esc_html_e("Set Folders permissions for user roles and specific users.", "folders"); ?>
                                                </span>
                                            </td>
                                            <td class="last-td" >
                                                <a class="upgrade-box-link" target="_blank" href="<?php echo esc_url($this->getRegisterKeyURL()) ?>" >
                                                    <span>
                                                        <label class="folder-switch send-user-to-pro" for="folders_by_users">
                                                            <input type="hidden">
                                                            <div class="folder-slider round"></div>
                                                        </label>
                                                    </span>
                                                    <button type="button" class="upgrade-link" ><?php esc_html_e("Activate your License key", 'folders') ?></button>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                                    }//end if
                                }//end if
                                ?>
                            </table>
                        </div>
                        <?php if(!empty($roles) && in_array('administrator', $roles)) {
                            include_once "folder-user-settings.php";
                        } ?>
                    <?php } else { ?>
                        <?php if ($folders_by_users == "on") { ?>
                            <input type="hidden" name="customize_folders[folders_by_users]" value="on" >
                        <?php } ?>
                        <?php if ($dynamic_folders_for_admin_only == "on") { ?>
                            <input type="hidden" name="customize_folders[dynamic_folders_for_admin_only]" value="on" >
                        <?php } ?>
                    <?php }//end if
                    ?>
                </div>
            </div>
        </div>
        <?php
        ?>
        <input type="hidden" name="folder_nonce" value="<?php echo esc_attr(wp_create_nonce("folder_settings")) ?>">
        <input type="hidden" name="folder_page" value="<?php echo sanitize_text_field($_SERVER['REQUEST_URI']) ?>">
        <?php if ($setting_page != "license-key") { ?>
    </form>
<?php } ?>
</div>

<div class="folder-popup-form" id="import-plugin-data">
    <div class="popup-form-content">
        <div class="popup-content">
            <div class="close-popup-button">
                <a class="" href="javascript:;"><span></span></a>
            </div>
            <div class="import-folder-title"></div>
            <div class="import-folder-note"></div>
            <div class="folder-form-buttons">
                <button type="submit" class="form-submit-btn" id="import-folder-button"><?php esc_html_e("Import", 'folders'); ?></button>
                <a href="javascript:;" class="form-cancel-btn"><?php esc_html_e("Cancel", 'folders'); ?></a>
            </div>
        </div>
    </div>
</div>

<?php
// if($plugin['is_exists']) { ?>
<div class="folder-popup-form" id="import-folders-popup">
    <div class="popup-form-content">
        <div class="popup-content">
            <div class="close-popup-button">
                <a class="" href="javascript:;"><span></span></a>
            </div>
            <div class="import-plugin-title"><?php esc_html_e("Import data", 'folders'); ?></div>
            <div class="plugin-import-table">
                <div class="import-folder-table">
                    <table>
                        <tbody>
                        <?php foreach ($plugin_info as $slug => $plugin) { ?>
                            <?php if ($plugin['is_exists']) { ?>
                                <tr class="other-plugins-<?php echo esc_attr($slug) ?>" data-plugin="<?php echo esc_attr($slug) ?>" data-nonce="<?php echo esc_attr(wp_create_nonce("import_data_from_".$slug)) ?>" data-folders="<?php echo esc_attr($plugin['total_folders']) ?>" data-attachments="<?php echo esc_attr($plugin['total_attachments']) ?>">
                                    <th class="plugin-name"><?php echo esc_attr($plugin['name']) ?></th>
                                    <td>
                                        <span class="import-message"><?php printf(esc_html__("%1\$s folder%2\$s and %3\$s attachment%4\$s", "folders"), esc_attr($plugin['total_folders']), ($plugin['total_folders'] > 1) ? esc_html__("s") : "", esc_attr($plugin['total_attachments']), ($plugin['total_attachments'] > 1) ? esc_html__("s") : "") ?></span>
                                        <button type="button" class="button button-primary import-folder-data in-popup"><?php esc_html_e("Import", "folders"); ?> <span class="spinner"></span></button>
                                        <button type="button" class="button button-secondary remove-folder-data in-popup"><?php esc_html_e("Delete plugin data", "folders"); ?> <span class="spinner"></span></button>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="folder-form-buttons">
                <div class=""></div>
                <a href="javascript:;" class="form-cancel-btn"><?php esc_html_e("Close", 'folders'); ?></a>
            </div>
        </div>
    </div>
</div>
<?php
// } ?>

<div class="folder-popup-form" id="remove-plugin-data">
    <div class="popup-form-content">
        <div class="popup-content">
            <div class="close-popup-button">
                <a class="" href="javascript:;"><span></span></a>
            </div>
            <div class="remove-folder-title"><?php esc_html_e("Are you sure?", 'folders'); ?></div>
            <div class="remove-folder-note"><?php printf(esc_html__("You're about to delete %s's folders. Are you sure you'd like to proceed?", 'folders'), '$plugin'); ?></div>
            <div class="folder-form-buttons">
                <button type="submit" class="form-submit-btn delete-folder-plugin" id="remove-folder-button"><?php esc_html_e("Delete plugin data", 'folders'); ?></button>
                <a href="javascript:;" class="form-cancel-btn"><?php esc_html_e("Cancel", 'folders'); ?></a>
            </div>
        </div>
    </div>
</div>

<div class="folder-popup-form remove-folders-box" id="remove-folders-data-box">
    <div class="popup-form-content">
        <div class="popup-content">
            <div class="close-popup-button close-remove-folders">
                <a class="" href="javascript:;"><span></span></a>
            </div>
            <div class="remove-folder-title"><?php esc_html_e("Are you sure?", 'folders'); ?></div>
            <div class="remove-folder-note"><?php printf(esc_html__("Folders will remove all created folders once you remove the plugin. We recommend you %snot to use this feature%s if you plan to use Folders in future.", 'folders'), "<b>", "</b>"); ?></div>
            <div class="folder-form-buttons">
                <a href="javascript:;" class="form-cancel-btn cancel-folders"><?php esc_html_e("Cancel", 'folders'); ?></a>
                <button type="submit" class="form-cancel-btn delete-button"><?php esc_html_e("I want to delete anyway", 'folders'); ?></button>
            </div>
        </div>
    </div>
</div>

<div class="folder-popup-form" id="remove-confirmation-box">
    <div class="popup-form-content">
        <div class="popup-content">
            <form id="remove_folders_data" autocomplete="off" >
                <div class="close-popup-button">
                    <a class="" href="javascript:;"><span></span></a>
                </div>
                <div class="remove-folder-title"></div>
                <div class="remove-folder-note text-left">
                    <b><?php printf(esc_html__("Type %s to confirm", 'folders'), "DELETE"); ?></b>
                    <div class="input-box">
                        <input autocomplete="off" type="text" id="delete-input" name="delete" >
                    </div>
                    <div class="delete-confirmation-message"><?php esc_html_e('', 'folders'); ?></div>
                </div>
                <div class="folder-form-buttons">
                    <input type="hidden" name="nonce" id="remove-folder-nonce" value="<?php echo esc_attr(wp_create_nonce("remove_folders_data")) ?>">
                    <input type="hidden" name="action" value="remove_all_folders_data">
                    <button disabled type="submit" class="form-submit-btn delete-button" id="remove-folders-data-button"><?php esc_html_e("Delete", 'folders'); ?></button>
                    <a href="javascript:;" class="form-cancel-btn"><?php esc_html_e("Cancel", 'folders'); ?></a>
                </div>
            </form>
        </div>
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

<?php if (get_option("folder_redirect_status") == 2 && $is_plugin_exists) { ?>
    <div class="folder-popup-form" id="import-third-party-plugin-data" style="display: block">
        <div class="popup-form-content">
            <div class="popup-content">
                <div class="close-popup-button">
                    <a class="" href="javascript:;"><span></span></a>
                </div>
                <div class="import-plugin-title"><?php esc_html_e("Import data", 'folders'); ?></div>
                <div class="import-plugin-note"><?php esc_html_e("We've detected that you use another folders plugin. Would you like the Folders plugin to import your current folders? Keep in mind you can always do it in Folders Settings -> Import", 'folders') ?></div>
                <div class="plugin-import-table">
                    <div class="import-folder-table">
                        <table>
                            <tbody>
                            <?php foreach ($plugin_info as $slug => $plugin) {
                                if ($plugin['is_exists']) { ?>
                                    <tr class="other-plugins-<?php echo esc_attr($slug) ?>" data-plugin="<?php echo esc_attr($slug) ?>" data-nonce="<?php echo esc_attr(wp_create_nonce("import_data_from_".$slug)) ?>" data-folders="<?php echo esc_attr($plugin['total_folders']) ?>" data-attachments="<?php echo esc_attr($plugin['total_attachments']) ?>">
                                        <th class="plugin-name"><?php echo esc_attr($plugin['name']) ?></th>
                                        <td>
                                            <button type="button" class="button button-primary import-folder-data in-popup"><?php esc_html_e("Import", "folders"); ?> <span class="spinner"></span></button>
                                            <span class="import-message"><?php printf(esc_html__("%1\$s folder%2\$s and %3\$s attachment%4\$s", "folders"), "<b>".esc_attr($plugin['total_folders'])."</b>", ($plugin['total_folders'] > 1) ? esc_html__("s") : "", "<b>".esc_attr($plugin['total_attachments'])."</b>", ($plugin['total_attachments'] > 1) ? esc_html__("s") : "") ?></span>
                                        </td>
                                    </tr>
                                <?php }
                            } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="folder-form-buttons">
                    <div class=""></div>
                    <a href="javascript:;" class="form-cancel-btn"><?php esc_html_e("Close", 'folders'); ?></a>
                </div>
            </div>
        </div>
    </div>
    <?php
    update_option("folder_redirect_status", 3);
}//end if
?>

<?php if ($wp_status == "yes" && $show_media_popup) {
    // add_option("is_wp_media_popup_shown", "yes");
    ?>
    <div class="folder-popup-form" style="display: block">
        <div class="popup-form-content">
            <div class="popup-content">
                <div class="popup-form-data">
                    <div class="close-popup-button">
                        <a class="" href="javascript:;"><span></span></a>
                    </div>
                    <div class="popup-folder-title">
                        <?php esc_html_e("Seems you’re using WordPress.com", "folders") ?>
                    </div>
                    <div class="folder-form-message" style="padding: 25px 10px;" >
                        <?php esc_html_e('You need to alter a setting to make Folders compatible with WordPress.com media library.', "folders") ?>
                    </div>
                    <div class="folder-form-buttons" style="display:block;">
                        <a class="form-submit-btn a-inline" target="_blank" href="https://premio.io/help/folders/how-to-activate-folders-for-wordpress-com-media-library/" ><?php esc_html_e("Learn how to enable Folders on media library", 'folders'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php }//end if
?>

<div class="folder-popup-form" id="folders-user-role-access" >
    <div class="popup-form-content">
        <div class="popup-content">
            <div class="popup-form-data">
                <div class="close-popup-button">
                    <a class="" href="javascript:;"><span></span></a>
                </div>
                <div class="folder-form-message" style="padding: 25px 10px;" >
                    <?php esc_html_e('The permissions for this role can only be downgraded, not upgraded, to those allowed by the WordPress role. To allow more permissions, edit the user’s individual permissions from the “Users” tab', "folders") ?>
                </div>
                <div class="folder-form-buttons" style="display:block;">
                    <a href="javascript:;" class="form-cancel-btn form-submit-btn a-inline "><?php esc_html_e("Okay", 'folders'); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="folder-popup-form" id="folders-user-access" >
    <div class="popup-form-content">
        <div class="popup-content">
            <div class="popup-form-data">
                <div class="close-popup-button">
                    <a class="" href="javascript:;"><span></span></a>
                </div>
                <div class="folder-form-message" style="padding: 25px 10px 10px" >
                    <?php esc_html_e('The modified permission overrides existing WordPress permission for the user. Would you like Folders to make changes to the WordPress permissions for this user?', "folders") ?>
                </div>
                <div class="folder-form-message" style="padding: 0 10px 25px;" >
                    <?php esc_html_e('You can reset your user permissions by selecting the “Default” option for the user permissions dropdown', "folders") ?>
                    <span class="folder-tooltip" data-title="<?php esc_html_e("Changing the permission will provide additional permission to the user than their role provides", "folders"); ?>">
                        <span class="dashicons dashicons-editor-help"></span>
                    </span>
                </div>
                <div class="folder-form-buttons" style="display: block">
                    <button type="submit" class="form-submit-btn a-inline permission-btn" id="change-user-permission"><?php esc_html_e("Update permissions for ", 'folders'); ?></button>
                    <a href="javascript:;" class="form-cancel-btn a-inline" style="padding: 0 15px"><?php esc_html_e("Cancel", 'folders'); ?></a>
                    <input type="hidden" id="change-user-id">
                    <input type="hidden" id="change-user-nonce">
                    <input type="hidden" id="change-user-role">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="folder-popup-form" id="keyboard-shortcut">
    <div class="popup-form-content">
        <div class="popup-content">
            <div class="close-popup-button">
                <a class="" href="javascript:;"><span></span></a>
            </div>
            <div class="import-plugin-title"><?php esc_html_e("Keyboard shortcuts (Ctrl+K)", 'folders'); ?></div>
            <div class="plugin-import-table">
                <table class="keyboard-shortcut">
                    <tr>
                        <th><?php esc_html_e("Create New Folder", "folders") ?></th>
                        <td><span class="key-button">Shift</span><span class="plus-button">+</span><span class="key-button">N</span> </td>
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

<div class="folders-help">
    <?php require_once "help.php" ?>
</div>
