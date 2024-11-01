<?php

if ( ! defined( 'THREATPRESS_VERSION' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}
?>

<table class="wp-list-table widefat fixed striped pages scanner-status">
    <thead>
    <tr>
        <th>
            <?php _e('Status', 'threatpress'); ?>
        </th>
    </tr>
    </thead>

    <tbody>
    <tr>
        <td>
            <p id="scanner-data">
                <?php

                if ( $extra['threat_status'] == true ) :
                    if ( isset( $extra['threats'] ) ) :
                        $threats = $extra['threats'];

                        if ( $threats['vulnerabilities']['plugins']['status'] == true ) :
                        ?>
                        <div class="item">
                        <?php
                            foreach ( $threats['vulnerabilities']['plugins']['list'] as $plugin_path => $info ) :
                            $plugin = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $plugin_path );
                            ?>
                            <p>
                                <strong class="warning"><?php echo sprintf( __('%s plugin %s version is vulnerable. Update immediately.', 'threatpress'), $plugin['Name'], $plugin['Version'] ); ?></strong><br />
                                <strong><?php _e('Details:', 'threatpress'); ?></strong>
                                <?php echo esc_html($info); ?>
                            </p>
                        <?php
                            endforeach;
                        ?>
                        </div>
                        <?php
                        endif;

                        if ( $threats['vulnerabilities']['themes']['status'] == true ) :
                        ?>
                        <div class="item">
                        <?php
                            foreach ( $threats['vulnerabilities']['themes']['list'] as $theme_file => $info ) :
                                $theme = wp_get_theme( $theme_file );
                                ?>
                                <p>
                                    <strong class="warning"><?php echo sprintf( __('%s theme %s version is vulnerable. Update immediately.', 'threatpress'), $theme->get( 'Name' ), $theme->get( 'Version' ) ); ?></strong><br />
                                    <strong><?php _e('Details:', 'threatpress'); ?></strong>
                                    <?php echo esc_html($info); ?>
                                </p>
                                <?php
                            endforeach;
                        ?>
                        </div>
                        <?php
                        endif;

                        if ( $threats['vulnerabilities']['wordpress']['status'] == true ) :
                            ?>
                            <div class="item">
                                <p><strong class="warning"><?php echo sprintf( __('Current WordPress %s version has known security issues. Update immediately.', 'threatpress'), $wp_version ); ?></strong></p>
                            </div>
                            <?php
                        endif;

                        if ( $threats['site']['malware'] === true ) :
                            ?>
                            <div class="item">
                                <p><strong class="warning"><?php _e('Malware has been detected on your site. You need to remove your site from blacklisting.', 'threatpress'); ?></strong></p>
                             </div>
                            <?php
                        endif;

                        if ( $threats['site']['phishing'] === true ) :
                            ?>
                            <div class="item">
                                <p><strong class="warning"><?php _e('Your site has been flagged as phishing. You need to remove your site from blacklisting.', 'threatpress'); ?></strong></p>
                            </div>
                            <?php
                        endif;

                        if ( $threats['site']['spam'] === true ) :
                            ?>
                            <div class="item">
                                <p><strong class="warning"><?php _e('Your site has been flagged as spam. You need to remove your site from blacklisting.', 'threatpress'); ?></strong></p>
                            </div>
                            <?php
                        endif;

                        if ( $threats['site']['errors'] === true ) :
                            ?>
                            <div class="item">
                                <p>
                                    <strong class="warning"><?php _e('Site errors have been detected.', 'threatpress'); ?></strong>
                                    <br />
                                    <strong><?php _e('Errors:', 'threatpress'); ?></strong>
                                    <br />
                                    <?php
                                    foreach( $threats['site']['errors_info'] as $error ) :
                                        echo esc_html($error) . '<br />';
                                    endforeach;
                                    ?>
                                </p>
                            </div>
                            <?php
                        endif;

                        if ( $threats['checksums']['status'] == true ) : ?>
                            <div class="item">
                                <p>
                                    <strong class="warning"><?php _e('Checksum verification failed.', 'threatpress'); ?></strong>
                                    <br />
                                    <strong><?php _e('Affected files:', 'threatpress'); ?><br /></strong>
                                    <?php
                                    foreach( $threats['checksums']['files'] as $file ) :
                                        echo esc_html($file) . '<br />';
                                    endforeach;
                                    ?>
                                </p>
                            </div>
                        <?php
                        endif;

                    endif;

                 else :
                    ?> <span class="success"><?php _e('Congratulations! No threats found.', 'threatpress'); ?></span>
                 <?php
                 endif;

                ?>
            </p>
        </td>
    </tr>
    </tbody>
</table>

<table class="wp-list-table widefat fixed striped pages scanner">
    <thead>
    <tr>
        <th>
            <?php _e('Scanner', 'threatpress'); ?>
        </th>
    </tr>
    </thead>

    <tbody>
    <tr>
        <td>
            <p>
                <?php

                echo $tform->button( array(
                    'name' => 'threatpress_ajax[run_scanner]',
                    'id' => 'run-scanner-btn',
                    'class' => 'button-primary',
                    'value' => __( 'Scan now', 'threatpress' )
                    ) ) . $tform->nonce( 'scanner', '_scanner_nonce' );

                ?>
            </p>

            <div id="scanning">
                <div class="scanning-img"></div>
                <div class="scanning-text"><?php _e('Initializing scanner, please wait...', 'threatpress'); ?></div>
            </div>

            <strong><?php _e('Scan details', 'threatpress'); ?></strong><br />
            <strong><?php _e('Last scan time:', 'threatpress'); ?></strong>
            <?php
            if ( $extra['last_scan_time'] ) :
                echo date( 'Y-m-d H:i:s', $extra['last_scan_time'] );
            else :
                _e('No last scan detected. We recommend that you run a Full Scan after installing the plugin.', 'threatpress');
            endif;
            ?>
        </td>
    </tr>
    </tbody>
</table>