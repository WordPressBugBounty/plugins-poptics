<?php
/**
 * Exporter Class
 *
 * @since 1.0.5
 *
 * @package Poptics
 */
namespace Poptics\Base\Exporter;

use Exception;

/**
 * Class Exporter
 */
class Exporter_Factory {
    /**
     * Get exporter method
     *
     * @since 1.0.5
     *
     * @return  \Exporter_Interface
     */
    public static function get_exporter( $format ) {
        switch ( $format ) {
        case 'csv':
            return new CSV_Exporter();

        case 'json':
            return new Json_Exporter();

        default:
            throw new Exception( esc_html__( 'Unknown format', 'poptics' ) );
        }
    }
}
