<?php
/**
 * MageMind
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 *
 * @package     bin
 * @source      MMind\ProductMeta
 * @copyright   Copyright (c) 2015 MageMind (http://www.magemind.com)
 * @license     http://opensource.org/licenses/OSL-3.0
 *
 * Example of bin file
 */

// Execute only via shell
if (php_sapi_name() !== 'cli') {
    die ("Script executable only via shell");
}
error_reporting(E_ALL);
ini_set('display_errors', '1');

// EDIT: Set path to ProductMeta.php class
$_file_path = realpath(dirname(__FILE__));
require_once($_file_path.'/../src/MMind/ProductMeta/ProductMeta.php');

// EDIT: Magento root folder
$_magento_root = realpath(dirname(__FILE__)."/../");

// EDIT: Magento Store ID
$_store_id = 0;

// Execution
$productMeta = new MMind\ProductMeta\ProductMeta($_magento_root, $_store_id);
if(isset($argv[1]))
{
    switch($argv[1])
    {
        case "run":
            $productMeta->run();
            break;
        case "clean":
            $productMeta->clean();
            break;
        default:
            echo $productMeta->help();
    }
}
else {
    echo $productMeta->help();
}