<?php

if ( ! defined( 'THREATPRESS_VERSION' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

?>

<?php if ( $records = ThreatPress_Module_Lockouts::get_records() ) : ?>
    <table class="wp-list-table widefat fixed striped pages the-ip-list-table">
        <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <input type="checkbox">
                </td>
                <th>
                    <?php _e('Username', 'threatpress'); ?>
                </th>

                <th>
                    <?php _e('Lockout time', 'threatpress'); ?>
                </th>

                <th>
                    <?php _e('IP', 'threatpress'); ?>
                </th>
            </tr>
        </thead>

        <tbody id="the-ip-list">
            <?php foreach( $records as $record ) : ?>
                <tr>
                    <th scope="row" class="check-column">
                        <input name="id[]" value="<?php echo absint( $record->id ); ?>" type="checkbox">
                    </th>
                    <td><?php echo esc_attr( $record->username ); ?></td>
                    <td><?php echo date( 'Y-m-d H:i:s', absint( $record->time ) ); ?></td>
                    <td><?php echo esc_attr( $record->ip ); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php echo $tform->nonce( 'remove_lockout', '_remove_lockout_nonce' ); ?>

    <input type="button" name="threatpress_ajax[remove_lockout]" id="remove-lockout" class="button-secondary" value="<?php _e('Remove selected', 'threatpress'); ?>" />
    <div id="remove-lockout-messages"></div>
<?php else :

    echo '<p>' . __( 'No locked out users found.', 'threatpress' ) . '</p>';

endif; ?>