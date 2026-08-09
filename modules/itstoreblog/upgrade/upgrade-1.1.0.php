<?php
/**
 * Upgrade the "blog preview" module into the real blog CMS: create the posts
 * table + back-office tab and seed a few starter articles.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_0($module)
{
    $ok = $module->installTable() && $module->installTab();

    // Seed sample posts on an empty table (reuses the module's protected seeder
    // via a fresh install-time path is not accessible here, so seed inline).
    require_once _PS_MODULE_DIR_ . 'itstoreblog/classes/ItstoreBlogPost.php';
    if ($ok && ItstoreBlogPost::countActive(0) === 0) {
        $samples = [
            ['Buying Guide', 'How to spec a business desktop fleet in 2026', 'A practical checklist for standardising desktops across your organisation.'],
            ['Gaming', 'RTX 4070 vs 4070 Ti: which belongs in your build?', 'Price-to-performance, power draw and 1440p vs 4K targets compared.'],
            ['Networking', 'Wi-Fi 6E in the office: is it worth upgrading?', 'What the 6 GHz band changes for a busy office.'],
        ];
        foreach ($samples as $s) {
            $post = new ItstoreBlogPost();
            $post->id_shop = 0;
            $post->title = $s[1];
            $post->tag = $s[0];
            $post->excerpt = $s[2];
            $post->content = '<p>' . $s[2] . '</p>';
            $post->active = 1;
            try {
                $post->save();
            } catch (Exception $e) {
                // ignore a failed seed row
            }
        }
    }

    return $ok;
}
