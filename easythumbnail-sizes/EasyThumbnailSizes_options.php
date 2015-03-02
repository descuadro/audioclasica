<?php

// Declare new customize control for button

add_action( 'customize_register', 'register_custom_controls' );
function register_custom_controls( $wp_customize ) {

	class Customize_Control_Multiple_Select extends WP_Customize_Control {

		public $type = 'multiple-select';

		public function render_content() {
			?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<select multiple="multiple" id="selectAddImageSize" style="width:100%;" <?php $this->link(); ?>>
				</select>
		<?php
		} //render_content()
	}

	class Customize_Control_Button extends WP_Customize_Control {

		public $type = 'button';

		public function render_content() {
			?>
			<input type="button" id="add_button" value="<?php echo $this->choices['name']; ?>"/>
			<?php
		} //render_content()
	}

	class Customize_Control_Message extends WP_Customize_Control {

		public $type = 'message';

		public function render_content() {
			?>
			<div id="error_message" style="color:#ff0000;"></div>
		<?php
		} //render_content()
	}
}

// ---------------------------------------------------------------------------------------------------------------------

add_action( 'customize_register', 'EasyThumbnailSizes_customizer' );
function EasyThumbnailSizes_customizer( $wp_customize ) {

	add_action('admin_enqueue_scripts', 'EasyThumbnailSize_add_js');

	$wp_customize->add_section(
		'EasyThumbnailSizes',
		array(
			'title'       => 'Easy Thumbnail Sizes',
			'description' => ''
		)
	);

	$wp_customize->add_setting('easythumbnailsizes_name', array('default' => ''));
	$wp_customize->add_control(
		'easythumbnailsizes_name',
		array(
			'label'    => __('Name', 'EasyThumbnailSizes'),
			'section'  => 'EasyThumbnailSizes',
			'type'     => 'text',
			'priority' => 1
		)
	);

	$wp_customize->add_setting('easythumbnailsizes_width', array('default' => ''));
	$wp_customize->add_control(
		'easythumbnailsizes_width',
		array(
			'label'    => __('Width', 'EasyThumbnailSizes'),
			'section'  => 'EasyThumbnailSizes',
			'type'     => 'text',
			'priority' => 2
		)
	);

	$wp_customize->add_setting('easythumbnailsizes_height', array('default' => ''));
	$wp_customize->add_control(
		'easythumbnailsizes_height',
		array(
			'label'    => __('Height', 'EasyThumbnailSizes'),
			'section'  => 'EasyThumbnailSizes',
			'type'     => 'text',
			'priority' => 3
		)
	);

	$wp_customize->add_setting('easythumbnailsizes_crop', array('default' => ''));
	$wp_customize->add_control(
		'easythumbnailsizes_crop',
		array(
			'label'    => __('Crop', 'EasyThumbnailSizes'),
			'section'  => 'EasyThumbnailSizes',
			'type'     => 'checkbox',
			'priority' => 4
		)
	);

	$wp_customize->add_setting( 'easythumbnailsizes_add_button', array(
		'default' => array(),
	) );
	$wp_customize->add_control(
		new Customize_Control_Button(
			$wp_customize,
			'easythumbnailsizes_add_button',
			array(
				'section'   => 'EasyThumbnailSizes' ,
				'type'      => 'button',
				'priority'  => 5,
				'choices'   => array(
				'name'  => __('Add', 'EasyThumbnailSizes')
				)
			)
		)
	);

	$wp_customize->add_setting( 'easythumbnailsizes_message', array('default' => array() ));
	$wp_customize->add_control(
		new Customize_Control_Message(
			$wp_customize,
			'easythumbnailsizes_message',
			array(
				'title'    => '',
				'section'  => 'EasyThumbnailSizes',
				'priority' => 6
			)
		)
	);

	$wp_customize->add_setting( 'easythumbnailsizes_multiple_select', array('default' => array() ));
	$wp_customize->add_control(
		new Customize_Control_Multiple_Select(
			$wp_customize,
			'easythumbnailsizes_multiple_select',
			array(
				'section'  => 'EasyThumbnailSizes',
				'type'     => 'select',
				'priority' => 7
			)
		)
	);

	$wp_customize->add_setting( 'easythumbnailsizes_remove_button', array('default' => array() ));
	$wp_customize->add_control(
		new Customize_Control_Button(
			$wp_customize,
			'easythumbnailsizes_remove_button',
			array(
				'section'   => 'EasyThumbnailSizes',
				'type'      => 'button',
				'priority'  => 8,
				'choices'   => array('name' => __('Remove', 'EasyThumbnailSizes'))
			)
		)
	);

} //EasyThumbnailSizes_customizer()

// ---------------------------------------------------------------------------------------------------------------------

function EasyThumbnailSize_add_js() {

	if (! is_admin() )
		return;

	// i18n
	$i18n = array(
		'error'                 => __('Error:', 'EasyThumbnailSizes'),
		'no_name_error'         => __('No name given', 'EasyThumbnailSizes'),
		'no_width_error'        => __('No width given', 'EasyThumbnailSizes'),
		'no_height_error'       => __('No height given', 'EasyThumbnailSizes'),
		'invalid_width_error'   => __('Invalid value for width', 'EasyThumbnailSizes'),
		'invalid_height_error'  => __('Invalid value for height', 'EasyThumbnailSizes'),
		'name_conflict_error'   => __('Name conflict', 'EasyThumbnailSizes'),
		);

	// load easyThumbnailImages data from database and send to JS
	$options = get_option('EasyThumbnailSizes-' . str_replace(' ', '-', wp_get_theme()->get('Name')));
	if (! $options)
		$options = array();

	wp_register_script('EasyThumbnailSizes', plugins_url( 'EasyThumbnailSizes.js', __FILE__ ), '', time(), true );
	wp_localize_script('EasyThumbnailSizes', 'vars', $options);
	wp_localize_script('EasyThumbnailSizes', 'i18n', $i18n);
	wp_enqueue_script( 'EasyThumbnailSizes' );
}
// ---------------------------------------------------------------------------------------------------------------------

add_action('wp_ajax_save_options', 'save_options');
function save_options() {
	$option_name = 'EasyThumbnailSizes-'  . str_replace(' ', '-', wp_get_theme()->get('Name'));

	if (isset($_POST['imagesizes'])) {
		update_option($option_name, $_POST['imagesizes']);
	}
	die;
} //save_options()
