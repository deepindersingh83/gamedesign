<?php
/**
 * Register the psgdpr export/delete hooks on already-installed instances.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_2_0($module)
{
    return $module->registerHook('actionExportGDPRData')
        && $module->registerHook('actionDeleteGDPRData');
}
