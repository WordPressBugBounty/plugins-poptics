<?php
/**
 * Exporter Interface
 *
 * @since 1.0.5
 *
 * @package Poptics
 */
namespace Poptics\Base\Exporter;

/**
 * Exporter interface
 */
interface Exporter_Interface {
    /**
     * Export data
     *
     * @since 1.0.5
     *
     * @return void
     */
    public function export( $data, $columns = [], $file_name = '' );
}
