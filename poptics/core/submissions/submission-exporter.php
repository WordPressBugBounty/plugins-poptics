<?php
/**
 * SUbmission Exporter Class
 *
 * @since 1.0.5
 * 
 * @package Poptics
 */
namespace Poptics\Core\Submissions;

use Poptics\Base\Exporter\Exporter_Factory;

/**
 * Class Submission Exporter
 *
 * Export Submission Data
 */
class Submission_Exporter {
    /**
     * Store file name
     *
     * @since 1.0.5
     *
     * @var string
     */
    private $file_name = 'submission-data';

    /**
     * Store submission data
     *
     * @since 1.0.5
     *
     * @var array
     */
    private $data;

    /**
     * Export submission data
     *
     * @since 1.0.5
     *
     * @return void
     */
    public function export( $data, $format ) {
        $this->data = $data;

        $rows      = $this->prepare_data();
        $columns   = $this->get_columns();
        $file_name = $this->file_name;

        $exporter = Exporter_Factory::get_exporter( $format );

        $exporter->export( $rows, $columns, $file_name );
    }

    /**
     * Prepare data to export
     *
     * @since 1.0.5
     *
     * @return  array
     */
    private function prepare_data() {
        $ids           = $this->data;
        $exported_data = [];

        foreach ( $ids as $id ) {
            $data = [
                'submission_id' => $id,
            ];
            $submission         = new Submission( $data );
            $submission_details = $submission->get_single_submission_details();

            $schedule_data = [
                'id'            => $id,
                'campaign_id'   => $submission_details['campaign_id'],
                'campaign_name' => $submission_details['campaign_name'],
                'email'         => $submission_details['email'],
                'browser'       => $submission_details['browser'],
                'device'        => $submission_details['device'],
                'location'      => $submission_details['location'],
                'other_details' => $submission_details['other_details'],
                'status'        => $submission_details['status'],
            ];

            array_push( $exported_data, $schedule_data );
        }

        return $exported_data;
    }

    /**
     * Get columns
     *
     * @since 1.0.5
     *
     * @return  array
     */
    private function get_columns() {
        return [
            'id'            => __( 'ID', 'poptics' ),
            'campaign_id'   => __( 'Campaign ID', 'poptics' ),
            'campaign_name' => __( 'Campaign Name', 'poptics' ),
            'email'         => __( 'Email', 'poptics' ),
            'browser'       => __( 'Browser', 'poptics' ),
            'location'      => __( 'Location', 'poptics' ),
            'other_details' => __( 'Other Details', 'poptics' ),
            'status'        => __( 'Status', 'poptics' ),
        ];
    }
}
