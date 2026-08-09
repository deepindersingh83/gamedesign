<?php
/**
 * IT Store — blog article (single post) front controller.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'itstoreblog/classes/ItstoreBlogPost.php';

class ItstoreblogPostModuleFrontController extends ModuleFrontController
{
    public $php_self = 'module-itstoreblog-post';

    /** @var ItstoreBlogPost|null */
    protected $post;

    public function init()
    {
        parent::init();

        $idPost = (int) Tools::getValue('id_post');
        $this->post = $idPost ? new ItstoreBlogPost($idPost) : null;

        $idShop = (int) $this->context->shop->id;
        $valid = $this->post
            && Validate::isLoadedObject($this->post)
            && (int) $this->post->active === 1
            && ((int) $this->post->id_shop === 0 || (int) $this->post->id_shop === $idShop);

        if (!$valid) {
            $this->post = null;
            header('HTTP/1.1 404 Not Found');
            $this->errors[] = $this->trans('Article not found.', [], 'Modules.Itstoreblog.Shop');
        }
    }

    public function initContent()
    {
        parent::initContent();

        if (!$this->post) {
            $this->setTemplate('errors/404.tpl');

            return;
        }

        $metaTitle = $this->post->meta_title ?: $this->post->title;
        $metaDesc = $this->post->meta_description ?: Tools::substr(strip_tags((string) $this->post->excerpt), 0, 250);

        // Override the page meta the theme prints in <head>.
        $page = $this->context->smarty->getTemplateVars('page');
        if (!is_array($page)) {
            $page = [];
        }
        $page['meta'] = array_merge(isset($page['meta']) && is_array($page['meta']) ? $page['meta'] : [], [
            'title' => $metaTitle,
            'description' => $metaDesc,
        ]);
        $this->context->smarty->assign('page', $page);

        $idShop = (int) $this->context->shop->id;

        $tags = [];
        foreach (ItstoreBlogPost::splitTags($this->post->tags) as $t) {
            $tags[] = [
                'name' => $t,
                'url' => $this->context->link->getModuleLink('itstoreblog', 'list', ['tag' => $t], true),
            ];
        }

        $related = [];
        foreach (ItstoreBlogPost::getRelated($idShop, (int) $this->post->id, (string) $this->post->category, 3) as $r) {
            $related[] = [
                'title' => $r['title'],
                'image' => $r['image'],
                'date' => Tools::displayDate($r['date_add']),
                'url' => $this->context->link->getModuleLink('itstoreblog', 'post', ['id_post' => (int) $r['id_post'], 'slug' => $r['slug']], true),
            ];
        }

        $this->context->smarty->assign([
            'post' => [
                'title' => $this->post->title,
                'tag' => $this->post->tag,
                'category' => $this->post->category,
                'category_url' => $this->post->category ? $this->context->link->getModuleLink('itstoreblog', 'list', ['cat' => $this->post->category], true) : '',
                'author' => $this->post->author,
                'image' => $this->post->image,
                'content' => $this->post->content,
                'date' => Tools::displayDate($this->post->date_add),
                'tags' => $tags,
            ],
            'post_related' => $related,
            'post_jsonld' => $this->buildJsonLd($metaDesc),
            'blog_list_url' => $this->context->link->getModuleLink('itstoreblog', 'list', [], true),
        ]);

        $this->setTemplate('module:itstoreblog/views/templates/front/post.tpl');
    }

    protected function buildJsonLd($description)
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $this->post->title,
            'datePublished' => date('c', strtotime($this->post->date_add)),
            'dateModified' => date('c', strtotime($this->post->date_upd ?: $this->post->date_add)),
            'description' => $description,
            'publisher' => ['@type' => 'Organization', 'name' => Configuration::get('PS_SHOP_NAME')],
        ];
        if ($this->post->image) {
            $data['image'] = $this->post->image;
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = [
            'title' => Configuration::get('ITSTORE_BL_TITLE') ?: $this->trans('Blog', [], 'Modules.Itstoreblog.Shop'),
            'url' => $this->context->link->getModuleLink('itstoreblog', 'list', [], true),
        ];
        if ($this->post) {
            $breadcrumb['links'][] = ['title' => $this->post->title, 'url' => ''];
        }

        return $breadcrumb;
    }
}
