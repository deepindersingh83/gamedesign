<?php
/**
 * IT Store — blog index (listing) front controller.
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
        $total = ItstoreBlogPost::countActive($idShop);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $pages);

        $rows = ItstoreBlogPost::getActive($idShop, $perPage, ($page - 1) * $perPage);
        $posts = [];
        foreach ($rows as $p) {
            $posts[] = [
                'id_post' => (int) $p['id_post'],
                'title' => $p['title'],
                'tag' => $p['tag'],
                'excerpt' => $p['excerpt'],
                'image' => $p['image'],
                'date' => Tools::displayDate($p['date_add']),
                'url' => $this->context->link->getModuleLink('itstoreblog', 'post', ['id_post' => (int) $p['id_post'], 'slug' => $p['slug']], true),
            ];
        }

        $this->context->smarty->assign([
            'blog_title' => Configuration::get('ITSTORE_BL_TITLE') ?: $this->trans('Blog', [], 'Modules.Itstoreblog.Shop'),
            'blog_posts' => $posts,
            'blog_page' => $page,
            'blog_pages' => $pages,
            'blog_prev' => $page > 1 ? $this->pageUrl($page - 1) : '',
            'blog_next' => $page < $pages ? $this->pageUrl($page + 1) : '',
        ]);

        $this->setTemplate('module:itstoreblog/views/templates/front/list.tpl');
    }

    protected function pageUrl($page)
    {
        return $this->context->link->getModuleLink('itstoreblog', 'list', ['page' => (int) $page], true);
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
