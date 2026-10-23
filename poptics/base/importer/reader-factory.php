<?php
/**
 * File Reader Factory
 *
 * @package Poptics
 */
namespace Poptics\Base\Importer;

class Reader_Factory {
    /**
     * Get reader depends on file type
     *
     * @return Reader_Interface
     */
    public static function get_reader( $file ) {
        $file_name = !empty( $file['tmp_name'] ) ? $file['tmp_name'] : '';
        $file_type = !empty( $file['type'] ) ? $file['type'] : '';

        switch ( $file_type ) {
        case 'application/json':
            return new JSON_Reader( $file_name );
        case 'text/csv':
            return new CSV_Reader( $file_name );
        default:
            throw new \Exception( esc_html__( 'You must provide a valid file type', 'poptics' ) );
        }
    }
}
