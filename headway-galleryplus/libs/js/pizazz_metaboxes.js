jQuery(document).ready(function() {

		jQuery('#pizazz-meta-boxes').tabs({});

	// have to do this individually because the functions is (currently) generic
	jQuery('#pizazz-form-table-row-General-field-pzsp_taxonomy').hide();
	jQuery('#pizazz-form-table-row-General-field-pzsp_tags').hide();
	jQuery('#pizazz-form-table-row-General-field-pzsp_category').hide();
	jQuery('#pizazz-form-table-row-General-field-pzsp_specific_ids').hide();
	jQuery('#pizazz-form-table-row-General-field-pzsp_slide_set').hide();
	var y = jQuery('#pzsp_filtering').val();
	jQuery('#pizazz-form-table-row-General-field-pzsp_'+y).show();

	jQuery('#pzsp_filtering').change(function(){
		var x = jQuery(this).val();
		jQuery('#pizazz-form-table-row-General-field-pzsp_taxonomy').hide();
		jQuery('#pizazz-form-table-row-General-field-pzsp_tags').hide();
		jQuery('#pizazz-form-table-row-General-field-pzsp_category').hide();
		jQuery('#pizazz-form-table-row-General-field-pzsp_specific_ids').hide();
		jQuery('#pizazz-form-table-row-General-field-pzsp_slide_set').hide();
		jQuery('#pizazz-form-table-row-General-field-pzsp_'+x).show();
	});
});
