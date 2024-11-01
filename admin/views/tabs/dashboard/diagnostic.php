<?php

if ( ! defined( 'THREATPRESS_VERSION' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

$diagnostic = ThreatPress_Module_Diagnostic::get_data();
?>

<table class="threatpress_diagnostic_table wordpress-diagnostic widefat" cellspacing="0">
    <thead>
    <tr>
        <th colspan="3" data-export-label="<?php _e('WordPress environment', 'threatpress'); ?>"><h2><?php _e('WordPress environment', 'threatpress'); ?></h2></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><?php _e('Salts and security keys:', 'threatpress'); ?></td>
        <td>
            <?php

            if ( ! $diagnostic['salts_and_security_keys'] ) :
                echo '<mark class="error"><span class="dashicons dashicons-warning"></span> '. __( 'Security keys are missing.', 'threatpress' ) .'</mark>';
            else :
                echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
            endif;

            ?>
        </td>
    </tr>
    <tr>
        <td><?php _e('Database prefix:', 'threatpress'); ?></td>
        <td>
            <?php

            if ( ! $diagnostic['database_prefix'] ) :
                echo '<mark class="error"><span class="dashicons dashicons-warning"></span> '. __( 'Your site is using default database prefix. We recommend you to change it.', 'threatpress' ) .'</mark>';
            else :
                echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
            endif;

            ?>
        </td>
    </tr>
    <tr>
        <td><?php _e('File editing:', 'threatpress'); ?></td>
        <td>
            <?php

            if ( ! $diagnostic['file_editing'] ) :
                echo '<mark class="error"><span class="dashicons dashicons-warning"></span> '. __( 'It is recommended to disable file editing within the WordPress dashboard.', 'threatpress' ) .'</mark>';
            else :
                echo '<mark class="yes"><span class="dashicons dashicons-yes"></span> <code class="private">'. __('Disabled', 'threatpress') .'</code></mark>';
            endif;

            ?>
        </td>
    </tr>
    <tr>
        <td><?php _e('Unfiltered HTML:', 'threatpress'); ?></td>
        <td>
            <?php

            if ( ! $diagnostic['unfiltered_html'] ) :
                echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . __( 'It is recommended to disable unfiltered HTML.', 'threatpress' ) .'</mark>';
            else :
                echo '<mark class="yes"><span class="dashicons dashicons-yes"></span> <code class="private">'. __('Disabled', 'threatpress') .'</code></mark>';
            endif;

            ?>
        </td>
    </tr>
    <tr>
        <td><?php _e('Unfiltered uploads:', 'threatpress'); ?></td>
        <td>
            <?php

            if ( ! $diagnostic['unfiltered_uploads'] ) :
                echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . __( 'It is recommended to disable unfiltered uploads.', 'threatpress' ) .'</mark>';
            else :
                echo '<mark class="yes"><span class="dashicons dashicons-yes"></span> <code class="private">'. __('Disabled', 'threatpress') .'</code></mark>';
            endif;

            ?>
        </td>
    </tr>
    <tr>
        <td><?php _e('File permissions:', 'threatpress'); ?></td>
        <td>
            <?php

            if ( ! $diagnostic['permissions'] ) :
                echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'It is recommended to use 644 permissions for wp-config.php file. <a href="%s" target="_blank">Learn more about changing file permissions</a>', 'threatpress' ), 'https://codex.wordpress.org/Changing_File_Permissions' ) . '</mark>';
            else :
                echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
            endif;

            ?>
        </td>
    </tr>
    </tbody>
</table>

<table class="threatpress_diagnostic_table wordpress-diagnostic widefat" cellspacing="0">
    <thead>
    <tr>
        <th colspan="3" data-export-label="<?php _e('Server environment', 'threatpress'); ?>"><h2><?php _e('Server environment', 'threatpress'); ?></h2></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><?php _e('Secure connection (HTTPS):', 'threatpress'); ?>
        <td>
            <?php

            if ( ! $diagnostic['https'] ) :
                echo '<mark class="error"><span class="dashicons dashicons-warning"></span> '. __( 'Your site is not using HTTPS.', 'threatpress' ) .'</mark>';
            else :
                echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
            endif;

            ?>
        </td>
    </tr>
    <tr>
        <td><?php _e('Server info:', 'threatpress'); ?></td>
        <td><?php echo esc_html( $diagnostic['server_info'] ); ?></td>
    </tr>
    <tr>
        <td><?php _e('PHP version:', 'threatpress'); ?></td>
        <td><?php echo esc_html( $diagnostic['php_version'] ); ?></td>
    </tr>
    </tbody>
</table>
