<div class="threatpress-container__alert">
    <h3><span class="dashicons dashicons-<?php echo $dashicon; ?>"></span> <?php echo $i18n_title; ?></h3>
    <div id="threatpress-alerts">
        <br />
        <div class="container" id="threatpress-alerts-active">
            <div class="threatpress-alert-holder" id="ThreatPress-dismiss-tagline-notice" data-json="">
                <?php

                if ( $messages ) :
                    foreach( $messages as $message ) :
                ?>
                <div class="threatpress-alert">
                    <p><?php echo $message; ?></p>
                </div>
                <?php
                    endforeach;
                endif;
                ?>
            </div>
        </div>
        <div class="container" id="threatpress-alerts-dismissed">
        </div>
        <div class="threatpress-bottom-spacing"></div>
    </div>
</div>