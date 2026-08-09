<?php
/**
 * IT Store — blog RSS 2.0 feed.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'itstoreblog/classes/ItstoreBlogPost.php';

class ItstoreblogRssModuleFrontController extends ModuleFrontController
{
    /** No template — we stream XML directly. */
    public function initContent()
    {
        $idShop = (int) $this->context->shop->id;
        $shopName = Configuration::get('PS_SHOP_NAME');
        $title = (Configuration::get('ITSTORE_BL_TITLE') ?: 'Blog') . ' — ' . $shopName;
        $selfUrl = $this->context->link->getModuleLink('itstoreblog', 'rss', [], true);
        $listUrl = $this->context->link->getModuleLink('itstoreblog', 'list', [], true);

        $posts = ItstoreBlogPost::getActive($idShop, 20);

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"></rss>');
        $channel = $xml->addChild('channel');
        $channel->addChild('title', htmlspecialchars($title, ENT_XML1));
        $channel->addChild('link', htmlspecialchars($listUrl, ENT_XML1));
        $channel->addChild('description', htmlspecialchars($this->trans('Latest articles', [], 'Modules.Itstoreblog.Shop'), ENT_XML1));
        $channel->addChild('language', $this->context->language->iso_code);
        $atom = $channel->addChild('atom:link', null, 'http://www.w3.org/2005/Atom');
        $atom->addAttribute('href', $selfUrl);
        $atom->addAttribute('rel', 'self');
        $atom->addAttribute('type', 'application/rss+xml');

        foreach ($posts as $p) {
            $url = $this->context->link->getModuleLink('itstoreblog', 'post', ['id_post' => (int) $p['id_post'], 'slug' => $p['slug']], true);
            $item = $channel->addChild('item');
            $item->addChild('title', htmlspecialchars($p['title'], ENT_XML1));
            $item->addChild('link', htmlspecialchars($url, ENT_XML1));
            $item->addChild('guid', htmlspecialchars($url, ENT_XML1));
            $item->addChild('pubDate', date(DATE_RSS, strtotime($p['date_add'])));
            if (!empty($p['category'])) {
                $item->addChild('category', htmlspecialchars($p['category'], ENT_XML1));
            }
            $desc = $p['excerpt'] !== '' ? $p['excerpt'] : Tools::substr(strip_tags((string) $p['content']), 0, 300);
            $item->addChild('description', htmlspecialchars($desc, ENT_XML1));
        }

        if (!headers_sent()) {
            header('Content-Type: application/rss+xml; charset=utf-8');
        }
        echo $xml->asXML();
        exit;
    }
}
