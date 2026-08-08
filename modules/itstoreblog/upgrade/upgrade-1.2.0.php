<?php
/**
 * Add category / tags / author columns to the blog posts table.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_2_0($module)
{
    $table = _DB_PREFIX_ . 'itstore_blog_post';
    $columns = [
        'category' => 'VARCHAR(128) NOT NULL DEFAULT "" AFTER `tag`',
        'tags' => 'VARCHAR(512) NOT NULL DEFAULT "" AFTER `category`',
        'author' => 'VARCHAR(128) NOT NULL DEFAULT "" AFTER `tags`',
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
