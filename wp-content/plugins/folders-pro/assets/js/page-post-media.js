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
    var folderSelectedAttachmentID = "";
    var treeId = ".folder-modal #js-tree-menu";
    var folderPropertyArray = [];
    var selectedFolderMediaId = -1;
    var defaultFolderHtml;
    var folderID = 0;
    var fileAddUpdateStatus = "add";
    var fileFolderID = 0;
    var folderNameDynamic = "";
    var n_o_file = -1;
    var isKeyActive = 0;
    var nonce = "";
    var folderId = 0;
    var fID = 0;
    var folderCurrentURL = folders_media_options.page_url;
    var activeRecordID = "";
    var folderIDs = "";
    var isMultipleRemove = false;
    var isItFromMedia = false;
    var isDuplicate = false;
    var duplicateFolderId = 0;
    var $action_form;
    var lastOrderStatus = "";
    var ajaxURL = "";
    var folderOrder = 0;
    var foldersArray = [];
    var contextOffsetX = null;
    var contextOffsetY = null;
    var hasStars;
    var hasChildren;
    var hasValidLicense = 0;
    var colorAJAX = null;
    var previousСolor = "";
    var isFolderCopy = 0;
    var CPCAction = "";
    var CPCActionLast = "";
    var lastParentID = 0;
    var lastCopiedFolder = 0;
    var uploadFolderID = 0;
    var lastFolderOrder = 0;
    var isChanged = false;
    var oldParent;
    var oldPosition;
    var cutFolderID;

    var userAccess = folders_media_options.user_access;

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

    var MediaLibraryOrganizerTaxonomyFilter = wp.media.view.AttachmentFilters.extend({
        id: 'media-attachment-taxonomy-filter',
        createFilters: function() {
            var filters = {};
            var totalItems = folders_media_options.terms.length;
            _.each(folders_media_options.terms || {}, function(term, index) {
                filters[term.term_id] = {
                    text: term.name + ' (' + term.trash_count + ')',
                    props: {
                        'media_folder': term.slug
                    }
                };
            });
            filters.all = {
                text: 'All Folders',
                props: {
                    'media_folder': ''
                },
                priority: 10
            };
            filters.unassigned = {
                text: '(Unassigned)',
                props: {
                    'media_folder': "-1"
                },
                priority: 10
            };
            this.filters = filters;
        },
        change: function() {
            var filters = {};
            _.each(folders_media_options.terms || {}, function(term, index) {
                filters[term.term_id] = {
                    text: term.name + ' (' + term.trash_count + ')',
                    props: {
                        'media_folder': term.slug
                    }
                };
            });
            filters.all = {
                text: 'Select a folder >>',
                props: {
                    'media_folder': ''
                },
                priority: 10
            };
            filters.unassigned = {
                text: '(Unassigned)',
                props: {
                    'media_folder': "-1"
                },
                priority: 10
            };
            this.filters = filters;
            var filter = this.filters[ this.el.value ];
            if( filter ) {
                this.model.set( filter.props );
            }
        }
    });

    var selectedFolderPageID = "all";
    var filesInQueue = 0;
    var uploadedFileCount = 0;
    var lastFolderData = [];

    var AttachmentsBrowser = wp.media.view.AttachmentsBrowser;
    wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
        createToolbar: function() {
            AttachmentsBrowser.prototype.createToolbar.call(this);
            this.toolbar.set('MediaLibraryOrganizerTaxonomyFilter', new MediaLibraryOrganizerTaxonomyFilter({
                controller: this.controller,
                model: this.collection.props,
                priority: -75
            }).render());
        }
    });

    var DynamicTaxonomyFilter = wp.media.view.AttachmentFilters.extend({
        id: 'media-attachment-dynamic-filter',
        createFilters: function() {
            var filters = {};
            var totalItems = folders_media_options.dynamic_folders.length;
            _.each(folders_media_options.dynamic_folders || {}, function(term, index) {
                filters[term.value] = {
                    text: term.name,
                    props: {
                        'dynamic_media_folder': term.value
                    }
                };
            });
            this.filters = filters;
        },
        change: function() {
            var filters = {};
            _.each(folders_media_options.dynamic_folders || {}, function(term, index) {
                filters[term.value] = {
                    text: term.name,
                    props: {
                        'dynamic_media_folder': term.value
                    }
                };
            });
            this.filters = filters;
            var filter = this.filters[ this.el.value ];
            if( filter ) {
                this.model.set( filter.props );
            }
        }
    });

    var AttachmentsDynamicBrowser = wp.media.view.AttachmentsBrowser;
    wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
        createToolbar: function() {
            AttachmentsDynamicBrowser.prototype.createToolbar.call(this);
            this.toolbar.set('DynamicTaxonomyFilter', new DynamicTaxonomyFilter({
                controller: this.controller,
                model: this.collection.props,
                priority: -75
            }).render());
        }
    });

    var Query = wp.media.model.Query;
    _.extend(Query, {
        get: (function() {
            var queries = [];
            return function(props, options) {
                var args = {},
                    orderby = Query.orderby,
                    defaults = Query.defaultProps,
                    query,
                    cache = false; // Always disable query
                delete props.query;
                delete props.cache;
                _.defaults(props, defaults);

                _.each(['include', 'exclude'], function(prop) {
                    if(props[prop] && !_.isArray(props[prop])) {
                        props[prop] = [props[prop]];
                    }
                });
                _.each(props, function(value, prop) {
                    if(_.isNull(value)) {
                        return;
                    }
                    args[Query.propmap[prop] || prop] = value;
                });
                _.defaults(args, Query.defaultArgs);
                args.orderby = orderby.valuemap[props.orderby] || props.orderby;
                // Disable query caching
                cache = false;
                // Search the query cache for a matching query.
                if(cache) {
                    query = _.find(queries, function(query) {
                        return _.isEqual(query.args, args);
                    });
                } else {
                    queries = [];
                }
                // Otherwise, create a new query and add it to the cache.
                if(!query) {
                    query = new Query([], _.extend(options || {}, {
                        props: props,
                        args: args
                    }));
                    queries.push(query);
                }
                return query;
            };
        }())
    });


    var wpMediaObj = window.wp;
    if(typeof wpMediaObj !== 'undefined' && typeof wpMediaObj.Uploader === 'function') {
        wpMediaObj.media.view.Modal.prototype.on('open', function() {
            folderSelectedAttachmentID = "";
            setTimeout(function(){
                if($(".folder-modal #media-attachment-taxonomy-filter").length) {
                    if($(".folder-modal #media-attachment-taxonomy-filter").val() == "all") {
                        $(".folder-modal #media-attachment-taxonomy-filter option:gt(1)").remove();
                        _.each(folders_media_options.terms, function(term, index){
                            $(".folder-modal #media-attachment-taxonomy-filter").append("<option value='" + term.term_id + "'>" + term.name + " (" + term.trash_count + ")</option>")
                        });
                    }

                    if(!$("#wcp-content").length) {
                        if($(".editor-post-featured-image").length) {
                            $(".attachment-filters").val(selectedFolderPageID).trigger("change");
                        }
                    }
                }
            },200);
        });
        wpMediaObj.media.view.Modal.prototype.on('close', function() {
            folderSelectedAttachmentID = "";
        });
        $.extend(wpMediaObj.Uploader.prototype, {
            progress: function () {

            },
            init: function () {
                if(this.uploader) {
                    this.uploader.bind('FileFiltered', function (up, file) {
                        filesInQueue++;
                        $(".folder-meter").css("width", "0%");
                        $(".media-folder-loader").show();
                        $("#total_upload_files").text(filesInQueue);
                    });
                    this.uploader.bind('BeforeUpload', function (uploader, file) {
                        var folder_id = selectedFolderMediaId;
                        var params = uploader.settings.multipart_params;
                        folder_id = parseInt(folder_id);
                        if(folder_id > 0) {
                            params.folder_for_media = folder_id;
                        }
                        if(uploadedFileCount < filesInQueue) {
                            $(".media-folder-loader").show();
                            var progress_width = uploadedFileCount/filesInQueue*100;
                            $(".folder-meter").css("width", progress_width+"%");
                        }
                        uploadedFileCount++;
                        $("#current_upload_files").text(uploadedFileCount);

                    });
                    this.uploader.bind('UploadComplete', function (up, files) {
                        var wp_media = window.wp;
                        selectedFolderMediaId = -1;
                        $(".folder-meter").css("width", "100%");
                        setTimeout(function(){
                            $(".media-folder-loader").hide();
                            $(".folder-meter").css("width", "0%");
                            filesInQueue = 0;
                            uploadedFileCount = 0;
                        }, 1250);

                        resetMediaCounter();
                        // if(typeof wp_media.media.frame !== "undefined" && wp_media.media.frame.content.get() !== null && typeof(wp_media.media.frame.content.get().collection) != "undefined") {
                        //     folderSelectedAttachmentID = [];
                        //     if($(".folder-modal ul.attachments li.selected").length) {
                        //         $(".folder-modal ul.attachments li.selected").each(function(){
                        //             folderSelectedAttachmentID.push($(this).data("id"));
                        //         });
                        //     } else {
                        //         // wp_media.media.frame.content.get().collection.props.set({ignore: (+new Date())});
                        //         // wp_media.media.frame.content.get().options.selection.reset();
                        //     }
                        // }
                    });
                }
            }
        });
    }

    var wpMedia = window.wp;
    if(typeof wpMedia !== 'undefined' && typeof wpMedia.Uploader === 'function') {
        var windowMedia = window.wp.media;
        var windowModal = windowMedia.view.Modal
        windowMedia.view.Modal = windowMedia.view.Modal.extend({
            className: "folder-modal",
            initialize: function () {
                windowModal.prototype.initialize.apply(this, arguments);
            }, open: function () {
                if(windowModal.prototype.open.apply(this, arguments)) {
                    if(!$(".folder-modal").length) {
                        if($(".supports-drag-drop").length) {
                            $(".supports-drag-drop").each(function(){
                                if($(this).css("display") == "block" || $(this).css("display") == "inline-block") {
                                    $(this).addClass("folder-modal");
                                }
                            });
                        }
                    }
                    if($(".folder-modal").length) {
                        $(".folder-custom-menu").remove();
                        $(".folder-modal .media-frame-tab-panel").removeClass("has-folder-menu");
                        if($(".folder-modal .media-frame").hasClass("hide-menu")) {
                            if(!$(".folder-custom-menu").length) {
                                $(".folder-modal .media-frame-tab-panel").before("<div class='folder-custom-menu'><div class='folder-menu-content'><div class='cssload-container'><div class='cssload-tube-tunnel'></div></div></div></div>");
                                $(".folder-modal .folder-menu-content").load(folders_media_options.media_page_url + "?media_folder= #wcp-content-resize", function () {
                                    checkForExpandCollapse();
                                    setCustomScrollForFolder();
                                    initJSTree();
                                });
                            }
                        } else {
                            if(!$(".folder-custom-menu").length) {
                                $(".folder-modal .media-frame-menu").addClass("has-folder-menu");
                                $(".folder-modal .media-frame-menu .media-menu").append("<div class='folder-custom-menu'><div class='folder-menu-content'><div class='cssload-container'><div class='cssload-tube-tunnel'></div></div></div></div>");
                                $(".folder-modal .folder-menu-content").load(folders_media_options.media_page_url + "?media_folder= #wcp-content-resize", function () {
                                    checkForExpandCollapse();
                                    setCustomScrollForFolder();
                                    initJSTree();
                                });
                            }
                        }

                        $(".folder-form-data").remove();
                        $(".media-frame-tab-panel:first").before("<div class='folder-form-data'></div>");
                        $(".folder-form-data").html(folders_media_options.form_content);
                    } else {
                        setTimeout(function(){
                            if(selectedFolderMediaId != -1) {
                                $("#media-attachment-taxonomy-filter").each(function () {
                                    $(this).val(selectedFolderMediaId);
                                    $(this).trigger("change");
                                });
                            }
                        }, 1000);
                    }
                }
            }, close: function () {
                windowModal.prototype.close.apply(this, arguments);
                $(".folder-modal").removeClass("folder-modal");
            }
        });
    }

    $(document).ready(function(){

        $(document).on("click", "#js-tree-menu input.checkbox", function(){
            checkForCopyPaste();
        });

        hasStars = parseInt(folders_media_options.hasStars);
        hasChildren = parseInt(folders_media_options.hasChildren);
        hasValidLicense = parseInt(folders_media_options.hasValidLicense);

        $(document).on("click",".folder-color-option , .folder-color-default",function (e) {
            $(".custom-folder-icon-color").spectrum("hide");
            e.stopPropagation();
            folderID = $(this).closest(".dynamic-menu").data("id");
            var current_color = $(this).attr("data-color");
            if(typeof(current_color) == "undefined") {
                current_color = "";
            }
            var folderPostId = getIndexForPostSetting(folderID);
            folderPropertyArray[folderPostId]['has_color'] = current_color;
            update_custom_folder_color_css();
            nonce = getSettingForPost(folderID, 'nonce');
            $(".dynamic-menu").remove();
            $.ajax({
                url: folders_media_options.ajax_url,
                data: "term_id=" + folderID + "&type=" + folders_media_options.post_type + "&action=wcp_change_color_folder&nonce="+nonce+"&color="+current_color,
                method: 'post',
                cache: false,
                success: function (res) {
                    res = $.parseJSON(res);
                    update_custom_folder_color_css();
                }
            });

        });
        /* version 04/2021 */
        $(document).on("click", ".folder-settings-btn > a", function(e){
            e.stopPropagation();
            $(".folder-settings-btn").toggleClass('active');
        });
        $(document).on("click", "body,html", function(){
            $(".folder-settings-btn").removeClass('active');
        });
        $(document).on("click", ".folder-setting-menu", function(e){
            e.stopPropagation();
        });
        $(document).on("click", ".lock-unlock-all-folders" ,function(){
            if($(this).hasClass("open-folders")) {
                $("#lock-all-folder-popup").show();
            } else {
                $("#unlock-all-folder-popup").show();
            }
        });
        $(document).on("click", ".lock-all-folder, .unlock-all-folder", function(e){
            var lock_folders = 0;
            if($(this).hasClass("lock-all-folder")) {
                lock_folders = 1;
            }
            $(".folder-settings-btn").removeClass("active");
            $(".form-loader-count").css("width", "100%");
            e.preventDefault();
            $("#unlock-all-folder-popup, #lock-all-folder-popup").hide();
            $.ajax({
                url: folders_media_options.ajax_url,
                data: {
                    post_type: folders_media_options.post_type,
                    nonce: folders_media_options.nonce,
                    action: 'premio_lock_unlock_all_folders',
                    lock_folders: lock_folders
                },
                method: 'post',
                success: function (res) {
                    res = $.parseJSON(res);
                    $(".form-loader-count").css("width", "0");
                    ajaxAnimation();
                    if(res.status == 1) {
                        if(folderPropertyArray.length > 0) {
                            for (var i=0; i<folderPropertyArray.length; i++) {
                                folderPropertyArray[i]['is_locked'] = res.data.is_locked;
                            }
                        }
                    }
                    update_js_tree_data();
                }
            });
        });

        /* uploading folder */
        $(document).on("change", "#upload_media_folder", function(){
            var fd = new FormData();
            if($('#upload_media_folder')[0].files.length) {
                for(var i=0; i<$('#upload_media_folder')[0].files.length; i++) {
                    fd.append('media_files_'+i, $('#upload_media_folder')[0].files[i]);
                    fd.append('media_file_name['+i+']', $('#upload_media_folder')[0].files[i].webkitRelativePath);
                }

                fd.append('post_type', folders_media_options.post_type);
                fd.append('nonce', folders_media_options.nonce);
                fd.append('action', 'upload_premio_folder');

                $(".form-loader-count").css("width", "100%");

                $.ajax({
                    url: folders_media_options.ajax_url,
                    type: 'post',
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function(result){
                        result = $.parseJSON(result);
                        if(result.status == 1) {

                            var firstFolder = 0;

                            if(result.data.length > 0) {
                                for(var i=0; i<result.data.length; i++) {
                                    var folderProperty = {
                                        'folder_id': result.data[i].term_id,
                                        'folder_count': 0,
                                        'is_sticky': result.data[i]['is_sticky'],
                                        'is_high': result.data[i]['is_high'],
                                        'is_locked': result.data[i]['is_locked'],
                                        'has_color': result.data[i]['has_color'],
                                        'nonce': result.data[i]['nonce'],
                                        'slug': result.data[i]['slug'],
                                        'is_deleted': 0
                                    };
                                    if(firstFolder == 0) {
                                        firstFolder = result.data[i].term_id;
                                    }
                                    folderPropertyArray.push(folderProperty);
                                    var folderTitle = result.data[i]['title'];
                                    folderTitle = folderTitle.replace(/\\/g, '');
                                    $('#js-tree-menu').jstree().create_node(result.data[i].parent_id, {
                                        "id": result.data[i]['term_id'],
                                        "text": " " + folderTitle
                                    }, "last", function () {
                                        $(".jstree-node[id='" + result.data[i]['term_id'] + "']").attr("data-nonce", result.data[i]['nonce']);
                                        $(".jstree-node[id='" + result.data[i]['term_id'] + "']").attr("data-slug", result.data[i]['slug']);
                                    });
                                }
                            }

                            // wp.media.frame.setState('insert');
                            //
                            // if( wp.media.frame.content.get() !== null) {
                            //     wp.media.frame.content.get().collection.props.set({ignore: (+ new Date())});
                            //     wp.media.frame.content.get().options.selection.reset();
                            // } else {
                            //     wp.media.frame.library.props.set ({ignore: (+ new Date())});
                            // }

                            if(firstFolder != 0) {
                                uploadFolderID = firstFolder;
                            }

                            $(".form-loader-count").css("width", "0px");
                            ajaxAnimation();
                            if($("#media-attachment-taxonomy-filter").length) {
                                resetMediaData(0);
                            }
                            $('#upload_media_folder').val("");
                        }
                    },
                });
            }
        });

        $(document).on("click", ".close-popup-button", function(e){
            $(".folder-popup-form").hide();
            if($(".folder-modal .jstree-node[id='"+fileFolderID+"']").length) {
                $(".folder-modal .jstree-node[id='"+fileFolderID+"'] > a.jstree-anchor").trigger("focus");
            }
        });

        $(document).on("click", ".thumbnail-hover-box a", function(e){
            e.stopPropagation();
            e.stopImmediatePropagation();
            e.preventDefault();
            window.open($(this).prop("href"), "_blank");
            //wp.media.frame.close();
            return false;
        });

        $( document ).ajaxComplete(function( event, xhr, settings ) {
            // if(settings.data != undefined && settings.data != "" && typeof settings.data == "string" && settings.data.indexOf("action=query-attachments") != -1) {
            //     setDragAndDropElements();
            // }
            // if(folderSelectedAttachmentID.length > 0) {
            //     for(var i=0; i<folderSelectedAttachmentID.length; i++) {
            //         if (jQuery(".folder-modal ul.attachments li[data-id='" + folderSelectedAttachmentID[i] + "']").length && !jQuery(".folder-modal ul.attachments li[data-id='" + folderSelectedAttachmentID[i] + "']").hasClass("selected")) {
            //             var e = jQuery.Event("click");
            //             e.ctrlKey = true;
            //             //jQuery(".folder-modal ul.attachments li[data-id='" + folderSelectedAttachmentID[i] + "']").trigger(e);
            //         }
            //     }
            // }
        });

        foldersArray = folders_media_options.terms;
        isKeyActive = parseInt(folders_media_options.is_key_active);
        n_o_file = parseInt(folders_media_options.folders);
        folderPropertyArray = folders_media_options.folder_settings;

        $(document).on("click", ".header-posts a.all-posts", function(e){
            fileFolderID = 0;
            activeRecordID = "";
            selectedFolderMediaId = "all";
            $(".active-item").removeClass("active-item");
            $(".jstree-clicked").removeClass("jstree-clicked");
            $(this).addClass("active-item");
            $(".sticky-folders .active-item").removeClass("active-item");
            $(this).closest(".media-frame").find("#media-attachment-taxonomy-filter").val("all").trigger("change");
            $(this).closest(".media-frame").find(".folder_for_media").val("all").trigger("change");
            checkForCopyPaste();
        });

        $(document).on("click", ".un-categorised-items", function(e){
            fileFolderID = 0;
            activeRecordID = "";
            selectedFolderMediaId = "unassigned";
            $(".active-item").removeClass("active-item");
            $(".sticky-folders .active-item").removeClass("active-item");
            $(".jstree-clicked").removeClass("jstree-clicked");
            $(this).addClass("active-item");
            $(this).closest(".media-frame").find("#media-attachment-taxonomy-filter").val("unassigned").trigger("change");
            $(this).closest(".media-frame").find(".folder_for_media").val("-1").trigger("change");
            checkForCopyPaste();
        });

        $(document).on("click", "#js-tree-menu a.jstree-anchor", function(e){
            $("#dynamic-tree-folders .jstree-clicked").removeClass("jstree-clicked");
            $("#media-attachment-dynamic-filter").val("dynamic-folders");
            var thisIndex = $(this).closest("li.jstree-node").attr("id");
            fileFolderID = thisIndex;
            selectedFolderMediaId = fileFolderID;
            $(this).closest(".folder-modal").find(".active-item").removeClass("active-item");
            $(this).closest(".folder-modal").find("#media-attachment-taxonomy-filter").val(thisIndex);
            $(this).closest(".folder-modal").find("#media-attachment-taxonomy-filter").trigger("change");
            thisSlug = getSettingForPost(thisIndex, 'slug');
            set_default_folders(thisSlug);
            $(".custom-media-select").removeClass("active");
            checkForCopyPaste();
        });

        $(document).on("click", "#dynamic-tree-folders a.jstree-anchor", function(e){
            $("#media-attachment-taxonomy-filter").val("all");
            fileFolderID = "";
            selectedFolderMediaId = "";
            $(".active-item").removeClass("active-item");
            $(".sticky-folders .active-item").removeClass("active-item");
            $("#js-tree-menu .jstree-clicked").removeClass("jstree-clicked");
            var thisIndex = $(this).closest("li.jstree-node").attr("id");
            $("#media-attachment-dynamic-filter").val(thisIndex).trigger("change");
            checkForCopyPaste();
        });

        $(document).on("change", ".folder_for_media", function(){
            if($(this).val() != "add-folder" && $(this).val() != null) {
                selectedFolderMediaId = $(this).val();
            } else if($(this).val() == "add-folder") {
                selectedFolderMediaId = -1;
            }
        });

        $(document).on("change", "#media-attachment-dynamic-filter", function(){
            if($(this).val() != 'dynamic-folders') {
                $("#media-attachment-taxonomy-filter").val("all");
            }
        });
        $(document).on("change", "#media-attachment-taxonomy-filter", function(){
            if($(this).val() != 'all') {
                $("#media-attachment-dynamic-filter").val("dynamic-folders");
            }
        });

        $(document).on("click", "body, html", function(e){
            $(".dynamic-menu").remove();
            $(".active-menu").removeClass("active-menu");
            $(".folder-order").removeClass("active");
        });

        $(document).on("click", ".dynamic-menu, .folder-order", function(e){
            e.stopPropagation();
        });

        if(userAccess == "admin" || userAccess == "view-edit") {

            $(document).on("contextmenu", ".js-tree-data .jstree-anchor", function (e) {
                contextOffsetX = e.pageX;
                contextOffsetY = e.pageY;
                $(this).find("span.folder-inline-edit").trigger("click");
                return false;
            });

            /* right click menu end */
            $(document).on("click", ".folder-actions span.folder-inline-edit", function (e) {
                e.stopImmediatePropagation()
                e.stopPropagation();
                e.preventDefault();
                if (folders_media_options.can_manage_folder == 0) {
                    return;
                }
                isHigh = $(this).closest("li.jstree-node").hasClass("is-high");
                isLocked = $(this).closest("li.jstree-node").hasClass("is-locked");
                isSticky = $(this).closest("li.jstree-node").hasClass("is-sticky");
                isDefault = $(this).closest("li.jstree-node").hasClass("is-current");
                var fID = $(this).closest("li").prop("id");
                isStickyClass = (isSticky) ? true : false;
                $(".dynamic-menu").remove();
                $(".active-menu").removeClass("active-menu");
                menuHtml = "<div class='dynamic-menu " + ((isLocked) ? "is-locked" : "") + " " + ((!hasValidLicense) ? "no-key" : "") + "' data-id='" + $(this).closest("li").prop("id") + "'><ul>";
                if (userAccess == "admin") {
                    if (hasValidLicense || hasChildren) {
                        menuHtml += "<li class='new-folder'><a href='javascript:;'><span class=''><i class='pfolder-add-folder'></i></span>" + folders_media_options.lang.NEW_SUB_FOLDER + "</a></li>";
                    } else {
                        menuHtml += "<li class='new-folder-pro'><a href='javascript:;'><span class=''><i class='pfolder-add-folder'></i></span>" + folders_media_options.lang.ACTIVATE.NEW_SUB_FOLDER + "</a></li>";
                    }
                }
                menuHtml += "<li class='rename-folder'><a href='javascript:;'><span class=''><i class='pfolder-edit'></i></span>" + folders_media_options.lang.RENAME + "</a></li>";

                menuHtml += "<li class='color-folder'><a href='javascript:;'><span class=''><i class='pfolder-edit'></i></span>" + folders_media_options.lang.CHANGE_COLOR + "<span class='dashicons dashicons-arrow-right-alt2'></span></a>";
                menuHtml += "<ul class='color-selector'>";
                menuHtml += "<li class='color-selector-ul'>";
                $(folders_media_options.selected_colors).each(function(key,value) {
                    menuHtml += "<span class='folder-color-option' data-color='"+value+"' style='background-color:"+value+"'></span>";
                });
                if(parseInt(folders_media_options.hasValidLicense)) {
                    menuHtml += "<span><input type='text' class='custom-folder-icon-color' /></span>";
                }
                menuHtml += "</li>";
                if(!parseInt(folders_media_options.hasValidLicense)) {
                    menuHtml += "<li><a href='" + folders_media_options.register_url + "' target='_blank' class='folder-color-default'>" + folders_media_options.lang.ACTIVATE_COLOR_KEY + "</a></li>";
                }
                menuHtml += "<li><a href='javascript:;' class='folder-color-default'>"+folders_media_options.lang.REMOVE_COLOR+"</a></li>";
                menuHtml += "</ul>";
                menuHtml += "</li>";

                if (hasValidLicense) {
                    menuHtml += "<li class='sticky-folder'><a href='javascript:;'><span class='sticky-pin'><i class='pfolder-pin'></i></span>" + ((isStickyClass) ? folders_media_options.lang.REMOVE_STICKY_FOLDER : folders_media_options.lang.STICKY_FOLDER) + "</a></li>";
                } else {
                    menuHtml += "<li class='sticky-folder-pro'><a target='_blank' href='" + folders_media_options.register_url + "'><span class='sticky-pin'><i class='pfolder-pin'></i></span>" + folders_media_options.lang.ACTIVATE.STICKY_FOLDER + "</a></li>";
                }
                if (hasValidLicense || hasStars) {
                    menuHtml += "<li class='mark-folder'><a href='javascript:;'><span class=''><i class='pfolder-star'></i></span>" + ((isHigh) ? folders_media_options.lang.REMOVE_STAR : folders_media_options.lang.ADD_STAR) + "</a></li>";
                } else {
                    menuHtml += "<li class='mark-folder-pro'><a target='_blank' href='" + folders_media_options.register_url + "'><span class=''><i class='pfolder-star'></i></span>" + ((isHigh) ? folders_media_options.lang.ACTIVATE.REMOVE_STAR : folders_media_options.lang.ACTIVATE.ADD_STAR) + "</a></li>";
                }
                if (hasValidLicense) {
                    menuHtml += "<li class='lock-folder'><a href='javascript:;'><span class='dashicons dashicons-" + ((isLocked) ? "unlock" : "lock") + "'></span>" + ((isLocked) ? folders_media_options.lang.UNLOCK_FOLDER : folders_media_options.lang.LOCK_FOLDER) + "</a></li>";
                    menuHtml += "<li class='duplicate-folder'><a href='javascript:;'><span class=''><i class='pfolder-clone'></i></span>" + folders_media_options.lang.DUPLICATE_FOLDER + "</a></li>";
                } else {
                    menuHtml += "<li class='lock-folder-pro'><a target='_blank' href='" + folders_media_options.register_url + "'><span class='dashicons dashicons-lock'></span>" + folders_media_options.lang.ACTIVATE.LOCK_FOLDER + "</a></li>";
                    menuHtml += "<li class='duplicate-folder-pro'><a target='_blank' href='" + folders_media_options.register_url + "'><span class=''><i class='pfolder-clone'></i></span>" + folders_media_options.lang.ACTIVATE.DUPLICATE_FOLDER + "</a></li>";
                }

                hasPosts = parseInt($(this).closest("a.jstree-anchor").find(".premio-folder-count").text());
                if (hasPosts) {
                    if (hasValidLicense) {
                        menuHtml += "<li target='_blank' class='download-folder'><a href='javascript:;'><span class=''><i class='pfolder-zip-file'></i></span>" + folders_media_options.lang.DOWNLOAD_ZIP + "</a></li>";
                    } else {
                        menuHtml += "<li target='_blank' class='download-folder-pro'><a target='_blank' href='" + folders_media_options.register_url + "'><span class=''><i class='pfolder-zip-file'></i></span>" + folders_media_options.lang.ACTIVATE.DOWNLOAD_ZIP + "</a></li>";
                    }
                }

                if (!isDefault) {
                    menuHtml += "<li class='default-folders'><a href='javascript:;'><span><i class='pfolder-active-icon'></i></span>" + folders_media_options.lang.OPEN_THIS_FOLDER + "</a></li>";
                } else {
                    menuHtml += "<li class='remove-default-folder'><a href='javascript:;'><span><i class='pfolder-active-icon'></i></span>" + folders_media_options.lang.REMOVE_THIS_FOLDER + "</a></li>";
                }

                menuHtml += "<li class='cut-folders'><a href='javascript:;'><span><i class='pfolder-cut'></i></span>" + folders_media_options.lang.CUT + "</a></li>";
                menuHtml += "<li class='copy-folders'><a href='javascript:;'><span><i class='pfolder-copy'></i></span>" + folders_media_options.lang.COPY + "</a></li>";

                if (isFolderCopy) {
                    menuHtml += "<li class='paste-folders'><a href='javascript:;'><span><i class='pfolder-paste'></i></span>" + folders_media_options.lang.PASTE + "</a></li>";
                }
                menuHtml += "<li class='remove-folder'><a href='javascript:;'><span class=''><i class='pfolder-remove'></i></span>" + folders_media_options.lang.DELETE + "</a></li>" +
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

                $(this).parents("li.jstree-node").addClass("active-menu");

                if($(".custom-folder-icon-color").length) {
                    var folderPostId = getIndexForPostSetting(fID);
                    previousСolor = (folderPropertyArray[folderPostId]['has_color'] == "")?folders_media_options.default_color:folderPropertyArray[folderPostId]['has_color'];
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
                                    url: folders_media_options.ajax_url,
                                    data: {
                                        term_id: folderID,
                                        type: folders_media_options.post_type,
                                        action: "wcp_change_color_folder",
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
                                url: folders_media_options.ajax_url,
                                data: {
                                    term_id: folderID,
                                    type: folders_media_options.post_type,
                                    action: "wcp_change_color_folder",
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
                return false;
            });
        }

        $(document).on("click", ".dynamic-menu", function(e){
            e.stopImmediatePropagation()
            e.stopPropagation();
        });

        $(document).on("click", "body, html", function(){
            $(".dynamic-menu").remove();
        });

        /* add new folder functionality */
        $(document).on("click", ".new-folder-pro", function(e) {
            $('.dynamic-menu').remove();
            $("#sub-folder-popup").show();
        });

        $(document).on("change", "#media-attachment-taxonomy-filter", function(e){
            if($("#js-tree-menu").hasClass("jstree-default")) {
                $("#js-tree-menu").jstree(true).deselect_all();
            }
            if($("#dynamic-folders").length && $("#dynamic-folders").hasClass("jstree-default")) {
                $("#dynamic-folders").jstree(true).deselect_all();
            }
            $(".active-item").removeClass("active-item");
            if($(this).val() == "all") {
                $(".all-posts").addClass("active-item");
            } else if($(this).val() == "unassigned") {
                $(".un-categorised-items").addClass("active-item");
            } else {
                $("#js-tree-menu").jstree('select_node', $(this).val());
                $(".sticky-folder-"+$(this).val()+" a").addClass("active-item");
            }
        });

        $(document).on("click", ".new-folder", function(e) {
            e.stopPropagation();
            fileFolderID = $(this).closest(".dynamic-menu").data("id");
            $(".dynamic-menu").remove();
            $(".active-menu").removeClass("active-menu");
            isItFromMedia = false;
            isDuplicate = false;
            $("#add-update-folder-title").text(folders_media_options.lang.ADD_NEW_FOLDER);
            addFolder();
        });

        $(document).on("click", "#add-new-folder", function(e) {
            if($("#custom-menu li.active-item").length) {
                fileFolderID = $("#custom-menu li.active-item").data("folder-id");
            } else {
                fileFolderID = 0;
            }
            isItFromMedia = false;
            isDuplicate = false;
            $("#add-update-folder-title").text(folders_media_options.lang.ADD_NEW_FOLDER);
            addFolder();
        });

        $(document).on("submit", "#save-folder-form", function(e){
            e.stopPropagation();
            e.preventDefault();

            folderNameDynamic = $("#add-update-folder-name").val();

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
                    url: folders_media_options.ajax_url,
                    data: {
                        parent_id: parentId,
                        type: folders_media_options.post_type,
                        action: "wcp_add_new_folder",
                        nonce: folders_media_options.nonce,
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
                            if(result.data.length) {
                                for(var i=0; i<result.data.length; i++) {
                                    var folderProperty = {
                                        'folder_id': result.data[i].term_id,
                                        'folder_count': result.data[i].folder_count,
                                        'is_sticky': result.data[i]['is_sticky'],
                                        'is_high': result.data[i]['is_high'],
                                        'is_locked': result.data[i]['is_locked'],
                                        'has_color': result.data[i]['has_color'],
                                        'nonce': result.data[i]['nonce'],
                                        'slug': result.data[i]['slug'],
                                        'is_deleted': 0
                                    };
                                    var folderTitle = result.data[i]['title'];
                                    folderTitle = folderTitle.replace(/\\/g, '');
                                    folderPropertyArray.push(folderProperty);
                                    $('#js-tree-menu').jstree().create_node(result.parent_id, {
                                        "id": result.data[i]['term_id'],
                                        "text": " " + folderTitle
                                    }, i, function () {
                                        $(".jstree-node[id='" + result.data[i]['term_id'] + "']").attr("data-nonce", result.data[i]['nonce']);
                                        $(".jstree-node[id='" + result.data[i]['term_id'] + "']").attr("data-slug", result.data[i]['slug']);
                                        $(".jstree-node[id='" + result.data[i]['term_id'] + "']").attr("data-parent", result.parent_id);
                                        $(".jstree-node[id='" + result.data[i]['term_id'] + "'] > a.jstree-anchor .premio-folder-count").text(result.data[i].folder_count);
                                    });

                                    if($(".folder-modal .jstree-node[id='"+result.parent_id+"']").length) {
                                        $(".folder-modal #js-tree-menu").jstree("open_node", $("#"+result.parent_id));
                                    }

                                    if($(".folder-modal .jstree-node[id='"+result.parent_id+"']").length) {
                                        $(".folder-modal .jstree-node[id='"+result.parent_id+"'] > a.jstree-anchor").trigger("focus");
                                    } else {
                                        if($(".folder-modal .jstree-node[id='"+result.data[i]['term_id']+"']").length) {
                                            $(".folder-modal .jstree-node[id='"+result.data[i]['term_id']+"'] > a.jstree-anchor").trigger("focus");
                                        }
                                    }
                                }
                            }
                            ajaxAnimation();
                            update_js_tree_data();
                            fileFolderID = result.term_id;
                            resetMediaData(0);
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

        $(document).on("click", ".form-cancel-btn", function(){
            $(".folder-popup-form").hide();

            if($(".jstree-node[id='"+fileFolderID+"']").length) {
                $(".jstree-node[id='"+fileFolderID+"'] > a.jstree-anchor").trigger("focus");
            } else if($("#js-tree-menu .jstree-anchor.jstree-clicked").length) {
                $("#js-tree-menu .jstree-anchor.jstree-clicked").trigger("focus");
            }
        });

        $(document).on("click", ".folder-popup-form", function (e) {
            $(".folder-popup-form").hide();

            if($(".jstree-node[id='"+fileFolderID+"']").length) {
                $(".jstree-node[id='"+fileFolderID+"'] > a.jstree-anchor").trigger("focus");
            } else if($("#js-tree-menu .jstree-anchor.jstree-clicked").length) {
                $("#js-tree-menu .jstree-anchor.jstree-clicked").trigger("focus");
            }
        });

        $(document).on("click", ".popup-form-content", function (e) {
            e.stopPropagation();
        });

        document.onkeydown = function(evt) {
            evt = evt || window.event;
            var isEscape = false;
            if("key" in evt) {
                isEscape = (evt.key === "Escape" || evt.key === "Esc");
            } else {
                isEscape = (evt.keyCode === 27);
            }
            if(isEscape) {
                $(".folder-popup-form").hide();
            }
        };

        /* Update folder */
        $(document).on("click", ".rename-folder", function(e){
            e.stopPropagation();
            fileFolderID = $(this).closest(".dynamic-menu").data("id");
            updateFolder();
            $(".dynamic-menu").remove();
        });

        $(document).on("click", "#inline-update", function (e) {
            if($("#js-tree-menu a.jstree-clicked").length) {
                fileFolderID = $("#js-tree-menu a.jstree-clicked").closest("li.jstree-node").attr("id");
                updateFolder();
            }
        });

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
                    url: folders_media_options.ajax_url,
                    data: {
                        parent_id: parentID,
                        type: folders_media_options.post_type,
                        action: "wcp_update_folder",
                        nonce: nonce,
                        term_id: fileFolderID,
                        order: folderOrder,
                        name: folderNameDynamic
                    },
                    method: 'post',
                    success: function (res) {
                        result = $.parseJSON(res);
                        if(result.status == '1') {
                            $("#js-tree-menu").jstree('rename_node', result.id , " "+result.term_title);
                            $(".sticky-folder-"+result.id+" .folder-title").html(result.term_title);
                            folderPostId = getIndexForPostSetting(result.id);
                            if(folderPostId != null) {
                                folderPropertyArray[folderPostId]['nonce'] = result.nonce;
                                folderPropertyArray[folderPostId]['slug'] = result.slug;
                            }
                            $(".folder-popup-form").hide();
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
                    }
                });
            }
            return false;
        });

        /* Mark Folder */
        $(document).on("click", ".mark-folder", function(e){
            e.stopPropagation();
            folderID = $(this).closest(".dynamic-menu").data("id");
            nonce = getSettingForPost(folderID, 'nonce');
            $(".form-loader-count").css("width","100%");
            $(".dynamic-menu").remove();
            $(".active-menu").removeClass("active-menu");
            $.ajax({
                url: folders_media_options.ajax_url,
                data: "term_id=" + folderID + "&type=" + folders_media_options.post_type + "&action=wcp_mark_un_mark_folder&nonce="+nonce,
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
                        ajaxAnimation();
                        if($(".folder-modal .jstree-node[id='"+res.id+"']").length) {
                            $(".folder-modal .jstree-node[id='"+res.id+"'] > a.jstree-anchor").trigger("focus");
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
                url: folders_media_options.ajax_url,
                data: "term_id=" + folderID + "&type=" + folders_media_options.post_type + "&action=wcp_lock_unlock_folder&nonce="+nonce,
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
                        ajaxAnimation();
                        if($(".folder-modal .jstree-node[id='"+res.id+"']").length) {
                            $(".folder-modal .jstree-node[id='"+res.id+"'] > a.jstree-anchor").trigger("focus");
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

        /* Duplicate Folder */
        $(document).on("click", ".duplicate-folder", function(e){
            e.stopPropagation();
            fileFolderID = $(this).closest(".dynamic-menu").data("id");
            $(".dynamic-menu").remove();
            isItFromMedia = false;
            isDuplicate = true;
            $("#add-update-folder-title").text(folders_media_options.lang.DUPLICATING_FOLDER);
            addFolder();
        });

        /* Remove folder */
        $(document).on("click", ".remove-folder", function(){
            if(!$(this).closest(".dynamic-menu").hasClass("is-locked")) {
                folderID = $(this).closest(".dynamic-menu").data("id");
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
                    url: folders_media_options.ajax_url,
                    data: "type=" + folders_media_options.post_type + "&action=wcp_remove_folder&term_id=" + fileFolderID + "&nonce=" + nonce,
                    method: 'post',
                    success: function (res) {
                        res = $.parseJSON(res);
                        if(res.status == '1') {
                            var nextNode = getParentNodeInfo(fileFolderID);
                            $('#js-tree-menu').jstree().delete_node(fileFolderID);
                            isKeyActive = parseInt(res.is_key_active);
                            n_o_file = parseInt(res.folders);
                            $("#current-folder").text(n_o_file);
                            $("#ttl-fldr").text((3*3)+(4/(2*2)));
                            $(".sticky-folders .sticky-folder-"+fileFolderID).remove();
                            ajaxAnimation();
                            $(".folder-popup-form").hide();
                            $(".folder-popup-form").removeClass("disabled");
                            resetMediaAndPosts();
                            make_sticky_folder_menu();
                            if(nextNode != 0 && $(".folder-modal #"+nextNode+"_anchor").length) {
                                $(".folder-modal #"+nextNode+"_anchor").trigger("click").focus();
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

        $(document).on("click", "#sort-order-list", function(e){
            e.stopPropagation();
            $(".folder-order").toggleClass("active");
        });

        $(document).on("click", "#expand-collapse-list", function (e){
            e.stopPropagation();
            var statusType = 0;
            if($(this).hasClass("all-open")) {
                $(this).removeClass("all-open");
                statusType = 0;
                $(this).attr("data-folder-tooltip",folders_media_options.lang.EXPAND);
                $("#expand-collapse-list .text").text(folders_media_options.lang.EXPAND);
                $("#js-tree-menu").jstree("close_all");
            } else {
                $(this).addClass("all-open");
                statusType = 1;
                $(this).attr("data-folder-tooltip",folders_media_options.lang.COLLAPSE);
                $("#expand-collapse-list .text").text(folders_media_options.lang.COLLAPSE);
                $("#js-tree-menu").jstree("open_all");
            }
            folderIDs = "";
            $("#js-tree-menu .jstree-node:not(.jstree-leaf)").each(function(){
                folderIDs += $(this).attr("id")+",";
            });
            if(folderIDs != "") {
                $(".form-loader-count").css("width","100%");
                nonce = folders_media_options.nonce;
                $.ajax({
                    url: folders_media_options.ajax_url,
                    data: "type=" + folders_media_options.post_type + "&action=wcp_change_all_status&status=" + statusType + "&folders="+folderIDs+"&nonce="+nonce,
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

        $(document).on("click", ".folder-sort-menu a", function(e) {
            e.stopPropagation();
            e.preventDefault();
            $(".form-loader-count").css("width", "100%");
            $(".folder-order").removeClass("active");
            lastOrderStatus = $(this).attr("data-sort");
            $.ajax({
                url: folders_media_options.ajax_url,
                data: "type=" + folders_media_options.post_type + "&action=wcp_folders_by_order&nonce=" + folders_media_options.nonce+"&order="+$(this).attr("data-sort"),
                method: 'post',
                success: function (res) {
                    res = $.parseJSON(res);
                    if(res.status == 1) {
                        $("#js-tree-menu").jstree().destroy();
                        $("#js-tree-menu").append("<ul></ul>");
                        $("#js-tree-menu ul").html(res.data);
                        foldersArray = res.terms;
                        setFolderCountAndDD();
                        initJSTree();
                    }
                    $(".form-loader-count").css("width", "0");
                    add_active_item_to_list();
                }
            });
        });

        $(document).on("click", ".jstree-node .jstree-icon", function(){
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
                url: folders_media_options.ajax_url,
                data: "is_active=" + folderStatus + "&action=save_wcp_folder_state&term_id=" + folderID+"&nonce="+nonce,
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
                url: folders_media_options.ajax_url,
                data: {
                    is_active: folderStatus,
                    action: 'save_wcp_dynamic_folder_state',
                    term_id: folderID,
                    nonce: folders_media_options.nonce,
                    post_type: folders_media_options.post_type
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
                }
            });
            update_js_tree_data();
        });

        $(document).on("keyup", "#folder-search", function(){
            checkForFolderSearch();
        });

        $(document).on("change", "#folder-search", function(){
            checkForFolderSearch();
        });

        $(document).on("blur", "#folder-search", function(){
            checkForFolderSearch();
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

        $(document).on("click", ".jstree-node .jstree-icon", function(e){
            folderID = $(this).closest("li.jstree-node").attr("id");
            if($("li.jstree-node[id='"+folderID+"']").hasClass("jstree-open")) {
                folderStatus = 0;
            } else {
                folderStatus = 1;
            }
            $(".form-loader-count").css("width","100%");
            nonce = getSettingForPost(folderID, 'nonce');
            checkForExpandCollapse();
            $.ajax({
                url: folders_media_options.ajax_url,
                data: "is_active=" + folderStatus + "&action=save_wcp_folder_state&term_id=" + folderID+"&nonce="+nonce,
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
                }
            });
        });

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
                url: folders_media_options.ajax_url,
                data: "term_id=" + folderID + "&type=" + folders_media_options.post_type + "&action=wcp_make_sticky_folder&nonce="+nonce+"&status="+folder_status,
                method: 'post',
                cache: false,
                success: function (res) {
                    res = $.parseJSON(res);
                    if(res.status == '1') {
                        if($(".media-modal .jstree-node[id='"+res.id+"']").length) {
                            $(".media-modal .jstree-node[id='"+res.id+"'] > a.jstree-anchor").trigger("focus");
                        }
                    }
                }
            });
        });

        $(document).on("click", ".download-folder", function(e){
            // folderID = $(this).closest(".dynamic-menu").data("id");
            // dataNonce =  getSettingForPost(folderID, 'nonce');
            // window.location = folders_media_options.admin_url+"?action=download-folders&folder_id="+folderID+"&nonce="+dataNonce;
            folderID = $(this).closest(".dynamic-menu").data("id");
            $(".folder-download-loading").show();
            $(".dynamic-menu").hide();
            $.ajax({
                data: {
                    folder_id: folderID,
                    nonce: getSettingForPost(folderID, 'nonce'),
                    action: 'download_folders_data'
                },
                method: 'post',
                url: folders_media_options.ajax_url,
                success: function (res) {
                    $(".folder-download-loading").hide();
                    res = $.parseJSON(res);
                    if(res.status) {
                        downloadFile(res.file_url, res.file_name);
                    } else {
                        alert(res.message);
                    }
                }
            });
        });

        function downloadFile(url, filename) {
            fetch(url)
                .then(response => response.blob())
                .then(blob => {
                    const link = document.createElement("a");
                    link.href = URL.createObjectURL(blob);
                    link.download = filename;
                    link.click();
                })
                .catch(console.error);
        }

        //
        $(document).on("click", ".sticky-folders > ul > li > a", function(e) {
            e.stopPropagation();
            var folder_ID = $(this).closest("li").data("folder-id");
            if($(".jstree-node[id='"+folder_ID+"']").length) {
                $(".jstree-clicked").removeClass("jstree-clicked");
                $(".active-item").removeClass("active-item");
                $("#js-tree-menu").jstree('select_node', activeRecordID);
                $(".jstree-node[id='"+folder_ID+"'] > a.jstree-anchor").trigger("click");
                $(".jstree-node[id='"+folder_ID+"'] > a.jstree-anchor").addClass("jstree-clicked");
                $(".sticky-folders .sticky-folder-"+folder_ID+" a").addClass("active-item");
                checkForCopyPaste();
            }
        });

        if(userAccess == "admin" || userAccess == "view-edit") {

            $(document).on("contextmenu", ".sticky-fldr >  a", function (e) {
                e.stopPropagation();
                contextOffsetX = e.pageX;
                contextOffsetY = e.pageY;
                $(this).find("span.update-inline-record").trigger("click");
                return false;
            });

            /* right click menu */
            $(document).on("click", ".update-inline-record", function (e) {
                e.stopImmediatePropagation()
                e.stopPropagation();
                if (folders_media_options.can_manage_folder == 0) {
                    return;
                }
                isHigh = $(this).closest("li.sticky-fldr").hasClass("is-high");
                isLocked = $(this).closest("li.sticky-fldr").hasClass("is-locked");
                isSticky = $(this).closest("li.sticky-fldr").hasClass("is-sticky");
                isStickyClass = (isSticky) ? true : false;
                $(".dynamic-menu").remove();
                $(".active-menu").removeClass("active-menu");
                menuHtml = "<div class='dynamic-menu " + ((isLocked) ? "is-locked" : "") + " " + ((hasValidLicense) ? "no-key" : "") + " ' data-id='" + $(this).closest("li").data("folder-id") + "'><ul>";
                if (hasValidLicense || hasChildren) {
                    menuHtml += "<li class='new-folder'><a href='javascript:;'><span class=''><i class='pfolder-add-folder'></i></span>" + folders_media_options.lang.NEW_SUB_FOLDER + "</a></li>";
                } else {
                    menuHtml += "<li class='new-folder-pro'><a href='javascript:;'><span class=''><i class='pfolder-add-folder'></i></span>" + folders_media_options.lang.ACTIVATE.NEW_SUB_FOLDER + "</a></li>";
                }
                menuHtml += "<li class='rename-folder'><a href='javascript:;'><span class=''><i class='pfolder-edit'></i></span>" + folders_media_options.lang.RENAME + "</a></li>";
                if (hasValidLicense) {
                    menuHtml += "<li class='sticky-folder'><a href='javascript:;'><span class='sticky-pin'><i class='pfolder-pin'></i></span>" + ((isStickyClass) ? folders_media_options.lang.REMOVE_STICKY_FOLDER : folders_media_options.lang.STICKY_FOLDER) + "</a></li>";
                } else {
                    menuHtml += "<li class='sticky-folder-pro'><a target='_blank' href='" + folders_media_options.register_url + "'><span class='sticky-pin'><i class='pfolder-pin'></i></span>" + folders_media_options.lang.ACTIVATE.STICKY_FOLDER + "</a></li>";
                }
                if (hasValidLicense || hasStars) {
                    menuHtml += "<li class='mark-folder'><a href='javascript:;'><span class=''><i class='pfolder-star'></i></span>" + ((isHigh) ? folders_media_options.lang.REMOVE_STAR : folders_media_options.lang.ADD_STAR) + "</a></li>";
                } else {
                    menuHtml += "<li class='mark-folder-pro'><a target='_blank' href='" + folders_media_options.register_url + "'><span class=''><i class='pfolder-star'></i></span>" + ((isHigh) ? folders_media_options.lang.ACTIVATE.REMOVE_STAR : folders_media_options.lang.ACTIVATE.ADD_STAR) + "</a></li>";
                }
                if (hasValidLicense) {
                    menuHtml += "<li class='lock-folder'><a href='javascript:;'><span class='dashicons dashicons-" + ((isLocked) ? "unlock" : "lock") + "'></span>" + ((isLocked) ? folders_media_options.lang.UNLOCK_FOLDER : folders_media_options.lang.LOCK_FOLDER) + "</a></li>";
                    menuHtml += "<li class='duplicate-folder'><a href='javascript:;'><span class=''><i class='pfolder-clone'></i></span>" + folders_media_options.lang.DUPLICATE_FOLDER + "</a></li>";
                } else {
                    menuHtml += "<li class='lock-folder-pro'><a target='_blank' href='" + folders_media_options.register_url + "'><span class='dashicons dashicons-lock'></span>" + folders_media_options.lang.ACTIVATE.LOCK_FOLDER + "</a></li>";
                    menuHtml += "<li class='duplicate-folder-pro'><a target='_blank' href='" + folders_media_options.register_url + "'><span class=''><i class='pfolder-clone'></i></span>" + folders_media_options.lang.ACTIVATE.DUPLICATE_FOLDER + "</a></li>";
                }

                hasPosts = parseInt($(this).closest("a.jstree-anchor").find(".premio-folder-count").text());
                if (hasPosts) {
                    if (hasValidLicense) {
                        menuHtml += "<li target='_blank' class='download-folder'><a href='javascript:;'><span class=''><i class='pfolder-zip-file'></i></span>" + folders_media_options.lang.DOWNLOAD_ZIP + "</a></li>";
                    } else {
                        menuHtml += "<li target='_blank' class='download-folder-pro'><a target='_blank' href='" + folders_media_options.register_url + "'><span class=''><i class='pfolder-zip-file'></i></span>" + folders_media_options.lang.ACTIVATE.DOWNLOAD_ZIP + "</a></li>";
                    }
                }

                menuHtml += "<li class='cut-folders'><a href='javascript:;'><span><i class='pfolder-cut'></i></span>" + folders_media_options.lang.CUT + "</a></li>";
                menuHtml += "<li class='copy-folders'><a href='javascript:;'><span><i class='pfolder-copy'></i></span>" + folders_media_options.lang.COPY + "</a></li>";

                if (isFolderCopy) {
                    menuHtml += "<li class='paste-folders'><a href='javascript:;'><span><i class='pfolder-paste'></i></span>" + folders_media_options.lang.PASTE + "</a></li>";
                }
                menuHtml += "<li class='remove-folder'><a href='javascript:;'><span class=''><i class='pfolder-remove'></i></span>" + folders_media_options.lang.DELETE + "</a></li>" +
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

        $(document).on("change", ".folder_for_media", function(){
            if($(this).val() == "add-folder") {
                isItFromMedia = true;
                $("#add-new-folder").trigger("click");
            }
        });

        //
        $(document).on("click", "#custom-menu .ui-icon, #custom-menu h3", function(){
            $("#custom-menu .active-item").removeClass("active-item");
            $(this).closest(".route").addClass("active-item");
            // add_menu_to_list();
        });

        $(document).on("click", ".media-menu button.media-menu-item, .media-button-insert", function(){
            setCustomScrollForFolder();
        });

        $(document).on("click", ".undo-button, .undo-folder-action:not(.disabled)", function(){
            $(".folders-undo-notification").removeClass("active");
            if($(this).hasClass("copy-paste-action")) {
                if(lastCopiedFolder != 0 || cutFolderID != 0) {
                    if(CPCActionLast == "cut") {
                        if(lastParentID == 0) {
                            lastParentID = "";
                        }
                        $('#js-tree-menu').jstree("move_node", "#"+cutFolderID, "#"+oldParent, oldPosition);
                        lastFolderOrder = 0;
                        lastParentID = 0;
                        cutFolderID = 0;
                        oldPosition = 0;
                    } else {
                        if(lastCopiedFolder != 0) {
                            fileFolderID = lastCopiedFolder;
                            nonce = getSettingForPost(fileFolderID, 'nonce');
                            $.ajax({
                                url: folders_media_options.ajax_url,
                                data: "type=" + folders_media_options.post_type + "&action=wcp_remove_folder&term_id=" + fileFolderID + "&nonce=" + nonce,
                                method: 'post',
                                success: function (res) {
                                    res = $.parseJSON(res);
                                    if(res.status == '1') {
                                        $('#js-tree-menu').jstree().delete_node(fileFolderID);
                                        isKeyActive = parseInt(res.is_key_active);
                                        n_o_file = parseInt(res.folders);
                                        $("#current-folder").text(n_o_file);
                                        $("#ttl-fldr").text((3*3)+(4/(2*2)));
                                        $(".sticky-folders .sticky-folder-"+fileFolderID).remove();
                                        ajaxAnimation();
                                        $(".folder-popup-form").hide();
                                        $(".folder-popup-form").removeClass("disabled");
                                        resetMediaAndPosts();
                                        make_sticky_folder_menu();
                                        if(activeRecordID == fileFolderID) {
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
                    }
                }
            } else {
                if(folders_media_options.useFolderUndo == "yes") {
                    $.ajax({
                        url: folders_media_options.ajax_url,
                        type: 'post',
                        data: {
                            post_type: folders_media_options.post_type,
                            nonce: folders_media_options.nonce,
                            action: 'wcp_undo_folder_changes'
                        },
                        success: function (res) {
                            $("#undo-done").addClass("active");
                            $(".undo-folder-action").addClass("disabled");
                            setTimeout(function () {
                                $("#undo-done").removeClass("active");
                            }, 2500);
                            resetMediaAndPosts();
                            if($("#media-attachment-taxonomy-filter").length) {
                                var wp1 = parent.wp;
                                if(wp1.media != undefined) {
                                    wp1.media.frame.setState('insert');
                                    if(wp1.media.frame.content.get() !== null && typeof(wp1.media.frame.content.get().collection) != "undefined") {
                                        wp1.media.frame.content.get().collection.props.set({ignore: (+new Date())});
                                        wp1.media.frame.content.get().options.selection.reset();
                                    } else {
                                        wp1.media.frame.library.props.set({ignore: (+new Date())});
                                    }
                                }
                            }
                        }
                    })
                }
            }
        });

        $(document).on("click", ".close-undo-box", function(e){
            e.preventDefault();
            $("#do-undo").removeClass("active");
        });
    });

    function checkForUndoFunctionality() {
        if(folders_media_options.useFolderUndo == "yes") {
            $("#do-undo").addClass("active");
            $('.undo-folder-action').removeClass("disabled");
            setTimeout(function(){
                $("#do-undo").removeClass("active");
                $('.undo-folder-action').addClass("disabled");
            }, parseInt(folders_media_options.defaultTimeout));
        }
    }

    function getFolderIDFromSlug(folderSlug) {
        if(folderPropertyArray.length > 0) {
            for(i=0; i<folderPropertyArray.length; i++) {
                if(folderPropertyArray[i]['slug'] == folderSlug) {
                    return folderPropertyArray[i]['folder_id'];
                }
            }
        }
        return "";
    }

    function setDragAndDropElements() {
       // make_sticky_folder_menu();

        if (userAccess == "admin" || userAccess == "view-edit") {
            $("#js-tree-menu .jstree-anchor:not(.ui-droppable)").droppable({
                accept: ".wcp-move-file, .wcp-move-multiple, .attachments-browser li.attachment",
                hoverClass: 'wcp-drop-hover',
                tolerance: "pointer",
                classes: {
                    "ui-droppable-active": "ui-state-highlight"
                },
                drop: function (event, ui) {
                    folderID = $(this).closest("li.jstree-node").attr('id');
                    if ($(".media-modal #js-tree-menu .jstree-clicked").length) {
                        activeRecordID = $(".media-modal #js-tree-menu .jstree-clicked").closest(".jstree-node").attr("id");
                    }
                    if (ui.draggable.hasClass('wcp-move-multiple')) {
                        if ($(".wp-list-table input:checked").length) {
                            chkStr = "";
                            $(".wp-list-table input:checked").each(function () {
                                chkStr += $(this).val() + ",";
                            });
                            nonce = getSettingForPost(folderID, 'nonce');
                            $.ajax({
                                url: folders_media_options.ajax_url,
                                data: "post_ids=" + chkStr + "&type=" + folders_media_options.post_type + "&action=wcp_change_multiple_post_folder&folder_id=" + folderID + "&nonce=" + nonce + "&status=" + folders_media_options.taxonomy_status + "&taxonomy=" + activeRecordID,
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
                            url: folders_media_options.ajax_url,
                            data: "post_ids=" + chkStr + "&type=" + folders_media_options.post_type + "&action=wcp_change_multiple_post_folder&folder_id=" + folderID + "&nonce=" + nonce + "&status=" + folders_media_options.taxonomy_status + "&taxonomy=" + activeRecordID,
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
                            url: folders_media_options.ajax_url,
                            data: "post_ids=" + chkStr + "&type=" + folders_media_options.post_type + "&action=wcp_change_multiple_post_folder&folder_id=" + folderID + "&nonce=" + nonce + "&status=" + folders_media_options.taxonomy_status + "&taxonomy=" + activeRecordID,
                            method: 'post',
                            success: function (res) {
                                // window.location.reload();
                                resetMediaAndPosts();
                                checkForUndoFunctionality();
                                ajaxAnimation();
                            }
                        });
                    }
                }
            });

            $(".media-frame:not(.hide-router) .attachments-browser li.attachment:not(.ui-draggable)").draggable({
                revert: "invalid",
                containment: "document",
                helper: function (event, ui) {
                    $(".selected-items").remove();
                    var selectedItems = $(".media-frame:not(.hide-router) .attachments-browser li.attachment.selected").length;
                    selectedItems = (selectedItems == 0 || selectedItems == 1) ? "1 Item" : selectedItems + " Items";
                    return $("<div class='selected-items'><span class='total-post-count'>" + selectedItems + " Selected</span></div>");
                },
                start: function (event, ui) {
                    $("body").addClass("no-hover-css");
                },
                cursor: "move",
                appendTo: ".media-modal",
                cursorAt: {
                    left: 0,
                    top: 0
                },
                stop: function (event, ui) {
                    $(".selected-items").remove();
                    $("body").removeClass("no-hover-css");
                }
            });

            $(".un-categorised-items:not(.ui-droppable)").droppable({
                accept: ".wcp-move-file, .wcp-move-multiple, .attachments-browser li.attachment",
                hoverClass: 'wcp-hover-list',
                tolerance: "pointer",
                classes: {
                    "ui-droppable-active": "ui-state-highlight"
                },
                drop: function (event, ui) {
                    folderID = -1;
                    nonce = folders_media_options.nonce;
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
                tolerance: "pointer",
                classes: {
                    "ui-droppable-active": "ui-state-highlight"
                },
                drop: function (event, ui) {
                    $("body").removeClass("no-hover-css");
                    if ($(".media-modal #js-tree-menu .jstree-clicked").length) {
                        activeRecordID = $(".media-modal #js-tree-menu .jstree-clicked").closest(".jstree-node").attr("id");
                    }
                    folderID = $(this).data('id');
                    if (ui.draggable.hasClass('wcp-move-multiple')) {
                        nonce = getSettingForPost(folderID, 'nonce');
                        if ($(".wp-list-table input:checked").length) {
                            chkStr = "";
                            $(".wp-list-table input:checked").each(function () {
                                chkStr += $(this).val() + ",";
                            });
                            $.ajax({
                                url: folders_media_options.ajax_url,
                                data: "post_ids=" + chkStr + "&type=" + folders_media_options.post_type + "&action=wcp_change_multiple_post_folder&folder_id=" + folderID + "&nonce=" + nonce + "&status=" + folders_media_options.taxonomy_status + "&taxonomy=" + activeRecordID,
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
                            url: folders_media_options.ajax_url,
                            data: "post_ids=" + chkStr + "&type=" + folders_media_options.post_type + "&action=wcp_change_multiple_post_folder&folder_id=" + folderID + "&nonce=" + nonce + "&status=" + folders_media_options.taxonomy_status + "&taxonomy=" + activeRecordID,
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
                            url: folders_media_options.ajax_url,
                            data: "post_ids=" + chkStr + "&type=" + folders_media_options.post_type + "&action=wcp_change_multiple_post_folder&folder_id=" + folderID + "&nonce=" + nonce + "&status=" + folders_media_options.taxonomy_status + "&taxonomy=" + activeRecordID,
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
                accept: ".wcp-move-file, .wcp-move-multiple, .media-frame:not(.hide-router) .attachments-browser li.attachment",
                hoverClass: 'wcp-drop-hover',
                tolerance: "pointer",
                classes: {
                    "ui-droppable-active": "ui-state-highlight"
                },
                drop: function (event, ui) {
                    folderID = $(this).closest("li").data('folder-id');
                    if ($(".media-modal #js-tree-menu .jstree-clicked").length) {
                        activeRecordID = $(".media-modal #js-tree-menu .jstree-clicked").closest(".jstree-node").attr("id");
                    }
                    if (ui.draggable.hasClass('wcp-move-multiple')) {
                        if ($(".wp-list-table input:checked").length) {
                            var chkStr = "";
                            $(".wp-list-table input:checked").each(function () {
                                chkStr += $(this).val() + ",";
                            });
                            nonce = getSettingForPost(folderID, 'nonce');
                            $.ajax({
                                url: folders_media_options.ajax_url,
                                data: "post_ids=" + chkStr + "&type=" + folders_media_options.post_type + "&action=wcp_change_multiple_post_folder&folder_id=" + folderID + "&nonce=" + nonce + "&status=" + folders_media_options.taxonomy_status + "&taxonomy=" + activeRecordID,
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
                        var postID = ui.draggable[0].attributes['data-id'].nodeValue;
                        nonce = getSettingForPost(folderID, 'nonce');
                        chkStr = postID + ",";
                        $(".wp-list-table input:checked").each(function () {
                            if ($(this).val() != postID) {
                                chkStr += $(this).val() + ",";
                            }
                        });
                        $.ajax({
                            url: folders_media_options.ajax_url,
                            data: "post_ids=" + chkStr + "&type=" + folders_media_options.post_type + "&action=wcp_change_multiple_post_folder&folder_id=" + folderID + "&nonce=" + nonce + "&status=" + folders_media_options.taxonomy_status + "&taxonomy=" + activeRecordID,
                            method: 'post',
                            success: function (res) {
                                var res = $.parseJSON(res);
                                if (res.status == "1") {
                                    // window.location.reload();
                                    resetMediaAndPosts();
                                    checkForUndoFunctionality();
                                    ajaxAnimation();
                                } else {
                                    $(".folder-popup-form").hide();
                                    $(".folder-popup-form").removeClass("disabled");
                                    $("#error-folder-popup-message").html(res.message);
                                    $("#error-folder-popup").show()
                                }
                            }
                        });
                    } else if (ui.draggable.hasClass('attachment')) {
                        var chkStr = ui.draggable[0].attributes['data-id'].nodeValue;
                        nonce = getSettingForPost(folderID, 'nonce');
                        if ($(".media-frame:not(.hide-router) .attachments-browser li.attachment.selected").length > 1) {
                            chkStr = "";
                            $(".media-frame:not(.hide-router) .attachments-browser li.attachment.selected").each(function () {
                                chkStr += $(this).data("id") + ",";
                            });
                        }
                        folderIDs = chkStr;
                        $.ajax({
                            url: folders_media_options.ajax_url,
                            data: "post_ids=" + chkStr + "&type=" + folders_media_options.post_type + "&action=wcp_change_multiple_post_folder&folder_id=" + folderID + "&nonce=" + nonce + "&status=" + folders_media_options.taxonomy_status + "&taxonomy=" + activeRecordID,
                            method: 'post',
                            success: function (res) {
                                // window.location.reload();
                                resetMediaAndPosts();
                                checkForUndoFunctionality();
                                ajaxAnimation();
                            }
                        });
                    }
                }
            });
        }
    }

    function initJSTree() {

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
        }));

        $(".upload-media-action").removeClass("disabled");
        $("#upload_media_folder").prop("disabled", false);

        // $("#js-tree-menu").remove();
        $(treeId).jstree({
            "core": {
                'cache':false,
                "animation": 0,
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
            if(folders_media_options.default_folder != "" && folders_media_options.default_folder != 0) {
                var defaultFolder = getFolderIDFromSlug(folders_media_options.default_folder);
                if(defaultFolder != "") {
                    if($(".folder-modal #media-attachment-taxonomy-filter option[value='"+defaultFolder+"']").length) {
                        $(".folder-modal #media-attachment-taxonomy-filter").val(defaultFolder).trigger("change");
                        $(".folder-modal #js-tree-menu .jstree-clicked").removeClass("jstree-clicked");
                        $('.folder-modal #js-tree-menu').jstree('select_node', defaultFolder);
                        $(".folder-modal #js-tree-menu .jstree-node[id='"+defaultFolder+"'] > a").addClass("jstree-clicked");

                    } else {
                        $(".folder-modal select[name='folder_for_media']").val(defaultFolder);
                    }
                }
            }
            checkForCopyPaste();
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
            setFolderCount();
            folderMoveId = n.node.id;
            orderString = "";
            $(".folder-modal .jstree-node[id='"+folderMoveId+"']").closest("ul").children().each(function(){
                if($(this).attr("id") != 'undefined') {
                    orderString += $(this).attr("id") + ",";
                }
            });
            if($(".folder-modal #"+folderMoveId+"_anchor").closest(".jstree-node").parent().parent().hasClass("jstree-node")) {
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
                    url: folders_media_options.ajax_url,
                    data: {
                        term_ids: orderString,
                        action: "wcp_save_folder_order",
                        type: folders_media_options.post_type,
                        nonce: folders_media_options.nonce,
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
                            ajaxAnimation();
                            setFolderCountAndDD();
                            setDragAndDropElements();
                        } else {
                            $(".folder-popup-form").hide();
                            $(".folder-popup-form").removeClass("disabled");
                            $("#error-folder-popup-message").html(res.message);
                            $("#error-folder-popup").show();
                            window.location.reload(true);
                        }
                    }
                });
            }
        }));
        setDragAndDropElements();
    }

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
                url: folders_media_options.ajax_url,
                data: {
                    post_id: $("#unassigned_folders").val(),
                    action: 'wcp_remove_post_folder',
                    active_folder: activeRecordID,
                    type: folders_media_options.post_type,
                    folder_id: -1,
                    nonce: folders_media_options.nonce,
                    status: 0,
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
                folders_media_options.default_folder = postSlug;
                $.ajax({
                    url: folders_media_options.ajax_url,
                    type: 'post',
                    data: 'action=save_premio_default_folder&post_type=' + folders_media_options.post_type + "&post_slug=" + postSlug + "&nonce=" + folders_media_options.nonce+ "&folder_id="+post_id,
                    cache: false,
                    async: false,
                    success: function () {
                        update_js_tree_data();
                        if(res.status == '1') {
                            if($(".folder-modal .jstree-node[id='"+res.folder_id+"']").length) {
                                $(".folder-modal .jstree-node[id='"+res.folder_id+"'] > a.jstree-anchor").trigger("focus");
                            }
                        }
                    }
                });
            }
        });

        $(document).on("click", ".remove-default-folder a", function(e){
            e.preventDefault();
            $(".dynamic-menu").hide();
            folders_media_options.default_folder = "";
            $.ajax({
                url: folders_media_options.ajax_url,
                type: 'post',
                data: 'action=remove_premio_default_folder&post_type=' + folders_media_options.post_type + "&nonce=" + folders_media_options.nonce+ "&folder_id="+post_id,
                cache: false,
                async: false,
                success: function () {
                    update_js_tree_data();
                    if(res.status == '1') {
                        if($(".folder-modal .jstree-node[id='"+res.folder_id+"']").length) {
                            $(".folder-modal .jstree-node[id='"+res.folder_id+"'] > a.jstree-anchor").trigger("focus");
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
            if($(".folder-modal .jstree-node[id='"+isFolderCopy+"']").length) {
                $(".folder-modal .jstree-node[id='"+isFolderCopy+"'] > a.jstree-anchor").trigger("focus");
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
            setTimeout(function(){
                $("#cut-message").removeClass("active");
            }, 5000);
            checkForCopyPaste();
            if($(".folder-modal .jstree-node[id='"+isFolderCopy+"']").length) {
                $(".folder-modal .jstree-node[id='"+isFolderCopy+"'] > a.jstree-anchor").trigger("focus");
            }
        });

        $(document).on("click", ".dynamic-menu .paste-folders", function(e){
            e.stopPropagation();
            var currentFolderID = $(this).closest(".dynamic-menu").data("id");
            if(isFolderCopy != 0 && isFolderCopy != "" && currentFolderID != isFolderCopy) {
                if(CPCAction == "cut") {
                    if(isFolderCopy == "custom") {
                        if ($("#menu-checkbox").is(":checked") && $("#js-tree-menu input.checkbox:checked").length > 0) {
                            $(".folder-modal #js-tree-menu input.checkbox:checked").each(function(){
                                isFolderCopy = parseInt($(this).closest("li.jstree-node").attr("id"));
                                if (currentFolderID != "" && currentFolderID != 0) {
                                    $('.folder-modal #js-tree-menu').jstree("move_node", "#" + isFolderCopy, "#" + currentFolderID, 0);
                                } else {
                                    $('.folder-modal #js-tree-menu').jstree("move_node", "#" + isFolderCopy, "#", $("#js-tree-menu > ul > li.jstree-node").length);
                                }
                            });
                            $(".folders-undo-notification").removeClass("active");
                            $("#paste-message").addClass("active");
                            setTimeout(function () {
                                $("#paste-message").removeClass("active");
                            }, 5000);
                            isFolderCopy = "";
                        }
                    } else {
                        lastParentID = $("#" + isFolderCopy).data("parent");
                        lastCopiedFolder = isFolderCopy;
                        lastFolderOrder = $("#" + isFolderCopy).index();
                        $('.folder-modal #js-tree-menu').jstree("move_node", "#" + isFolderCopy, "#" + currentFolderID, 0);
                        //ajaxAnimation();
                        CPCActionLast = "cut";
                        $(".folders-undo-notification").removeClass("active");
                        $("#paste-message").addClass("active");
                        setTimeout(function () {
                            $("#paste-message").removeClass("active");
                            lastFolderOrder = 0;
                            lastParentID = 0;
                        }, 5000);
                        if ($(".folder-modal .jstree-node[id='" + currentFolderID + "']").length) {
                            $(".folder-modal .jstree-node[id='" + currentFolderID + "'] > a.jstree-anchor").trigger("focus");
                        }
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
                    if(isFolderCopy == "custom") {
                        if($("#menu-checkbox").is(":checked") && $("#js-tree-menu input.checkbox:checked").length > 0) {
                            $(".folder-modal #js-tree-menu input.checkbox:checked").each(function(){
                                isFolderCopy = parseInt($(this).closest("li.jstree-node").attr("id"));
                                if (activeRecordID != "" && activeRecordID != 0) {
                                    $('.folder-modal #js-tree-menu').jstree("move_node", "#" + isFolderCopy, "#" + activeRecordID, 0);
                                } else {
                                    $('.folder-modal #js-tree-menu').jstree("move_node", "#" + isFolderCopy, "#", $("#js-tree-menu > ul > li.jstree-node").length);
                                }
                            });
                            $(".folders-undo-notification").removeClass("active");
                            $("#paste-message").addClass("active");
                            setTimeout(function () {
                                $("#paste-message").removeClass("active");
                            }, 5000);
                        }
                    } else {
                        lastParentID = $("#" + isFolderCopy).data("parent");
                        lastCopiedFolder = isFolderCopy;
                        lastFolderOrder = $("#" + isFolderCopy).index() + 1;
                        if (activeRecordID != "" && activeRecordID != 0) {
                            $('#js-tree-menu').jstree("move_node", "#" + isFolderCopy, "#" + activeRecordID, 0);
                        } else {
                            $('#js-tree-menu').jstree("move_node", "#" + isFolderCopy, "#", $("#js-tree-menu > ul > li.jstree-node").length);
                        }
                        $(".folders-undo-notification").removeClass("active");
                        $("#paste-message").addClass("active");
                        setTimeout(function () {
                            $("#paste-message").removeClass("active");
                        }, 5000);
                        if ($(".folder-modal .jstree-node[id='" + activeRecordID + "']").length) {
                            $(".folder-modal .jstree-node[id='" + activeRecordID + "'] > a.jstree-anchor").trigger("focus");
                        }
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

        if(folders_media_options.post_type == "attachment") {
            $(".upload-media-action").removeClass("disabled");
        }
    });

    function checkForOtherFolders(folderIDs) {
        var folderID = -1;
        if(!$(".folder-modal #js-tree-menu .jstree-anchor.jstree-clicked").length) {
            nonce = folders_media_options.nonce;
            $.ajax({
                url: folders_media_options.ajax_url,
                data: "post_id=" + folderIDs + "&type=" + folders_media_options.post_type + "&action=wcp_remove_post_folder&folder_id=" + folderID + "&nonce=" + nonce + "&status=" + folders_media_options.taxonomy_status + "&taxonomy=" + activeRecordID,
                method: 'post',
                success: function (res) {
                    // window.location.reload();
                    resetMediaAndPosts();
                    checkForUndoFunctionality();
                }
            });
        } else {
            activeRecordID = $(".folder-modal #js-tree-menu .jstree-anchor.jstree-clicked").closest(".jstree-node").attr("id");
            $.ajax({
                url: folders_media_options.ajax_url,
                data: {
                    post_id: folderIDs,
                    action: 'premio_check_for_other_folders',
                    active_folder: activeRecordID,
                    type: folders_media_options.post_type,
                    folder_id: folderID,
                    nonce: folders_media_options.nonce,
                    status: folders_media_options.taxonomy_status,
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
            url: folders_media_options.ajax_url,
            data: {
                copy_from: copyFrom,
                copy_to: copyTo,
                action: 'copy_premio_folders',
                nonce: folders_media_options.nonce,
                post_type: folders_media_options.post_type,
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
        var folderProperty = {
            'folder_id': folders.term_id,
            'folder_count': 0,
            'is_sticky': folders.is_sticky,
            'is_high': folders.is_high,
            'is_locked': folders.is_locked,
            'has_color': folders.has_color,
            'nonce': folders.nonce,
            'slug': folders.slug,
            'is_deleted': 0
        };
        folderPropertyArray.push(folderProperty);
        var folderTitle = folders.title;
        folderTitle = folderTitle.replace(/\\/g, '');
        $('#js-tree-menu').jstree().create_node(folders.parent_id, {
            "id": folders.term_id,
            "text": folderTitle
        }, "last", function () {
            $(".jstree-node[id='" + folders.term_id + "']").attr("data-nonce", folders.nonce);
            $(".jstree-node[id='" + folders.term_id + "']").attr("data-slug", folders.slug);
        });

        if(folders.child.length > 0) {
            for(var i=0; i < folders.child.length; i++) {
                setCopyFolders(folders.child[i]);
            }
        }

        update_js_tree_data();
        make_sticky_folder_menu();
        resetMediaAndPosts();

        $(".folders-undo-notification").removeClass("active");
        $("#paste-message").addClass("active");
        setTimeout(function(){
            $("#paste-message").removeClass("active");
        }, 5000);
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

    function setFolderCount() {
        $("#js-tree-menu .jstree-node").each(function(){
            var folderCount = parseInt($(this).data("count"));
            if(folderCount > 0) {
                $(".jstree-node[id='" + $(this).attr("id") + "'] > a span.premio-folder-count").text(folderCount);
            } else {
                $(".jstree-node[id='" + $(this).attr("id") + "'] > a span.premio-folder-count").text(0);
            }
        });

        if(activeRecordID != "" && activeRecordID != 0) {
            if($(".jstree-node[id='"+activeRecordID+"']").length) {
                $("#js-tree-menu").jstree('select_node', activeRecordID);
                if($(".sticky-folders .sticky-folder-"+activeRecordID+" a").length) {
                    $(".sticky-folders .sticky-folder-" + activeRecordID + " a").addClass("active-item");
                }
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

    function make_sticky_folder_menu() {
        $(".sticky-folders > ul").html("");
        var stickyMenuHtml = "";

        $("#js-tree-menu li.jstree-node.is-sticky").each(function(){
            var folder_ID = $(this).attr("id");
            var folderName = $.trim($("#js-tree-menu").jstree(true).get_node(folder_ID).text);
            var folderCount = $("#"+folder_ID+"_anchor .premio-folder-count").text();
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
        setCustomScrollForFolder();
    }

    function setCustomScrollForFolder() {
        /*var scrollTop = 0;
        if($("#custom-scroll-menu").hasClass("mCustomScrollbar")) {
            var $scrollerOuter  = $( '#custom-scroll-menu' );
            var $dragger        = $scrollerOuter.find( '.mCSB_dragger' );
            var scrollHeight    = $scrollerOuter.find( '.mCSB_container' ).height();
            var draggerTop      = $dragger.position().top;

            scrollTop = draggerTop / ($scrollerOuter.height() - $dragger.height()) * (scrollHeight - $scrollerOuter.height());

            $("#custom-scroll-menu").mCustomScrollbar('destroy');
        }
        var contentHeight = parseInt($(".folder-modal .media-modal-content").height()) - parseInt($(".folder-modal .sticky-wcp-custom-form").height()) - 40;
        if($(".folder-modal #custom-scroll-menu").closest(".media-frame-menu").length) {
            if($(".folder-custom-menu").length && $(".media-frame-menu").length) {
                contentHeight = parseInt($(".folder-modal .media-modal-content").height()) - parseInt($(".folder-modal .sticky-wcp-custom-form").height()) - 40;

                if($(".folder-modal .folder-custom-menu").length && $(".folder-modal .media-modal-content").length) {

                }
            }
        }

        if(contentHeight < 0) {
            contentHeight = 350;
        }

        $("#custom-scroll-menu").mCustomScrollbar({
            axis:"y",
            scrollButtons:{enable:true},
            setHeight: contentHeight,
            theme:"3d",
            scrollbarPosition:"inside",
            scrollInertia: 500,
            mouseWheelPixels: 60
        });
        if(scrollTop != 0) {
            $("#custom-scroll-menu").mCustomScrollbar("scrollTo", scrollTop+"px",{scrollInertia:0});
        }*/
        var contentHeight = $(window).height() - $("#wpadminbar").height() - $(".sticky-wcp-custom-form").height() - 70;
        $("#custom-scroll-menu").height(contentHeight);
        $("#custom-scroll-menu").overlayScrollbars({
            resize : 'none',
            sizeAutoCapable :true,
            autoUpdateInterval : 33,
            x :'scroll',
            clipAlways :false,
            y :'scroll'
        });

        if($(".folder-modal .custom-scroll-menu").hasClass("hor-scroll")) {
            jQuery("#custom-scroll-menu .os-viewport").on("scroll", function () {
                setActionPosition();
            });
            setActionPosition();
        }
    }

    function setActionPosition() {
        jQuery(".folder-modal #js-tree-menu span.folder-actions").css("right", (jQuery(".folder-modal #custom-scroll-menu .horizontal-scroll-menu").width() - jQuery(".folder-modal #custom-scroll-menu .os-viewport").width() - $(".folder-modal #custom-scroll-menu .os-viewport").scrollLeft() - 10));
    }

    function removeMultipleFolderItems() {
        if($("#menu-checkbox").is(":checked")) {
            if($("#js-tree-menu input.checkbox:checked").length > 0) {
                var folderIDs = "";
                var activeItemDeleted = false;
                $("#js-tree-menu input.checkbox:checked").each(function(){
                    folderIDs += $(this).closest("li.jstree-node").attr("id")+",";
                    if($(this).closest("li.jstree-node").hasClass("jstree-clicked")) {
                        activeItemDeleted = true;
                    }
                });
                $(".form-loader-count").css("width", "100%");
                $.ajax({
                    url: folders_media_options.ajax_url,
                    data: "type=" + folders_media_options.post_type + "&action=wcp_remove_muliple_folder&term_id=" + folderIDs+"&nonce="+folders_media_options.nonce,
                    method: 'post',
                    success: function (res) {
                        res = $.parseJSON(res);
                        $(".form-loader-count").css("width", "0px");
                        if(res.status == '1') {
                            isKeyActive = parseInt(res.is_key_active);
                            n_o_file = parseInt(res.folders);
                            $("#current-folder").text(n_o_file);
                            for(i=0; i<res.term_ids.length; i++) {
                                $('.folder-modal #js-tree-menu').jstree().delete_node(res.term_ids[i]);
                            }

                            $("#ttl-fldr").text((4*2)+(4/2));
                            // add_menu_to_list();
                            ajaxAnimation();
                            $(".folder-popup-form").hide();
                            $(".folder-popup-form").removeClass("disabled");
                            resetMediaAndPosts();
                            make_sticky_folder_menu();

                            ajaxAnimation();

                            if(!$(".folder-modal #wcp_folder_"+activeRecordID).length) {
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
                url: folders_media_options.ajax_url,
                data: "type=" + folders_media_options.post_type + "&action=get_folders_default_list",
                method: 'post',
                success: function (res) {
                    res = $.parseJSON(res);
                    // $("#js-tree-menu > ul#space_0").html(res.data);
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
                    if(folders_media_options.show_in_page == "show" && !$(".tree-structure").length) {
                        $(".wp-header-end").before('<div class="tree-structure-content"><div class="tree-structure"><ul></ul><div class="clear clearfix"></div></div></div>');
                    }
                    add_active_item_to_list();
                    triggerInlineUpdate();
                });
            } else {
                $("#wpbody").load(folderCurrentURL + " #wpbody-content", false, function (res) {
                    var obj = { Title: "", Url: folderCurrentURL };
                    history.pushState(obj, obj.Title, obj.Url);
                    if(folders_media_options.show_in_page == "show" && !$(".tree-structure").length) {
                        $(".wp-header-end").before('<div class="tree-structure-content"><div class="tree-structure"><ul></ul><div class="clear clearfix"></div></div></div>');
                    }
                    add_active_item_to_list();
                });
            }
        }
    }

    function update_js_tree_data() {
        add_active_item_to_list();
        make_sticky_folder_menu();
    }
    function update_custom_folder_color_css() {
        $("#custome_folder_color_css").remove();
        var custom_color = "<style id='custome_folder_color_css'>"
        $(folderPropertyArray).each(function (key,val) {
            if(val.has_color != "") {
                if(val.has_color  != folders_media_options.default_color) {
                    custom_color += "li.jstree-node[id='" + val.folder_id + "'] .pfolder-folder-close {color: " + val.has_color + "!important;}";
                }
            }
        });
        custom_color += "</style>";
        $("head").append(custom_color);
    }


    function add_active_item_to_list() {
        if(folderPropertyArray.length) {
            var totalLockedFolders = 0;
            var totalFolders = 0;
            $(".folder-modal #js-tree-menu li.jstree-node").each(function(){
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
                    if(folderPropertyArray[folderPostId]['slug'] == folders_media_options.default_folder) {
                        $(this).addClass("is-current");
                    } else {
                        $(this).removeClass("is-current");
                    }
                }
            });
            update_custom_folder_color_css();
            if(totalLockedFolders > 0 && (totalLockedFolders == $("#js-tree-menu li.jstree-node").length)) {
                $(".lock-unlock-all-folders").addClass("all-folder-locked").removeClass("open-folders").attr("data-folder-tooltip", "Use this to lock a folder's position so it cannot be moved\n\n Click to Unlock all folders");
            } else {
                $(".lock-unlock-all-folders").addClass("open-folders").removeClass("all-folder-locked").attr("data-folder-tooltip", "Use this to lock a folder's position so it cannot be moved\n\n Click to Lock all folders");
            }
        }
    }

    function triggerInlineUpdate() {
        add_active_item_to_list();

        $(".form-loader-count").css("width", "0");
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

        if(folders_media_options.post_type == "attachment") {
            if(!$(".move-to-folder-top").length) {
                $("#bulk-action-selector-top").append("<option class='move-to-folder-top' value='move_to_folder'>"+folders_media_options.lang.MOVE_TO_FOLDER+"</option>");
            }
            if(!$(".move-to-folder-bottom").length) {
                $("#bulk-action-selector-bottom").append("<option class='move-to-folder-bottom' value='move_to_folder'>"+folders_media_options.lang.MOVE_TO_FOLDER+"</option>");
            }
        }
    }

    function resetDDCounter() {
        var currentDDVal = $("#media-attachment-taxonomy-filter").val();
        resetMediaFlag = $.ajax({
            url: folders_media_options.ajax_url,
            data: "type=attachment&action=wcp_get_default_list&active_id=0",
            method: 'post',
            beforeSend: function() {
                if(resetMediaFlag != null) {
                    resetMediaFlag.abort();
                }
            },
            success: function(res) {
                var res = $.parseJSON(res);
                foldersArray = res.taxonomies;
                setFolderCountAndDD();
                setDragAndDropElements();
            }
        });
    }

    function resetMediaCounter() {
        resetMediaFlag = $.ajax({
            url: folders_media_options.ajax_url,
            data: "type=attachment&action=wcp_get_default_list&active_id=0",
            method: 'post',
            beforeSend: function() {
                if(resetMediaFlag != null) {
                    resetMediaFlag.abort();
                }
            },
            success: function(res) {
                var res = $.parseJSON(res);
                foldersArray = res.taxonomies;
                setFolderCountAndDD();
                $(".all-posts .total-count").text(res.total_items);
                $(".un-categorized-posts .total-count").text(res.empty_items);
            }
        });
    }

    function setFolderCountAndDD() {
        add_active_item_to_list();
        make_sticky_folder_menu();
        if($("#media-attachment-taxonomy-filter").length) {
            $("#media-attachment-taxonomy-filter").each(function(){
                folders_media_options.terms = foldersArray;
                var selectedDD = $(this);
                currentDDVal = $(this).val();
                selectedDD.html("<option value='all'>All Folders</option><option value='unassigned'>(Unassigned)</option>");
                lastFolderData = foldersArray;
                for (var i = 0; i < foldersArray.length; i++) {
                    selectedDD.append("<option value='" + foldersArray[i].term_id + "'>" + foldersArray[i].name + " (" + foldersArray[i].trash_count + ")</option>");
                }
                if(uploadFolderID != 0) {
                    $(".jstree-clicked").removeClass("jstree-clicked");
                    $("#js-tree-menu").jstree('select_node', uploadFolderID);
                    selectedDD.val(uploadFolderID).trigger("change");
                } else {
                    selectedDD.val(currentDDVal);
                }
            });
            if($("select.folder_for_media").length) {
                var selectedVal = $("select.folder_for_media").val();
                $("select.folder_for_media option:not(:first-child):not(:last-child)").remove();
                for (var i = 0; i < foldersArray.length; i++) {
                    $("select.folder_for_media option:last-child").before("<option value='" + foldersArray[i].term_id + "'>" + foldersArray[i].name +"</option>");
                }
                if(selectedVal != "") {
                    $(".folder_for_media").val(selectedVal);
                }
            }
        }
        $("span.premio-folder-count").text("");
        $(".folder-count").text("");
        for (i = 0; i < foldersArray.length; i++) {
            if(parseInt(foldersArray[i].trash_count) != 0) {
                $(".jstree-node[id='" + foldersArray[i].term_id + "'] > a.jstree-anchor span.premio-folder-count").text(foldersArray[i].trash_count);
                $(".sticky-folder-"+foldersArray[i].term_id+" .premio-folder-count").text(foldersArray[i].trash_count);
            } else {
                $(".jstree-node[id='" + foldersArray[i].term_id + "'] > a.jstree-anchor span.premio-folder-count").text(0);
                $(".sticky-folder-"+foldersArray[i].term_id+" .premio-folder-count").text(0);
            }
        }

        if(activeRecordID != "") {
            $("#wcp_folder_"+activeRecordID).addClass("active-item");
        }

        if(isItFromMedia) {
            $("#title_"+fileFolderID).trigger("click");
            isItFromMedia = false;
        }

        uploadFolderID = 0;
    }

    function checkForFolderSearch() {
        var searchVal = $.trim($("#folder-search").val());
        $('#js-tree-menu').jstree('search', searchVal);
    }

    function removeFolderFromID(popup_type) {
        var removeMessage = folders_media_options.lang.DELETE_FOLDER_MESSAGE;
        var removeNotice = folders_media_options.lang.ITEM_NOT_DELETED;
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
                    $("#error-folder-popup-message").html(folders_media_options.lang.SELECT_AT_LEAST_ONE_FOLDER);
                    $("#error-folder-popup").show();
                    return;
                } else {
                    if($("#js-tree-menu input.checkbox:checked").length > 1) {
                        removeMessage = folders_media_options.lang.DELETE_FOLDERS_MESSAGE;
                        removeNotice = folders_media_options.lang.ITEMS_NOT_DELETED;
                    }
                }
            }
        }
        $(".folder-popup-form").hide();
        $(".folder-popup-form").removeClass("disabled");
        $("#remove-folder-item").text(folders_media_options.lang.YES_DELETE_IT);
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

        $("#update-folder-data").text(folders_media_options.lang.SUBMIT);
        $(".folder-form-errors").removeClass("active");
        $("#update-folder-item-name").val(folderName);
        $("#update-folder-item").removeClass("disabled");
        $("#update-folder-item").show();
        $("#update-folder-item-name").focus();
        $(".dynamic-menu").remove();
    }

    function addFolder() {
        if(isKeyActive == 0 && n_o_file >= ((4*4)-(3*3)+(4/4)+(8/(2*2)))) {
            $("#folder-limitation-message").html("You've "+"reached the "+((4*4)-(2*2)-2)+" folder limitation!");
            $("#no-more-folder-credit").show();
            return false;
        }

        $("#save-folder-data").text("Submit");
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

    function checkForExpandCollapse() {
        setTimeout(function(){
            var currentStatus = true;
            if($("#js-tree-menu .jstree-node.jstree-leaf").length == $("#js-tree-menu .jstree-node").length) {
                $("#expand-collapse-list").removeClass("all-open");
                $("#expand-collapse-list").attr("data-folder-tooltip",folders_media_options.lang.EXPAND);
                $("#expand-collapse-list .text").text(folders_media_options.lang.EXPAND);
            } else {
                var totalChild = $("#js-tree-menu .jstree-node.jstree-closed").length + $("#js-tree-menu .jstree-node.jstree-open").length;
                if($("#js-tree-menu .jstree-node.jstree-closed").length == totalChild) {
                    $("#expand-collapse-list").removeClass("all-open");
                    $("#expand-collapse-list").attr("data-folder-tooltip", folders_media_options.lang.EXPAND);
                    $("#expand-collapse-list .text").text(folders_media_options.lang.EXPAND);
                } else {
                    $("#expand-collapse-list").addClass("all-open");
                    $("#expand-collapse-list").attr("data-folder-tooltip", folders_media_options.lang.COLLAPSE);
                    $("#expand-collapse-list .text").text(folders_media_options.lang.COLLAPSE);
                }
            }
        }, 500);

        setDragAndDropElements();
    }

    var resetMediaFlag;
    function resetMediaData(loadData) {
        resetMediaFlag = $.ajax({
            url: folders_media_options.ajax_url,
            data: "type=" + folders_media_options.post_type + "&action=wcp_get_default_list&active_id="+activeRecordID,
            method: 'post',
            beforeSend: function() {
                if(resetMediaFlag != null) {
                    resetMediaFlag.abort();
                }
            },
            success: function (res) {
                res = $.parseJSON(res);
                // $("#js-tree-menu > ul#space_0").html(res.data);
                $(".header-posts .total-count").text(res.total_items);
                $(".un-categorised-items .total-count").text(res.empty_items);
                selectedVal = $("#media-attachment-taxonomy-filter").val();
                if(selectedVal != "all" && loadData == 1) {
                    var wp1 = parent.wp;
                    if(wp1.media != undefined) {
                        wp1.media.frame.setState('insert');
                        if(wp1.media.frame.content.get() !== null && typeof(wp1.media.frame.content.get().collection) != "undefined") {
                            wp1.media.frame.content.get().collection.props.set({ignore: (+new Date())});
                            wp1.media.frame.content.get().options.selection.reset();
                        } else {
                            wp1.media.frame.library.props.set({ignore: (+new Date())});
                        }
                    }
                }
                foldersArray = res.taxonomies;
                setFolderCountAndDD();
                setDragAndDropElements();
            }
        });
    }

    function set_default_folders(post_id) {
        $.ajax({
            url: folders_media_options.ajax_url,
            type: 'post',
            data: 'action=save_folder_last_status&post_type='+folders_media_options.post_type+"&post_id="+post_id+"&nonce="+folders_media_options.nonce,
            cache: false,
            async: false,
            success: function(){

            }
        })
    }

    function ajaxAnimation() {
        $(".folder-loader-ajax").addClass("active");
        $(".folder-loader-ajax img").removeClass("active");
        $(".folder-loader-ajax svg#successAnimation").addClass("active").addClass("animated");
        setTimeout(function(){
            $(".folder-loader-ajax").removeClass("active");
            $(".folder-loader-ajax img").addClass("active");
            $(".folder-loader-ajax svg#successAnimation").removeClass("active").removeClass("animated");
        }, 2000);

        make_sticky_folder_menu();
        setDragAndDropElements();
    }

    $(document).ready(function(){
        if(folders_media_options.use_shortcuts == "yes") {
            $(document).on("click", ".view-shortcodes", function (e) {
                e.preventDefault();
                $("#keyboard-shortcut").show();
            });

            $(document).on("keydown", function (e) {
                var isCtrlPressed = (e.ctrlKey || e.metaKey) ? true : false;

                // Shift + N : New Folder
                if(!($("input").is(":focus") || $("textarea").is(":focus"))) {
                    if (e.altKey && (e.keyCode == 78 || e.which == 78)) {
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
                        $("#add-update-folder-title").text(folders_media_options.lang.DUPLICATING_FOLDER);
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
    });
}));
