<?php

/**
 * Class for generating a html select.
 */
class ThreatPress_Admin_Input_Select {

    /**
     * @var string
     */
    private $select_id;

    /**
     * @var string
     */
    private $select_name;

    /**
     * @var array
     */
    private $select_attributes = array();

    /**
     * @var array Array with the options to parse.
     */
    private $select_options;

    /**
     * @var string The current selected option.
     */
    private $selected_option;

    /**
     * Constructor.
     *
     * @param string $select_id       ID for the select.
     * @param string $select_name     Name for the select.
     * @param array  $select_options  Array with the options to parse.
     * @param string $selected_option The current selected option.
     */
    public function __construct( $select_id, $select_name, array $select_options, $selected_option ) {
        $this->select_id         = $select_id;
        $this->select_name       = $select_name;
        $this->select_options    = $select_options;
        $this->selected_option   = $selected_option;
    }

    /**
     * Get view.
     */
    public function output_html() {
        // Extract it, because we want each value accessible via a variable instead of accessing it as an array.
        extract( $this->get_select_values() );

        $select = '<select '. $attributes .' name="'. esc_attr( $name ) .'" id="'. esc_attr( $id ) .'">';
        foreach ( $options as $option_attribute_value => $option_html_value ) :
            $select .= '<option value="'. esc_attr( $option_attribute_value ) .'" '. selected( $selected, $option_attribute_value, false ) .'>' . esc_html( $option_html_value ) .'</option>';
        endforeach;
        $select .= '</select>';

        return $select;
    }

    /**
     * Return the rendered view
     *
     * @return string
     */
    public function get_html() {
        $output = $this->output_html();

        return $output;
    }

    /**
     * Add an attribute to the attributes property
     *
     * @param string $attribute The name of the attribute to add.
     * @param string $value     The value of the attribute.
     */
    public function add_attribute( $attribute, $value ) {
        $this->select_attributes[ $attribute ] = $value;
    }

    /**
     * Return the set fields for the select
     *
     * @return array
     */
    private function get_select_values() {
        return array(
            'id'         => $this->select_id,
            'name'       => $this->select_name,
            'attributes' => $this->get_attributes(),
            'options'    => $this->select_options,
            'selected'   => $this->selected_option,
        );
    }

    /**
     * Return the attribute string, when there are attributes set.
     *
     * @return string
     */
    private function get_attributes() {
        $attributes = $this->select_attributes;

        if ( ! empty( $attributes ) ) {
            array_walk( $attributes, array( $this, 'parse_attribute' ) );

            return implode( ' ', $attributes ) . ' ';
        }

        return '';
    }

    /**
     * Get an attribute from the attributes.
     *
     * @param string $value     The value of the attribute.
     * @param string $attribute The attribute to look for.
     */
    private function parse_attribute( & $value, $attribute ) {
        $value = sprintf( '%s="%s"', esc_html( $attribute ), esc_attr( $value ) );
    }
}
