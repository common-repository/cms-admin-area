// Uploading files
var file_frame;


jQuery('.admin-area-logo-upload').live( 'click', function (event) {

	event.preventDefault();


	var uploader_widget = jQuery(this);

// If the media frame already exists, reopen it.
	if (file_frame) {
		file_frame.open('id_editor');
		return;
	}

// Create the media frame.


	file_frame = wp.media.frames.file_frame = wp.media({
		title   :jQuery(this).data('uploader_title'),
		button  :{
			text:jQuery(this).data('uploader_button_text')
		},
		multiple:false // Set to true to allow multiple files to be selected
	});

// When an image is selected, run a callback.
	file_frame.on('select', function () {
// We set multiple to false so only get one image from the uploader
		attachment = file_frame.state().get('selection').first().toJSON();

		jQuery('#amin_area_logo-field').val(attachment.url);
		jQuery('.logo-outer-admin-area').addClass('custom-image-outer');
		jQuery('.logo-outer-admin-area img').attr('src', attachment.url);

	});

// Finally, open the modal
	file_frame.open();
});

/*delete image*/
jQuery('.logo-outer-admin-area .button').live( 'click',function(){

	  var default_url = jQuery(this).attr('rel');

		//reset all values
	  jQuery('#amin_area_logo-field').val('');
		jQuery('.logo-outer-admin-area').removeClass('custom-image-outer');

	  jQuery('.logo-outer-admin-area img').attr('src', default_url);
});
