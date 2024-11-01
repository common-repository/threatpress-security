<?php

/**
 * Class ThreatPress_Option_Tab
 */
class ThreatPress_Admin_Option_Tab {

    /** @var string Name of the tab */
    private $name;

    /** @var string Label of the tab */
    private $label;

    /** @var array Optional arguments */
    private $arguments = array();

    /**
     * ThreatPress_Option_Tab constructor.
     *
     * @param string $name      Name of the tab.
     * @param string $label     Label of the tab.
     * @param array  $arguments Optional arguments.
     */
    public function __construct( $name, $label, $arguments = array() ) {
        $this->name      = sanitize_title( $name );
        $this->label     = $label;
        $this->arguments = (array) $arguments;
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Get the label
     *
     * @return string
     */
    public function get_label() {
        return $this->label;
    }

}
