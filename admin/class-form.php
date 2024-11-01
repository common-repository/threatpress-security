<?php

/**
 * Admin form class.
 */
class ThreatPress_Admin_Form {

	/**
	 * @var object    Instance of this class
	 */
	public static $instance;

	/**
	 * @var string
	 */
	public $option_name;

	/**
	 * @var array
	 */
	public $options;

	/**
	 * Get the singleton instance of this class
	 *
	 * @return ThreatPress_Form
	 */
	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Generates the form table header
	 *
	 */
	public function table_header() {
		?>
		<table class="form-table">
		<?php
	}

	/**
	 * Generates a table row
	 *
	 */
	public function table_row( $label_text, $label_attr, $field, $description = '' ){
		?>
		<tr>
			<th scope="row"><?php echo $this->label( $label_text, $label_attr );?></th>
			<td>
				<?php echo $field; ?>
				<p class="description"><?php echo esc_attr( $description ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Generates the form table footer
	 *
	 */
	public function table_footer() {
		?>
		</table>
		<?php
	}

	/**
	 * Generates the header for admin pages
	 *
	 * @param bool   $form             Whether or not the form start tag should be included.
	 * @param string $option           The short name of the option to use for the current page
	 * @param bool   $option_long_name Group name of the option.
	 */
	public function admin_header( $form = true, $option = 'threatpress' ) {

		$option_long_name = ThreatPress_Admin_Options::get_group_name( $option );

		?>
		<div class="wrap threatpress-admin-page page-<?php echo $option; ?>">
		<?php

		/**
		 * Display the updated/error messages
		 * Only needed as our settings page is not under options, otherwise it will automatically be included
		 *
		 * @see settings_errors()
		 */
		require_once( ABSPATH . 'wp-admin/options-head.php' );
		?>
		<h1 id="threatpress-title"><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<div class="threatpress_content_wrapper">
		<div class="threatpress_content_cell" id="threatpress_content_top">
		<?php
		if ( $form === true ) {
			echo '<form action="' . esc_url( admin_url( 'options.php' ) ) . '" method="post" id="threatpress-conf" accept-charset="' . esc_attr( get_bloginfo( 'charset' ) ) . '">';
			settings_fields( $option_long_name );
		}

		$this->set_option( $option );
	}

	/**
	 * Set the option used in output for form elements
	 *
	 * @param string $option_name Option key.
	 */
	public function set_option( $option_name ) {
		$this->option_name = $option_name;
		$this->options     = $this->get_option();
	}

	/**
	 * Get option
	 *
	 * @return array
	 */
	private function get_option() {
		return get_option( $this->option_name );
	}

	/**
	 * Generates the footer for admin pages
	 *
	 * @param bool $submit       Whether or not a submit button and form end tag should be shown.
	 */
	public function admin_footer( $submit = true ) {
		if ( $submit ) {
			submit_button();

			echo '
			</form>';
		}

		/**
		 * Apply general admin_footer hooks
		 */
		do_action( 'threatpress_admin_footer' );

		echo '
			</div><!-- end of div threatpress_content_top -->';

		echo '</div><!-- end of div threatpress_content_wrapper -->';

		echo '
			</div><!-- end of wrap -->';
	}

	/**
	 * Output a nonce field
	 *
	 * @param string $action action name
	 * @param string $name name
	 */
	public function nonce( $action, $name ) {
		return wp_nonce_field( $action, $name, false, false );
	}

	/**
	 * Output button
	 *
	 * @param array  $attr HTML attributes set.
	 */
	public function button( $attr ) {
		$attr = wp_parse_args( $attr, array(
				'name' => '',
				'class'   => 'button-secondary',
				'id'   => '',
				'value'   => ''
			)
		);

		return "<input type='button' name='" . esc_attr( $attr['name'] ) . "' class='" . esc_attr( $attr['class'] ) . "' id='" . esc_attr( $attr['id'] ) . "' value='" . esc_attr( $attr['value'] ) . "' />";
	}

	/**
	 * Output a label element
	 *
	 * @param string $text Label text string.
	 * @param array  $attr HTML attributes set.
	 */
	public function label( $text, $attr ) {
		$attr = wp_parse_args( $attr, array(
				'class' => '',
				'for'   => '',
			)
		);
		return "<label class='" . esc_attr( $attr['class'] ) . "' for='" . esc_attr( $attr['for'] ) . "'>$text</label>";
	}

	/**
	 * Output a legend element.
	 *
	 * @param string $text Legend text string.
	 * @param array  $attr HTML attributes set.
	 */
	public function legend( $text, $attr ) {
		$attr = wp_parse_args( $attr, array(
				'id' => '',
				'class' => '',
			)
		);
		$id = ( '' === $attr['id'] ) ? '' : ' id="' . esc_attr( $attr['id'] ) . '"';
		return '<legend class="threatpress-form-legend ' . esc_attr( $attr['class'] ) . '"' . $id . '>' . $text . '</legend>';
	}

	/**
	 * Create a Checkbox input field.
	 *
	 * @param string $var        The variable within the option to create the checkbox for.
	 * @param string $label      The label to show for the variable.
	 * @param bool   $label_left Whether the label should be left (true) or right (false).
	 */
	public function checkbox( $group, $var ) {
		if ( ! isset( $this->options[ $group ][ $var ] ) ) {
			$this->options[ $group ][ $var ] = false;
		}

		if ( $this->options[ $group ][ $var ] === true ) {
			$this->options[ $group ][ $var ] = 'on';
		}

		return '<input class="checkbox" type="checkbox" id="'. esc_attr( $var ) .'" name="'. esc_attr( $this->option_name ) .'['. esc_attr( $group ) .']['. esc_attr( $var ) .']" value="on"'. checked( $this->options[ $group ][ $var ], 'on', false ) .'/>';
	}

	/**
	 * Create a light switch input field.
	 *
	 * @param string  $var        The variable within the option to create the checkbox for.
	 * @param string  $label      The label to show for the variable.
	 * @param array   $buttons    Array of two labels for the buttons (defaults Off/On).
	 * @param boolean $reverse    Reverse order of buttons (default true).
	 */
	public function light_switch( $group, $var, $buttons = array(), $reverse = true ) {

		if ( ! isset( $this->options[ $group ][ $var ] ) ) {
			$this->options[ $group ][ $var ] = false;
		}

		if ( $this->options[ $group ][ $var ] === true ) {
			$this->options[ $group ][ $var ] = 'on';
		}

		$class = 'switch-light switch-candy switch-candy-blue switch-threatpress';
		$aria_labelledby = esc_attr( $var ) . '-label';

		if ( $reverse ) {
			$class .= ' switch-ThreatPress-seo-reverse';
		}

		if ( empty( $buttons ) ) {
			$buttons = array( __( 'Disabled', 'threatpress' ), __( 'Enabled', 'threatpress' ) );
		}

		list( $off_button, $on_button ) = $buttons;

		$light_switch = '<div class="switch-container">' .
		'<label class="'. esc_attr( $class ) .'">'.
		'<input type="checkbox" aria-labelledby="'. $aria_labelledby .'" id="'. esc_attr( $var ) .'" name="'. esc_attr( $this->option_name ) .'['. esc_attr( $group ) .']['. esc_attr( $var ) .']" value="on"'. checked( $this->options[ $group ][ $var ], 'on', false ) .'/>'.
		'<span aria-hidden="true">
			<span>'. esc_html( $off_button ) .'</span>
			<span>'. esc_html( $on_button ) .'</span>
			<a></a>
		 </span>
		 </label><div class="clear"></div></div>';

		return $light_switch;
	}

	/**
	 * Create a Text input field.
	 *
	 * @param string       $var   The variable within the option to create the text input field for.
	 * @param string       $label The label to show for the variable.
	 * @param array|string $attr  Extra class to add to the input field.
	 */
	public function textinput( $group, $var, $attr = array() ) {
		if ( ! is_array( $attr ) ) {
			$attr = array(
				'class' => $attr,
			);
		}
		$attr = wp_parse_args( $attr, array(
			'placeholder' => '',
			'class'       => '',
		) );
		$val  = ( isset( $this->options[ $group ][ $var ] ) ) ? $this->options[ $group ][ $var ] : '';

		return '<input class="textinput '. esc_attr( $attr['class'] ) .' " placeholder="'. esc_attr( $attr['placeholder'] ) .'" type="text" id="'. esc_attr( $var ) .'" name="'. esc_attr( $this->option_name ) .'['. esc_attr( $group ) .']['. esc_attr( $var ) .']" value="'. esc_attr( $val ) .'"/>';
	}

	/**
	 * Create a Number input field.
	 *
	 * @param string       $var   The variable within the option to create the text input field for.
	 * @param string       $label The label to show for the variable.
	 * @param array|string $attr  Extra class to add to the input field.
	 */
	public function number( $group, $var, $attr = array() ) {
		if ( ! is_array( $attr ) ) {
			$attr = array(
				'class' => $attr,
			);
		}
		$attr = wp_parse_args( $attr, array(
			'placeholder' => '',
			'class'       => '',
		) );
		$val  = ( isset( $this->options[ $group ][ $var ] ) ) ? $this->options[ $group ][ $var ] : '';

		return '<input class="textinput '. esc_attr( $attr['class'] ) .' " placeholder="'. esc_attr( $attr['placeholder'] ) .'" type="number" id="'. esc_attr( $var ) .'" name="'. esc_attr( $this->option_name ) .'['. esc_attr( $group ) .']['. esc_attr( $var ) .']" value="'. esc_attr( $val ) .'"/>';
	}

	/**
	 * Create a textarea.
	 *
	 * @param string $var   The variable within the option to create the textarea for.
	 * @param string $label The label to show for the variable.
	 * @param array  $attr  The CSS class to assign to the textarea.
	 */
	public function textarea( $group, $var, $attr = array() ) {
		if ( ! is_array( $attr ) ) {
			$attr = array(
				'class' => $attr,
			);
		}
		$attr = wp_parse_args( $attr, array(
			'cols'  => '',
			'rows'  => '',
			'class' => '',
		) );
		$val  = ( isset( $this->options[ $group ][ $var ] ) ) ? $this->options[ $group ][ $var ] : '';

		return '<textarea cols="' . esc_attr( $attr['cols'] ) . '" rows="' . esc_attr( $attr['rows'] ) . '" class="textinput ' . esc_attr( $attr['class'] ) . '" id="' . esc_attr( $var ) . '" name="' . esc_attr( $this->option_name ) . '['. esc_attr( $group ) .'][' . esc_attr( $var ) . ']">' . esc_textarea( $val ) . '</textarea>';
	}

	/**
	 * Create a hidden input field.
	 *
	 * @param string $var The variable within the option to create the hidden input for.
	 * @param string $id  The ID of the element.
	 */
	public function hidden( $group, $var, $id = '' ) {
		$val = ( isset( $this->options[ $group ][ $var ] ) ) ? $this->options[ $group ][ $var ] : '';
		if ( is_bool( $val ) ) {
			$val = ( $val === true ) ? 'true' : 'false';
		}

		if ( '' === $id ) {
			$id = 'hidden_' . $var;
		}

		return '<input type="hidden" id="' . esc_attr( $id ) . '" name="' . esc_attr( $this->option_name ) . '['. esc_attr( $group ) .'][' . esc_attr( $var ) . ']" value="' . esc_attr( $val ) . '"/>';
	}

	/**
	 * Create a Select Box.
	 *
	 * @param string $field_name     The variable within the option to create the select for.
	 * @param string $label          The label to show for the variable.
	 * @param array  $select_options The select options to choose from.
	 */
	public function select( $group, $field_name, array $select_options ) {

		if ( empty( $select_options ) ) {
			return;
		}

		$select_name   = esc_attr( $this->option_name ) . '['. esc_attr( $group ) .'][' . esc_attr( $field_name ) . ']';
		$active_option = ( isset( $this->options[ $group ][ $field_name ] ) ) ? $this->options[ $group ][ $field_name ] : '';

		$select = new ThreatPress_Admin_Input_Select( $field_name, $select_name, $select_options, $active_option );
		$select->add_attribute( 'class', 'select' );
		return $select->get_html();
	}

	/**
	 * Create a Radio input field.
	 *
	 * @param string $var         The variable within the option to create the radio button for.
	 * @param array  $values      The radio options to choose from.
	 * @param string $legend      Optional. The legend to show for the field set, if any.
	 * @param array  $legend_attr Optional. The attributes for the legend, if any.
	 */
	public function radio( $group, $var, $values, $legend = '', $legend_attr = array() ) {
		if ( ! is_array( $values ) || $values === array() ) {
			return;
		}
		if ( ! isset( $this->options[ $group ][ $var ] ) ) {
			$this->options[ $group ][ $var ] = false;
		}

		$group_esc = esc_attr( $group );
		$var_esc = esc_attr( $var );

		$radio = '<fieldset class="threatpress-form-fieldset threatpress_radio_block" id="' . $var_esc . '">';

		if ( is_string( $legend ) && '' !== $legend ) {

			$legend_attr = wp_parse_args( $legend_attr, array(
				'id'    => '',
				'class' => 'radiogroup',
			) );

			$radio .= $this->legend( $legend, $legend_attr );
		}

		foreach ( $values as $key => $value ) {
			$key_esc = esc_attr( $key );
			$radio .= '<input type="radio" class="radio" id="' . $var_esc . '-' . $key_esc . '" name="' . esc_attr( $this->option_name ) . '['. $group_esc .'][' . $var_esc . ']" value="' . $key_esc . '" ' . checked( $this->options[ $group ][ $var ], $key_esc, false ) . ' />';
			$radio .= $this->label( $value, array( 'for' => $var_esc . '-' . $key_esc, 'class' => 'radio' ) );
		}
		$radio .= '</fieldset>';

		return $radio;
	}

	/**
	 * Create a toggle switch input field.
	 *
	 * @param string $var    The variable within the option to create the file upload field for.
	 * @param array  $values The radio options to choose from.
	 * @param string $label  The label to show for the variable.
	 */
	public function toggle_switch( $group, $var, $values ) {
		if ( ! is_array( $values ) || $values === array() ) {
			return;
		}
		if ( ! isset( $this->options[ $group ][ $var ] ) ) {
			$this->options[ $group ][ $var ] = false;
		}
		if ( $this->options[ $group ][ $var ] === true ) {
			$this->options[ $group ][ $var ] = 'on';
		}
		if ( $this->options[ $group ][ $var ] === false ) {
			$this->options[ $group ][ $var ] = 'off';
		}

		$var_esc = esc_attr( $var );
		$group_esc = esc_attr( $group );

		$toggle_switch = '<div class="switch-container">';
		$toggle_switch .= '<fieldset id="'. $var_esc .'" class="fieldset-switch-toggle">
		<div class="switch-toggle switch-candy switch-candy-blue switch-threatpress">';

		foreach ( $values as $key => $value ) {
			$key_esc = esc_attr( $key );
			$for     = $var_esc . '-' . $key_esc;
			$toggle_switch .= '<input type="radio" id="' . $for . '" name="' . esc_attr( $this->option_name ) . '['. $group_esc .'][' . $var_esc . ']" value="' . $key_esc . '" ' . checked( $this->options[ $group ][ $var ], $key_esc, false ) . ' />';
		}

		$toggle_switch .= '<a></a></div></fieldset><div class="clear"></div></div>' . "\n\n";

		return $toggle_switch;
	}
}
