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
    var orderBy = 'size';
    var orderType = 'DESC';
    var isAjaxLoading = 0;
    var isLimitReached = 1;
    var ajaxLoadVar = null;
    var selectedMediaIds = "";
    var actionList = [
        'remove_data',
        'check_in_content'
    ];
    var currentStep = 0;
    var totalPages = 0;
    var currentPage = 1;


    $(document).ready(function(){

        if(!$(".scan-data").hasClass("is-hidden")) {
            $(".scan-container").removeClass("active");
            $(".scan-progress").addClass("active");
            startScanning();
        }

        $(document).on("click", "#scan-media", function(){
           $(".scan-container").removeClass("active");
           $(".scan-progress").addClass("active");
           startScanning();
        });

        $(document).on("click", ".responsive-table .table thead th a", function(e){
            e.stopPropagation();
            if($(this).hasClass("desc-order")) {
                $(".responsive-table .table thead th a").removeClass("desc-order").removeClass("asc-order");
                $(this).addClass("asc-order");
                orderType = "ASC";
            } else {
                $(".responsive-table .table thead th a").removeClass("asc-order").removeClass("desc-order");
                $(this).addClass("desc-order");
                orderType = "DESC";
            }
            orderBy = $(this).data("sort");
            if(ajaxLoadVar != null) {

            }
            isLimitReached = 1;
            isAjaxLoading = 0;
            currentPage = 1;
            $("#media-table .table tbody").html("");
            $(".media-selection").removeClass("active");
            getMediaFiles();
            $("#scan-for-media").addClass("active");
            $("#media-table").removeClass("active");
        });

        $(document).on("click", ".single-checkbox", function(){
            checkForParentCheckbox();
        });

        $(document).on("click", ".cancel-selections", function(e){
            e.preventDefault();
            $(".single-checkbox").prop("checked", false);
            checkForParentCheckbox();
        });

        $(document).on("click", ".parent-checkbox", function(){
            if($(this).is(":checked")) {
                $(".single-checkbox").prop("checked", true);
            } else {
                $(".single-checkbox").prop("checked", false);
            }
            checkForParentCheckbox();
        });

        $(document).on("click", ".remove-media-button", function(e){
            e.preventDefault();
            selectedMediaIds = $(this).closest("tr").data("id");
            $(".media-clean-delete-box-content").removeClass("form-loading");
            $("#show_delete_box").show();
        });

        $(document).on("click", ".remove-records", function(e){
            e.preventDefault();
            $(".media-clean-delete-box-content").removeClass("form-loading");
            $("#show_delete_form_box").show();
        });

        $(document).on("click", "#delete_button", function (e) {
            e.preventDefault();
            $(".media-clean-delete-box-content").removeClass("form-loading");
            $("#show_delete_box .media-clean-delete-box-content").addClass("form-loading");
            var productId = selectedMediaIds;
            var productNonce = $("#col-"+selectedMediaIds).data("token");
            $.ajax({
                url: folders_settings.ajax_url,
                data: {
                    action: 'wcp_remove_scanned_media',
                    attachment_id: productId,
                    nonce: productNonce
                },
                type: 'post',
                dataType: 'json',
                success: function(response) {
                    if(response.status) {
                        $(".media-clean-delete-box").hide();
                        $(".media-clean-delete-box-content").removeClass("form-loading");
                        $("#col-"+response.attachment_id).hide("slow", function(){
                            $(this).remove();
                            checkForParentCheckbox();
                        });
                    } else {

                    }
                }
            })
        });

        $(document).on("click", "#delete_form_button", function (e) {
            e.preventDefault();
            $(".media-clean-delete-box-content").removeClass("form-loading");
            $("#show_delete_form_box .media-clean-delete-box-content").addClass("form-loading");
            var productIds = [];
            $("#media-table tbody input.single-checkbox:checked").each(function(){
                productIds.push($(this).val());
            });
            $.ajax({
                url: folders_settings.ajax_url,
                data: {
                    action: 'wcp_remove_multiple_scanned_media',
                    attachment_ids: productIds,
                    nonce: folders_settings.nonce
                },
                type: 'post',
                dataType: 'json',
                success: function(response) {
                    if(response.status) {
                        $(".media-clean-delete-box").hide();
                        $(".media-clean-delete-box-content").removeClass("form-loading");
                        for(var i=0; i<response.attachment_ids.length; i++) {
                            $("#col-"+response.attachment_ids[i]).hide("slow", function(){
                                $(this).remove();
                                checkForParentCheckbox();
                            });
                        }
                        checkForParentCheckbox();
                    } else {

                    }
                }
            })
        });

        $(document).on("click", "#agree_media_terms", function (e) {
            if($(this).is(":checked")) {
                $(".confirm-button button").prop("disabled", false);
            } else {
                $(".confirm-button button").prop("disabled", true);
            }
        });

        $(document).on("click", ".media-clean-delete-box-overlay", function () {
            $(".media-clean-delete-box").hide();
        });

        $(document).on("click", ".media-clean-delete-box-content", function (e) {
            e.stopPropagation();
        });

        $(document).on("click", ".close-icon", function () {
            $("#show_delete_box").hide();
            $("#show_delete_form_box").hide();
        });

        $(document).on("click", ".cancel-button", function () {
            $("#show_delete_box").hide();
            $("#show_delete_form_box").hide();
        });
    });

    $(window).on("scroll", function(){
        checkForPaginationData();
    });

    function checkForPaginationData() {
        if($(".scan-data").hasClass("is-hidden") && $("#load-media-data").length && isLimitReached) {
            if($("#load-media-data").offset().top - $(window).height() - $(window).scrollTop() < 0) {
                getMediaFiles();
            }
        }
    }

    function checkForParentCheckbox() {
        if($(".single-checkbox").length && $(".single-checkbox").length == $(".single-checkbox:checked").length) {
            $(".parent-checkbox").prop("checked",true).removeClass("partial");
        } else if($(".single-checkbox:checked").length) {
            $(".parent-checkbox").prop("checked",false).addClass("partial");
        } else {
            $(".parent-checkbox").prop("checked",false).removeClass("partial");
        }
        if($(".single-checkbox:checked").length) {
            $(".media-selection").addClass("active");
            if(folders_settings.trash_enabled == 1) {
                if ($(".single-checkbox:checked").length == 1) {
                    $(".remove-records .button-text").text("Move 1 Record to Trash");
                } else {
                    $(".remove-records .button-text").text("Move " + $(".single-checkbox:checked").length + " Records to Trash");
                }
            } else {
                if ($(".single-checkbox:checked").length == 1) {
                    $(".remove-records .button-text").text("Delete 1 Record Permanently");
                } else {
                    $(".remove-records .button-text").text("Delete " + $(".single-checkbox:checked").length + " Records Permanently");
                }
            }
        } else {
            $(".media-selection").removeClass("active");
        }
    }


    function startScanning() {
        if(currentStep >= folders_settings.steps.length) {
            $(".scan-data").addClass("is-hidden").hide();
            currentPage = 1;
            getMediaFiles();
            return;
        }
        if(totalPages == 0) {
            $(".scan-meter span").text("0%");
            $(".scan-meter").css("width", "0%");
        } else {
            var scanProgress = parseInt(currentPage/totalPages*100);
            $(".scan-meter span").text(scanProgress+"%");
            $(".scan-meter").css("width", scanProgress+"%");
        }
        $(".scan-step").html(folders_settings.step+" "+(currentStep+1)+"/"+(folders_settings.steps).length);
        $(".scan-title").html(folders_settings.steps[currentStep].title);

        $.ajax({
            url: folders_settings.ajax_url,
            data: {
                action: 'folders_scan_for_files',
                scan_action: folders_settings.steps[currentStep].action_name,
                nonce: folders_settings.nonce,
                current: currentPage,
                total: totalPages
            },
            type: 'post',
            dataType: 'json',
            success: function (response) {
                if (response.status) {
                    if(parseInt(response.current) >= parseInt(response.total)) {
                        totalPages = 0;
                        currentPage  = 1;
                        currentStep = currentStep+1;
                        startScanning();
                    } else if (parseInt(response.current) < parseInt(response.total)) {
                        totalPages = parseInt(response.total);
                        currentPage = parseInt(response.current)+1;
                        startScanning();
                    }
                } else {

                }
            }
        });
    }

    function getMediaFiles() {
        if(isAjaxLoading) {
            return;
        }
        isAjaxLoading = 1;
        $(".folder-ajax-loading").addClass("active");
        $.ajax({
            url: folders_settings.ajax_url,
            data: {
                action: 'get_folders_scanned_files',
                nonce: folders_settings.nonce,
                page_number: currentPage,
                order_by: orderBy,
                order: orderType,
            },
            type: 'post',
            dataType: 'json',
            success: function (response) {
                if (response.status) {
                    $("#scan-for-media").removeClass("active");
                    $("#media-table").addClass("active");
                    $(".folder-ajax-loading").removeClass("active");
                    if (response.files.length) {
                        currentPage++;
                        isAjaxLoading = 0;
                        for (i = 0; i < response.files.length; i++) {
                            if(!$("#col-"+response.files[i]['id']).length) {
                                $("#media-table .table tbody").append("<tr data-id='" + response.files[i]['id'] + "' data-token='" + response.files[i]['token'] + "' id='col-" + response.files[i]['id'] + "'>" +
                                    "<td class='checkbox-col'><span class='folder-checkbox'><input type='checkbox' class='single-checkbox' id='file_" + response.files[i]['id'] + "' value='" + response.files[i]['id'] + "'><label for='file_" + response.files[i]['id'] + "'></label></span></td>" +
                                    "<td><img src='" + response.files[i]['thumb_url'] + "' class='folder-image-icon' alt='" + response.files[i]['id'] + "' /></td>" +
                                    "<td><a href='" + response.files[i]['edit_url'] + "' target='_blank'>" + response.files[i]['id'] + "</a></td>" +
                                    "<td><a href='" + response.files[i]['edit_url'] + "' target='_blank'>" + response.files[i]['title'] + "</a><span class='file-path'>" + response.files[i]['path'] + "</span><a href='#' class='remove-media-button'>" + folders_settings.button_text + "</a></td>" +
                                    "<td>" + response.files[i]['file_size'] + "</td>" +
                                    "<td>" + response.files[i]['date'] + "</td>" +
                                    "</tr>");
                            }
                        }
                        $(".parent-checkbox").prop("checked",false).removeClass("partial");
                    } else {
                        isLimitReached = 0;
                    }
                }
            }
        });
    }
}));
