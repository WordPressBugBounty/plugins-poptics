<?php
/**
 * JSON Exporter Class
 *
 * @since 1.0.5
 *
 * @package Poptics
 */
namespace Poptics\Base\Exporter;

/**
 *  JSON Exporter Class
 *  Export data for json
 */
class Json_Exporter implements Exporter_Interface {
    /**
     * Exported data
     *
     * @since 1.0.5
     *
     * @var array
     */
    private $data;

    /**
     * Store column name that will be exported
     *
     * @since 1.0.5
     *
     * @var array
     */
    private $columns;

    /**
     * Exported file name
     *
     * @since 1.0.5
     *
     * @var string
     */
    private $file_name = 'data';

    /**
     * Export data
     *
     * @since 1.0.5
     *
     * @param   array  $data
     * @param   data  $columns
     *
     * @return  void
     */
    public function export( $data, $columns = [], $file_name = '' ) {
        $this->data      = $data;
        $this->columns   = $columns;
        $this->file_name = $file_name;
        $this->export_json();
    }
    /**
     * Set content type
     *
     * @since 1.0.5
     *
     * @return void
     */
    protected function send_headers() {
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $this->file_name . '.json' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );
    }

    /**
     * Export data to JSON format
     *
     * @since 1.0.5
     *
     * @return  void
     */
    public function export_json() {
        $this->send_headers();
        $data = $this->data;

        $output = wp_json_encode( $data, JSON_PRETTY_PRINT );

        echo $output; // Escape JSON output for safety
        die();
    }
}