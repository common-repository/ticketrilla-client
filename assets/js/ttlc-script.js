jQuery(document).ready(function ($) {
    "use strict";
	var screen = $(window).width();

	var screenXs = 480,
		screenSm = 768,
		screenMd = 992,
		screenLg = 1200;

	$(window).resize(function() {
		screen = $(window).width();
	});

	var scrollWidth = window.innerWidth - document.body.clientWidth;

	$(document).on("shown.bs.modal", ".modal", function () {
        if($("body").is(".modal-open")) {
            $("body").css("cssText", "padding-right: " + scrollWidth + "px !important;");
        }
    });

	var Selector = {
		wrapper       : '#ttlc-container',
		contentWrapper: '.ttlc-wrapper',
		editorID : 'ttlc-ckeditor',
	}
	
	var TTLC = {
		attachments : [],
	}
	
	history.replaceState({url: window.location.href}, null);

	function initCkeditor() {
		if ( $( '#' + Selector.editorID ).length ) {
			CKEDITOR.replace( Selector.editorID );
		}
		
	}
	
	function reloadWrapper( url = false, pushState = true ){
		if( url === false ) {
			var url = window.location.href;
		}
		$(Selector.wrapper).addClass('ttlc__disabled');
		$( Selector.wrapper ).load( url.split('#')[0] + ' ' + Selector.contentWrapper, function(){
			if ( pushState ) {
				history.pushState({url: url}, null, url);			
			}
			$(Selector.wrapper).removeClass('ttlc__disabled');
			initCkeditor();
		} );

	}
	
	window.onpopstate = function(e){
		if( e.state ) {
			reloadWrapper(e.state.url, false);
		}
	}
	
	initCkeditor();

	$('a[href="#"]').live('click', function(e) {
		e.preventDefault();
	});

	/* =================== Scroll ==================== */

	$('a[data-scroll]').live('click', function(){
		var target = $(this.getAttribute('href'));
		if( target.length ) {
			$('html, body').stop().animate({
				scrollTop: target.offset().top - 82
			}, 400);
		}
	});

	/* ============== Ticket Row Click =============== */

	$('.ttlc__tickets-inner table > tbody > tr').live('click', function(){
		var url = $(this).children('td:nth-child(2)').children('a').attr('href');
		document.location.href = url;
	});	

	/* ================ Check Server ================= */

	$('.ttlc-product-server-check').live('click', function(e){
		e.preventDefault();
		var form = $(this).parents('.ttlc-product-server-check-form');
		$(form).trigger('submit');
	});

	$('.ttlc-product-server-check-form').live('submit', function(e){
		e.preventDefault();
		var form = $( this ); console.log(form);
		var modal = form.parents('.modal-dialog');
		var data = form.serializeArray();
		var action = 'ttlc/server/check';
		modal.addClass('ttlc__disabled');
		data.push( {
			'name' : 'action',
			'value' : action,
		} );

		$.post( {
			'url' : ajaxurl,
			'data' : data,
		}).done( function( response ) {
			if ( response ) {
				modal.replaceWith( $(response).find('.modal-dialog') );
			}
		});
	});

	/* ================ Product Dynamic Licenses ================= */

	$('.ttlc-license-select').live('change', function(){
		var form = $(this).parents('form');
		var target = $(this).val();
		$('.ttlc-license-fields-' + target, form).removeClass('collapse').find('input, textarea, select').prop('disabled', false);
		$('.ttlc-license-fields-' + target, form).siblings().addClass('collapse').find('input, textarea, select').prop('disabled', true);
	});
	
	$('.ttlc-license-field-checkbox').live('click', function(){
		var target = '#' + $(this).val();
		var disabled = ! $(this).prop('checked');
		$(target).prop('disabled', disabled).parents('.form-group').toggleClass('collapse');
	});

	/* ================ Save Product ================= */
	
	$('.ttlc-product-save-btn').live('click', function(){
		var form = $(this).parents('.modal-content').find('.tab-pane.active form');
		form.trigger('submit');
	});

	$('.ttlc-product-settings-form').live('submit', function(e){
		e.preventDefault();
		var form = $(this);
		var modal = form.parents('.modal');
		var modalDialog = $('.modal-dialog', modal);
		var data = form.serializeArray();
		var action = 'ttlc/product/save';
		modalDialog.addClass('ttlc__disabled');
		data.push( {
			'name' : 'action',
			'value' : action,
		} );

		$.post( {
			'url' : ajaxurl,
			'data' : data,
		}).done( function( response ) {
			if ( response.status ) {
				modal.modal('hide');
				$( modal ).one('hidden.bs.modal', function(){
					reloadWrapper();
				});
			} else {
				form.replaceWith( response.data );
			}
		}).fail( function( response ) {
			alert('Ajax Error');
		}).always( function(){
			modalDialog.removeClass('ttlc__disabled');
		});
	});

	/* ================ Trash Product ================= */
	
	$('.ttlc-product-trash, .ttlc-product-untrash').live('click', function(e){
		e.preventDefault();
		var data = $(this).attr('href').split('?')[1];
		$(Selector.wrapper).addClass('ttlc__disabled');
		$.post( {
			'url' : ajaxurl,
			'data' : data,
		} ).done( function( response ) {
			if ( response.status ) {
				reloadWrapper();
			} else {
				alert(response.data);
				$(Selector.wrapper).removeClass('ttlc__disabled');
			}
		}).fail( function(){
			$(Selector.wrapper).removeClass('ttlc__disabled');
			alert('Ajax Error');
		});
	});

	/* ============ Change Password Input ============ */

	$('.ttlc-password-toggle').live('click', function(){
		$(this).toggleClass('active').children('i').toggleClass('fa-eye-slash').toggleClass('fa-eye');
		var input = $('[name=password]', $(this).parents('.form-group'));
		var type = input.attr('type');
		
		if (type === 'password') {
			input.attr('type', 'text');
		} else {
			input.attr('type', 'password');			
		}			
		
	});

	/* ============ Password Reset ============ */

	$('.ttlc-product-settings-password-reset').live('submit', function(e){
		e.preventDefault();
		var form = $(this);
		var step = form.parents('.modal-content');
		var stepID = step.attr('id');
		var nextStep = step.next();
		var data = form.serializeArray();
		var action = 'ttlc/password/reset';
		step.addClass('ttlc__disabled');
		data.push( {
			'name' : 'action',
			'value' : action,
		} );

		$.post( {
			'url' : ajaxurl,
			'data' : data,
		}).done( function( response ) {
			if ( response.status ) {
				var emailLogin = $('[name=email_login]', form).val();
				$('[name=email_login]', nextStep).val( emailLogin );
				step.removeClass('in');
				nextStep.addClass('in');
				$('.has-error', step).removeClass('has-error');
				$('.help-block', step).remove();
				if ( response.data.selector && response.data.value ) {
					$(response.data.selector).val(response.data.value);
				}
			} else {
				if ( response.data ) {
					form.replaceWith( $(response.data).find('#' + stepID + ' form') );
				}
			}
		}).fail( function( response ) {
			alert('Ajax Error');
		}).always( function(){
			step.removeClass('ttlc__disabled');
		});
	});	

	/* ============ Tabs ============ */

	$('.ttlc-tabs a').live('click', function(){
		if ( ! $(this).hasClass('disabled') ) {
			$( this ).addClass('active').siblings('.active').removeClass('active');
		}
	});

	$('.ttlc-modal-nav').live('click', function(){
		var modal = $( $(this).attr('href') );
		if ( modal.length ) {
			modal.addClass('in');
			modal.siblings().removeClass('in');
		}
	});
	
	/* ============ Filter / Pagination ============ */
	
	$('.ttlc__filter a, .ttlc-pagination a').live('click', function(e){
		e.preventDefault();
		var url = $(this).attr('href');
		reloadWrapper( url );
	});

	/* ============ Pagination Ticket ============ */

	$('.ttlc-pagination-ticket a').live('click', function(e){
		e.preventDefault()
		if ( ! $(this).hasClass( 'ttlc-load-more-ticket' ) ) {
			var url = $(this).attr('href');
			reloadWrapper( url );
		}
	});

	/* ============ Load More Ticket ============ */
	
	$('.ttlc-load-more-ticket').live('click', function(e){
		e.preventDefault();
		var url = $(this).attr('href');
		$(Selector.wrapper).addClass('ttlc__disabled');
		$.get({
			url : url
		}).done(function(response){
			if ( response ) {
				var html = $(response);
				$('.ttlc__tickets-responses').append( html.find('.ttlc__tickets-responses').children() );
				$('.ttlc-pagination-ticket').replaceWith( html.find('.ttlc-pagination-ticket') );
			} else {
				alert('Ajax Error');
			}
			$(Selector.wrapper).removeClass('ttlc__disabled');
		});
	});

	/* ============ Sort Responses ============ */
	
	$('.ttlc__tickets-sort a').live('click', function(e){
		e.preventDefault();
		if ( ! $(this).hasClass( 'disabled' ) ) {
			var url = $(this).attr('href');
			reloadWrapper( url );
		}
	});

	/* ============ Add Ticket ============ */

	$(document).on( 'submit', '#ttlc-add-ticket', function (e) {
		e.preventDefault();
		var form = e.target;
		var formData = new FormData( form );
		var action = 'ttlc/add/ticket';
		var attachments = $('#ttlc-attachments');

		$(Selector.wrapper).addClass('ttlc__disabled');

		formData.append( 'action', action );
		formData.delete('attachment' );
		TTLC.attachments.forEach( function( el ) {
			formData.append( 'attachment[]', el, el.name );
		} );
		$.ajax( {
			type : 'post',
			url : ajaxurl,
			data : formData,
			processData : false,
			contentType : false
		} ).done( function( response ) {
			if( response.status ) {
				if ( response.data && response.data.ticket_id ) {

					if ( response.data.ticket_parent ) {
						// Response
						reloadWrapper();
						TTLC.attachments = [];
					} else {
						// Ticket
						window.location.replace( window.location.href.split('#')[0] + '&ticket_id=' + response.data.ticket_id );
					}

				}
			} else {
				if ( response.data ) {
					var formUpdate = $( response.data );
					$('#ttlc-attachments', formUpdate).replaceWith( attachments );
					$( form ).replaceWith( formUpdate );

					setTimeout( function(){
						initCkeditor();
					}, 200);
				} else {
					alert( 'error' );
				}
				$(Selector.wrapper).removeClass('ttlc__disabled');
			}
		} ).fail( function( response ) {
			$(Selector.wrapper).removeClass('ttlc__disabled');
			alert( 'error' );
		} );
	} );

	/* ============ Manual Attachment Download / Reload ============ */
	
	$(document).on( 'click', '.ttlc-manual-attachment-download, .ttlc-attachment-reload', function (e) {
		e.preventDefault();
		var el = $( e.target ).closest( 'a' );
		var form = el.find('form');
		var data = form.serializeArray();
		var item = el.closest('li');
		var ext_id = item.data('attachment-external-id');
		var loadingItem = item.siblings('.ttlc-attachment-loading-template').clone().removeClass('hidden ttlc-attachment-loading-template');
		var errorItem = item.siblings('.ttlc-attachment-error-template').clone().removeClass('hidden ttlc-attachment-error-template');
		$('.ttlc-attachment-reload', errorItem).append(form);
		item.replaceWith(loadingItem);
		$('.progress-bar', loadingItem).css('width', '0%');
		$.post({
			url : ajaxurl,
			data : data,
			xhr : function () {
			    var xhr = new window.XMLHttpRequest();
			    //Upload progress
			    xhr.upload.addEventListener('progress', function(evt){
			      if (evt.lengthComputable) {
			        var percentComplete = Math.round(evt.loaded / evt.total * 50);
			        $('.progress-bar', loadingItem).css('width', percentComplete + '%');
			      }
			    }, false);
			    //Download progress
			    xhr.addEventListener('progress', function(evt){
			      if (evt.lengthComputable) {
			        var percentComplete = Math.round(evt.loaded / evt.total * 100);
			        $('.progress-bar', loadingItem).css('width', percentComplete + '%');
			      }
			    }, false);
			    return xhr;
			},
		}).done( function( response ) {
			if ( response.status && response.data ) {
				setTimeout( function(){
					loadingItem.replaceWith(response.data)
					$('[data-attachment-external-id="' + ext_id + '"]').replaceWith(response.data);
				}, 600)
			} else {
				loadingItem.replaceWith(errorItem);
			}
		}).fail(function(response){
			loadingItem.replaceWith(errorItem);
		});

	} );

	/* ============ Attachments on Adding Ticket ============ */
	
	$(document).on( 'change', '#ttlc-ticket-attachment', function (e) {
		var input = e.target;
		for (var i = 0, numFiles = input.files.length; i < numFiles; i++) {
			var newFile = input.files[i];				
			var compare = TTLC.attachments.filter( File => File.name === newFile.name );
			if ( ! compare.length ) {
				TTLC.attachments.push( newFile );
				var fileSize;
				if ( newFile.size >= 1000 ) {
					if ( newFile.size >= 1000000 ) {
						fileSize = Math.ceil( newFile.size / 1000000 ) + ' MB';
					} else {
						fileSize = Math.ceil( newFile.size / 1000) + ' KB';
					}
				} else {
					fileSize = Math.ceil(newFile.size) + ' B';
				}
				var fileName = newFile.name.length > 20 ? newFile.name.substr( 0, 20) + '...' : newFile.name;
				var attachmentBox = $( '.ttlc-attachment-template' ).clone().removeClass( 'hidden ttlc-attachment-template' );
				attachmentBox.find( '.size' ).text( fileSize );
				attachmentBox.find( '.title' ).text( fileName );
				attachmentBox.find( '.ttlc-ticket-attachment-delete' ).data( 'file-name', newFile.name );
				attachmentBox.prependTo( '#ttlc-attachments' );
			}
		}
		input.value = '';
	} );

	/* ============ Attachment Delete on Adding Ticket ============ */
	
	$(document).on( 'click', '.ttlc-ticket-attachment-delete', function (e) {
		e.preventDefault();
		var el = $( e.target ).closest( 'a' );
		var fileName = el.data( 'file-name' );
		el.parents( 'li' ).remove();
		for (var i = 0; i < TTLC.attachments.length; i++) {
			if ( TTLC.attachments[i].name === fileName ) {
				TTLC.attachments.splice( i, 1 );
			}
		}
	} );

	/* ============ Attachments Settings ============ */

	$('#ttlc-attachment-settings').live('submit', function(e){
		e.preventDefault();
		var form = $(this);
		var data = form.serializeArray();
		$(Selector.wrapper).addClass('ttlc__disabled');
		$.post({
			url : ajaxurl,
			data : data,
		}).done(function(response){
			if( response.data ) {
				form.replaceWith(response.data);
			} else {
				$('.state', form).addClass('text-danger').html('Unknown Error');
			}
		}).fail(function(){
			$('.state', form).addClass('text-danger').html('Unknown Error');
		}).always(function(){
			$(Selector.wrapper).removeClass('ttlc__disabled');
		});
	});
	
	$(document).on('click', '#ttlc-attachment-settings [type=submit]', function(e){
		if ($(this).hasClass('disabled')) {
			e.preventDefault();
		}
	});
	
	$(document).on('change', '.ttlc-settings input', function(){
		var form = $(this).parents('form');
		var state = $('.state', form);
		state.addClass('text-warning').removeClass('text-success text-danger').text(ttlcText.waiting_save);
		$('[type=submit]', form).removeClass('disabled');
	});
	
	/* ============ Ticket Open / Close ============ */

	$('.ttlc-ticket-edit').live('click', function(e){
		e.preventDefault();
		var form = $(this).find('form');
		var data = form.serializeArray();
		$.post( {
			'url' : ajaxurl,
			'data' : data,
		}).done( function( response ) {
			reloadWrapper();
		}).fail( function(){
			alert('Ajax error');
		});
	});
	
});

