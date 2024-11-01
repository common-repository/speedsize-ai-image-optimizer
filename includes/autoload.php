<?php
/**
 * SpeedSize Image & Video AI-Optimizer - Worpress plugin
 *
 * @category Image Optimization / CDN / Page Speed & Performance
 * @package  speedsize
 * @author   SpeedSize (https://speedsize.com/)
 * @author   Developed by Pniel (Pini) Cohen | Trus (https://www.trus.co.il/)
 */

 if (! defined('ABSPATH')) {
     exit; // Exit if accessed directly.
 }

spl_autoload_register(function ($className = '') {
    if (strpos($className, 'SpeedSize_') !== 0) {
        return;
    }
    require_once __DIR__ . str_replace('_', DIRECTORY_SEPARATOR, substr($className, strlen('SpeedSize'))) . '.php';
});
