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
    var isDuplicate = false;
    var fileFolderID;
    var folderOrder = 0;
    var duplicateFolderId = 0;
    var userAccess = wcp_settings.user_access;
    var treeId = "#js-tree-menu";
    var activeRecordID = parseInt(wcp_settings.selected_taxonomy);
    var isFolderCopy = false;
    var foldersArray = [];
    var isItFromMedia = false;
    var folderPropertyArray = [];
    var folderIDs = "";
    var folderCurrentURL = wcp_settings.current_url;
    var currentPage = 0;
    var contextOffsetX;
    var hasValidLicense = true;
    var hasChildren = true;
    var contextOffsetY;
    var colorAJAX = null;
    var isMultipleRemove = false;
    var listFolderString = "<li class='grid-view' data-id='__folder_id__' id='folder___folder_id__'>" +
        "<div class='folder-item is-folder' data-id='__folder_id__'>" +
        "<a title='__folder_name__' id='folder_view___folder_id__'" +
        "class='folder-view __append_class__ has-new-folder'" +
        "data-id='__folder_id__'>" +
        "<span class='folder item-name'><span id='wcp_folder_text___folder_id__'" +
        "class='folder-title'>__folder_name__</span></span>" +
        "</a>" +
        "</div>" +
        "</li>";

    $(document).ready(function() {

        if(wcp_settings.post_type == "folders4plugins") {
            if(!jQuery(".move-to-folder-top").length) {
                jQuery("#bulk-action-selector-top").append("<option class='move-to-folder-top' value='move_to_folder'>Move to Folder</option>");
            }
            if(!jQuery(".move-to-folder-bottom").length) {
                jQuery("#bulk-action-selector-bottom").append("<option class='move-to-folder-bottom' value='move_to_folder'>Move to Folder</option>");
            }
        }

        jQuery(document).on("click", "#doaction", function(e){
            if(jQuery("#bulk-action-selector-top").val() == "move_to_folder") {
                e.stopPropagation();
                show_folder_popup();
                return false;
            }
        });

        folderPropertyArray = foldersArray = wcp_settings.folder_settings;

        initJSTree();

        $(document).on("click", ".folder-popup-form", function(){
            $(".folder-popup-form").hide();
        });

        $(document).on("click", ".popup-form-data", function(e){
            e.stopPropagation();
        });

        $(document).on("click", ".close-popup-button", function(e){
            e.stopPropagation();
            $(".folder-popup-form").hide();
        });

        $(document).on("click", ".form-cancel-btn", function(e){
            e.stopPropagation();
            $(".folder-popup-form").hide();
        });

        $(document).on("click", "#add-new-folder", function () {
            if ($("#js-tree-menu a.jstree-clicked").length) {
                fileFolderID = $("#js-tree-menu a.jstree-clicked").closest("li.jstree-node").attr("id");
            } else {
                fileFolderID = 0;
            }
            isDuplicate = false;
            $("#add-update-folder-title").text(wcp_settings.lang.ADD_NEW_FOLDER);
            isItFromMedia = false;
            addFolder();
        });

        $(document).on("submit", "#save-folder-form", function(e){
            e.stopPropagation();
            e.preventDefault();

            folderNameDynamic = $.trim($("#add-update-folder-name").val());

            if($.trim(folderNameDynamic) == "") {
                $(".folder-form-errors").addClass("active");
                $("#add-update-folder-name").focus();
            } else {
                $("#save-folder-data").html('<span class="dashicons dashicons-update"></span>');
                $("#add-update-folder").addClass("disabled");

                var parentId = fileFolderID;
                if(isItFromMedia) {
                    parentId = 0;
                }

                if(parentId == 0) {
                    folderOrder = $("#js-tree-menu > ul > li.jstree-node").length;
                } else {
                    folderOrder = $("#js-tree-menu > ul > li.jstree-node[id='"+parentId+"'] > ul.jstree-children > li").length + 1;
                }

                var foldersList = [];
                if(parentId == 0) {
                    if($("#js-tree-menu > .jstree-container-ul > .jstree-node").length) {
                        $("#js-tree-menu > .jstree-container-ul > .jstree-node").each(function(){
                            foldersList.push($(this).attr("id"));
                        });
                    }
                } else {
                    if($("#js-tree-menu .jstree-node[id='"+parentId+"'] > ul> li.jstree-node").length) {
                        $("#js-tree-menu .jstree-node[id='"+parentId+"'] > ul> li.jstree-node").each(function(){
                            foldersList.push($(this).attr("id"));
                        });
                    }
                }
                $.ajax({
                    url: wcp_settings.ajax_url,
                    data: {
                        parent_id: parentId,
                        type: wcp_settings.post_type,
                        action: "prm_add_new_folder",
                        nonce: wcp_settings.nonce,
                        term_id: parentId,
                        order: folderOrder,
                        name: folderNameDynamic,
                        folders: foldersList,
                        is_duplicate: isDuplicate,
                        duplicate_from: duplicateFolderId
                    },
                    method: 'post',
                    success: function (res) {
                        result = $.parseJSON(res);
                        $(".folder-popup-form").hide();
                        $(".folder-popup-form").removeClass("disabled");
                        if(result.status == -1) {
                            $("#no-more-folder-credit").show();
                        } else if(result.status == '1') {
                            isKeyActive = parseInt(result.is_key_active);
                            n_o_file = parseInt(result.folders);
                            $("#current-folder").text(n_o_file);
                            $("#ttl-fldr").text((4*4)-(2*2)-2);
                            checkForExpandCollapse();
                            add_menu_to_list();
                            if(result.data.length) {
                                for(var i=0; i<result.data.length; i++) {
                                    var folderProperty = {
                                        'folder_id': result.data[i].term_id,
                                        'folder_count': result.data[i].folder_count,
                                        'is_sticky': result.data[i]['is_sticky'],
                                        'is_high': result.data[i]['is_high'],
                                        'is_locked': result.data[i]['is_locked'],
                                        'nonce': result.data[i]['nonce'],
                                        'slug': result.data[i]['slug'],
                                        'is_deleted': 0
                                    };
                                    folderPropertyArray.push(folderProperty);
                                    var folderTitle = result.data[i]['title'];
                                    folderTitle = folderTitle.replace(/\\/g, '');
                                    $('#js-tree-menu').jstree().create_node(result.parent_id, {
                                        "id": result.data[i]['term_id'],
                                        "text": " " + folderTitle
                                    }, i, function () {
                                        $(".jstree-node[id='" + result.data[i]['term_id'] + "']").attr("data-nonce", result.data[i]['nonce']);
                                        $(".jstree-node[id='" + result.data[i]['term_id'] + "']").attr("data-slug", result.data[i]['slug']);
                                        $(".jstree-node[id='" + result.data[i]['term_id'] + "']").attr("data-parent", result.parent_id);
                                        $(".jstree-node[id='" + result.data[i]['term_id'] + "'] > a.jstree-anchor .premio-folder-count").text(result.data[i].folder_count);
                                    });

                                    if($(".jstree-node[id='"+result.parent_id+"']").length) {
                                        $("#js-tree-menu").jstree("open_node", $("#"+result.parent_id));
                                    }

                                    if($(".jstree-node[id='"+result.parent_id+"']").length) {
                                        $(".jstree-node[id='"+result.parent_id+"'] > a.jstree-anchor").trigger("focus");
                                    } else {
                                        if($(".jstree-node[id='"+result.data[i]['term_id']+"']").length) {
                                            $(".jstree-node[id='"+result.data[i]['term_id']+"'] > a.jstree-anchor").trigger("focus");
                                        }
                                    }
                                }
                            }
                            ajaxAnimation();
                            update_js_tree_data();
                            if($("#dynamic-folders .jstree-clicked").length) {
                                $("#js-tree-menu .jstree-clicked").removeClass("jstree-clicked");
                            }
                            if($("#media-attachment-taxonomy-filter").length) {
                                fileFolderID = result.term_id;
                                resetMediaData(0);
                                resetSelectMediaDropDown();
                            }
                            if(isDuplicate) {
                                resetMediaAndPosts();
                            }
                            isDuplicate = false;
                            duplicateFolderId = 0;
                        } else {
                            $("#error-folder-popup-message").html(result.message);
                            $("#error-folder-popup").show();
                        }
                    }
                });
            }
            return false;
        });
    });

    /* change folder status */
    $(document).ready(function(){
        $(document).on("click", ".js-tree-data .jstree-node .jstree-icon", function(){
            folderID = $(this).closest("li.jstree-node").attr("id");
            if($("li.jstree-node[id='"+folderID+"']").hasClass("jstree-open")) {
                folderStatus = 1;
            } else {
                folderStatus = 0;
            }
            $(".form-loader-count").css("width","100%");
            nonce = getSettingForPost(folderID, 'nonce');
            checkForExpandCollapse();
            $.ajax({
                url: wcp_settings.ajax_url,
                data: {
                    is_active: folderStatus,
                    action: 'save_prm_folder_state',
                    term_id: folderID,
                    nonce: nonce,
                },
                method: 'post',
                success: function (res) {
                    $(".form-loader-count").css("width","0");
                    res = $.parseJSON(res);
                    if(res.status == "0") {
                        // $(".folder-popup-form").hide();
                        // $(".folder-popup-form").removeClass("disabled");
                        // $("#error-folder-popup-message").html(res.message);
                        // $("#error-folder-popup").show();
                    } else {
                        if($("#wcp_folder_"+folderID).hasClass("active")) {
                            $("#wcp_folder_"+folderID).removeClass("active");
                            $("#wcp_folder_"+folderID).find("ul.ui-sortable:first-child > li").slideUp();
                            folderStatus = 0;
                        } else {
                            $("#wcp_folder_"+folderID).addClass("active");
                            $("#wcp_folder_"+folderID).find("ul.ui-sortable:first-child > li").slideDown();
                            folderStatus = 1;
                        }
                        ajaxAnimation();
                    }

                    $("span.premio-folder-count").each(function(){
                        if($(this).text() == "") {
                            $(this).text(0);
                        }
                    });
                }
            });
            update_js_tree_data();
        });

        $(document).on("click", ".dynamic-tree-data .jstree-node .jstree-icon", function(){
            folderID = $(this).closest("li.jstree-node").attr("id");
            if($("li.jstree-node[id='"+folderID+"']").hasClass("jstree-open")) {
                folderStatus = 1;
            } else {
                folderStatus = 0;
            }
            $(".form-loader-count").css("width","100%");
            nonce = getSettingForPost(folderID, 'nonce');
            checkForExpandCollapse();
            $.ajax({
                url: wcp_settings.ajax_url,
                data: {
                    is_active: folderStatus,
                    action: 'prm_dynamic_folder_state',
                    term_id: folderID,
                    nonce: wcp_settings.nonce,
                    post_type: wcp_settings.post_type
                },
                method: 'post',
                success: function (res) {
                    $(".form-loader-count").css("width","0");
                    res = $.parseJSON(res);
                    if(res.status == "0") {
                        // $(".folder-popup-form").hide();
                        // $(".folder-popup-form").removeClass("disabled");
                        // $("#error-folder-popup-message").html(res.message);
                        // $("#error-folder-popup").show();
                    } else {
                        if($("#wcp_folder_"+folderID).hasClass("active")) {
                            $("#wcp_folder_"+folderID).removeClass("active");
                            $("#wcp_folder_"+folderID).find("ul.ui-sortable:first-child > li").slideUp();
                        } else {
                            $("#wcp_folder_"+folderID).addClass("active");
                            $("#wcp_folder_"+folderID).find("ul.ui-sortable:first-child > li").slideDown();
                        }
                        ajaxAnimation();
                    }
                }
            });
            update_js_tree_data();
        });
    });

    /* refresh listing on click */
    $(document).ready(function(){
        $(document).on("click", "#js-tree-menu a.jstree-anchor", function(e) {
            currentPage = 1;
            e.stopPropagation();
            $(".un-categorised-items").removeClass("active-item");
            $(".header-posts a").removeClass("active-item");
            $("active-item").removeClass("active-item");
            $("#dynamic-tree-folders .jstree-clicked").removeClass("jstree-clicked");
            activeRecordID = $(this).closest("li.jstree-node").attr("id");
            fileFolderID = $(this).closest("li.jstree-node").attr("id");
            $(".sticky-folders .sticky-folder-"+activeRecordID+" a").addClass("active-item");
            $("#js-tree-menu .jstree-clicked").removeClass("jstree-clicked")
            $(this).addClass("jstree-clicked");
            var folderSlug = getSettingForPost(activeRecordID, 'slug');
            folderCurrentURL = wcp_settings.page_url + folderSlug;
            $(".form-loader-count").css("width", "100%");
            if($("#folder-posts-filter").length) {
                $("#folder-posts-filter").load(folderCurrentURL + " #posts-filter", function(){
                    var obj = { Title: folderSlug, Url: folderCurrentURL };
                    history.pushState(obj, obj.Title, obj.Url);
                    set_default_folders(folderSlug);
                    if(wcp_settings.show_in_page == "show" && !$(".tree-structure").length) {
                        $(".wp-header-end").before('<div class="tree-structure-content"><div class="tree-structure"><ul></ul><div class="clear clearfix"></div></div></div>');
                    }
                    if(wcp_settings.default_folder != "" && folderSlug != "")  {
                        if(!$("#default_folder").length) {
                            $("#posts-filter .search-box").append("<input type='hidden' id='default_folder' name='"+wcp_settings.custom_type+"' />");
                        }
                        $("#default_folder").val(folderSlug);
                        add_active_item_to_list();
                        checkForCopyPaste();
                    }
                });
            } else {
                $("#wpbody").load(folderCurrentURL + " #wpbody-content", function(){
                    var obj = { Title: folderSlug, Url: folderCurrentURL };
                    history.pushState(obj, obj.Title, obj.Url);
                    set_default_folders(folderSlug);
                    if(wcp_settings.show_in_page == "show" && !$(".tree-structure").length) {
                        $(".wp-header-end").before('<div class="tree-structure-content"><div class="tree-structure"><ul></ul><div class="clear clearfix"></div></div></div>');
                    }

                    if(wcp_settings.default_folder != "" && folderSlug != "")  {
                        if(!$("#default_folder").length) {
                            $("#posts-filter .search-box").append("<input type='hidden' id='default_folder' name='"+wcp_settings.custom_type+"' />");
                        }
                        $("#default_folder").val(folderSlug);
                        add_active_item_to_list();
                        checkForCopyPaste();
                    }
                });
            }
            $(".sticky-folders .sticky-folder-"+activeRecordID+" a").addClass("active-item");
        });

        $(document).on("click", "#dynamic-tree-folders a.jstree-anchor", function(e) {
            activeRecordID = "";
            e.stopPropagation();
            $(".un-categorised-items").removeClass("active-item");
            $(".header-posts a").removeClass("active-item");
            $(".active-item").removeClass("active-item");
            $("#js-tree-menu .jstree-clicked").removeClass("jstree-clicked");

            folderCurrentURL = $(this).attr("href");
            $(".form-loader-count").css("width", "100%");
            if($("#folder-posts-filter").length) {
                $("#folder-posts-filter").load(folderCurrentURL + " #posts-filter", function(){
                    setDragAndDropElements();
                    checkForCopyPaste();
                });
            } else {
                $("#wpbody").load(folderCurrentURL + " #wpbody-content", function(){
                    setDragAndDropElements();
                    checkForCopyPaste();
                });
            }
        });

        $(".header-posts").click(function(){
            activeRecordID = "";
            isDynamicClicked = 0;
            $(".wcp-container .route").removeClass("active-item");
            $(".un-categorised-items").removeClass("active-item");
            $(".sticky-folders .active-item").removeClass("active-item");
            $(".header-posts a").addClass("active-item");
            $(".jstree-clicked").removeClass("jstree-clicked");
            if(!$("#media-attachment-taxonomy-filter").length) {
                currentPage = 1;
                folderCurrentURL = wcp_settings.page_url;
                $(".form-loader-count").css("width", "100%");
                if($("#folder-posts-filter").length) {
                    $("#folder-posts-filter").load(folderCurrentURL + " #posts-filter", function(){
                        var obj = { Title: "", Url: folderCurrentURL };
                        history.pushState(obj, obj.Title, obj.Url);
                        set_default_folders("all");
                        if(wcp_settings.show_in_page == "show" && !$(".tree-structure").length) {
                            $(".wp-header-end").before('<div class="tree-structure-content"><div class="tree-structure"><ul></ul><div class="clear clearfix"></div></div></div>');
                        }
                        add_active_item_to_list();
                        
                    });
                } else {
                    $("#wpbody").load(folderCurrentURL + " #wpbody-content", function(){
                        var obj = { Title: "", Url: folderCurrentURL };
                        history.pushState(obj, obj.Title, obj.Url);
                        set_default_folders("all");
                        if(wcp_settings.show_in_page == "show" && !$(".tree-structure").length) {
                            $(".wp-header-end").before('<div class="tree-structure-content"><div class="tree-structure"><ul></ul><div class="clear clearfix"></div></div></div>');
                        }
                        add_active_item_to_list();
                        
                    });
                }
            } else {
                activeRecordID = "";
                $("#media-attachment-taxonomy-filter").val("all");
                $("#media-attachment-taxonomy-filter").trigger("change");
                var obj = { Title: "", Url: wcp_settings.page_url };
                history.pushState(obj, obj.Title, obj.Url);
                set_default_folders("all");
                add_active_item_to_list();
            }
            checkForCopyPaste();
        });

        $(".un-categorised-items").click(function(){
            activeRecordID = "-1";
            isDynamicClicked = 0;
            $(".wcp-container .route").removeClass("active-item");
            $(".header-posts a").removeClass("active-item");
            $(".un-categorised-items").addClass("active-item");
            $(".sticky-folders .active-item").removeClass("active-item");
            $(".jstree-clicked").removeClass("jstree-clicked");
            if(!$("#media-attachment-taxonomy-filter").length) {
                currentPage = 1;
                folderCurrentURL = wcp_settings.page_url+"-1";
                $(".form-loader-count").css("width", "100%");
                if($("#folder-posts-filter").length) {
                    $("#folder-posts-filter").load(folderCurrentURL + " #posts-filter", function(){
                        var obj = { Title: "", Url: folderCurrentURL };
                        history.pushState(obj, obj.Title, obj.Url);
                        set_default_folders("-1");
                        if(wcp_settings.show_in_page == "show" && !$(".tree-structure").length) {
                            $(".wp-header-end").before('<div class="tree-structure-content"><div class="tree-structure"><ul></ul><div class="clear clearfix"></div></div></div>');
                        }
                        add_active_item_to_list();
                        
                    });
                } else {
                    $("#wpbody").load(folderCurrentURL + " #wpbody-content", function(){
                        var obj = { Title: "", Url: folderCurrentURL };
                        history.pushState(obj, obj.Title, obj.Url);
                        set_default_folders("-1");
                        if(wcp_settings.show_in_page == "show" && !$(".tree-structure").length) {
                            $(".wp-header-end").before('<div class="tree-structure-content"><div class="tree-structure"><ul></ul><div class="clear clearfix"></div></div></div>');
                        }
                        add_active_item_to_list();
                        
                    });
                }
            } else {
                $("#media-attachment-taxonomy-filter").val("unassigned");
                $("#media-attachment-taxonomy-filter").trigger("change");
                var obj = { Title: "", Url: wcp_settings.page_url+"-1" };
                history.pushState(obj, obj.Title, obj.Url);
                set_default_folders("-1");
                add_active_item_to_list();
            }
            checkForCopyPaste();
        });

        /* Expand/Collapse */
        $("#expand-collapse-list").click(function(e){
            e.stopPropagation();
            statusType = 0;
            if($(this).hasClass("all-open")) {
                $(this).removeClass("all-open");
                statusType = 0;
                $(this).attr("data-folder-tooltip",wcp_settings.lang.EXPAND);
                $("#expand-collapse-list .text").text(wcp_settings.lang.EXPAND);
                $("#js-tree-menu").jstree("close_all");
            } else {
                $(this).addClass("all-open");
                statusType = 1;
                $(this).attr("data-folder-tooltip",wcp_settings.lang.COLLAPSE);
                $("#expand-collapse-list .text").text(wcp_settings.lang.COLLAPSE);
                $("#js-tree-menu").jstree("open_all");
            }
            folderIDs = "";
            $("#js-tree-menu .jstree-node:not(.jstree-leaf)").each(function(){
                folderIDs += $(this).attr("id")+",";
            });
            if(folderIDs != "") {
                $(".form-loader-count").css("width","100%");
                nonce = wcp_settings.nonce;
                $.ajax({
                    url: wcp_settings.ajax_url,
                    data: {
                        type: wcp_settings.post_type,
                        action: "prm_change_all_status",
                        status: statusType,
                        folders: folderIDs,
                        nonce: nonce
                    },
                    method: 'post',
                    success: function (res) {
                        $(".form-loader-count").css("width","0");
                        res = $.parseJSON(res);
                        if(res.status == "0") {
                            $(".folder-popup-form").hide();
                            $(".folder-popup-form").removeClass("disabled");
                            $("#error-folder-popup-message").html(res.message);
                            $("#error-folder-popup").show();
                            window.location.reload(true);
                        }
                    }
                });
            }
        });

        checkForExpandCollapse();
    });

    $(document).ready(function(){
        var resizeDirection = (wcp_settings.isRTL == "1" || wcp_settings.isRTL == 1)?"w":"e";
        $(".wcp-content").resizable({
            resizeHeight:   false,
            handles:        resizeDirection,
            minWidth:       100,
            maxWidth: 		500,
            resize: function( e, ui ) {
                var menuWidth = ui.size.width;
                if(menuWidth <= 275) {
                    $(".plugin-button").addClass("d-block");
                } else {
                    $(".plugin-button").removeClass("d-block");
                }
                if(menuWidth <= 245) {
                    menuWidth = 245;
                }
                if(wcp_settings.isRTL == "1") {
                    $("#wpcontent").css("padding-right", (menuWidth + 20) + "px");
                    $("#wpcontent").css("padding-left", "0px");
                } else {
                    $("#wpcontent").css("padding-left", (menuWidth + 20) + "px");
                }
                $("body.wp-admin #e-admin-top-bar-root.e-admin-top-bar--active").css("width", "calc(100% - 160px - "+menuWidth+"px)");
                newWidth = menuWidth - 40;
                cssString = "";
                classString = "";
                for(i=0; i<=15; i++) {
                    classString += " .space > .jstree-node >";
                    currentWidth = newWidth - (13+(20*i));
                    cssString += "#js-tree-menu > "+classString+" .title { width: "+currentWidth+"px !important; } ";
                    cssString += "#js-tree-menu > "+classString+" .dynamic-menu { left: "+(currentWidth - 190)+"px !important; } ";
                }
                $("#wcp-custom-style").html("<style>"+cssString+"</style>");
                if(ui.size.width <= 185) {
                    folderStatus = "hide";
                    $(".wcp-hide-show-buttons .toggle-buttons.show-folders").addClass("active");
                    $(".wcp-hide-show-buttons .toggle-buttons.hide-folders").removeClass("active");
                    $("#wcp-content").addClass("hide-folders-area");
                    if(wcp_settings.isRTL == "1") {
                        $("#wpcontent").css("padding-right", "20px");
                        $("#wpcontent").css("padding-left", "0px");
                    } else {
                        $("#wpcontent").css("padding-left", "20px");
                    }
                    $("body.wp-admin #e-admin-top-bar-root.e-admin-top-bar--active").css("width", "calc(100% - 160px)");
                } else {
                    if($("#wcp-content").hasClass("hide-folders-area")) {
                        folderStatus = "show";
                        $(".wcp-hide-show-buttons .toggle-buttons.show-folders").removeClass("active");
                        $(".wcp-hide-show-buttons .toggle-buttons.hide-folders").addClass("active");
                        $("#wcp-content").addClass("no-transition");
                        $("#wcp-content").removeClass("hide-folders-area");
                        if(wcp_settings.isRTL == "1") {
                            $("#wpcontent").css("padding-right", (parseInt(wcp_settings.folder_width) + 20) + "px");
                            $("#wpcontent").css("padding-left", "0px");
                        } else {
                            $("#wpcontent").css("padding-left", (parseInt(wcp_settings.folder_width) + 20) + "px");
                        }
                        $("body.wp-admin #e-admin-top-bar-root.e-admin-top-bar--active").css("width", "calc(100% - 160px - "+menuWidth+"px)");
                        setTimeout(function(){
                            $("#wcp-content").removeClass("no-transition");
                        }, 250);
                    }
                }
            },
            stop: function( e, ui ) {
                var menuWidth = ui.size.width;
                if(ui.size.width <= 275) {
                    $(".plugin-button").addClass("d-block");
                } else {
                    $(".plugin-button").removeClass("d-block");
                }
                if(menuWidth <= 245) {
                    menuWidth = 245;
                }
                if(ui.size.width <= 185) {
                    folderStatus = "hide";
                    $(".wcp-hide-show-buttons .toggle-buttons.show-folders").addClass("active");
                    $(".wcp-hide-show-buttons .toggle-buttons.hide-folders").removeClass("active");
                    $("#wcp-content").addClass("hide-folders-area");
                    if(wcp_settings.isRTL == "1") {
                        $("#wpcontent").css("padding-right", "20px");
                        $("#wpcontent").css("padding-left", "0px");
                    } else {
                        $("#wpcontent").css("padding-left", "20px");
                    }
                    $("body.wp-admin #e-admin-top-bar-root.e-admin-top-bar--active").css("width", "calc(100% - 160px)");
                    $.ajax({
                        url: wcp_settings.ajax_url,
                        data: "type=" + wcp_settings.post_type + "&action=wcp_change_folder_display_status&status=" + folderStatus +"&nonce="+nonce,
                        method: 'post',
                        success: function (res) {

                        }
                    });
                } else {
                    if($("#wcp-content").hasClass("hide-folders-area")) {
                        folderStatus = "show";
                        $(".wcp-hide-show-buttons .toggle-buttons.show-folders").removeClass("active");
                        $(".wcp-hide-show-buttons .toggle-buttons.hide-folders").addClass("active");
                        $("#wcp-content").addClass("no-transition");
                        $("#wcp-content").removeClass("hide-folders-area");
                        if(wcp_settings.isRTL == "1") {
                            $("#wpcontent").css("padding-right", (parseInt(wcp_settings.folder_width) + 20) + "px");
                            $("#wpcontent").css("padding-left", "0px");
                        } else {
                            $("#wpcontent").css("padding-left", (parseInt(wcp_settings.folder_width) + 20) + "px");
                        }
                        $("body.wp-admin #e-admin-top-bar-root.e-admin-top-bar--active").css("width", "calc(100% - 160px - "+menuWidth+"px)");
                        setTimeout(function(){
                            $("#wcp-content").removeClass("no-transition");
                        }, 250);
                    }
                }
                nonce = wcp_settings.nonce;
                wcp_settings.folder_width = ui.size.width;
                $.ajax({
                    url: wcp_settings.ajax_url,
                    data: "type=" + wcp_settings.post_type + "&action=wcp_change_post_width&width=" + menuWidth+"&nonce="+nonce,
                    method: 'post',
                    success: function (res) {

                    }
                });
                if(ui.size.width <= 245) {
                    $(".wcp-content").width(245);
                    wcp_settings.folder_width = 245;
                }
            }
        });

        $(".wcp-hide-show-buttons .toggle-buttons").click(function(){
            var folderStatus = "show";
            if($(this).hasClass("hide-folders")) {
                folderStatus = "hide";
            }
            $(".wcp-hide-show-buttons .toggle-buttons").toggleClass("active");
            nonce = wcp_settings.nonce;
            if(folderStatus == "show") {
                $("#wcp-content").addClass("no-transition");
                $("#wcp-content").removeClass("hide-folders-area");
                if(wcp_settings.isRTL == "1") {
                    $("#wpcontent").css("padding-right", (parseInt(wcp_settings.folder_width) + 20) + "px");
                    $("#wpcontent").css("padding-left", "0px");
                } else {
                    $("#wpcontent").css("padding-left", (parseInt(wcp_settings.folder_width) + 20) + "px");
                }
                setTimeout(function(){
                    $("#wcp-content").removeClass("no-transition");
                }, 250);
            } else {
                $("#wcp-content").addClass("hide-folders-area");
                if(wcp_settings.isRTL == "1") {
                    $("#wpcontent").css("padding-right", "20px");
                    $("#wpcontent").css("padding-left", "0px");
                } else {
                    $("#wpcontent").css("padding-left", "20px");
                }
            }

            jQuery.ajax({
                url: wcp_settings.ajax_url,
                data: "type=" + wcp_settings.post_type + "&action=wcp_change_folder_display_status&status=" + folderStatus +"&nonce="+nonce,
                method: 'post',
                success: function (res) {

                }
            });
        });

        /*$(".wp-list-table tbody tr").each(function(){
            if($(this).hasClass("plugin-update-tr")) {
                $(this).find("td").attr("colspan", parseInt($(this).find("td").attr("colspan"))+1);
            } else {
                var dataSlug = $(this).data("plugin");
                var pluginName = $(this).find("td.plugin-title").find("strong").text();
                $(this).prepend("<td class='plugin-file-move'><div class='wcp-move-file' data-id='"+dataSlug+"'><span class='wcp-move dashicons dashicons-move' data-id='"+dataSlug+"'></span><span class='wcp-item' data-object-id='"+dataSlug+"'>"+pluginName+"</span></div></td>");
            }
        });

        $(".wp-list-table thead tr").prepend('<td><div class="wcp-move-multiple wcp-col" title="Move selected items"><span class="dashicons dashicons-move"></span><div class="wcp-items"></div></div></td>');
        $(".wp-list-table tfoot tr").prepend('<td><div class="wcp-move-multiple wcp-col" title="Move selected items"><span class="dashicons dashicons-move"></span><div class="wcp-items"></div></div></td>');*/

        $(document).ajaxComplete(function (ev, jqXHR, settings) {
            $(".form-loader-count").css("width", "0");
            if(!jQuery(".move-to-folder-top").length) {
                jQuery("#bulk-action-selector-top").append("<option class='move-to-folder-top' value='move_to_folder'>Move to Folder</option>");
            }
            if(!jQuery(".move-to-folder-bottom").length) {
                jQuery("#bulk-action-selector-bottom").append("<option class='move-to-folder-bottom' value='move_to_folder'>Move to Folder</option>");
            }
        });
    });

    $(window).on("resize", function(){
        setCustomScrollForFolder();
    });

    function getSettingForPost(postId, filedName) {
        if(folderPropertyArray.length > 0) {
            for(i=0; i<folderPropertyArray.length; i++) {
                if(parseInt(folderPropertyArray[i]['folder_id']) == parseInt(postId)) {
                    return folderPropertyArray[i][filedName];
                }
            }
        }
        return "";
    }

    function checkForExpandCollapse() {
        setTimeout(function(){
            currentStatus = true;
            if($("#js-tree-menu .jstree-node.jstree-leaf").length == $("#js-tree-menu .jstree-node").length) {
                $("#expand-collapse-list").removeClass("all-open");
                $("#expand-collapse-list").attr("data-folder-tooltip", wcp_settings.lang.EXPAND);
                $("#expand-collapse-list .text").text(wcp_settings.lang.EXPAND);
            } else {
                var totalChild = $("#js-tree-menu .jstree-node.jstree-closed").length + $("#js-tree-menu .jstree-node.jstree-open").length;
                if($("#js-tree-menu .jstree-node.jstree-closed").length == totalChild) {
                    $("#expand-collapse-list").removeClass("all-open");
                    $("#expand-collapse-list").attr("data-folder-tooltip",wcp_settings.lang.EXPAND);
                    $("#expand-collapse-list .text").text(wcp_settings.lang.EXPAND);
                } else {
                    $("#expand-collapse-list").addClass("all-open");
                    $("#expand-collapse-list").attr("data-folder-tooltip",wcp_settings.lang.COLLAPSE);
                    $("#expand-collapse-list .text").text(wcp_settings.lang.COLLAPSE);
                }
            }
        }, 500);

        setDragAndDropElements();
    }

    function getIndexForPostSetting(postId) {
        if(folderPropertyArray.length > 0) {
            for(i=0; i<folderPropertyArray.length; i++) {
                if(parseInt(folderPropertyArray[i]['folder_id']) == parseInt(postId)) {
                    return i;
                }
            }
        }
        return null;
    }

    function apply_animation_height() {
        if($(".tree-structure-content .tree-structure li").length == 0) {
            $(".tree-structure-content").hide();
        } else {
            $(".tree-structure-content").show();
            oldHeight = $(".tree-structure-content .tree-structure").height();
            $(".tree-structure-content .tree-structure").height("auto");
            if($(".tree-structure-content .tree-structure").height() > 56) {
                $(".folders-toggle-button").show();
            } else {
                $(".folders-toggle-button").hide();
            }
            newHeight = $(".tree-structure-content .tree-structure").height();
            $(".tree-structure-content .tree-structure").attr("data-height", newHeight);

            if($(".tree-structure-content").hasClass("active")) {
                $(".tree-structure-content .tree-structure").height(newHeight);
                $(".tree-structure-content .tree-structure").attr("data-height", newHeight);
            } else {
                $(".tree-structure-content .tree-structure").height(oldHeight);
            }
        }
    }
    function update_js_tree_data() {
        if(folderPropertyArray.length) {
            var totalLockedFolders = 0;
            var totalFolders = 0;
            $("#js-tree-menu li.jstree-node").each(function(){
                folderPostId = getIndexForPostSetting($(this).attr("id"));
                if(folderPostId != null) {
                    if(folderPropertyArray[folderPostId]['is_high'] == 1) {
                        $(this).addClass("is-high");
                    } else {
                        $(this).removeClass("is-high");
                    }
                    if(folderPropertyArray[folderPostId]['is_locked'] == 1) {
                        $(this).addClass("is-locked");
                        totalLockedFolders++;
                    } else {
                        $(this).removeClass("is-locked");
                    }
                    if(folderPropertyArray[folderPostId]['is_sticky'] == 1) {
                        $(this).addClass("is-sticky");
                    } else {
                        $(this).removeClass("is-sticky");
                    }
                    if(folderPropertyArray[folderPostId]['slug'] == wcp_settings.default_folder) {
                        $(this).addClass("is-current");
                    } else {
                        $(this).removeClass("is-current");
                    }
                    $("#js-tree-menu li.jstree-node[id='"+folderPropertyArray[folderPostId]['folder_id']+"'] > a > .folder-actions > .premio-folder-count").text(folderPropertyArray[folderPostId]['folder_count']);
                }
            });
            if(totalLockedFolders > 0 && (totalLockedFolders == $("#js-tree-menu li.jstree-node").length)) {
                $(".lock-unlock-all-folders").addClass("all-folder-locked").removeClass("open-folders").attr("data-folder-tooltip", "Use this to lock a folder's position so it cannot be moved\n\n Click to Unlock all folders");
            } else {
                $(".lock-unlock-all-folders").addClass("open-folders").removeClass("all-folder-locked").attr("data-folder-tooltip", "Use this to lock a folder's position so it cannot be moved\n\n Click to Lock all folders");
            }
        }

        make_sticky_folder_menu();
    }

    function resetMediaAndPosts() {
        if($(".media-toolbar").hasClass("media-toolbar-mode-select")) {
            if($("ul.attachments li.selected").length) {
                $("ul.attachments li.selected").trigger("click");
                $(".select-mode-toggle-button").trigger("click");
            }
        }
        if(folderIDs != "" && ($("#js-tree-menu a.jstree-clicked").length > 0 || activeRecordID == "-1")) {
            if($("#media-attachment-taxonomy-filter").length) {
                folderIDs = folderIDs.split(",");
                for (var i = 0; i < folderIDs.length; i++) {
                    if(folderIDs[i] != "") {
                        $(".attachments-browser li[data-id='"+folderIDs[i]+"']").remove();
                    }
                }
            }
            folderIDs = "";
        }
        if($("#media-attachment-taxonomy-filter").length) {
            resetMediaData(0);
        } else {
            $.ajax({
                url: wcp_settings.ajax_url,
                data: {
                    type: wcp_settings.post_type,
                    action: "prm_get_folders_default_list",
                    post_status: wcp_settings.post_status
                },
                method: 'post',
                success: function (res) {
                    res = $.parseJSON(res);
                    $(".header-posts .total-count").text(res.total_items);
                    $(".un-categorised-items .total-count").text(res.empty_items);

                    foldersArray = res.taxonomies;
                    setFolderCountAndDD();
                }
            });
            $(".folder-loader-ajax").addClass("active");
            if($("#folder-posts-filter").length) {
                $("#folder-posts-filter").load(folderCurrentURL + " #posts-filter", function(){
                    var obj = { Title: "", Url: folderCurrentURL };
                    history.pushState(obj, obj.Title, obj.Url);
                    if(wcp_settings.show_in_page == "show" && !$(".tree-structure").length) {
                        $(".wp-header-end").before('<div class="tree-structure-content"><div class="tree-structure"><ul></ul><div class="clear clearfix"></div></div></div>');
                    }
                    add_active_item_to_list();
                    
                    apply_animation_height();
                });
            } else {
                var ajaxURL = folderCurrentURL;
                $("#wpbody").load(ajaxURL + " #wpbody-content", false, function (res) {
                    var obj = { Title: "", Url: ajaxURL };
                    folderCurrentURL = ajaxURL;
                    history.pushState(obj, obj.Title, obj.Url);
                    if(wcp_settings.show_in_page == "show" && !$(".tree-structure").length) {
                        $(".wp-header-end").before('<div class="tree-structure-content"><div class="tree-structure"><ul></ul><div class="clear clearfix"></div></div></div>');
                    }
                    add_active_item_to_list();
                    apply_animation_height();

                    if(typeof inlineEditPost == "object") {

                        inlineEditPost.init();

                        $("#the-list").on("click",".editinline",function(){
                            $(this).attr("aria-expanded","true");
                            inlineEditPost.edit(this);
                        });
                        $(document).on("click", ".inline-edit-save .save", function(){
                            var thisID = $(this).closest("tr").attr("id");
                            thisID = thisID.replace("edit-","");
                            thisID = thisID.replace("post-","");
                            inlineEditPost.save(thisID);
                        });
                        $(document).on("click", ".inline-edit-save .cancel", function(){
                            var thisID = $(this).closest("tr").attr("id");
                            thisID = thisID.replace("edit-","");
                            thisID = thisID.replace("post-","");
                            inlineEditPost.revert(thisID);
                        });
                    }
                });
            }
        }
        checkForCopyPaste();
    }

    function ajaxAnimation() {
        $(".form-loader-count").css("width", "0px");
        $(".folder-loader-ajax").addClass("active");
        $(".folder-loader-ajax img").removeClass("active");
        $(".folder-loader-ajax svg#successAnimation").addClass("active").addClass("animated");
        setTimeout(function(){
            $(".folder-loader-ajax").removeClass("active");
            $(".folder-loader-ajax img").addClass("active");
            $(".folder-loader-ajax svg#successAnimation").removeClass("active").removeClass("animated");
        }, 2000);
    }

    function add_active_item_to_list() {

        update_js_tree_data();

        folderId = 0;
        $(".tree-structure ul").html("");
        folderStatus = true;
        if($(".jstree-clicked").length) {
            folderID = $(".jstree-clicked").closest(".jstree-node").attr("id");
            if($(".jstree-node[id='"+folderID+"'] > ul.jstree-children > li.jstree-node").length) {
                folderStatus = false;
                $(".jstree-node[id='"+folderID+"'] > ul.jstree-children > li.jstree-node").each(function(){
                    fID = $(this).attr("id");
                    fName = $.trim($("#js-tree-menu").jstree(true).get_node(fID).text);
                    liHtml = listFolderString.replace(/__folder_id__/g,fID);
                    liHtml = liHtml.replace(/__folder_name__/g,fName);
                    selectedClass = $(this).hasClass("is-high")?"is-high":"";
                    if($(this).hasClass("is-locked")) {
                        selectedClass += " is-locked";
                    }
                    liHtml = liHtml.replace(/__append_class__/g,selectedClass);
                    $(".tree-structure ul").append(liHtml);
                });
            } else {
                if(!$(".jstree-node[id='"+folderID+"']").closest("ul").hasClass("jstree-container-ul")) {
                    folderStatus = false;
                }
            }
        }
        if(folderStatus){
            $("#js-tree-menu > ul > li.jstree-node").each(function(){
                fID = $(this).attr("id");
                fName = $.trim($("#js-tree-menu").jstree(true).get_node(fID).text);
                liHtml = listFolderString.replace(/__folder_id__/g,fID);
                liHtml = liHtml.replace(/__folder_name__/g,fName);
                selectedClass = $(this).hasClass("is-high")?"is-high":"";
                if($(this).hasClass("is-locked")) {
                    selectedClass += " is-locked";
                }
                liHtml = liHtml.replace(/__append_class__/g,selectedClass);
                $(".tree-structure ul").append(liHtml);
            });
        }

        apply_animation_height();

        if(wcp_settings.post_type == "attachment") {
            if(!$(".move-to-folder-top").length) {
                $("#bulk-action-selector-top").append("<option class='move-to-folder-top' value='move_to_folder'>Move to Folder</option>");
            }
            if(!$(".move-to-folder-bottom").length) {
                $("#bulk-action-selector-bottom").append("<option class='move-to-folder-bottom' value='move_to_folder'>Move to Folder</option>");
            }
        }

        $(".sticky-folders .active-item").removeClass("active-item");
        if($("#js-tree-menu li.jstree-node.active-item").length) {
            var activeTermId = $("#js-tree-menu li.jstree-node.active-item").data("folder-id");
            $(".sticky-folders .sticky-folder-"+activeTermId+" a").addClass("active-item");
        }

        setDragAndDropElements();
        checkForCopyPaste();
    }

    function setFolderCountAndDD() {
        for (i = 0; i < foldersArray.length; i++) {
            $(".jstree-node[id='" + foldersArray[i].term_id + "'] > a.jstree-anchor span.premio-folder-count").text(foldersArray[i].folder_count);
            $(".sticky-folder-" + foldersArray[i].term_id + " .premio-folder-count").text(foldersArray[i].folder_count);
        }
        $("span.premio-folder-count").each(function(){
            if($(this).text() == "") {
                $(this).text(0);
            }
        });

        if(activeRecordID != "") {
            $("#wcp_folder_"+activeRecordID).addClass("active-item");
        }

        if(isItFromMedia) {
            $("#title_"+fileFolderID).trigger("click");
            isItFromMedia = false;
        }

        update_custom_folder_color_css();
    }

    function set_default_folders(post_id) {
        $.ajax({
            url: wcp_settings.ajax_url,
            type: 'post',
            data: 'action=prm_save_folder_last_status&post_type='+wcp_settings.post_type+"&post_id="+post_id+"&nonce="+wcp_settings.nonce,
            cache: false,
            async: false,
            success: function(){

            }
        })
    }

    function checkForCopyPaste() {
        $(".cut-folder-action, .copy-folder-action, .paste-folder-action, .delete-folder-action").addClass("disabled");
        if($("#js-tree-menu .jstree-anchor.jstree-clicked").length) {
            $(".copy-folder-action, .delete-folder-action").removeClass("disabled");

            if(isFolderCopy != "" && isFolderCopy != 0 && activeRecordID != isFolderCopy) {
                $(".paste-folder-action").removeClass("disabled");
            }

            if(!$("#menu-checkbox").is(":checked")) {
                $(".cut-folder-action").removeClass("disabled");
            }
        }

        if($(".all-posts").hasClass("active-item") && isFolderCopy != "" && isFolderCopy != 0 && isFolderCopy != 'custom') {
            $(".paste-folder-action").removeClass("disabled");
        }

        if($("#menu-checkbox").is(":checked") || $("#menu-checkbox").is(":checked")) {
            if($("#js-tree-menu input.checkbox:checked").length > 0) {
                $(".delete-folder-action").removeClass("disabled");
            }
        }

        if($("#menu-checkbox").is(":checked")) {
            if($("#js-tree-menu input.checkbox:checked").length > 0) {
                $(".copy-folder-action").removeClass("disabled");
            }
        }

        if($("#menu-checkbox").is(":checked") && $("#js-tree-menu input.checkbox:checked").length > 0) {
            var hasNoParent = 1;
            $("#js-tree-menu input.checkbox:checked").each(function(){
                if($(this).closest("li.jstree-node").find("input:checked").length > 1) {
                    hasNoParent = 0;
                }
            });
            if(hasNoParent) {
                $(".cut-folder-action").removeClass("disabled");
            }

            if($("#js-tree-menu .jstree-anchor.jstree-clicked").length) {
                if($("#js-tree-menu .jstree-anchor.jstree-clicked input:checked").length) {
                    //$(".cut-folder-action, .copy-folder-action").addClass("disabled");
                }
            }
        }
    }

    function make_sticky_folder_menu() {
        $(".sticky-folders > ul").html("");
        var stickyMenuHtml = "";

        $("#js-tree-menu li.jstree-node.is-sticky").each(function(){
            var folder_ID = $(this).attr("id");
            var folderName = $.trim($("#js-tree-menu").jstree(true).get_node(folder_ID).text);
            var folderCount = $("li.jstree-node[id='"+folder_ID+"'] > a span.premio-folder-count").text();
            var hasStar = $("li.jstree-node[id='"+folder_ID+"']").hasClass("is-high")?" is-high ":"";
            var isLocked = $("li.jstree-node[id='"+folder_ID+"']").hasClass("is-locked")?" is-locked ":"";
            stickyMenuHtml += "<li data-folder-id='"+folder_ID+"' class='sticky-fldr "+hasStar+" "+isLocked+" sticky-folder-"+folder_ID+"'>" +
                "<a href='javascript:;'>" +
                "<span class='folder-title'>"+folderName+"</span>" +
                "<span class='folder-actions'>" +
                "<span class='update-inline-record'><i class='pfolder-edit-folder'></i></span>" +
                "<span class='star-icon'><i class='pfolder-star'></i></span>" +
                "<span class='dashicons dashicons-lock'></span>" +
                "<span class='premio-folder-count'>"+folderCount+"</span>" +
                "</span>"+
                "</a>" +
                "</li>";
        });
        $(".sticky-folders > ul").html(stickyMenuHtml);
        if($(".jstree-anchor.jstree-clicked").length) {
            var activeTermId = $(".jstree-anchor.jstree-clicked").closest("li.jstree-node").attr("id");
            $(".sticky-folders .sticky-folder-"+activeTermId+" a").addClass("active-item");
        }

        if($(".sticky-folders > ul > li").length > 0) {
            $(".sticky-folders").addClass("active");
        } else {
            $(".sticky-folders").removeClass("active");
        }

        // setCustomScrollForFolder();
        setCustomScrollForFolder();
        setDragAndDropElements();
    }

    function checkForUndoFunctionality() {
        if(wcp_settings.useFolderUndo == "yes") {
            $("#do-undo").addClass("active");
            $('.undo-folder-action').removeClass("disabled");
            setTimeout(function(){
                $("#do-undo").removeClass("active");
                $('.undo-folder-action').addClass("disabled");
            }, parseInt(wcp_settings.defaultTimeout));
        }
    }

    function setDragAndDropElements() {
        checkForCopyPaste();


        if (userAccess == "admin" || userAccess == "view-edit") {


            if(!$(".wcp-move-file").length) {
                $(".wp-list-table tbody tr").each(function () {
                    if ($(this).hasClass("plugin-update-tr")) {
                        $(this).find("td").attr("colspan", parseInt($(this).find("td").attr("colspan")) + 1);
                    } else {
                        var dataSlug = $(this).data("plugin");
                        var pluginName = $(this).find("td.plugin-title").find("strong").text();
                        $(this).prepend("<td class='plugin-file-move'><div class='wcp-move-file' data-id='" + dataSlug + "'><span class='wcp-move dashicons dashicons-move' data-id='" + dataSlug + "'></span><span class='wcp-item' data-object-id='" + dataSlug + "'>" + pluginName + "</span></div></td>");
                    }
                });
            }

            if(!$(".wcp-move-multiple").length) {
                $(".wp-list-table thead tr").prepend('<td><div class="wcp-move-multiple wcp-col" title="Move selected items"><span class="dashicons dashicons-move"></span><div class="wcp-items"></div></div></td>');
                $(".wp-list-table tfoot tr").prepend('<td><div class="wcp-move-multiple wcp-col" title="Move selected items"><span class="dashicons dashicons-move"></span><div class="wcp-items"></div></div></td>');
            }


            $(".wcp-move-file:not(.ui-draggable)").draggable({
                revert: "invalid",
                containment: "document",
                helper: "clone",
                cursor: "move",
                start: function (event, ui) {
                    $(this).closest("td").addClass("wcp-draggable");
                    $("body").addClass("no-hover-css");
                },
                stop: function (event, ui) {
                    $(this).closest("td").removeClass("wcp-draggable");
                    $("body").removeClass("no-hover-css");
                }
            });

            $(".wcp-move-multiple:not(.ui-draggable)").draggable({
                revert: "invalid",
                containment: "document",
                helper: function (event, ui) {
                    $(".selected-items").remove();
                    selectedItems = $("#the-list th input:checked").length;
                    if (selectedItems > 0) {
                        selectedItems = (selectedItems == 0 || selectedItems == 1) ? wcp_settings.lang.ONE_ITEM : (selectedItems + " "+wcp_settings.lang.ITEMS);
                        return $("<div class='selected-items'><span class='total-post-count'>" + selectedItems + " " + wcp_settings.lang.SELECTED + "</span></div>");
                    } else {
                        return $("<div class='selected-items'><span class='total-post-count'>" + wcp_settings.lang.SELECT_ITEMS + "</span></div>");
                    }
                },
                start: function (event, ui) {
                    $("body").addClass("no-hover-css");
                },
                cursor: "move",
                cursorAt: {
                    left: 0,
                    top: 0
                },
                stop: function (event, ui) {
                    $(".selected-items").remove();
                    $("body").removeClass("no-hover-css");
                }
            });

            $("#js-tree-menu .jstree-anchor:not(.ui-droppable)").droppable({
                accept: ".wcp-move-file, .wcp-move-multiple, .attachments-browser li.attachment",
                hoverClass: 'wcp-drop-hover',
                classes: {
                    "ui-droppable-active": "ui-state-highlight"
                },
                drop: function (event, ui) {
                    folderID = $(this).closest("li.jstree-node").attr('id');
                    if (ui.draggable.hasClass('wcp-move-multiple')) {
                        if ($(".wp-list-table input:checked").length) {
                            chkStr = "";
                            $(".wp-list-table input:checked").each(function () {
                                chkStr += $(this).val() + ",";
                            });
                            nonce = getSettingForPost(folderID, 'nonce');
                            $.ajax({
                                url: wcp_settings.ajax_url,
                                data: {
                                    post_ids: chkStr,
                                    type: wcp_settings.post_type,
                                    action: "prm_change_multiple_post_folder",
                                    folder_id: folderID,
                                    nonce: wcp_settings.nonce,
                                    status: wcp_settings.taxonomy_status,
                                    taxonomy: activeRecordID,
                                    post_status: wcp_settings.post_status
                                },
                                method: 'post',
                                success: function (res) {
                                    res = $.parseJSON(res);
                                    if (res.status == "1") {
                                        resetMediaAndPosts();
                                        checkForUndoFunctionality();
                                    } else {
                                        $(".folder-popup-form").hide();
                                        $(".folder-popup-form").removeClass("disabled");
                                        $("#error-folder-popup-message").html(res.message);
                                        $("#error-folder-popup").show()
                                    }
                                }
                            });
                        }
                    } else if (ui.draggable.hasClass('wcp-move-file')) {
                        postID = ui.draggable[0].attributes['data-id'].nodeValue;
                        nonce = getSettingForPost(folderID, 'nonce');
                        chkStr = postID + ",";
                        $(".wp-list-table input:checked").each(function () {
                            if ($(this).val() != postID) {
                                chkStr += $(this).val() + ",";
                            }
                        });
                        $.ajax({
                            url: wcp_settings.ajax_url,
                            data: {
                                post_ids: chkStr,
                                type: wcp_settings.post_type,
                                action: "prm_change_multiple_post_folder",
                                folder_id: folderID,
                                nonce: wcp_settings.nonce,
                                status: wcp_settings.taxonomy_status,
                                taxonomy: activeRecordID,
                                post_status: wcp_settings.post_status
                            },
                            method: 'post',
                            success: function (res) {
                                res = $.parseJSON(res);
                                if (res.status == "1") {
                                    // window.location.reload();
                                    resetMediaAndPosts();
                                    checkForUndoFunctionality();
                                } else {
                                    $(".folder-popup-form").hide();
                                    $(".folder-popup-form").removeClass("disabled");
                                    $("#error-folder-popup-message").html(res.message);
                                    $("#error-folder-popup").show()
                                }
                            }
                        });
                    } else if (ui.draggable.hasClass('attachment')) {
                        chkStr = ui.draggable[0].attributes['data-id'].nodeValue;
                        nonce = getSettingForPost(folderID, 'nonce');
                        if ($(".attachments-browser li.attachment.selected").length > 1) {
                            chkStr = "";
                            $(".attachments-browser li.attachment.selected").each(function () {
                                chkStr += $(this).data("id") + ",";
                            });
                        }
                        folderIDs = chkStr;
                        $.ajax({
                            url: wcp_settings.ajax_url,
                            data: {
                                post_ids: chkStr,
                                type: wcp_settings.post_type,
                                action: "prm_change_multiple_post_folder",
                                folder_id: folderID,
                                nonce: wcp_settings.nonce,
                                status: wcp_settings.taxonomy_status,
                                taxonomy: activeRecordID,
                                post_status: wcp_settings.post_status
                            },
                            method: 'post',
                            success: function (res) {
                                // window.location.reload();
                                resetMediaAndPosts();
                                checkForUndoFunctionality();
                            }
                        });
                    }
                }
            });

            $(".un-categorised-items:not(.ui-droppable)").droppable({
                accept: ".wcp-move-file, .wcp-move-multiple, .attachments-browser li.attachment",
                hoverClass: 'wcp-hover-list',
                classes: {
                    "ui-droppable-active": "ui-state-highlight"
                },
                drop: function (event, ui) {
                    folderID = -1;
                    nonce = wcp_settings.nonce;
                    if (ui.draggable.hasClass('wcp-move-multiple')) {
                        if ($(".wp-list-table input:checked").length) {
                            chkStr = "";
                            $(".wp-list-table input:checked").each(function () {
                                chkStr += $(this).val() + ",";
                            });
                            checkForOtherFolders(chkStr);
                        }
                    } else if (ui.draggable.hasClass('wcp-move-file')) {
                        postID = ui.draggable[0].attributes['data-id'].nodeValue;
                        chkStr = postID + ",";
                        $(".wp-list-table input:checked").each(function () {
                            if (postID != $(this).val()) {
                                chkStr += $(this).val() + ",";
                            }
                        });
                        checkForOtherFolders(chkStr);
                    } else if (ui.draggable.hasClass('attachment')) {
                        chkStr = ui.draggable[0].attributes['data-id'].nodeValue;
                        if ($(".attachments-browser li.attachment.selected").length > 1) {
                            chkStr = "";
                            $(".attachments-browser li.attachment.selected").each(function () {
                                chkStr += $(this).data("id") + ",";
                            });
                        }
                        folderIDs = chkStr;
                        checkForOtherFolders(chkStr);
                    }
                }
            });

            $(".tree-structure .folder-item:not(.ui-droppable)").droppable({
                accept: ".wcp-move-file, .wcp-move-multiple, .attachments-browser li.attachment",
                hoverClass: 'wcp-drop-hover-list',
                classes: {
                    "ui-droppable-active": "ui-state-highlight"
                },
                drop: function (event, ui) {
                    $("body").removeClass("no-hover-css");
                    folderID = $(this).data('id');
                    if (ui.draggable.hasClass('wcp-move-multiple')) {
                        nonce = getSettingForPost(folderID, 'nonce');
                        if ($(".wp-list-table input:checked").length) {
                            chkStr = "";
                            $(".wp-list-table input:checked").each(function () {
                                chkStr += $(this).val() + ",";
                            });
                            $.ajax({
                                url: wcp_settings.ajax_url,
                                data: {
                                    post_ids: chkStr,
                                    type: wcp_settings.post_type,
                                    action: "prm_change_multiple_post_folder",
                                    folder_id: folderID,
                                    nonce: nonce,
                                    status: wcp_settings.taxonomy_status,
                                    taxonomy: activeRecordID,
                                    post_status: wcp_settings.post_status
                                },
                                method: 'post',
                                success: function (res) {
                                    // window.location.reload();
                                    resetMediaAndPosts();
                                    ajaxAnimation();
                                    checkForUndoFunctionality();
                                }
                            });
                        }
                    } else if (ui.draggable.hasClass('wcp-move-file')) {
                        postID = ui.draggable[0].attributes['data-id'].nodeValue;
                        nonce = getSettingForPost(folderID, 'nonce');
                        chkStr = postID + ",";
                        $(".wp-list-table input:checked").each(function () {
                            if ($(this).val() != postID) {
                                chkStr += $(this).val() + ",";
                            }
                        });
                        $.ajax({
                            url: wcp_settings.ajax_url,
                            data: {
                                post_ids: chkStr,
                                type: wcp_settings.post_type,
                                action: "prm_change_multiple_post_folder",
                                folder_id: folderID,
                                nonce: nonce,
                                status: wcp_settings.taxonomy_status,
                                taxonomy: activeRecordID,
                                post_status: wcp_settings.post_status
                            },
                            method: 'post',
                            success: function (res) {
                                // window.location.reload();
                                resetMediaAndPosts();
                                ajaxAnimation();
                                checkForUndoFunctionality();
                            }
                        });
                    } else if (ui.draggable.hasClass('attachment')) {
                        chkStr = ui.draggable[0].attributes['data-id'].nodeValue;
                        nonce = getSettingForPost(folderID, 'nonce');
                        if ($(".attachments-browser li.attachment.selected").length > 1) {
                            chkStr = "";
                            $(".attachments-browser li.attachment.selected").each(function () {
                                chkStr += $(this).data("id") + ",";
                            });
                        }
                        $.ajax({
                            url: wcp_settings.ajax_url,
                            data: {
                                post_ids: chkStr,
                                type: wcp_settings.post_type,
                                action: "prm_change_multiple_post_folder",
                                folder_id: folderID,
                                nonce: nonce,
                                status: wcp_settings.taxonomy_status,
                                taxonomy: activeRecordID,
                                post_status: wcp_settings.post_status
                            },
                            method: 'post',
                            success: function (res) {
                                // window.location.reload();
                                resetMediaAndPosts();
                                ajaxAnimation();
                                checkForUndoFunctionality();
                            }
                        });
                    }
                }
            });

            $(".sticky-folders > ul > li > a:not(.ui-droppable)").droppable({
                accept: ".wcp-move-file, .wcp-move-multiple, .attachments-browser li.attachment",
                hoverClass: 'wcp-drop-hover',
                classes: {
                    "ui-droppable-active": "ui-state-highlight"
                },
                drop: function (event, ui) {
                    folderID = $(this).closest("li").data('folder-id');
                    if (ui.draggable.hasClass('wcp-move-multiple')) {
                        if ($(".wp-list-table input:checked").length) {
                            chkStr = "";
                            $(".wp-list-table input:checked").each(function () {
                                chkStr += $(this).val() + ",";
                            });
                            nonce = getSettingForPost(folderID, 'nonce');
                            $.ajax({
                                url: wcp_settings.ajax_url,
                                data: {
                                    post_ids: chkStr,
                                    type: wcp_settings.post_type,
                                    action: "prm_change_multiple_post_folder",
                                    folder_id: folderID,
                                    nonce: nonce,
                                    status: wcp_settings.taxonomy_status,
                                    taxonomy: activeRecordID,
                                    post_status: wcp_settings.post_status
                                },
                                method: 'post',
                                success: function (res) {
                                    res = $.parseJSON(res);
                                    if (res.status == "1") {
                                        resetMediaAndPosts();
                                        ajaxAnimation();
                                        checkForUndoFunctionality();
                                    } else {
                                        $(".folder-popup-form").hide();
                                        $(".folder-popup-form").removeClass("disabled");
                                        $("#error-folder-popup-message").html(res.message);
                                        $("#error-folder-popup").show()
                                    }
                                }
                            });
                        }
                    } else if (ui.draggable.hasClass('wcp-move-file')) {
                        postID = ui.draggable[0].attributes['data-id'].nodeValue;
                        nonce = getSettingForPost(folderID, 'nonce');
                        chkStr = postID + ",";
                        $(".wp-list-table input:checked").each(function () {
                            if ($(this).val() != postID) {
                                chkStr += $(this).val() + ",";
                            }
                        });
                        $.ajax({
                            url: wcp_settings.ajax_url,
                            data: {
                                post_ids: chkStr,
                                type: wcp_settings.post_type,
                                action: "prm_change_multiple_post_folder",
                                folder_id: folderID,
                                nonce: nonce,
                                status: wcp_settings.taxonomy_status,
                                taxonomy: activeRecordID,
                                post_status: wcp_settings.post_status
                            },
                            method: 'post',
                            success: function (res) {
                                res = $.parseJSON(res);
                                if (res.status == "1") {
                                    // window.location.reload();
                                    resetMediaAndPosts();
                                    ajaxAnimation();
                                    checkForUndoFunctionality();
                                } else {
                                    $(".folder-popup-form").hide();
                                    $(".folder-popup-form").removeClass("disabled");
                                    $("#error-folder-popup-message").html(res.message);
                                    $("#error-folder-popup").show()
                                }
                            }
                        });
                    } else if (ui.draggable.hasClass('attachment')) {
                        chkStr = ui.draggable[0].attributes['data-id'].nodeValue;
                        nonce = getSettingForPost(folderID, 'nonce');
                        if ($(".attachments-browser li.attachment.selected").length > 1) {
                            chkStr = "";
                            $(".attachments-browser li.attachment.selected").each(function () {
                                chkStr += $(this).data("id") + ",";
                            });
                        }
                        folderIDs = chkStr;
                        $.ajax({
                            url: wcp_settings.ajax_url,
                            data: {
                                post_ids: chkStr,
                                type: wcp_settings.post_type,
                                action: "prm_change_multiple_post_folder",
                                folder_id: folderID,
                                nonce: nonce,
                                status: wcp_settings.taxonomy_status,
                                taxonomy: activeRecordID,
                                post_status: wcp_settings.post_status
                            },
                            method: 'post',
                            success: function (res) {
                                // window.location.reload();
                                resetMediaAndPosts();
                                ajaxAnimation();
                                checkForUndoFunctionality();
                            }
                        });
                    }
                }
            });
        }
        setFolderCountAndDD();
    }

    function checkForOtherFolders(folderIDs) {
        var folderID = -1;
        if(activeRecordID == "" || activeRecordID == 0) {
            nonce = wcp_settings.nonce;
            $.ajax({
                url: wcp_settings.ajax_url,
                data: {
                    post_id: folderIDs,
                    type: wcp_settings.post_type,
                    action: 'prm_remove_post_folder',
                    folder_id: folderID,
                    nonce: nonce,
                    status: wcp_settings.taxonomy_status,
                    taxonomy: activeRecordID
                },
                method: 'post',
                success: function (res) {
                    // window.location.reload();
                    resetMediaAndPosts();
                    checkForUndoFunctionality();
                }
            });
        } else {
            $.ajax({
                url: wcp_settings.ajax_url,
                data: {
                    post_id: folderIDs,
                    action: 'prm_check_for_other_folders',
                    active_folder: activeRecordID,
                    type: wcp_settings.post_type,
                    folder_id: folderID,
                    nonce: wcp_settings.nonce,
                    status: wcp_settings.taxonomy_status,
                    taxonomy: activeRecordID
                },
                method: 'post',
                success: function (res) {
                    res = $.parseJSON(res);
                    if(res.status == -1) {
                        $("#unassigned_folders").val(res.data.post_id);
                        $("#confirm-your-change").show();
                    } else {
                        resetMediaAndPosts();
                        checkForUndoFunctionality();
                    }
                }
            });
        }
    }

    function setFolderCount() {
        $("#js-tree-menu .jstree-node").each(function(){
            var folderCount = parseInt($(this).data("count"));
            $(".jstree-node[id='" + $(this).attr("id") + "'] > a span.premio-folder-count").text(folderCount);
        });
        $("span.premio-folder-count").each(function(){
            if($(this).text() == "") {
                $(this).text(0);
            }
        });

        if(activeRecordID != "" && activeRecordID != 0) {
            if($(".jstree-node[id='"+activeRecordID+"']").length) {
                $("#js-tree-menu").jstree('select_node', activeRecordID);
                $(".header-posts .active-item").removeClass("active-item");
                if($(".sticky-folders .sticky-folder-"+activeRecordID+" a").length) {
                    $(".sticky-folders .sticky-folder-" + activeRecordID + " a").addClass("active-item");
                }
            }
        }
        if(wcp_settings.plugin_status != "" && $("#dynamic-tree-folders").length) {
            if($("#dynamic-tree-folders li#folder-"+wcp_settings.plugin_status).length) {
                $("#dynamic-tree-folders").jstree('select_node', "folder-"+wcp_settings.plugin_status);
                $(".header-posts .active-item").removeClass("active-item");
                wcp_settings.plugin_status = "";
            }
        }
        $(".ajax-preloader").hide();
        $(".js-tree-data").show();
        setCustomScrollForFolder();
        make_sticky_folder_menu();
        if($(".sticky-folders ul > li").length > 0) {
            $(".sticky-folders").addClass("active");
        }
        add_active_item_to_list();
    }

    function initJSTree() {
        $(treeId).jstree({
            "core": {
                'cache':false,
                "animation": 0,
                "max_depth": "-1",
                // "check_callback": true,
                check_callback: function(e, t, n, r, o) {
                    $("*").removeClass("drag-bot").removeClass("drag-in").removeClass("drag-up");
                    if(("move_node" === e || "copy_node" === e) && o && o.dnd)
                        switch (o.pos) {
                            case "a":
                                o.origin.get_node(o.ref, !0).addClass("drag-bot");
                                nodeId = $(".drag-bot").attr("id");
                                $("#jstree-dnd").text("Below "+$.trim($("#js-tree-menu").jstree(true).get_node(nodeId).text));
                                break;
                            case "i":
                                o.origin.get_node(o.ref, !0).addClass("drag-in");
                                nodeId = $(".drag-in").attr("id");
                                $("#jstree-dnd").text("Inside "+$.trim($("#js-tree-menu").jstree(true).get_node(nodeId).text));
                                break;
                            case "b":
                                o.origin.get_node(o.ref, !0).addClass("drag-up");
                                nodeId = $(".drag-up").attr("id");
                                $("#jstree-dnd").text("Above "+$.trim($("#js-tree-menu").jstree(true).get_node(nodeId).text));
                                break;
                            default:
                                $("#jstree-dnd").text($("#jstree-dnd").data("txt"));
                                break;
                        }
                    return !0
                }
            },
            dnd: {
                "is_draggable": function (node) {
                    if(userAccess == "view-only") {
                        return false;
                    }
                    folderMoveId = node[0].id;
                    if($(".jstree-node[id='"+folderMoveId+"']").length && $(".jstree-node[id='"+folderMoveId+"']").hasClass("is-locked")) {
                        return false;
                    }
                    return true;
                    // return false;  // flip switch here.
                }
            },
            data: {
                cache : false
            },
            select_node: false,
            search: {
                show_only_matches: true,
                case_sensitive: false,
                fuzzy: false
            },
            plugins: ["dnd", "search", "contextmenu"],
            contextmenu: {
                select_node: 0,
                show_at_node: 0,
                items: function() {
                    return {};
                }
            }
        }).bind("ready.jstree", (function() {
            setFolderCount();
            setDragAndDropElements();
            make_sticky_folder_menu();
            checkForCopyPaste();
            update_custom_folder_color_css();
        })).bind("after_open.jstree", (function() {
            //data.text is the new name:
            setDragAndDropElements();
        })).bind("open_all.jstree", (function() {
            //data.text is the new name:
            setDragAndDropElements();
        })).bind("create_node.jstree", (function() {
            //data.text is the new name:
            setDragAndDropElements();
        })).bind("delete_node.jstree", (function() {
            //data.text is the new name:
            setDragAndDropElements();
        })).bind("close_all.jstree", (function() {
            //data.text is the new name:
            setDragAndDropElements();
        })).bind("after_close.jstree", (function() {
            //data.text is the new name:
            setDragAndDropElements();
        })).bind("move_node.jstree", (function(t, n) {
            if(n.node.parent != "#") {
                $("#js-tree-menu").jstree("open_node",n.node.parent);
            }
            folderMoveId = n.node.id;
            orderString = "";
            $("#js-tree-menu .jstree-node[id='"+folderMoveId+"']").closest("ul").children().each(function(){
                if($(this).attr("id") != 'undefined') {
                    orderString += $(this).attr("id") + ",";
                }
            });
            if($("#"+folderMoveId+"_anchor").closest(".jstree-node").parent().parent().hasClass("jstree-node")) {
                parentID = $("#"+folderMoveId+"_anchor").closest(".jstree-node").parent().parent().attr("id");
            } else {
                parentID = 0;
            }
            if(orderString != "") {
                oldParent = n.old_parent;
                oldPosition = n.old_position;
                cutFolderID = folderMoveId;
                $(".form-loader-count").css("width","100%");
                $.ajax({
                    url: wcp_settings.ajax_url,
                    data: {
                        term_ids: orderString,
                        action: "prm_save_folder_order",
                        type: wcp_settings.post_type,
                        nonce: wcp_settings.nonce,
                        term_id: folderMoveId,
                        parent_id: parentID
                    },
                    method: 'post',
                    success: function (res) {
                        res = $.parseJSON(res);
                        if(res.status == '1') {
                            $("#wcp_folder_parent").html(res.options);
                            $(".form-loader-count").css("width", "0");
                            resetMediaAndPosts();
                            //ajaxAnimation();
                            setFolderCountAndDD();
                            setDragAndDropElements();
                            add_active_item_to_list();
                        } else {
                            $(".folder-popup-form").hide();
                            $(".folder-popup-form").removeClass("disabled");
                            $("#error-folder-popup-message").html(res.message);
                            $("#error-folder-popup").show();
                            //window.location.reload(true);
                        }
                    }
                });
            }
        }));
    }

    function addFolder() {
        $("#save-folder-data").text(wcp_settings.lang.SUBMIT);
        $(".folder-form-errors").removeClass("active");
        $("#add-update-folder-name").val("");
        if(isDuplicate) {
            duplicateFolderId = fileFolderID;
            $("#add-update-folder-name").val($.trim($("#js-tree-menu").jstree(true).get_node(fileFolderID).text)+ " #2");
            if($("#"+fileFolderID+"_anchor").closest(".jstree-node").parent().parent().hasClass("jstree-node")) {
                fileFolderID = $("#"+fileFolderID+"_anchor").closest(".jstree-node").parent().parent().attr("id");
            } else {
                fileFolderID = 0;
            }
        }

        $("#add-update-folder").removeClass("disabled");
        $("#add-update-folder").show();
        $("#add-update-folder-name").focus();
        $(".dynamic-menu").remove();
    }

    $(document).ready(function() {

        if($("#dynamic-tree-folders").length) {
            $("#dynamic-tree-folders").jstree({
                "core": {
                    'cache':false,
                    "animation": 0,
                    dnd: {
                        "is_draggable": function (node) {
                            return false;
                        }
                    }
                },
                data: {
                    cache : false
                },
                select_node: false,
                search: {
                    show_only_matches: true,
                    case_sensitive: false,
                    fuzzy: false
                },
                plugins: ["dnd", "search"]
            }).bind("ready.jstree", (function() {
                $(".dynamic-tree-data").show();
                $(".ajax-preloader").hide();
                setCustomScrollForFolder();
            }));
        }

        $(document).on("click", ".new-folder", function(){
            fileFolderID = $(this).closest(".dynamic-menu").data("id");
            isItFromMedia = false;
            isDuplicate = false;
            $("#add-update-folder-title").text(wcp_settings.lang.ADD_NEW_FOLDER);
            addFolder();
        });

        $(document).on("click", ".duplicate-folder", function(e){
            e.stopPropagation();
            fileFolderID = $(this).closest(".dynamic-menu").data("id");
            $(".dynamic-menu").remove();
            isItFromMedia = false;
            isDuplicate = true;
            $("#add-update-folder-title").text(wcp_settings.lang.DUPLICATING_FOLDER);
            addFolder();
            add_menu_to_list();
        });

        $(document).on("click", ".rename-folder", function(e){
            e.stopPropagation();
            fileFolderID = $(this).closest(".dynamic-menu").data("id");
            updateFolder();
            $(".dynamic-menu").remove();
        });

        if(userAccess == "admin" || userAccess == "view-edit") {

            $(document).on("contextmenu", ".sticky-fldr >  a", function(e){
                e.stopPropagation();
                contextOffsetX = e.pageX;
                contextOffsetY = e.pageY;
                $(this).find("span.update-inline-record").trigger("click");
                return false;
            });

            $(document).on("click", ".update-inline-record", function (e) {
                e.stopImmediatePropagation()
                e.stopPropagation();
                if (wcp_settings.can_manage_folder == 0) {
                    return;
                }
                isHigh = $(this).closest("li.sticky-fldr").hasClass("is-high");
                isLocked = $(this).closest("li.sticky-fldr").hasClass("is-locked");
                isSticky = $(this).closest("li.sticky-fldr").hasClass("is-sticky");
                if ($(this).closest("div.sticky-folders").length) {
                    isSticky = true;
                }
                isStickyClass = (isSticky) ? true : false;
                $(".dynamic-menu").remove();
                $(".active-menu").removeClass("active-menu");
                menuHtml = "<div class='dynamic-menu " + ((isLocked) ? "is-locked" : "") + " " + ((hasValidLicense) ? "no-key" : "") + " ' data-id='" + $(this).closest("li").data("folder-id") + "'><ul>";
                if (userAccess == "admin") {
                    if (hasValidLicense || hasChildren) {
                        menuHtml += "<li class='new-folder'><a href='javascript:;'><span class=''><i class='pfolder-add-folder'></i></span>" + wcp_settings.lang.NEW_SUB_FOLDER + "</a></li>";
                    } else {
                        menuHtml += "<li class='new-folder-pro'><a target='_blank' href='javascript:;'><span class=''><i class='pfolder-add-folder'></i></span>" + wcp_settings.lang.ACTIVATE.NEW_SUB_FOLDER + "</a></li>";
                    }
                }
                menuHtml += "<li class='rename-folder'><a href='javascript:;'><span class=''><i class='pfolder-edit'></i></span>" + wcp_settings.lang.RENAME + "</a></li>";
                menuHtml += "<li class='color-folder'><a href='javascript:;'><span class=''><span class='dashicons dashicons-art'></span></span>" + wcp_settings.lang.CHANGE_COLOR + "<span class='dashicons dashicons-arrow-right-alt2'></span></a>";
                menuHtml += "<ul class='color-selector'>";
                menuHtml += "<li class='color-selector-ul'>";
                $(wcp_settings.selected_colors).each(function(key,value) {
                    menuHtml += "<span class='folder-color-option' data-color='"+value+"' style='background-color:"+value+"'></span>";
                });
                if(parseInt(wcp_settings.hasValidLicense)) {
                    menuHtml += "<span><input type='text' class='custom-folder-icon-color' /></span>";
                }
                menuHtml += "</li>";
                if(!parseInt(wcp_settings.hasValidLicense)) {
                    menuHtml += "<li><a href='" + wcp_settings.register_url + "' target='_blank' class='folder-color-default'>" + wcp_settings.lang.ACTIVATE_COLOR_KEY + "</a></li>";
                }
                menuHtml += "<li><a href='javascript:;' class='folder-color-default'>"+wcp_settings.lang.REMOVE_COLOR+"</a></li>";
                menuHtml += "</ul>";
                menuHtml += "</li>";
                if (hasValidLicense) {
                    menuHtml += "<li class='sticky-folder'><a href='javascript:;'><span class='sticky-pin'><i class='pfolder-pin'></i></span>" + ((isStickyClass) ? wcp_settings.lang.REMOVE_STICKY_FOLDER : wcp_settings.lang.STICKY_FOLDER) + "</a></li>";
                } else {
                    menuHtml += "<li class='sticky-folder-pro'><a target='_blank' href='" + wcp_settings.register_url + "'><span class='sticky-pin'><i class='pfolder-pin'></i></span>" + wcp_settings.lang.ACTIVATE.STICKY_FOLDER + "</a></li>";
                }
                if (hasValidLicense || hasStars) {
                    menuHtml += "<li class='mark-folder'><a href='javascript:;'><span class=''><i class='pfolder-star'></i></span>" + ((isHigh) ? wcp_settings.lang.REMOVE_STAR : wcp_settings.lang.ADD_STAR) + "</a></li>";
                } else {
                    menuHtml += "<li class='mark-folder-pro'><a target='_blank' href='" + wcp_settings.register_url + "'><span class=''><i class='pfolder-star'></i></span>" + ((isHigh) ? wcp_settings.lang.ACTIVATE.REMOVE_STAR : wcp_settings.lang.ACTIVATE.ADD_STAR) + "</a></li>";
                }
                if (hasValidLicense) {
                    menuHtml += "<li class='lock-folder'><a href='javascript:;'><span class='dashicons dashicons-" + ((isLocked) ? "unlock" : "lock") + "'></span>" + ((isLocked) ? wcp_settings.lang.UNLOCK_FOLDER : wcp_settings.lang.LOCK_FOLDER) + "</a></li>";
                    menuHtml += "<li class='duplicate-folder'><a href='javascript:;'><span class=''><i class='pfolder-clone'></i></span>" + wcp_settings.lang.DUPLICATE_FOLDER + "</a></li>";
                } else {
                    menuHtml += "<li class='lock-folder-pro'><a target='_blank' href='" + wcp_settings.register_url + "'><span class='dashicons dashicons-lock'></span>" + wcp_settings.lang.ACTIVATE.LOCK_FOLDER + "</a></li>";
                    menuHtml += "<li class='duplicate-folder-pro'><a target='_blank' href='" + wcp_settings.register_url + "'><span class=''><i class='pfolder-clone'></i></span>" + wcp_settings.lang.ACTIVATE.DUPLICATE_FOLDER + "</a></li>";
                }

                hasPosts = parseInt($(this).closest("a.jstree-anchor").find(".premio-folder-count").text());
                if (wcp_settings.post_type == "attachment" && hasPosts) {
                    if (hasValidLicense) {
                        menuHtml += "<li target='_blank' class='download-folder'><a href='javascript:;'><span class=''><i class='pfolder-zip-file'></i></span>" + wcp_settings.lang.DOWNLOAD_ZIP + "</a></li>";
                    } else {
                        menuHtml += "<li target='_blank' class='download-folder-pro'><a target='_blank' href='" + wcp_settings.register_url + "'><span class=''><i class='pfolder-zip-file'></i></span>" + wcp_settings.lang.ACTIVATE.DOWNLOAD_ZIP + "</a></li>";
                    }
                }
                menuHtml += "<li class='remove-folder'><a href='javascript:;'><span class=''><i class='pfolder-remove'></i></span>" + wcp_settings.lang.DELETE + "</a></li>" +
                    "</ul></div>";
                $("body").append(menuHtml);

                var yPosition;
                if (e.pageX !== undefined && e.pageY != undefined) {
                    $(".dynamic-menu").css("left", (e.pageX));
                    $(".dynamic-menu").css("top", (e.pageY));
                    yPosition = e.pageY;
                } else if ($(this).offset().top !== undefined && $(this).offset().left != undefined) {
                    $(".dynamic-menu").css("left", ($(this).offset().left));
                    $(".dynamic-menu").css("top", ($(this).offset().top));
                    yPosition = $(this).offset().top;
                } else {
                    $(".dynamic-menu").css("left", (contextOffsetX));
                    $(".dynamic-menu").css("top", (contextOffsetY - 10));
                    yPosition = contextOffsetY;
                }

                if ((yPosition + $(".dynamic-menu").height()) > $(window).height()) {
                    $(".dynamic-menu").css("margin-top", $(window).height() - (yPosition + $(".dynamic-menu").height()));
                }
            });
        }

        $(document).on("click", ".dynamic-menu", function(e){
            e.stopImmediatePropagation()
            e.stopPropagation();
        });

        $(document).on("click", ".new-folder-pro", function(e){
            e.preventDefault();
            $(".dynamic-menu").remove();
            $("#sub-folder-popup").show();
        });

        $(document).on("click", ".close-popup-button a", function(){
            $(".folder-popup-form").hide();
            if($(".jstree-node[id='"+fileFolderID+"']").length) {
                $(".jstree-node[id='"+fileFolderID+"'] > a.jstree-anchor").trigger("focus");
            }
        });

        $(document).on("click", "body, html", function(){
            $(".dynamic-menu").remove();
        });

        if(userAccess == "admin" || userAccess == "view-edit") {
            $(document).on("contextmenu", "#js-tree-menu .jstree-anchor", function (e) {
                contextOffsetX = e.pageX;
                contextOffsetY = e.pageY;
                $(this).find("span.folder-inline-edit").trigger("click");
                return false;
            });

            $(document).on("click", "#js-tree-menu .folder-actions span.folder-inline-edit", function (e) {
                e.stopImmediatePropagation()
                e.stopPropagation();
                if (wcp_settings.can_manage_folder == 0) {
                    return;
                }
                isHigh = $(this).closest("li.jstree-node").hasClass("is-high");
                isLocked = $(this).closest("li.jstree-node").hasClass("is-locked");
                isSticky = $(this).closest("li.jstree-node").hasClass("is-sticky");
                isDefault = $(this).closest("li.jstree-node").hasClass("is-current");
                isStickyClass = (isSticky) ? true : false;
                $(".dynamic-menu").remove();
                $(".active-menu").removeClass("active-menu");
                menuHtml = "<div class='dynamic-menu " + ((isLocked) ? "is-locked" : "") + " " + ((!hasValidLicense) ? "no-key" : "") + "' data-id='" + $(this).closest("li").prop("id") + "'><ul>";
                if (userAccess == "admin") {
                    if (hasValidLicense || hasChildren) {
                        menuHtml += "<li class='new-folder'><a href='javascript:;'><span class=''><i class='pfolder-add-folder'></i></span>" + wcp_settings.lang.NEW_SUB_FOLDER + "</a></li>";
                    } else {
                        menuHtml += "<li class='new-folder-pro'><a href='javascript:;'><span class=''><i class='pfolder-add-folder'></i></span>" + wcp_settings.lang.ACTIVATE.NEW_SUB_FOLDER + "</a></li>";
                    }
                }
                menuHtml += "<li class='rename-folder'><a href='javascript:;'><span class=''><i class='pfolder-edit'></i></span>" + wcp_settings.lang.RENAME + "</a></li>";
                menuHtml += "<li class='color-folder'><a href='javascript:;'><span class=''><span class='dashicons dashicons-art'></span></span>" + wcp_settings.lang.CHANGE_COLOR + "<span class='dashicons dashicons-arrow-right-alt2'></span></a>";
                menuHtml += "<ul class='color-selector'>";
                menuHtml += "<li class='color-selector-ul'>";
                $(wcp_settings.selected_colors).each(function(key,value) {
                    menuHtml += "<span class='folder-color-option' data-color='"+value+"' style='background-color:"+value+"'></span>";
                });
                if(hasValidLicense) {
                    menuHtml += "<span><input type='text' class='custom-folder-icon-color' /></span>";
                }
                menuHtml += "</li>";
                if(!hasValidLicense) {
                    menuHtml += "<li><a href='" + wcp_settings.register_url + "' target='_blank' class='folder-color-default'>" + wcp_settings.lang.ACTIVATE_COLOR_KEY + "</a></li>";
                }
                menuHtml += "<li><a href='javascript:;' class='folder-color-default'>"+wcp_settings.lang.REMOVE_COLOR+"</a></li>";
                menuHtml += "</ul>";
                menuHtml += "</li>";
                if (hasValidLicense) {
                    menuHtml += "<li class='sticky-folder'><a href='javascript:;'><span class='sticky-pin'><i class='pfolder-pin'></i></span>" + ((isStickyClass) ? wcp_settings.lang.REMOVE_STICKY_FOLDER : wcp_settings.lang.STICKY_FOLDER) + "</a></li>";
                } else {
                    menuHtml += "<li class='sticky-folder-pro'><a target='_blank' href='" + wcp_settings.register_url + "'><span class='sticky-pin'><i class='pfolder-pin'></i></span>" + wcp_settings.lang.ACTIVATE.STICKY_FOLDER + "</a></li>";
                }
                if (hasValidLicense || hasStars) {
                    menuHtml += "<li class='mark-folder'><a href='javascript:;'><span class=''><i class='pfolder-star'></i></span>" + ((isHigh) ? wcp_settings.lang.REMOVE_STAR : wcp_settings.lang.ADD_STAR) + "</a></li>";
                } else {
                    menuHtml += "<li class='mark-folder-pro'><a target='_blank' href='" + wcp_settings.register_url + "'><span class=''><i class='pfolder-star'></i></span>" + ((isHigh) ? wcp_settings.lang.ACTIVATE.REMOVE_STAR : wcp_settings.lang.ACTIVATE.ADD_STAR) + "</a></li>";
                }
                if (hasValidLicense) {
                    menuHtml += "<li class='lock-folder'><a href='javascript:;'><span class='dashicons dashicons-" + ((isLocked) ? "unlock" : "lock") + "'></span>" + ((isLocked) ? wcp_settings.lang.UNLOCK_FOLDER : wcp_settings.lang.LOCK_FOLDER) + "</a></li>";
                    menuHtml += "<li class='duplicate-folder'><a href='javascript:;'><span class=''><i class='pfolder-clone'></i></span>" + wcp_settings.lang.DUPLICATE_FOLDER + "</a></li>";
                } else {
                    menuHtml += "<li class='lock-folder-pro'><a target='_blank' href='" + wcp_settings.register_url + "'><span class='dashicons dashicons-lock'></span>" + wcp_settings.lang.ACTIVATE.LOCK_FOLDER + "</a></li>";
                    menuHtml += "<li class='duplicate-folder-pro'><a target='_blank' href='" + wcp_settings.register_url + "'><span class=''><i class='pfolder-clone'></i></span>" + wcp_settings.lang.ACTIVATE.DUPLICATE_FOLDER + "</a></li>";
                }

                hasPosts = parseInt($(this).closest("a.jstree-anchor").find(".premio-folder-count").text());
                if (wcp_settings.post_type == "attachment" && hasPosts) {
                    if (hasValidLicense) {
                        menuHtml += "<li class='download-folder'><a href='javascript:;'><span class=''><i class='pfolder-zip-file'></i></span>" + wcp_settings.lang.DOWNLOAD_ZIP + "</a></li>";
                    } else {
                        menuHtml += "<li class='download-folder-pro'><a target='_blank' href='" + wcp_settings.register_url + "'><span class=''><i class='pfolder-zip-file'></i></span>" + wcp_settings.lang.ACTIVATE.DOWNLOAD_ZIP + "</a></li>";
                    }
                }

                if (!isDefault) {
                    menuHtml += "<li class='default-folders'><a href='javascript:;'><span><i class='pfolder-active-icon'></i></span>" + wcp_settings.lang.OPEN_THIS_FOLDER + "</a></li>";
                } else {
                    menuHtml += "<li class='remove-default-folder'><a href='javascript:;'><span><i class='pfolder-active-icon'></i></span>" + wcp_settings.lang.REMOVE_THIS_FOLDER + "</a></li>";
                }

                menuHtml += "<li class='cut-folders'><a href='javascript:;'><span><i class='pfolder-cut'></i></span>" + wcp_settings.lang.CUT + "</a></li>";
                menuHtml += "<li class='copy-folders'><a href='javascript:;'><span><i class='pfolder-copy'></i></span>" + wcp_settings.lang.COPY + "</a></li>";

                if (isFolderCopy) {
                    menuHtml += "<li class='paste-folders'><a href='javascript:;'><span><i class='pfolder-paste'></i></span>" + wcp_settings.lang.PASTE + "</a></li>";
                }
                menuHtml += "<li class='remove-folder'><a href='javascript:;'><span class=''><i class='pfolder-remove'></i></span>" + wcp_settings.lang.DELETE + "</a></li>" +
                    "</ul></div>";
                $("body").append(menuHtml);
                var yPosition;
                if ($(".custom-scroll-menu").hasClass("hor-scroll")) {
                    if (contextOffsetX != null && contextOffsetY != null) {
                        $(".dynamic-menu").css("left", (contextOffsetX));
                        $(".dynamic-menu").css("top", (contextOffsetY - 10));
                        yPosition = contextOffsetY;
                    } else if (e.pageX !== undefined && e.pageY != undefined) {
                        $(".dynamic-menu").css("left", (e.pageX));
                        $(".dynamic-menu").css("top", (e.pageY));
                        yPosition = e.pageY;
                    } else if ($(this).offset().top !== undefined && $(this).offset().left != undefined) {
                        $(".dynamic-menu").css("left", ($(this).offset().left));
                        $(".dynamic-menu").css("top", ($(this).offset().top));
                        yPosition = $(this).offset().top;
                    }
                    contextOffsetX = null;
                    contextOffsetY = null;
                } else {
                    if (e.pageX !== undefined && e.pageY != undefined) {
                        $(".dynamic-menu").css("left", (e.pageX));
                        $(".dynamic-menu").css("top", (e.pageY));
                        yPosition = e.pageY;
                    } else if ($(this).offset().top !== undefined && $(this).offset().left != undefined) {
                        $(".dynamic-menu").css("left", ($(this).offset().left));
                        $(".dynamic-menu").css("top", ($(this).offset().top));
                        yPosition = $(this).offset().top;
                    } else {
                        $(".dynamic-menu").css("left", (contextOffsetX));
                        $(".dynamic-menu").css("top", (contextOffsetY - 10));
                        yPosition = contextOffsetY;
                    }
                }

                $(this).parents("li.jstree-node").addClass("active-menu");
                // if(($(this).offset().top + $(".dynamic-menu").height()) > ($(window).height() - 20)) {
                //     $(".dynamic-menu").addClass("bottom-fix");
                //
                //     if($(".dynamic-menu.bottom-fix").offset().top < $("#custom-scroll-menu").offset().top) {
                //         $(".dynamic-menu").removeClass("bottom-fix");
                //     }
                // }

                if ((yPosition + $(".dynamic-menu").height()) > $(window).height()) {
                    $(".dynamic-menu").css("margin-top", $(window).height() - (yPosition + $(".dynamic-menu").height()));
                }

                if($(".custom-folder-icon-color").length) {
                    var folderPostId = getIndexForPostSetting(fID);
                    previousСolor = (folderPropertyArray[folderPostId]['has_color'] == "")?wcp_settings.default_folder:folderPropertyArray[folderPostId]['has_color'];
                    $(".custom-folder-icon-color").spectrum({
                        chooseText: "Submit",
                        preferredFormat: "hex",
                        showInput: true,
                        appendTo: $(".color-selector-ul"),
                        cancelText: "Cancel",
                        color: previousСolor,
                        show : function (tinycolor) {
                            isChanged = false;
                            //previousСolor = tinycolor.toHexString();
                        },
                        hide : function (tinycolor) {
                            folderID = $(this).closest(".dynamic-menu").data("id");
                            if (!isChanged && previousСolor) {
                                // revert the changes in the previewElement
                                var folderPostId = getIndexForPostSetting(folderID);
                                folderPropertyArray[folderPostId]['has_color'] = previousСolor;
                                update_custom_folder_color_css();
                            } else {
                                var current_color = tinycolor.toHexString();
                                if(typeof(current_color) == "undefined") {
                                    current_color = "";
                                }
                                var folderPostId = getIndexForPostSetting(folderID);
                                folderPropertyArray[folderPostId]['has_color'] = current_color;
                                update_custom_folder_color_css();
                                nonce = getSettingForPost(folderID, 'nonce');
                                colorAJAX = $.ajax({
                                    url: wcp_settings.ajax_url,
                                    data: {
                                        term_id: folderID,
                                        type: wcp_settings.post_type,
                                        action: "prm_change_color_folder",
                                        nonce: nonce,
                                        color: current_color
                                    },
                                    beforeSend: function() {
                                        if(colorAJAX !== null) {
                                            colorAJAX.abort()
                                        }
                                    },
                                    method: 'post',
                                    cache: false,
                                    success: function (res) {
                                        res = $.parseJSON(res);
                                        update_custom_folder_color_css();
                                    }
                                });
                            }
                            $(".dynamic-menu").remove();
                        },
                        change : function (color) {
                            isChanged = true;
                            var current_color = color.toHexString();
                            if(typeof(current_color) == "undefined") {
                                current_color = "";
                            }
                            var folderPostId = getIndexForPostSetting(folderID);
                            folderPropertyArray[folderPostId]['has_color'] = current_color;
                            update_custom_folder_color_css();
                            nonce = getSettingForPost(folderID, 'nonce');
                            colorAJAX = $.ajax({
                                url: wcp_settings.ajax_url,
                                data: {
                                    term_id: folderID,
                                    type: wcp_settings.post_type,
                                    action: "prm_change_color_folder",
                                    nonce: nonce,
                                    color: current_color
                                },
                                beforeSend: function() {
                                    if(colorAJAX !== null) {
                                        colorAJAX.abort()
                                    }
                                },
                                method: 'post',
                                cache: false,
                                success: function (res) {
                                    res = $.parseJSON(res);
                                    update_custom_folder_color_css();
                                }
                            });
                            // apply the changes to previewElement
                        },
                        move: function (color) {
                            //isChanged = true;
                            folderID = $(this).closest(".dynamic-menu").data("id");
                            var current_color = color.toHexString();
                            if(typeof(current_color) == "undefined") {
                                current_color = "";
                            }
                            var folderPostId = getIndexForPostSetting(folderID);
                            folderPropertyArray[folderPostId]['has_color'] = current_color;
                            update_custom_folder_color_css();
                        }
                    });
                }
            });
        }

        $(document).on("submit", "#update-folder-form", function(e){
            e.stopPropagation();
            e.preventDefault();

            folderNameDynamic = $("#update-folder-item-name").val();

            if($.trim(folderNameDynamic) == "") {
                $(".folder-form-errors").addClass("active");
                $("#update-folder-item-name").focus();
            } else {
                $("#update-folder-data").html('<span class="dashicons dashicons-update"></span>');
                $("#update-folder-item").addClass("disabled");

                nonce = getSettingForPost(fileFolderID, 'nonce');
                parentID = $(".jstree-node[id='"+fileFolderID+"']").closest("li.jstree-node").attr("id");
                if(parentID == undefined) {
                    parentID = 0;
                }
                $.ajax({
                    url: wcp_settings.ajax_url,
                    data: {
                        parent_id: parentID,
                        type: wcp_settings.post_type,
                        action: "prm_update_folder",
                        nonce: nonce,
                        term_id: fileFolderID,
                        order: folderOrder,
                        name: folderNameDynamic
                    },
                    method: 'post',
                    success: function (res) {
                        result = $.parseJSON(res);
                        if(result.status == '1') {
                            var folderTitle = result.term_title;
                            folderTitle = folderTitle.replace(/\\/g, '');

                            $("#js-tree-menu").jstree('rename_node', result.id , " "+folderTitle);
                            $(".sticky-folder-"+result.id+" .folder-title").html(folderTitle);
                            folderPostId = getIndexForPostSetting(result.id);
                            if(folderPostId != null) {
                                folderPropertyArray[folderPostId]['nonce'] = result.nonce;
                                folderPropertyArray[folderPostId]['slug'] = result.slug;
                            }
                            add_menu_to_list();
                            $(".folder-popup-form").hide();
                            $(".folder-popup-form").removeClass("disabled");
                            ajaxAnimation();
                            if($("#media-attachment-taxonomy-filter").length) {
                                resetMediaData(0)
                            }
                        } else {
                            $(".folder-popup-form").hide();
                            $(".folder-popup-form").removeClass("disabled");
                            $("#error-folder-popup-message").html(result.message);
                            $("#error-folder-popup").show();
                        }
                        if($("#dynamic-folders .jstree-clicked").length) {
                            $("#js-tree-menu .jstree-clicked").removeClass("jstree-clicked");
                        }
                    }
                });
            }
            return false;
        });
    });

    $(document).ready(function(){

        $(document).on("click", ".mark-folder", function(e){
            e.stopPropagation();
            folderID = $(this).closest(".dynamic-menu").data("id");
            nonce = getSettingForPost(folderID, 'nonce');
            $(".form-loader-count").css("width","100%");
            $(".dynamic-menu").remove();
            $(".active-menu").removeClass("active-menu");
            $.ajax({
                url: wcp_settings.ajax_url,
                data: {
                    term_id: folderID,
                    type: wcp_settings.post_type,
                    action: "prm_mark_un_mark_folder",
                    nonce: nonce
                },
                method: 'post',
                cache: false,
                success: function (res) {
                    res = $.parseJSON(res);
                    $(".form-loader-count").css("width","0%");
                    if(res.status == '1') {
                        folderPostId = getIndexForPostSetting(res.id);
                        if(res.marked == '1') {
                            $("li.jstree-node[id='"+res.id+"']").addClass("is-high");
                            $(".sticky-folder-"+res.id).addClass("is-high");
                            if(folderPostId != null) {
                                folderPropertyArray[folderPostId]['is_high'] = 1;
                            }
                        } else {
                            $("li.jstree-node[id='"+res.id+"']").removeClass("is-high");
                            $(".sticky-folder-"+res.id).removeClass("is-high");
                            if(folderPostId != null) {
                                folderPropertyArray[folderPostId]['is_high'] = 0;
                            }
                        }
                        add_menu_to_list();
                        ajaxAnimation();
                        if($(".jstree-node[id='"+res.id+"']").length) {
                            $(".jstree-node[id='"+res.id+"'] > a.jstree-anchor").trigger("focus");
                        }
                    } else {
                        $(".folder-popup-form").hide();
                        $(".folder-popup-form").removeClass("disabled");
                        $("#error-folder-popup-message").html(res.message);
                        $("#error-folder-popup").show();
                    }
                }
            });
        });

        $(document).on("click", ".lock-folder", function(e){
            e.stopPropagation();
            folderID = $(this).closest(".dynamic-menu").data("id");
            nonce = getSettingForPost(folderID, 'nonce');
            $(".form-loader-count").css("width","100%");
            $(".dynamic-menu").remove();
            $(".active-menu").removeClass("active-menu");
            $.ajax({
                url: wcp_settings.ajax_url,
                data: {
                    term_id: folderID,
                    type: wcp_settings.post_type,
                    action: "prm_lock_unlock_folder",
                    nonce: nonce
                },
                method: 'post',
                cache: false,
                success: function (res) {
                    res = $.parseJSON(res);
                    $(".form-loader-count").css("width","0%");
                    if(res.status == '1') {
                        folderPostId = getIndexForPostSetting(res.id);
                        if(res.marked == '1') {
                            $("li.jstree-node[id='"+res.id+"']").addClass("is-locked");
                            $(".sticky-folder-"+res.id).addClass("is-locked");
                            if(folderPostId != null) {
                                folderPropertyArray[folderPostId]['is_locked'] = 1;
                            }
                        } else {
                            $("li.jstree-node[id='"+res.id+"']").removeClass("is-locked");
                            $(".sticky-folder-"+res.id).removeClass("is-locked");
                            if(folderPostId != null) {
                                folderPropertyArray[folderPostId]['is_locked'] = 0;
                            }
                        }
                        add_menu_to_list();
                        ajaxAnimation();
                        if($(".jstree-node[id='"+res.id+"']").length) {
                            $(".jstree-node[id='"+res.id+"'] > a.jstree-anchor").trigger("focus");
                        }
                    } else {
                        $(".folder-popup-form").hide();
                        $(".folder-popup-form").removeClass("disabled");
                        $("#error-folder-popup-message").html(res.message);
                        $("#error-folder-popup").show();
                    }
                }
            });
        });

        $(document).on("click",".folder-color-option , .folder-color-default",function (e) {
            e.stopPropagation();
            $(".dynamic-menu").remove();
            $(".custom-folder-icon-color").spectrum("hide");
            folderID = $(this).closest(".dynamic-menu").data("id");
            var current_color = $(this).attr("data-color");
            if(typeof(current_color) == "undefined") {
                current_color = "";
            }
            var folderPostId = getIndexForPostSetting(folderID);
            folderPropertyArray[folderPostId]['has_color'] = current_color;
            update_custom_folder_color_css();
            nonce = getSettingForPost(folderID, 'nonce');
            $.ajax({
                url: wcp_settings.ajax_url,
                data: {
                    term_id: folderID,
                    type: wcp_settings.post_type,
                    action: 'prm_change_color_folder',
                    nonce: nonce,
                    color: current_color
                },
                method: 'post',
                cache: false,
                success: function (res) {
                    res = $.parseJSON(res);
                    update_custom_folder_color_css();
                }
            });
        });

        $(document).on("click", ".sticky-folder", function(e){
            e.stopPropagation();
            folderID = $(this).closest(".dynamic-menu").data("id");
            var folder_status = 0;
            folderPostId = getIndexForPostSetting(folderID);
            if(!$("li.jstree-node[id='"+folderID+"']").hasClass("is-sticky")) {
                folder_status = 1;
                $("li.jstree-node[id='"+folderID+"']").addClass("is-sticky");
                if(folderPostId != null) {
                    folderPropertyArray[folderPostId]['is_sticky'] = 1;
                }
            } else {
                $("li.jstree-node[id='"+folderID+"']").removeClass("is-sticky");
                $(".sticky-folders .sticky-folder-"+fileFolderID).remove();
                if(folderPostId != null) {
                    folderPropertyArray[folderPostId]['is_sticky'] = 0;
                }
            }
            $(".dynamic-menu").hide();
            nonce = getSettingForPost(folderID, 'nonce');
            make_sticky_folder_menu();
            $.ajax({
                url: wcp_settings.ajax_url,
                data: {
                    term_id: folderID,
                    type: wcp_settings.post_type,
                    action: "prm_make_sticky_folder",
                    nonce: nonce,
                    status: folder_status
                },
                method: 'post',
                cache: false,
                success: function (res) {
                    res = $.parseJSON(res);
                    if(res.status == '1') {
                        if($(".jstree-node[id='"+res.id+"']").length) {
                            $(".jstree-node[id='"+res.id+"'] > a.jstree-anchor").trigger("focus");
                        }
                    }
                }
            });
        });
    });

    /* Remove Folders */
    $(document).ready(function(){
        $(document).on("click", "#inline-remove, .delete-folder-action:not(.disabled)", function(){
            if($("#menu-checkbox").is(":checked")) {
                $(".dynamic-menu").remove();
                removeFolderFromID(1);
            } else if($("#js-tree-menu a.jstree-clicked").length ) {
                if(!$("#js-tree-menu a.jstree-clicked").closest(".jstree-node").hasClass("is-locked")) {
                    fileFolderID = $("#js-tree-menu a.jstree-clicked").closest("li.jstree-node").attr("id");
                    removeFolderFromID(1);
                    $(".dynamic-menu").remove();
                    $(".active-menu").removeClass("active-menu");
                }
            }
        });

        $(document).on("click", "#js-tree-menu input.checkbox", function(){
            checkForCopyPaste();
        });

        $(document).on("click","#menu-checkbox",function(){
            if($(this).is(":checked")) {
                $(".js-tree-data").addClass("show-folder-checkbox");
                $("#menu-checkbox").prop("checked", true);
            } else {
                $(".js-tree-data input.checkbox").attr("checked", false);
                $(".js-tree-data").removeClass("show-folder-checkbox");
                $("#menu-checkbox").prop("checked", false);
            }
        });

        $(document).on("click", "#menu-checkbox", function(){
            if($(this).is(":checked")) {
                $("#menu-checkbox").prop("checked", true);
                $(".js-tree-data").addClass("show-folder-checkbox");
            } else {
                $("#menu-checkbox").prop("checked", false);
                $(".js-tree-data input.checkbox").attr("checked", false);
                $(".js-tree-data").removeClass("show-folder-checkbox");
            }
        });

        $(document).on("click", ".folder-checkbox, .input-checkbox", function(e){
            e.stopImmediatePropagation();
            e.stopPropagation();
        });

        $(document).on("click", ".remove-folder", function(){
            folderID = $(this).closest("li.jstree-node").data("id");
            if(!$(this).closest(".dynamic-menu").hasClass("is-locked")) {
                fileFolderID = $(this).closest(".dynamic-menu").data("id");
                removeFolderFromID(0);
                $(".dynamic-menu").remove();
                $(".active-menu").removeClass("active-menu");
            }
        });

        $(document).on("click", "#remove-folder-item", function (e){
            e.stopPropagation();
            $(".folder-popup-form").addClass("disabled");
            $("#remove-folder-item").html('<span class="dashicons dashicons-update"></span>');
            nonce = getSettingForPost(fileFolderID, 'nonce');
            if(isMultipleRemove) {
                removeMultipleFolderItems();
            } else {
                $.ajax({
                    url: wcp_settings.ajax_url,
                    data: {
                        type: wcp_settings.post_type,
                        action: "prm_remove_folder",
                        term_id: fileFolderID,
                        nonce: nonce,
                    },
                    method: 'post',
                    success: function (res) {
                        res = $.parseJSON(res);
                        if(res.status == '1') {
                            var nextNode = getParentNodeInfo(fileFolderID);
                            console.log(nextNode);
                            $('#js-tree-menu').jstree().delete_node(fileFolderID);
                            isKeyActive = parseInt(res.is_key_active);
                            n_o_file = parseInt(res.folders);
                            $("#current-folder").text(n_o_file);
                            $("#ttl-fldr").text((3*3)+(4/(2*2)));
                            $(".sticky-folders .sticky-folder-"+fileFolderID).remove();
                            add_menu_to_list();
                            ajaxAnimation();
                            $(".folder-popup-form").hide();
                            $(".folder-popup-form").removeClass("disabled");
                            resetMediaAndPosts();
                            make_sticky_folder_menu();
                            if(nextNode != 0 && $("#"+nextNode+"_anchor").length) {
                                $("#"+nextNode+"_anchor").trigger("click");
                            } else {
                                $(".header-posts").trigger("click");
                            }
                        } else {
                            $(".folder-popup-form").hide();
                            $(".folder-popup-form").removeClass("disabled");
                            $("#error-folder-popup-message").html(res.message);
                            $("#error-folder-popup").show();
                        }
                    }
                });
            }
        });
    });

    /* Copy/Paste/Cut functionality */
    $(document).ready(function(){

        $(document).on("click", "#remove-from-all-folders:not(.disabled), #remove-from-current-folder:not(.disabled)", function(){
            $("#remove-from-all-folders, #remove-from-current-folder").addClass("disabled");
            var removeFrom = 'all';
            if($(this).hasClass("remove-from-current-folder")) {
                removeFrom = 'current';
            }
            $("#confirm-your-change").hide();
            $.ajax({
                url: wcp_settings.ajax_url,
                data: {
                    post_id: $("#unassigned_folders").val(),
                    action: 'prm_remove_post_folder',
                    active_folder: activeRecordID,
                    type: wcp_settings.post_type,
                    folder_id: -1,
                    nonce: wcp_settings.nonce,
                    status: wcp_settings.taxonomy_status,
                    taxonomy: activeRecordID,
                    remove_from: removeFrom
                },
                method: 'post',
                success: function (res) {
                    $("#remove-from-all-folders, #remove-from-current-folder").removeClass("disabled");
                    ajaxAnimation();
                    resetMediaAndPosts();
                    checkForUndoFunctionality();
                }
            });
        });

        $(document).on("click", ".default-folders a", function(e){
            e.preventDefault();
            var post_id = $(this).closest(".dynamic-menu").data("id");
            var folderPostId = getIndexForPostSetting(post_id);
            $(".dynamic-menu").hide();
            if(folderPostId != null) {
                var postSlug = folderPropertyArray[folderPostId]['slug'];
                wcp_settings.default_folder = postSlug;
                $.ajax({
                    url: wcp_settings.ajax_url,
                    type: 'post',
                    data: 'action=save_premio_default_folder&post_type=' + wcp_settings.post_type + "&post_slug=" + postSlug + "&nonce=" + wcp_settings.nonce+ "&folder_id="+post_id,
                    cache: false,
                    async: false,
                    success: function (res) {
                        update_js_tree_data();
                        res = $.parseJSON(res);
                        if(res.status == '1') {
                            if($(".jstree-node[id='"+res.folder_id+"']").length) {
                                $(".jstree-node[id='"+res.folder_id+"'] > a.jstree-anchor").trigger("focus");
                            }
                        }
                    }
                });
            }
        });

        $(document).on("click", ".remove-default-folder a", function(e){
            e.preventDefault();
            $(".dynamic-menu").hide();
            var post_id = $(this).closest(".dynamic-menu").data("id");
            wcp_settings.default_folder = "";
            $.ajax({
                url: wcp_settings.ajax_url,
                type: 'post',
                data: 'action=remove_premio_default_folder&post_type=' + wcp_settings.post_type + "&nonce=" + wcp_settings.nonce+ "&folder_id="+post_id,
                cache: false,
                async: false,
                success: function (res) {
                    update_js_tree_data();
                    res = $.parseJSON(res);
                    if(res.status == '1') {
                        if($(".jstree-node[id='"+res.folder_id+"']").length) {
                            $(".jstree-node[id='"+res.folder_id+"'] > a.jstree-anchor").trigger("focus");
                        }
                    }
                }
            });
        });

        $(document).on("click", ".dynamic-menu .copy-folders", function(e){
            e.stopPropagation();
            isFolderCopy = $(this).closest(".dynamic-menu").data("id");
            CPCAction = "copy";
            $(".dynamic-menu").remove();
            ajaxAnimation();
            $(".folders-undo-notification").removeClass("active");
            $("#copy-message").addClass("active");
            setTimeout(function(){
                $("#copy-message").removeClass("active");
            }, 5000);
            checkForCopyPaste();
            if($(".jstree-node[id='"+isFolderCopy+"']").length) {
                $(".jstree-node[id='"+isFolderCopy+"'] > a.jstree-anchor").trigger("focus");
            }
        });

        $(document).on("click", ".dynamic-menu .cut-folders", function(e){
            e.stopPropagation();
            isFolderCopy = $(this).closest(".dynamic-menu").data("id");
            CPCAction = "cut";
            $(".dynamic-menu").remove();
            ajaxAnimation();
            $(".folders-undo-notification").removeClass("active");
            $("#cut-message").addClass("active");
            lastParentID = $("#"+isFolderCopy).data("parent");
            setTimeout(function(){
                $("#cut-message").removeClass("active");
            }, 5000);
            checkForCopyPaste();
            if($(".jstree-node[id='"+isFolderCopy+"']").length) {
                $(".jstree-node[id='"+isFolderCopy+"'] > a.jstree-anchor").trigger("focus");
            }
        });

        $(document).on("click", ".dynamic-menu .paste-folders", function(e){
            e.stopPropagation();
            var currentFolderID = $(this).closest(".dynamic-menu").data("id");
            if(isFolderCopy != 0 && isFolderCopy != "" && currentFolderID != isFolderCopy) {
                if(CPCAction == "cut") {
                    lastParentID = $("#"+isFolderCopy).data("parent");
                    lastCopiedFolder = isFolderCopy;
                    lastFolderOrder = $("#"+isFolderCopy).index();
                    $('#js-tree-menu').jstree("move_node", "#"+isFolderCopy, "#"+currentFolderID, 0);
                    //ajaxAnimation();
                    CPCActionLast = "cut";
                    $(".folders-undo-notification").removeClass("active");
                    $("#paste-message").addClass("active");
                    setTimeout(function(){
                        $("#paste-message").removeClass("active");
                        lastFolderOrder = 0;
                        lastParentID = 0;
                    }, 5000);
                    if($(".jstree-node[id='"+currentFolderID+"']").length) {
                        $(".jstree-node[id='"+currentFolderID+"'] > a.jstree-anchor").trigger("focus");
                    }
                } else {
                    copyFolders(isFolderCopy, currentFolderID);
                }
                checkForCopyPaste();
                CPCAction = "";
                isFolderCopy = 0;
            }
            $(".dynamic-menu").remove();
        });

        $(document).on("click",".paste-folder-action", function(){

            if(activeRecordID == "" || isNaN(activeRecordID)) {
                activeRecordID = 0;
            }
            if(isFolderCopy != 0 && isFolderCopy != "" && isFolderCopy != activeRecordID) {
                if(CPCAction == "cut") {
                    lastParentID = $("#"+isFolderCopy).data("parent");
                    lastCopiedFolder = isFolderCopy;
                    lastFolderOrder = $("#"+isFolderCopy).index()+1;
                    if(activeRecordID != "" && activeRecordID != 0) {
                        $('#js-tree-menu').jstree("move_node", "#" + isFolderCopy, "#" + activeRecordID, 0);
                    } else {
                        $('#js-tree-menu').jstree("move_node", "#" + isFolderCopy, "#", $("#js-tree-menu > ul > li.jstree-node").length);
                    }
                    $(".folders-undo-notification").removeClass("active");
                    $("#paste-message").addClass("active");
                    setTimeout(function(){
                        $("#paste-message").removeClass("active");
                    }, 5000);
                    if($(".jstree-node[id='"+activeRecordID+"']").length) {
                        $(".jstree-node[id='"+activeRecordID+"'] > a.jstree-anchor").trigger("focus");
                    }
                } else {
                    if(activeRecordID == "" || isNaN(activeRecordID)) {
                        activeRecordID = 0;
                    }
                    copyFolders(isFolderCopy, activeRecordID);
                }
                checkForCopyPaste();
                CPCAction = "";
                isFolderCopy = 0;
            }
        });



        $(document).on("click", ".cut-folder-action", function(e){
            e.stopPropagation();
            if($("#menu-checkbox").is(":checked") && $("#js-tree-menu input.checkbox:checked").length > 0) {
                isFolderCopy = "custom";
                CPCAction = "cut";
                $(".folders-undo-notification").removeClass("active");
                $("#cut-message").addClass("active");
                setTimeout(function () {
                    $("#cut-message").removeClass("active");
                }, 5000);
                checkForCopyPaste();
            } else {
                if (activeRecordID != "" && activeRecordID != 0) {
                    isFolderCopy = activeRecordID;
                    CPCAction = "cut";
                    $(".folders-undo-notification").removeClass("active");
                    $("#cut-message").addClass("active");
                    setTimeout(function () {
                        $("#cut-message").removeClass("active");
                    }, 5000);
                    checkForCopyPaste();
                    if ($(".jstree-node[id='" + isFolderCopy + "']").length) {
                        $(".jstree-node[id='" + isFolderCopy + "'] > a.jstree-anchor").trigger("focus");
                    }
                }
            }
        });

        $(document).on("click", ".copy-folder-action", function(e){
            e.stopPropagation();
            if($("#menu-checkbox").is(":checked") && $("#js-tree-menu input.checkbox:checked").length > 0) {
                isFolderCopy = 'custom';
                CPCAction = "copy";
                $(".folders-undo-notification").removeClass("active");
                $("#copy-message").addClass("active");
                setTimeout(function () {
                    $("#copy-message").removeClass("active");
                }, 5000);
                checkForCopyPaste();
            } else {
                if (activeRecordID != "" && activeRecordID != 0) {
                    isFolderCopy = activeRecordID;
                    CPCAction = "copy";
                    $(".folders-undo-notification").removeClass("active");
                    $("#copy-message").addClass("active");
                    setTimeout(function () {
                        $("#copy-message").removeClass("active");
                    }, 5000);
                    checkForCopyPaste();
                    if ($(".folder-modal .jstree-node[id='" + activeRecordID + "']").length) {
                        $(".folder-modal .jstree-node[id='" + activeRecordID + "'] > a.jstree-anchor").trigger("focus");
                    }
                }
            }
        });

        if(wcp_settings.post_type == "attachment") {
            $(".upload-media-action").removeClass("disabled");
            $("#upload_media_folder").prop("disabled", false);
        } else {
            $("#upload_media_folder").remove();
        }
    });

    $(document).ready(function() {

        $(document).on("click", ".subsubsub a", function (e) {
            if ($("#js-tree-menu .jstree-anchor.jstree-clicked").length) {
                var CurrentNode = $("#js-tree-menu .jstree-anchor.jstree-clicked").closest(".jstree-node").attr("id");
                var folderSlug = getSettingForPost(CurrentNode, 'slug');
                if (folderSlug != "") {
                    e.preventDefault();
                    var thisURL = $(this).attr("href");
                    thisURL = thisURL + "&" + wcp_settings.custom_type + "=" + folderSlug;
                    window.location = thisURL;
                }
            } else if ($("#dynamic-tree-folders").length && $("#dynamic-tree-folders .jstree-anchor.jstree-clicked").length) {
                var CurrentNode = $("#dynamic-tree-folders .jstree-anchor.jstree-clicked").closest(".jstree-node").attr("id");
                if (CurrentNode != "") {
                    e.preventDefault();
                    var thisURL = $(this).attr("href");
                    thisURL = thisURL + "&ajax_action=premio_dynamic_folders&dynamic_folder=" + CurrentNode + "_anchor";
                    window.location = thisURL;
                }

            }
        });

        /* version 04/2021 */
        $(document).on("click", ".folder-settings-btn > a", function (e) {
            e.stopPropagation();
            $(".folder-settings-btn").toggleClass('active');
        });
        $(document).on("click", "body,html", function () {
            $(".folder-settings-btn").removeClass('active');
        });
        $(document).on("click", ".folder-setting-menu", function (e) {
            e.stopPropagation();
        });
        $(document).on("click", "#unlock-all-folder", function () {
            $("#unlock-all-folder-popup").show();
        });
        $(document).on("click", "#lock-all-folder", function () {
            $("#lock-all-folder-popup").show();
        });
        $(document).on("click", ".lock-unlock-all-folders", function () {
            if ($(this).hasClass("open-folders")) {
                $("#lock-all-folder-popup").show();
            } else {
                $("#unlock-all-folder-popup").show();
            }
        });
        $(document).on("click", ".lock-all-folder, .unlock-all-folder", function (e) {
            var lock_folders = 0;
            if ($(this).hasClass("lock-all-folder")) {
                lock_folders = 1;
            }
            $(".folder-settings-btn").removeClass("active");
            $(".form-loader-count").css("width", "100%");
            e.preventDefault();
            $("#unlock-all-folder-popup, #lock-all-folder-popup").hide();
            var folders = [];
            if ($("#menu-checkbox").is(":checked")) {
                $("#js-tree-menu .checkbox:checked").each(function () {
                    folders.push($(this).closest("li.jstree-node").attr("id"));
                });
            }
            $.ajax({
                url: wcp_settings.ajax_url,
                data: {
                    post_type: wcp_settings.post_type,
                    nonce: wcp_settings.nonce,
                    action: 'prm_lock_unlock_all_folders',
                    lock_folders: lock_folders,
                    folders: folders
                },
                method: 'post',
                success: function (res) {
                    res = $.parseJSON(res);
                    $(".form-loader-count").css("width", "0");
                    ajaxAnimation();
                    if (res.status == 1) {
                        if (res.data.folders.length) {
                            for (var i = 0; i < folderPropertyArray.length; i++) {
                                if ($.inArray(String(folderPropertyArray[i]['folder_id']), res.data.folders) !== -1) {
                                    folderPropertyArray[i]['is_locked'] = 1;
                                }
                            }
                        } else if (folderPropertyArray.length > 0) {
                            for (var i = 0; i < folderPropertyArray.length; i++) {
                                folderPropertyArray[i]['is_locked'] = res.data.is_locked;
                            }
                        }
                    }
                    update_js_tree_data();
                }
            });
        });
    });

    /* sorting folders */
    $(document).ready(function(){
        $(document).on("click", "body, html", function(){
            $(".folder-order").removeClass("active");
        });

        $(document).on("click", "#sort-order-list", function(e){
            e.stopPropagation();
            $(".folder-order").toggleClass("active");
        });

        $(document).on("click", ".folder-sort-menu a:not(.pro-feature)", function(e) {
            e.stopPropagation();
            e.preventDefault();
            $(".form-loader-count").css("width", "100%");
            $(".folder-order").removeClass("active");
            lastOrderStatus = $(this).attr("data-sort");
            $.ajax({
                url: wcp_settings.ajax_url,
                data: {
                    type: wcp_settings.post_type,
                    action: "prm_folders_by_order",
                    nonce: wcp_settings.nonce,
                    order: $(this).attr("data-sort"),
                },
                method: 'post',
                success: function (res) {
                    res = $.parseJSON(res);
                    if(res.status == 1) {
                        $("#js-tree-menu").jstree().destroy();
                        $("#js-tree-menu").append("<ul></ul>");
                        $("#js-tree-menu ul").html(res.data);
                        initJSTree();
                        foldersArray = res.terms;
                        setFolderCountAndDD();
                    }
                    $(".form-loader-count").css("width", "0");
                    add_active_item_to_list();
                }
            });
        });
    });

    function update_custom_folder_color_css() {
        $("#custome_folder_color_css").remove();
        var custom_color = "<style id='custome_folder_color_css'>"
        $(folderPropertyArray).each(function (key,val) {
            if(val.has_color != "") {
                custom_color += "li.jstree-node[id='" + val.folder_id + "'] .pfolder-folder-close {color: "+val.has_color+ "!important;}";
            }
        });
        custom_color += "</style>";
        $("head").append(custom_color);
    }

    function check_for_sub_menu() {
        $("#js-tree-menu li.jstree-node").removeClass("has-sub-tree");
        $("#js-tree-menu li.jstree-node").each(function(){
            if($(this).find("ul.ui-sortable li").length) {
                $(this).addClass("has-sub-tree");
            } else {
                $(this).removeClass("active");
            }
        });
    }
    function removeMultipleFolderItems() {
        if($("#menu-checkbox").is(":checked")) {
            if($("#js-tree-menu input.checkbox:checked").length > 0) {
                var folderIDs = [];
                var activeItemDeleted = false;
                $("#js-tree-menu input.checkbox:checked").each(function(){
                    if(!$(this).closest("li.jstree-node").hasClass("is-locked")) {
                        folderIDs.push($(this).closest("li.jstree-node").attr("id"));
                        if($(this).closest("li.jstree-node").hasClass("jstree-clicked")) {
                            activeItemDeleted = true;
                        }
                    }
                });
                if(!folderIDs.length) {
                    return;
                }
                $(".form-loader-count").css("width", "100%");
                $.ajax({
                    url: wcp_settings.ajax_url,
                    data: {
                        type: wcp_settings.post_type,
                        action: "prm_remove_multiple_folder",
                        term_id: folderIDs,
                        nonce: wcp_settings.nonce
                    },
                    method: 'post',
                    success: function (res) {
                        res = $.parseJSON(res);
                        $(".form-loader-count").css("width", "0px");
                        if(res.status == '1') {
                            isKeyActive = parseInt(res.is_key_active);
                            n_o_file = parseInt(res.folders);
                            $("#current-folder").text(n_o_file);
                            console.log(res.term_ids);
                            console.log(res.term_ids.length);
                            for(i=0; i<res.term_ids.length; i++) {
                                console.log(res.term_ids[i]);
                                $('#js-tree-menu').jstree().delete_node(res.term_ids[i]);
                            }

                            $("#ttl-fldr").text((4*2)+(4/2));
                            // add_menu_to_list();
                            ajaxAnimation();
                            $(".folder-popup-form").hide();
                            $(".folder-popup-form").removeClass("disabled");
                            resetMediaAndPosts();
                            make_sticky_folder_menu();

                            ajaxAnimation();

                            check_for_sub_menu();

                            if(!$("#wcp_folder_"+activeRecordID).length) {
                                $(".header-posts a").trigger("click");
                                activeRecordID = 0;
                            }
                        } else {
                            window.location.reload();
                        }
                        $("#menu-checkbox").attr("checked", false);
                        $("#js-tree-menu input.checkbox").attr("checked", false);
                        $("#js-tree-menu").removeClass("show-folder-checkbox");
                    }
                });
            } else {

            }
        }
    }

    function copyFolders(copyFrom, copyTo) {
        $(".form-loader-count").css("width", "100%");
        var folderOrder = 0;
        if(activeRecordID == 0) {
            folderOrder = $("#js-tree-menu > ul > li").length;
        }
        var folderCopyIDs = [];
        if(copyFrom == "custom") {
            if($("#menu-checkbox").is(":checked") && $("#js-tree-menu input.checkbox:checked").length > 0) {
                $("#js-tree-menu input.checkbox:checked").each(function(){
                    folderCopyIDs.push(parseInt($(this).closest("li.jstree-node").attr("id")));
                });
            }
        }
        $.ajax({
            url: wcp_settings.ajax_url,
            data: {
                copy_from: copyFrom,
                copy_to: copyTo,
                action: 'prm_copy_premio_folders',
                nonce: wcp_settings.nonce,
                post_type: wcp_settings.post_type,
                folder_order: folderOrder,
                folders: folderCopyIDs
            },
            method: 'post',
            success: function(res) {
                res = $.parseJSON(res);
                if(res.status == 1) {
                    lastCopiedFolder = res.parent_id;
                    if(res.data.length > 0) {
                        for(var i=0; i < res.data.length; i++) {
                            setCopyFolders(res.data[i]);
                        }
                    }
                }
                ajaxAnimation();
            }
        });
    }

    function setCopyFolders(folders) {
        console.log(folders);
        var folderProperty = {
            'folder_id': folders.term_id,
            'folder_count': 0,
            'is_sticky': folders.is_sticky,
            'is_high': folders.is_high,
            'is_locked': folders.is_locked,
            'nonce': folders.nonce,
            'slug': folders.slug,
            'is_deleted': 0
        };
        folderPropertyArray.push(folderProperty);
        var folderTitle = folders.title;
        folderTitle = folderTitle.replace(/\\/g, '');
        $('#js-tree-menu').jstree().create_node(folders.parent_id, {
            "id": folders.term_id,
            "text": " "+folderTitle
        }, "last", function () {
            $(".jstree-node[id='" + folders.term_id + "']").attr("data-nonce", folders.nonce);
            $(".jstree-node[id='" + folders.term_id + "']").attr("data-slug", folders.slug);
        });

        update_js_tree_data();
        make_sticky_folder_menu();
        resetMediaAndPosts();

        $(".folders-undo-notification").removeClass("active");
        $("#paste-message").addClass("active");
        setTimeout(function(){
            $("#paste-message").removeClass("active");
        }, 5000);
    }

    function getParentNodeInfo(nodeID) {
        if($(".jstree-node[id='"+nodeID+"']").next().length) {
            return $(".jstree-node[id='"+nodeID+"']").next().attr("id");
        } else if($(".jstree-node[id='"+nodeID+"']").prev().length) {
            return $(".jstree-node[id='"+nodeID+"']").prev().attr("id");
        } else if($(".jstree-node[id='"+nodeID+"']").parent().parent().hasClass("jstree-node")) {
            return $(".jstree-node[id='"+nodeID+"']").parent().parent().attr("id");
        }
        return 0;
    }

    function removeFolderFromID(popup_type) {
        var removeMessage = wcp_settings.lang.DELETE_FOLDER_MESSAGE;
        var removeNotice = wcp_settings.lang.ITEM_NOT_DELETED;
        isMultipleRemove = false;
        if(popup_type == 1) {
            if($("#menu-checkbox").is(":checked")) {
                var ttlFolders = 0;
                $("#js-tree-menu input.checkbox:checked").each(function(){
                    if(!$(this).closest("li.jstree-node").hasClass("is-locked")) {
                        ttlFolders++;
                    }
                });
                if(ttlFolders == 0) {
                    return;
                }

                isMultipleRemove = true;
                if($("#js-tree-menu input.checkbox:checked").length ==	 0) {
                    $(".folder-popup-form").hide();
                    $(".folder-popup-form").removeClass("disabled");
                    $("#error-folder-popup-message").html(wcp_settings.lang.SELECT_AT_LEAST_ONE_FOLDER);
                    $("#error-folder-popup").show();
                    return;
                } else {
                    if($("#js-tree-menu input.checkbox:checked").length > 1) {
                        removeMessage = wcp_settings.lang.DELETE_FOLDERS_MESSAGE;
                        removeNotice = wcp_settings.lang.ITEMS_NOT_DELETED;
                    }
                }
            }
        }
        $(".folder-popup-form").hide();
        $(".folder-popup-form").removeClass("disabled");
        $("#remove-folder-item").text(wcp_settings.lang.YES_DELETE_IT);
        $("#remove-folder-message").text(removeMessage);
        $("#remove-folder-notice").text(removeNotice);
        $("#confirm-remove-folder").show();
        $("#remove-folder-item").focus();
    }

    function updateFolder() {
        folderName = $.trim($("#js-tree-menu").jstree(true).get_node(fileFolderID).text);
        parentID = $("#wcp_folder_"+fileFolderID).closest("li.jstree-node").data("folder-id");
        if(parentID == undefined) {
            parentID = 0;
        }

        $("#update-folder-data").text("Submit");
        $(".folder-form-errors").removeClass("active");
        $("#update-folder-item-name").val(folderName);
        $("#update-folder-item").removeClass("disabled");
        $("#update-folder-item").show();
        $("#update-folder-item-name").focus();
        $(".dynamic-menu").remove();
    }

    function setCustomScrollForFolder() {
        contentHeight = $(window).height() - $("#wpadminbar").height() - $(".sticky-wcp-custom-form").height() - 40;
        $("#custom-scroll-menu").height(contentHeight);
        $("#custom-scroll-menu").overlayScrollbars({
            resize : 'none',
            sizeAutoCapable :true,
            autoUpdateInterval : 33,
            x :'scroll',
            clipAlways :false,
            y :'scroll'
        });

        if($(".custom-scroll-menu").hasClass("hor-scroll")) {
            $("#custom-scroll-menu .os-viewport").on("scroll", function () {
                setActionPosition();
            });
            setActionPosition();
        }
    }

    function urlParam(name) {
        var results = new RegExp('[\?&]' + name + '=([^&#]*)')
            .exec(window.location.search);

        return (results !== null) ? results[1] || 0 : false;
    }

    function setActionPosition() {
        $("#js-tree-menu span.folder-actions").css("right", ($("#custom-scroll-menu .horizontal-scroll-menu").width() - $("#custom-scroll-menu .os-viewport").width() - $("#custom-scroll-menu .os-viewport").scrollLeft() - 10));
    }

    function add_menu_to_list() {
        add_active_item_to_list();
    }


    $(document).ready(function(){
        $(document).on("keydown", function (e) {
            // esc key
            if(e.keyCode == 27 || e.which == 27) {
                $(".folder-popup-form").hide();
            }
        });

        if(wcp_settings.use_shortcuts == "yes") {
            $(document).on("click", ".view-shortcodes", function (e) {
                e.preventDefault();
                $("#keyboard-shortcut").show();
            });

            $(document).on("keydown", function (e) {
                var isCtrlPressed = (e.ctrlKey || e.metaKey) ? true : false;

                // Shift + N : New Folder
                if(!($("input").is(":focus") || $("textarea").is(":focus"))) {
                    if (e.shiftKey && (e.keyCode == 78 || e.which == 78)) {
                        e.preventDefault();
                        $("#add-new-folder").trigger("click");
                    }
                }

                // F2 Rename Folder
                if(e.keyCode == 113 || e.which == 113) {
                    if($("#js-tree-menu .jstree-anchor").is(":focus")) {
                        fileFolderID = $("#js-tree-menu .jstree-anchor:focus").closest("li.jstree-node").attr("id");
                        updateFolder();
                        $(".dynamic-menu").remove();
                    }
                }

                // Ctrl+C/CMD+C: Copy Folder
                if(isCtrlPressed && (e.keyCode == 67 || e.which == 67)) {
                    if ($("#menu-checkbox").is(":checked") && $("#js-tree-menu input.checkbox:checked").length > 0) {
                        isFolderCopy = "custom";
                        CPCAction = "copy";
                        $(".folders-undo-notification").removeClass("active");
                        $("#copy-message").addClass("active");
                        setTimeout(function () {
                            $("#copy-message").removeClass("active");
                        }, 5000);
                        checkForCopyPaste();

                    } else if($("#js-tree-menu .jstree-anchor").is(":focus")) {
                        isFolderCopy = $("#js-tree-menu .jstree-anchor:focus").closest("li.jstree-node").attr("id");
                        CPCAction = "copy";
                        $(".folders-undo-notification").removeClass("active");
                        $("#copy-message").addClass("active");
                        setTimeout(function () {
                            $("#copy-message").removeClass("active");
                        }, 5000);
                        checkForCopyPaste();
                    }
                }


                // Ctrl+X/CMD+X: Cut Folder
                if(isCtrlPressed && (e.keyCode == 88 || e.which == 88)) {
                    if ($("#menu-checkbox").is(":checked") && $("#js-tree-menu input.checkbox:checked").length > 0) {
                        isFolderCopy = "custom";
                        CPCAction = "cut";
                        $(".folders-undo-notification").removeClass("active");
                        $("#cut-message").addClass("active");
                        setTimeout(function () {
                            $("#cut-message").removeClass("active");
                        }, 5000);
                        checkForCopyPaste();

                    } else if($("#js-tree-menu .jstree-anchor").is(":focus")) {
                        e.preventDefault();
                        isFolderCopy = $("#js-tree-menu .jstree-anchor:focus").closest("li.jstree-node").attr("id");
                        CPCAction = "cut";
                        $(".folders-undo-notification").removeClass("active");
                        $("#cut-message").addClass("active");
                        setTimeout(function () {
                            $("#cut-message").removeClass("active");
                        }, 5000);
                        checkForCopyPaste();
                    }
                }

                // Ctrl+V: Paste Folder
                if(isCtrlPressed && (e.keyCode == 86 || e.which == 86)) {
                    if($("#js-tree-menu .jstree-anchor").is(":focus")) {
                        e.preventDefault();
                        activeRecordID = $("#js-tree-menu .jstree-anchor:focus").closest("li.jstree-node").attr("id");
                        if(activeRecordID == "" || isNaN(activeRecordID)) {
                            activeRecordID = 0;
                        }
                        if(isFolderCopy != 0 && isFolderCopy != "" && isFolderCopy != activeRecordID) {
                            if(CPCAction == "cut") {
                                lastParentID = $("#" + isFolderCopy).data("parent");
                                lastCopiedFolder = isFolderCopy;
                                lastFolderOrder = $("#" + isFolderCopy).index() + 1;
                                if(activeRecordID != "" && activeRecordID != 0) {
                                    $('#js-tree-menu').jstree("move_node", "#" + isFolderCopy, "#" + activeRecordID, 0);
                                } else {
                                    $('#js-tree-menu').jstree("move_node", "#" + isFolderCopy, "#", $("#js-tree-menu > ul > li.jstree-node").length);
                                }
                                $(".folders-undo-notification").removeClass("active");
                                $("#paste-message").addClass("active");
                                setTimeout(function () {
                                    $("#paste-message").removeClass("active");
                                }, 5000);
                            } else {
                                if(activeRecordID == "" || isNaN(activeRecordID)) {
                                    activeRecordID = 0;
                                }
                                copyFolders(isFolderCopy, activeRecordID);
                            }
                            checkForCopyPaste();
                            CPCAction = "";
                            isFolderCopy = 0;
                        }
                    }
                }

                // Ctrl+d/cmd+d: Duplicate Folder
                if(isCtrlPressed && (e.keyCode == 68 || e.which == 68)) {
                    if($("#js-tree-menu .jstree-anchor").is(":focus")) {
                        e.preventDefault();
                        fileFolderID = $("#js-tree-menu .jstree-anchor:focus").closest("li.jstree-node").attr("id");
                        $(".dynamic-menu").remove();
                        isItFromMedia = false;
                        isDuplicate = true;
                        $("#add-update-folder-title").text(wcp_settings.lang.DUPLICATING_FOLDER);
                        addFolder();
                        // add_menu_to_list();
                    }
                }

                if(isCtrlPressed && (e.keyCode == 75 || e.which == 75)) {
                    $("#keyboard-shortcut").show();
                }

                // delete action
                if((e.keyCode == 46 || e.which == 46) || (e.keyCode == 8 || e.which == 8)) {
                    if ($("#menu-checkbox").is(":checked") && $("#js-tree-menu input.checkbox:checked").length > 0) {
                        $(".delete-folder-action").trigger("click");
                    } else if($("#js-tree-menu .jstree-anchor").is(":focus")) {
                        if(!$("#js-tree-menu .jstree-anchor:focus").closest("li.jstree-node").hasClass("is-locked")) {
                            fileFolderID = $("#js-tree-menu .jstree-anchor:focus").closest("li.jstree-node").attr("id");
                            removeFolderFromID(0);
                            $(".dynamic-menu").remove();
                            $(".active-menu").removeClass("active-menu");
                        }
                    }
                }

                // ctrl + down
                if(isCtrlPressed && (e.keyCode == 40 || e.which == 40)) {
                    if($("#js-tree-menu .jstree-anchor").is(":focus")) {
                        fileFolderID = $("#js-tree-menu .jstree-anchor:focus").closest("li.jstree-node").attr("id");
                        var lastParent = parseInt($("#"+fileFolderID).data("parent"));
                        var folderOrder = parseInt($("#"+fileFolderID).index())+1;
                        var dataChild = parseInt($("#"+fileFolderID).data("child"));
                        if(isNaN(lastParent)) {
                            lastParent = ($("li#" + fileFolderID).parents("li.jstree-node").length)?$("li#" + fileFolderID).parents("li.jstree-node").data("folder"):0;
                            dataChild = ($("li#" + fileFolderID).parents("li.jstree-node").length)?$("li#" + fileFolderID).parents("li.jstree-node").children():($("#js-tree-menu > ul > li").length);
                        }
                        if(lastParent == 0) {
                            lastParent = "";
                        }
                        if(dataChild == folderOrder) {
                            $('#js-tree-menu').jstree("move_node", "#"+fileFolderID, "#"+lastParent, 0);
                        } else {
                            $('#js-tree-menu').jstree("move_node", "#"+fileFolderID, "#"+lastParent, folderOrder+1);
                        }
                    }
                }

                // ctrl + down
                if(isCtrlPressed && (e.keyCode == 38 || e.which == 38)) {
                    if($("#js-tree-menu .jstree-anchor").is(":focus")) {
                        fileFolderID = $("#js-tree-menu .jstree-anchor:focus").closest("li.jstree-node").attr("id");
                        var lastParent = parseInt($("#" + fileFolderID).data("parent"));
                        var folderOrder = parseInt($("#" + fileFolderID).index()) - 1;
                        var dataChild = parseInt($("#" + fileFolderID).data("child"));
                        if(isNaN(lastParent)) {
                            folderOrder = parseInt($("#" + fileFolderID).index()) - 1;
                            lastParent = ($("li#" + fileFolderID).parents("li.jstree-node").length)?$("li#" + fileFolderID).parents("li.jstree-node").data("folder"):0;
                            dataChild = ($("li#" + fileFolderID).parents("li.jstree-node").length)?$("li#" + fileFolderID).parents("li.jstree-node").children():($("#js-tree-menu > ul > li").length);
                        }
                        if (lastParent == 0) {
                            lastParent = "";
                        }
                        if (folderOrder == -1) {
                            $('#js-tree-menu').jstree("move_node", "#" + fileFolderID, "#" + lastParent, dataChild);
                        } else {
                            $('#js-tree-menu').jstree("move_node", "#" + fileFolderID, "#" + lastParent, folderOrder);
                        }
                    }
                }

                // esc key
                if(e.keyCode == 27 || e.which == 27) {
                    $(".folder-popup-form").hide();
                }
            });
        }

        jQuery(document).on("submit", "#bulk-folder-form", function(e) {
            e.stopPropagation();
            e.preventDefault();

            if(jQuery("#bulk-select").val() != "") {
                chkStr = "";
                $(".wp-list-table tbody input:checked").each(function () {
                    chkStr += $(this).val() + ",";
                });
                if(jQuery("#bulk-select").val() != "") {
                    if (jQuery("#bulk-select").val() == "-1") {
                        jQuery.ajax({
                            url: wcp_settings.ajax_url,
                            data: "post_id=" + chkStr + "&type=" + wcp_settings.post_type + "&action=prm_remove_post_folder&folder_id=" + jQuery("#bulk-select").val() + "&nonce=" + wcp_settings.nonce + "&status=" + wcp_settings.taxonomy_status + "&taxonomy=" + activeRecordID,
                            method: 'post',
                            success: function (res) {
                                jQuery("#bulk-move-folder").hide();
                                resetMediaAndPosts();
                                ajaxAnimation();
                                checkForUndoFunctionality();
                            }
                        });
                    } else {
                        nonce = getSettingForPost($("#bulk-select").val(), 'nonce');
                        jQuery.ajax({
                            url: wcp_settings.ajax_url,
                            data: "post_ids=" + chkStr + "&type=" + wcp_settings.post_type + "&action=prm_change_multiple_post_folder&folder_id=" + jQuery("#bulk-select").val() + "&nonce=" + wcp_settings.nonce + "&status=" + wcp_settings.taxonomy_status + "&taxonomy=" + activeRecordID,
                            method: 'post',
                            success: function (res) {
                                res = jQuery.parseJSON(res);
                                jQuery("#bulk-move-folder").hide();
                                if (res.status == "1") {
                                    resetMediaAndPosts();
                                    ajaxAnimation();
                                    checkForUndoFunctionality();
                                } else {
                                    jQuery(".folder-popup-form").hide();
                                    jQuery(".folder-popup-form").removeClass("disabled");
                                    jQuery("#error-folder-popup-message").html(res.message);
                                    jQuery("#error-folder-popup").show()
                                }
                            }
                        });
                    }
                }
            }
        });


    });

    function show_folder_popup() {
        jQuery("#bulk-action-selector-top, #bulk-action-selector-bottom").val("-1");
        if(jQuery(".wp-list-table tbody input[type='checkbox']:checked").length == 0) {
            alert("Please select items to move in folder");
        } else {
            jQuery("#bulk-move-folder").show();
            jQuery("#bulk-select").html("<option value=''>Loading...</option>");
            $(".move-to-folder").attr("disabled", true);
            $("#move-to-folder").prop("disabled", true);
            jQuery.ajax({
                url: wcp_settings.ajax_url,
                data: "type=" + wcp_settings.post_type + "&action=prm_get_folders_default_list&active_id=" + activeRecordID,
                method: 'post',
                success: function (res) {
                    res = jQuery.parseJSON(res);
                    jQuery("#bulk-select").html("<option value=''>Select Folder</option><option value='-1'>(Unassigned)</option>");
                    $(".move-to-folder").attr("disabled", false);
                    $("#move-to-folder").prop("disabled", false);
                    if(res.status == 1) {
                        var taxonomies = res.taxonomies;
                        for(i=0;i<taxonomies.length;i++) {
                            jQuery("#bulk-select").append("<option value='"+taxonomies[i].term_id+"'>"+taxonomies[i].title+"</option>");
                        }
                    }
                }
            });
        }
    }
}));
