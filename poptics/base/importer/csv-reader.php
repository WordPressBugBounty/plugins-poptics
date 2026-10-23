<?php
/**
 * CSV Reader Class
 *
 * @package Poptics
 */
namespace Poptics\Base\Importer;

/**
 * CSV Reader Class
 */
class CSV_Reader implements Reader_Interface {
    /**
     * Store file
     *
     * @var string
     */
    private $file;

    /**
     * JSON Reader Constructor
     *
     * @param   string  $file
     *
     * @return  void
     */
    public function __construct( $file ) {
        $this->file = $file;
    }

    /**
     * Read file
     *
     * @return  array
     */
    public function read_file() {
        $data = $this->prepare_data();

        return $data;
    }

    /**
     * Prepare data from file
     *
     * @return  array
     */
    private function prepare_data() {
        global $wp_filesystem;

        // Initialize WP_Filesystem if not already done
        if ( !function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        WP_Filesystem();

        $file     = $this->file;
        $csv_data = [];

        // Ensure the file exists and is readable
        if ( !$wp_filesystem->exists( $file ) || !$wp_filesystem->is_readable( $file ) ) {
            return $csv_data;
        }

        // Read file contents
        $file_contents = $wp_filesystem->get_contents( $file );
        if ( empty( $file_contents ) ) {
            return $csv_data;
        }

        // Convert CSV contents into an array
        $lines   = explode( "\n", $file_contents );
        $headers = str_getcsv( array_shift( $lines ) );

        if ( !$headers ) {
            return $csv_data;
        }

        $header_count = count( $headers );

        foreach ( $lines as $line ) {
            $data = str_getcsv( $line );

            if ( count( $data ) === $header_count ) {
                $row = [];

                for ( $i = 0; $i < $header_count; $i++ ) {
                    $header       = str_replace( ' ', '_', strtolower( $headers[$i] ) );
                    $row[$header] = $data[$i];
                }

                $csv_data[] = $row;
            }
        }

        return $csv_data;
    }
}
