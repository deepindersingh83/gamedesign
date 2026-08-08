<?php
/**
 * IT Store — blog index (listing) front controller.
 *
 * Supports optional ?cat= (category) and ?tag= filters.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'itstoreblog/classes/ItstoreBlogPost.php';

class ItstoreblogListModuleFrontController extends ModuleFrontController
{
    public $php_self = 'module-itstoreblog-list';

    public function initContent()
    {
        parent::initContent();

        $idShop = (int) $this->context->shop->id;
        $perPage = 9;
        $page = max(1, (int) Tools::getValue('page', 1));
        $category = trim((string) Tools::getValue('cat'));
        $tag = trim((string) Tools::getValue('tag'));

        $total = ItstoreBlogPost::countFiltered($idShop, $category, $tag);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $pages);

        $rows = ItstoreBlogPost::getFiltered($idShop, $category, $tag, $perPage, ($page - 1) * $perPage);
        $posts = [];
        foreach ($rows as $p) {
            $posts[] = [
                'id_post' => (int) $p['id_post'],
                'title' => $p['title'],
                'tag' => $p['tag'],
                'category' => $p['category'],
                'author' => $p['author'],
                'excerpt' => $p['excerpt'],
                'image' => $p['image'],
                'date' => Tools::displayDate($p['date_add']),
                'url' => $this->context->link->getModuleLink('itstoreblog', 'post', ['id_post' => (int) $p['id_post'], 'slug' => $p['slug']], true),
                'cat_url' => $p['category'] !== '' ? $this->context->link->getModuleLink('itstoreblog', 'list', ['cat' => $p['category']], true) : '',
            ];
        }

        $categories = [];
        foreach (ItstoreBlogPost::getCategories($idShop) as $c) {
            $categories[] = [
                'name' => $c['category'],
                'count' => (int) $c['n'],
                'url' => $this->context->link->getModuleLink('itstoreblog', 'list', ['cat' => $c['category']], true),
                'active' => $c['category'] === $category,
            ];
        }

        $heading = Configuration::get('ITSTORE_BL_TITLE') ?: $this->trans('Blog', [], 'Modules.Itstoreblog.Shop');
        $filterLabel = '';
        if ($category !== '') {
            $filterLabel = $category;
        } elseif ($tag !== '') {
            $filterLabel = '#' . $tag;
        }

        $this->context->smarty->assign([
            'blog_title' => $heading,
            'blog_filter_label' => $filterLabel,
            'blog_categories' => $categories,
            'blog_all_url' => $this->context->link->getModuleLink('itstoreblog', 'list', [], true),
            'blog_rss_url' => $this->context->link->getModuleLink('itstoreblog', 'rss', [], true),
            'blog_posts' => $posts,
            'blog_page' => $page,
            'blog_pages' => $pages,
            'blog_prev' => $page > 1 ? $this->pageUrl($page - 1, $category, $tag) : '',
            'blog_next' => $page < $pages ? $this->pageUrl($page + 1, $category, $tag) : '',
        ]);

        $this->setTemplate('module:itstoreblog/views/templates/front/list.tpl');
    }

    protected function pageUrl($page, $category = '', $tag = '')
    {
        $params = ['page' => (int) $page];
        if ($category !== '') {
            $params['cat'] = $category;
        }
        if ($tag !== '') {
            $params['tag'] = $tag;
        }

        return $this->context->link->getModuleLink('itstoreblog', 'list', $params, true);
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = [
            'title' => Configuration::get('ITSTORE_BL_TITLE') ?: $this->trans('Blog', [], 'Modules.Itstoreblog.Shop'),
            'url' => $this->context->link->getModuleLink('itstoreblog', 'list', [], true),
        ];

        return $breadcrumb;
    }
}
