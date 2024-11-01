<?php

/**
 * Class ThreatPress_Option_Tabs_Formatter
 */
class ThreatPress_Admin_Option_Tabs_Formatter {

    /**
     * @param ThreatPress_Option_Tabs $option_tabs Option Tabs to get base from.
     * @param ThreatPress_Option_Tab  $tab         Tab to get name from.
     *
     * @return string
     */
    public function get_tab_view( ThreatPress_Admin_Option_Tabs $option_tabs, ThreatPress_Admin_Option_Tab $tab ) {
        return THREATPRESS_PLUGIN_DIR . 'admin/views/tabs/' . $option_tabs->get_base() . '/' . $tab->get_name() . '.php';
    }

    /**
     * @param ThreatPress_Option_Tabs $option_tabs Option Tabs to get tabs from.
     * @param ThreatPress_Form        $tform       ThreatPress Form which is being used in the views.
     * @param array            $options     Options which are being used in the views.
     * @param array            $extra     Extra data needed in the views.
     */
    public function run( ThreatPress_Admin_Option_Tabs $option_tabs, ThreatPress_Admin_Form $tform, $options = array(), $extra = array() ) {

        echo '<h2 class="nav-tab-wrapper" id="threatpress-tabs">';
        foreach ( $option_tabs->get_tabs() as $tab ) {
            printf( '<a class="nav-tab" id="%1$s-tab" href="#top#%1$s">%2$s</a>', $tab->get_name(), $tab->get_label() );
        }
        echo '</h2>';

        foreach ( $option_tabs->get_tabs() as $tab ) {

            $identifier = $tab->get_name();
            printf( '<div id="%s" class="threatpress_tab">', $identifier );


            // Output the settings view for all tabs.
            $tab_view = $this->get_tab_view( $option_tabs, $tab );
            if ( is_file( $tab_view ) ) {
                require_once $tab_view;
            }

            echo '</div>';
        }
    }
}
