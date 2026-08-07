<?php
/**
 * Add quote → order conversion tracking columns to itstore_quote.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_2_0($module)
{
    $table = _DB_PREFIX_ . 'itstore_quote';
    $columns = [
        'id_customer' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0',
        'id_cart' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0',
        'status' => 'VARCHAR(32) NOT NULL DEFAULT "new"',
    ];

    $existing = Db::getInstance()->executeS('SHOW COLUMNS FROM `' . $table . '`') ?: [];
    $have = [];
    foreach ($existing as $col) {
        $have[$col['Field']] = true;
    }

    foreach ($columns as $name => $def) {
        if (!isset($have[$name])) {
            Db::getInstance()->execute('ALTER TABLE `' . $table . '` ADD `' . $name . '` ' . $def);
        }
    }

    return true;
}
