<?php
/**
 * Submission Importer Class
 *
 * @package Poptics
 */
namespace Poptics\Core\Submissions;

use Poptics\Base\Importer\Reader_Factory;

/**
 * Class Submission Importer
 */
class Submission_Importer {
    /**
     * Store File
     *
     * @since 1.0.5
     *
     * @var string
     */
    private $file;

    /**
     * Store data
     *
     * @since 1.0.5
     *
     * @var array
     */
    private $data;

    /**
     * Submission import
     *
     * @since 1.0.5
     *
     * @return  void
     */
    public function import( $file ) {
        $this->file  = $file;
        $file_reader = Reader_Factory::get_reader( $file );

        $this->data = $file_reader->read_file();
        $this->create_submission();
    }

    /**
     * Create Submission
     *
     * @return  void
     */
    private function create_submission() {
        $rows      = $this->data;
        $file_type = !empty( $this->file['type'] ) ? $this->file['type'] : '';

        foreach ( $rows as $row ) {
            $args = [
                'campaign_id'   => !empty( $row['campaign_id'] ) ? $row['campaign_id'] : 0,
                'email'         => !empty( $row['email'] ) ? sanitize_text_field( $row['email'] ) : '',
                'browser'       => !empty( $row['browser'] ) ? sanitize_text_field( $row['browser'] ) : '',
                'location'      => !empty( $row['location'] ) ? sanitize_text_field( $row['location'] ) : '',
                'device'        => !empty( $row['device'] ) ? sanitize_text_field( $row['device'] ) : '',
                'other_details' => !empty( $row['other_details'] ) ? poptics_sanitize_recursive( $row['other_details'] ) : '',
            ];

            if ( $file_type == 'text/csv' ) {
                $other_details = !empty( $row['other_details'] ) ? poptics_sanitize_recursive( $row['other_details'] ) : '';

                if ( !empty( $other_details ) ) {
                    $other_details = explode( ',', $other_details );
                    $details       = [];
                    foreach ( $other_details as $key => $value ) {
                        $single_details              = explode( ':', $value );
                        $details[$single_details[0]] = $single_details[1];
                    }
                    $args['other_details'] = $details;

                }

            } else {
                $args['other_details'] = !empty( $row['other_details'] ) ? poptics_sanitize_recursive( $row['other_details'] ) : '';
            }

            $submission = new Submission( $args );
            if ( !empty( $args['campaign_id'] ) ) {

                $match_case = [
                    'email'       => $args['email'],
                    'campaign_id' => $args['campaign_id'],
                ];
                $is_already_exists = $submission->check_if_submission_already_exists( $match_case );

                if ( !$is_already_exists ) {
                    $submission->save();
                    do_action( 'poptics_after_submission_create', $args );
                }
            } else {
                $submission->save();
            }
        }
    }
}
