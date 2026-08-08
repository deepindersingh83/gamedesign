<?php
/**
 * IT Store — Blog.
 *
 * A lightweight blog CMS: posts are stored in `itstore_blog_post`, managed from
 * a back-office tab (AdminItstoreBlog) and published through front controllers
 * for the index (list) and the article (post). The design's "From the Blog"
 * home block now shows the three latest published posts, falling back to the
 * configurable teaser cards when no posts exist yet.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'itstoreblog/classes/ItstoreBlogPost.php';

class Itstoreblog extends Module
{
    const N = 3;

    public function __construct()
    {
        $this->name = 'itstoreblog';
        $this->tab = 'front_office_features';
        $this->version = '1.2.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('IT Store Blog', [], 'Modules.Itstoreblog.Admin');
        $this->description = $this->trans('A lightweight blog: back-office posts, a listing page, article pages and a “From the Blog” home block.', [], 'Modules.Itstoreblog.Admin');
    }

    protected function defaults()
    {
        return [
            'ITSTORE_BL_TITLE' => $this->trans('From the Blog', [], 'Modules.Itstoreblog.Admin'),
            // Teaser fallback (used only when there are no published posts yet).
            'ITSTORE_BL_1_TAG' => $this->trans('Buying Guide', [], 'Modules.Itstoreblog.Admin'), 'ITSTORE_BL_1_TITLE' => $this->trans('How to spec a business desktop fleet in 2026', [], 'Modules.Itstoreblog.Admin'),
            'ITSTORE_BL_1_IMG' => '', 'ITSTORE_BL_1_LINK' => '',
            'ITSTORE_BL_2_TAG' => $this->trans('Gaming', [], 'Modules.Itstoreblog.Admin'), 'ITSTORE_BL_2_TITLE' => $this->trans('RTX 4070 vs 4070 Ti: which belongs in your build?', [], 'Modules.Itstoreblog.Admin'),
            'ITSTORE_BL_2_IMG' => '', 'ITSTORE_BL_2_LINK' => '',
            'ITSTORE_BL_3_TAG' => $this->trans('Networking', [], 'Modules.Itstoreblog.Admin'), 'ITSTORE_BL_3_TITLE' => $this->trans('Wi-Fi 6E in the office: is it worth upgrading?', [], 'Modules.Itstoreblog.Admin'),
            'ITSTORE_BL_3_IMG' => '', 'ITSTORE_BL_3_LINK' => '',
        ];
    }

    public function install()
    {
        if (!parent::install()
            || !$this->installTable()
            || !$this->registerHook('displayHome')
            || !$this->registerHook('actionFrontControllerSetMedia')
            || !$this->installTab()) {
            return false;
        }
        foreach ($this->defaults() as $k => $v) {
            Configuration::updateValue($k, $v);
        }
        $this->seedSamplePosts();

        return true;
    }

    public function uninstall()
    {
        $this->uninstallTab();
        Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'itstore_blog_post`;');
        foreach (array_keys($this->defaults()) as $k) {
            Configuration::deleteByName($k);
        }

        return parent::uninstall();
    }

    public function installTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'itstore_blog_post` (
            `id_post` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shop` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            `title` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(255) NOT NULL DEFAULT "",
            `tag` VARCHAR(128) NOT NULL DEFAULT "",
            `category` VARCHAR(128) NOT NULL DEFAULT "",
            `tags` VARCHAR(512) NOT NULL DEFAULT "",
            `author` VARCHAR(128) NOT NULL DEFAULT "",
            `excerpt` VARCHAR(1024) NOT NULL DEFAULT "",
            `content` TEXT,
            `image` VARCHAR(1024) NOT NULL DEFAULT "",
            `meta_title` VARCHAR(255) NOT NULL DEFAULT "",
            `meta_description` VARCHAR(512) NOT NULL DEFAULT "",
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_post`),
            KEY `active` (`active`, `id_shop`),
            KEY `slug` (`slug`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        return Db::getInstance()->execute($sql);
    }

    public function installTab()
    {
        $tab = new Tab();
        $tab->class_name = 'AdminItstoreBlog';
        $tab->module = $this->name;
        $tab->active = 1;
        $tab->icon = 'article';

        $idParent = (int) Tab::getIdFromClassName('AdminItstore');
        if (!$idParent) {
            $idParent = (int) Tab::getIdFromClassName('AdminParentModulesSf');
        }
        if (!$idParent) {
            $idParent = (int) Tab::getIdFromClassName('AdminParentModules');
        }
        $tab->id_parent = $idParent;

        $tab->name = [];
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = 'IT Store Blog';
        }

        try {
            return (bool) $tab->add();
        } catch (Exception $e) {
            return false;
        }
    }

    public function uninstallTab()
    {
        $idTab = (int) Tab::getIdFromClassName('AdminItstoreBlog');
        if ($idTab) {
            try {
                $tab = new Tab($idTab);

                return (bool) $tab->delete();
            } catch (Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Seed a few starter articles so the blog is not empty on first install.
     */
    protected function seedSamplePosts()
    {
        if (ItstoreBlogPost::countActive(0) > 0) {
            return;
        }
        $samples = [
            ['Buying Guide', 'Guides', 'desktops, fleet, procurement', 'How to spec a business desktop fleet in 2026', 'A practical checklist for standardising desktops across your organisation — CPU tiers, RAM, warranty and imaging.'],
            ['Gaming', 'Hardware', 'gpu, gaming, benchmarks', 'RTX 4070 vs 4070 Ti: which belongs in your build?', 'We compare price-to-performance, power draw and 1440p vs 4K targets to help you pick the right GPU.'],
            ['Networking', 'Guides', 'networking, wifi, office', 'Wi-Fi 6E in the office: is it worth upgrading?', 'What the 6 GHz band actually changes for a busy office, and when a wired backbone still wins.'],
        ];
        foreach ($samples as $s) {
            $post = new ItstoreBlogPost();
            $post->id_shop = 0;
            $post->tag = $s[0];
            $post->category = $s[1];
            $post->tags = $s[2];
            $post->author = 'IT Store Team';
            $post->title = $s[3];
            $post->excerpt = $s[4];
            $post->content = '<p>' . $s[4] . '</p><p>' . $this->trans('Edit this article from the IT Store Blog back office.', [], 'Modules.Itstoreblog.Admin') . '</p>';
            $post->image = '';
            $post->active = 1;
            try {
                $post->save();
            } catch (Exception $e) {
                // Skip a failed seed row; the blog still installs.
            }
        }
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'itstore-blog',
            'modules/' . $this->name . '/views/css/blog.css',
            ['media' => 'all', 'priority' => 122]
        );
    }

    public function listUrl()
    {
        return $this->context->link->getModuleLink($this->name, 'list', [], true);
    }

    public function postUrl($post)
    {
        $id = is_array($post) ? (int) $post['id_post'] : (int) $post->id;
        $slug = is_array($post) ? $post['slug'] : $post->slug;

        return $this->context->link->getModuleLink($this->name, 'post', ['id_post' => $id, 'slug' => $slug], true);
    }

    public function hookDisplayHome($params)
    {
        $idShop = (int) $this->context->shop->id;
        $dbPosts = ItstoreBlogPost::getActive($idShop, self::N);

        $posts = [];
        if ($dbPosts) {
            foreach ($dbPosts as $p) {
                $posts[] = [
                    'tag' => $p['tag'],
                    'title' => $p['title'],
                    'img' => $p['image'],
                    'link' => $this->postUrl($p),
                ];
            }
        } else {
            // Fallback teaser cards from configuration.
            for ($i = 1; $i <= self::N; $i++) {
                $title = Configuration::get('ITSTORE_BL_' . $i . '_TITLE');
                if ($title === false || trim($title) === '') {
                    continue;
                }
                $posts[] = [
                    'tag' => Configuration::get('ITSTORE_BL_' . $i . '_TAG'),
                    'title' => $title,
                    'img' => Configuration::get('ITSTORE_BL_' . $i . '_IMG'),
                    'link' => Configuration::get('ITSTORE_BL_' . $i . '_LINK') ?: $this->listUrl(),
                ];
            }
        }

        if (empty($posts)) {
            return '';
        }

        $this->smarty->assign([
            'bl_title' => Configuration::get('ITSTORE_BL_TITLE'),
            'bl_all' => $this->listUrl(),
            'bl_posts' => $posts,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/blog.tpl');
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitItstoreBl')) {
            Configuration::updateValue('ITSTORE_BL_TITLE', Tools::getValue('ITSTORE_BL_TITLE'));
            $output .= $this->displayConfirmation($this->trans('Settings saved.', [], 'Modules.Itstoreblog.Admin'));
        }

        $manageUrl = $this->context->link->getAdminLink('AdminItstoreBlog');
        $output .= '<div class="panel"><div class="panel-heading"><i class="icon-rss"></i> '
            . $this->trans('Blog', [], 'Modules.Itstoreblog.Admin') . '</div>'
            . '<p>' . $this->trans('Write and manage articles from the dedicated back-office page:', [], 'Modules.Itstoreblog.Admin') . '</p>'
            . '<a class="btn btn-primary" href="' . htmlspecialchars($manageUrl) . '"><i class="icon-edit"></i> '
            . $this->trans('Manage blog posts', [], 'Modules.Itstoreblog.Admin') . '</a></div>';

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $form = ['form' => [
            'legend' => ['title' => $this->trans('Home block', [], 'Modules.Itstoreblog.Admin'), 'icon' => 'icon-cogs'],
            'input' => [
                ['type' => 'text', 'label' => $this->trans('Block title', [], 'Modules.Itstoreblog.Admin'), 'name' => 'ITSTORE_BL_TITLE'],
            ],
            'submit' => ['title' => $this->trans('Save', [], 'Modules.Itstoreblog.Admin'), 'name' => 'submitItstoreBl'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreBl';
        $helper->fields_value = ['ITSTORE_BL_TITLE' => Configuration::get('ITSTORE_BL_TITLE')];

        return $helper->generateForm([$form]);
    }
}
