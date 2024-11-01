<?php

/**
 * Utility methods
 *
 */
class ThreatPress_Admin_Utils {

    /**
     * Recursively trim whitespace round a string value or of string values within an array
     * Only trims strings to avoid typecasting a variable (to string)
     *
     * @static
     *
     * @param mixed $value Value to trim or array of values to trim.
     *
     * @return mixed Trimmed value or array of trimmed values
     */
    public static function trim_recursive( $value ) {
        if ( is_string( $value ) ) {
            $value = trim( $value );
        }
        elseif ( is_array( $value ) ) {
            $value = array_map( array( __CLASS__, 'trim_recursive' ), $value );
        }

        return $value;
    }

    /**
     * Validate integer
     *
     * @param $value
     * @return bool
     */
    public static function validate_int( $value ) {
        if ( filter_var( $value, FILTER_VALIDATE_INT ) !== false ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get file permissions
     *
     * @param $path
     * @return string
     */
    public static function get_file_permissions( $path ) {
        clearstatcache( null, $path );

        return decoct( fileperms( $path ) & 0777 );
    }

}