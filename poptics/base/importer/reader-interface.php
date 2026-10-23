<?php
/**
 * File Reader Interface
 * 
 * @package Poptics
 */
namespace Poptics\Base\Importer;

/**
 * Reader Interface
 */
interface Reader_Interface {
    /**
     * Read file
     *
     * @return  array
     */
    public function read_file();
}
