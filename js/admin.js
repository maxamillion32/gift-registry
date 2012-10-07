/**
Copyright 2012 Sliverware Applications, Inc

This file is part of the WordPress Gift Registry Plugin.

WordPress Gift Registry Plugin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

WordPress Gift Registry Plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with WordPress Gift Registry Plugin.  If not, see <http://www.gnu.org/licenses/>.
*/

jQuery(document).ready(function ($) {
	/* You can safely use $ in this code block to reference jQuery */
    var itemForm = $('#registry_item_form'),
        itemLightbox = $('#gr_item_lightbox'),
        placeholder_img = $('#img-preview').attr('src');

        jQuery.validator.addMethod("price", function(value, element) {
                return this.optional(element) || /^\$?[0-9]*(\.[0-9]{1,2})?$/.test(value);
                }, "Please specify a valid price");

        jQuery.validator.addMethod("scriptless", function(value, element) {
            return this.optional(element) || !/<\/?script[^>]*>/gi.test(value);
    }, "Script tags are not allowed");

    itemForm.validate({
        submitHandler: function(form) { // fires when form is valid
            var callback = $( 'input[name="action"]', $(form) ).val() == 'add_registry_item' ? addItemCallback : updateItemCallback,
		        imgErr = $('.gr-img-preview .gr-error');

            $( form ).addClass( 'loading' );

            if ( imgErr[0] ) {
                $('#img_url').select();
            } else {
                $.ajax({
                    type: 'POST',
                    url: GR.Data.ajaxUrl,
                    data: $("#registry_item_form").serialize(),
                    success: callback
                });
            }
        },
        rules: {
            title: { required: true, scriptless: true },
            descr: { scriptless: true },
            info_url: { url: true },
            img_url: { url: true },
	        qty_requested: { required: true, min: 1 },
            price: { required: true, price: true }
        }
    });

    $('#save_options_btn').click(function() {
        $("#gr_options_form").submit();
    });

    $("#gr_options_form").validate({
        submitHandler: function(form) { // fires when form is valid
            $( form ).addClass( 'loading' );

            $.ajax({
                type: 'POST',
                url: GR.Data.ajaxUrl,
                data: $(form).serialize(),
                success: function( data ) {
                    var response = $.parseJSON( data ), err_field;

                    $( form ).removeClass( 'loading' );

                    if ( response.err ) {
                        err_field = $('[name=' + response.field_name + ']');
                        err_field.closest('li').addClass('gr_page_err');
                        err_field.focus();
                        GR.Alert(response.msg, { error: true });
                    } else {
                        $('li.gr_page_err').removeClass('gr_page_err');
                        $('#item_currency_symbol').html( response.currency.symbol );

                        GR.Alert("Options saved successfully");
                    }
                }
            });
        },
        rules: {
            paypal_email: { required: true, email: true }
        }
    });

    $("#gr_messages_form").validate({
        submitHandler: function(form) { // fires when form is valid
            $( form ).addClass( 'loading' );

            $.ajax({
                type: 'POST',
                url: GR.Data.ajaxUrl,
                data: $(form).serialize(),
                success: function( data ) {
                    $( form ).removeClass( 'loading' );
                    GR.Alert("Message options saved successfully");
                }
            });
        },
        rules: {
            list_link_text: { required: true, scriptless: true },
            cart_link_text: { required: true, scriptless: true },
            gift_button_text: { required: true, scriptless: true }
        }
    });

    $("#gr_auth_form").validate({
        submitHandler: function(form) { // fires when form is valid
            $.ajax({
                type: 'POST',
                url: GR.Data.ajaxUrl,
                data: $(form).serialize(),
                success: function( data ) {
                    var response = $.parseJSON( data ),
                        _key;

                    $('#gr_auth_form').removeClass('loading');

                    if ( !response.error ) {
                        GR.Alert( response.message );
                        _key = 'verified';
                    } else {
                        GR.Alert( response.message, { error: true } );
                        _key = 'unverified';
                    }

                    $('#gr_auth_para').html( GR.Messages.auth_para[_key] );
                    $('#gr_auth_status_wrap').html( GR.Messages.auth_status[_key] );
                }
            });
        }
    });

    $('#save_messages_btn').click(function() {
        $("#gr_messages_form").submit();
    });



    $('#save_auth_btn').click(function() {
        $("#gr_auth_form").addClass('loading').submit();
    });


    function addItemCallback( data ) {
        var title = $('#title').val();

        $( '#registry_item_form' ).removeClass( 'loading' );

        $('#registry_items tr.gr_info').remove();
        $('#registry_items').append( data );
        itemForm[0].reset();
        $('#descr').text('');

        resetPlaceholderImage();

        $('#registry_items tr span.delete a').click( deleteItem );
        $('#registry_items tr span.edit a').click( editItem );
        
        GR.Alert("'" + title + "' has successfully been added to your list of registry items");
    }

    function updateItemCallback( data ) {
        // this is secure because inputs are validated
        var itemRow = $('#item_row_' + itemForm[0].current_id.value),
            title = $('#title').val();

        $('.gr_item_title', itemRow).html( title );
        $('.gr_item_qty_req', itemRow).html( $('#qty_requested').val() );
        $('.gr_item_price', itemRow).html( $('#price').val().replace('$', '') );

        clearItemForm();
        resetPlaceholderImage();

        GR.Alert("'" + title + "' has been successfully updated");
    }

    function resetPlaceholderImage() {
        var imgHtml = '<img src="' + placeholder_img + '" height="75px" width="115px" />';
        $('#img-preview-wrap').html(imgHtml);
        $('.gr-img-preview').removeClass('populated');
    }

    function deleteItem( e ) {
        var row = $(this).parents('#registry_items tr'),
            title = $('.gr_item_title', row).html(),
            id = row.data('registry_item_id'),
            data = {
                action: 'delete_registry_item',
                item_id: id
            };

        $.ajax({
            type: 'POST',
            url: GR.Data.ajaxUrl,
            data: data,
            success: function( data ) {
                var itemTable = $('#registry_items'),
                    rows;
                
                $(row).remove();

                rows = $('tr', itemTable);

                if ( rows.length == 1 ) {
                    itemTable.append("<tr class='gr_info'><td colspan=4>You have not added any items to your registry list. Get started by using the Add Registry Item form above!</td></tr>")
                }

                GR.Alert("'" + title + "' has successfully been deleted from your registry items");
            }
        });

        e.preventDefault();
        e.stopPropagation();
    }

    $('#registry_items tr span.delete a').click( deleteItem );

    function editItem( e ) {
        var row = $(this).parents('#registry_items tr'),
            id = row.data('registry_item_id'),
            data = {
                action: 'get_registry_item',
                item_id: id
            };

        $.ajax({
            type: 'GET',
            url: GR.Data.ajaxUrl,
            data: data,
            success: function( data ) {
                var item = $.parseJSON( data );

                GR.FormMap.map(item);
                $('#gr_item_form_title').html("Editing '" + item.title + "'");
                $('#save_item_btn').val('Update Item');
                $('#registry_item_form input[name="action"]').val('update_registry_item');
                $('#registry_item_form input[name="current_id"]').val(item.id);

                setThumbnail( item.img_url );
                itemLightbox.lightbox_me( {
                    centered: true,
                    onLoad: function() {
                        itemForm.addClass('editing');
                        $('#title').select();
                    }
                } );
            }
        });

        e.preventDefault();
        e.stopPropagation();
    }

    $('#registry_items tr span.edit a').click( editItem );

    function clearItemForm() {
        itemForm[0].reset();
	    $('#descr').text('');
        itemForm.removeClass('editing').removeClass('loading');

        $('#gr_item_form_title').html("Add A Registry Item");
        $('#save_item_btn').val('Add Item');
        $('#registry_item_form input[name="action"]').val('add_registry_item');
        $('#registry_item_form input[name="current_id"]').val('');

        var imgHtml = '<img src="' + placeholder_img + '" height="75px" width="115px" />';
        $('#img-preview-wrap').html(imgHtml);
        $('.gr-img-preview').removeClass('populated');
    }

    $('#clear_item_btn').click( clearItemForm );

    function setThumbnail(url) {
        var imgPreview = $('#img-preview-wrap'),
	    imgPreload = $('#gr-img-preload');
        
        $('.gr-img-preview').addClass('populated');
        imgPreview.addClass('loading');
        imgPreview.html('');

        imgPreload.load(function() {
            var size = GR.ConstrainedSize(this.clientWidth, this.clientHeight, 115, 75);
            var imgHtml = '<img src="' + url + '" height="' + size.height + 'px" width="' + size.width + 'px" />';

            imgPreview.removeClass('loading');
            imgPreview.html(imgHtml);

	        imgPreload.unbind();
        }).error(function() {
            imgPreview.removeClass('loading');
            imgPreview.html('<span class="gr-error">The url provided is not a valid image. Please double-check the url.</span>');

            imgPreload.unbind();
        }).attr('src', url);
    }

    $('#img_url').change(function() {
        var url = this.value;
        setThumbnail( url );
    });

    $("#img_url").bind("keypress", function(e) {
        if (e.keyCode == 13) {
	    $('#qty_requested').select();
	        return false;
        }
    });

    $('a.clear_img').click(function() {
        var imgHtml = '<img src="' + placeholder_img + '" height="75px" width="115px" />';
        $('#img_url').val('');
        $('#img-preview-wrap').html(imgHtml);
        $('.gr-img-preview').removeClass('populated');
    });

    $('#registry_orders a.order_items').click(function( e ) {
        var row = $(this).closest('tr'),
            orderId = row.data('order_id'),
            data = {
                action: 'get_order_items',
                order_id: orderId
            },
            lb = $('#gr_lightbox');

        e.stopPropagation();
        e.preventDefault();

        lb.lightbox_me({ centered: true });
        
        $.ajax({
            type: 'GET',
            url: GR.Data.ajaxUrl,
            data: data,
            success: function( data ) {
                lb.html( data );
            }
        });
    });

    $('.gr-admin-form input, .gr-admin-form textarea, .gr-admin-form select').focus(function() {
        $(this).closest('li').addClass('gr_active');
    }).blur(function() {
        $(this).closest('li').removeClass('gr_active');
    });

    $('.gr_quick_start_toggle').click( function( e ) {
        var qs = $('#gr_quick_start'),
            qs_link = $('#gr_show_quick_start');

        if ( qs.is(':visible') ) {
            qs_link.text('Show Quick Start');
            $.cookie('GR_quick_start_state', 'hidden' );
        } else {
            qs_link.text('Hide Quick Start');
            $.cookie('GR_quick_start_state', '' );
        }

        qs.slideToggle();

        e.preventDefault();
        e.stopPropagation();
    });

    $('#gr_add_items_btn').click(function( e ) {
        e.preventDefault();
        e.stopPropagation();

        clearItemForm();
        itemLightbox.lightbox_me({
            centered: true,
            onLoad: function() {
                $('#title').select();
            }
        });
    });

    $('.gr_close').click(function( e ) {
        e.preventDefault();
        e.stopPropagation();

        itemLightbox.trigger('close');
    });
});

