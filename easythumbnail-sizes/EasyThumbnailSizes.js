var $j;
$j = jQuery.noConflict();

$j(document).ready(function() {

    // clear input fields
    $j('input[data-customize-setting-link="easythumbnailsizes_name"]').val('');
    $j('input[data-customize-setting-link="easythumbnailsizes_width"]').val('');
    $j('input[data-customize-setting-link="easythumbnailsizes_height"]').val('');
    $j('input[data-customize-setting-link="easythumbnailsizes_crop"]').prop('checked', true);


    function add_to_selection(name, width, height, slug, crop) {
        var option, cropped;

        cropped = '';
        if (crop === 'true') {
            cropped = ' (cropped)';
        }
        option = name + ' - ' + width +  ' \u00D7 ' + height + cropped; // U+00D7 = multiplication sign
        $j("#selectAddImageSize").append('<option value="' + slug + '">' + option + '</option>');
    }

    var imageSizes = []; // array of additional image sizes
    // add current image size data to select box and internal array
    for (i = 0; i < vars.length; i++) {
        add_to_selection(vars[i].name, vars[i].width, vars[i].height, vars[i].name.replace(' ', '-'), vars[i].crop);
        imageSizes.push({
            name:   vars[i].name,
            height: vars[i].height,
            width:  vars[i].width,
            slug:   vars[i].slug,
            crop:   vars[i].crop
        });
    }



    function error_message(condition, message){
        var error;
        error = false;
        if (condition === true) {
            $j('#error_message').append(i18n.error + ' ' + message + '.<br/>');
            error = true;
        }
        return error;
    }
    $j('#customize-control-easythumbnailsizes_add_button').click(function(){

        $j('#error_message').empty(); // clear error message

        val_name   = $j('input[data-customize-setting-link="easythumbnailsizes_name"]').val();
        val_width  = $j('input[data-customize-setting-link="easythumbnailsizes_width"]').val();
        val_height = $j('input[data-customize-setting-link="easythumbnailsizes_height"]').val();
        val_crop   = $j('input[data-customize-setting-link="easythumbnailsizes_crop"]').prop('checked').toString();
        val_slug   = val_name.replace(' ', '-').toLowerCase();

        // check for plausibility of user entries
        var error = false;
        error = error_message(val_name === '', i18n.no_name_error);
        error = error_message(val_width === '', i18n.no_width_error)
                    || error_message(isNaN(parseInt(val_width)), i18n.invalid_width_error);
        error = error_message(val_height === '', i18n.no_height_error)
                    || error_message(isNaN(parseInt(val_height)), i18n.invalid_height_error);


        if (error === false) {
            // check if slug already exists
            i = 0;
            name_conflict = false;
            while (i<imageSizes.length && name_conflict === false) {
                name_conflict = error_message(val_slug === imageSizes[i].slug, i18n.name_conflict_error);
                i++;
            }

            if (name_conflict === false) {
                imageSizes.push({
                    name: val_name,
                    height: val_height,
                    width: val_width,
                    slug: val_slug,
                    crop: val_crop
                });

                // add new size to select box
                add_to_selection(val_name, val_width, val_height, val_slug, val_crop);
            }
        }//if
    });

    $j('#customize-control-easythumbnailsizes_remove_button').click(function(){

        //get selected item
        selected = $j('#selectAddImageSize :selected').val();
        // remove item from select box
        $j("#selectAddImageSize option[value='"+ selected +"']").remove();

        // remove item from the internal array
        for (var i = 0; i < imageSizes.length; i++) {
            if (imageSizes[i]['slug'] === selected ) {
                imageSizes.splice(i, 1);
            }
        }
    });

    $j('#save').click(function() {
        $j.ajax({
            type: "post",
            url: ajaxurl,
            cache: false,
            data: {
                action: "save_options",
                imagesizes: imageSizes
            }
        });
    });
}); //document ready