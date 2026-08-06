<?php
/**
 * IT Store — From the Blog.
 *
 * The design's blog preview: a 3-card grid (image, tag, title). Posts are
 * configurable (tag, title, image, link) — pair it with a CMS/blog for the
 * article pages.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstoreblog extends Module
{
    const N = 3;

    public function __construct()
    {
        $this->name = 'itstoreblog';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('IT Store Blog Preview', [], 'Modules.Itstoreblog.Admin');
        $this->description = $this->trans('“From the Blog” 3-card preview for the home page.', [], 'Modules.Itstoreblog.Admin');
    }

    protected function defaults()
    {
        return [
            'ITSTORE_BL_TITLE' => $this->trans('From the Blog', [], 'Modules.Itstoreblog.Admin'),
            'ITSTORE_BL_ALL' => '',
            'ITSTORE_BL_1_TAG' => $this->trans('Buying Guide', [], 'Modules.Itstoreblog.Admin'), 'ITSTORE_BL_1_TITLE' => $this->trans('How to spec a business desktop fleet in 2026', [], 'Modules.Itstoreblog.Admin'),
            'ITSTORE_BL_1_IMG' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=900&q=80', 'ITSTORE_BL_1_LINK' => '',
            'ITSTORE_BL_2_TAG' => $this->trans('Gaming', [], 'Modules.Itstoreblog.Admin'), 'ITSTORE_BL_2_TITLE' => $this->trans('RTX 4070 vs 4070 Ti: which belongs in your build?', [], 'Modules.Itstoreblog.Admin'),
            'ITSTORE_BL_2_IMG' => 'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?auto=format&fit=crop&w=900&q=80', 'ITSTORE_BL_2_LINK' => '',
            'ITSTORE_BL_3_TAG' => $this->trans('Networking', [], 'Modules.Itstoreblog.Admin'), 'ITSTORE_BL_3_TITLE' => $this->trans('Wi-Fi 6E in the office: is it worth upgrading?', [], 'Modules.Itstoreblog.Admin'),
            'ITSTORE_BL_3_IMG' => 'https://images.unsplash.com/photo-1591405351990-4726e331f141?auto=format&fit=crop&w=900&q=80', 'ITSTORE_BL_3_LINK' => '',
        ];
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayHome')
            || !$this->registerHook('actionFrontControllerSetMedia')) {
            return false;
        }
        foreach ($this->defaults() as $k => $v) {
            Configuration::updateValue($k, $v);
        }

        return true;
    }

    public function uninstall()
    {
        foreach (array_keys($this->defaults()) as $k) {
            Configuration::deleteByName($k);
        }

        return parent::uninstall();
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'itstore-blog',
            'modules/' . $this->name . '/views/css/blog.css',
            ['media' => 'all', 'priority' => 122]
        );
    }

    public function hookDisplayHome($params)
    {
        $posts = [];
        for ($i = 1; $i <= self::N; $i++) {
            $title = Configuration::get('ITSTORE_BL_' . $i . '_TITLE');
            if ($title === false || trim($title) === '') {
                continue;
            }
            $posts[] = [
                'tag' => Configuration::get('ITSTORE_BL_' . $i . '_TAG'),
                'title' => $title,
                'img' => Configuration::get('ITSTORE_BL_' . $i . '_IMG'),
                'link' => Configuration::get('ITSTORE_BL_' . $i . '_LINK') ?: '#',
            ];
        }
        if (empty($posts)) {
            return '';
        }
        $this->smarty->assign([
            'bl_title' => Configuration::get('ITSTORE_BL_TITLE'),
            'bl_all' => Configuration::get('ITSTORE_BL_ALL'),
            'bl_posts' => $posts,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/blog.tpl');
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitItstoreBl')) {
            foreach (array_keys($this->defaults()) as $k) {
                Configuration::updateValue($k, Tools::getValue($k));
            }
            $output .= $this->displayConfirmation($this->trans('Settings saved.', [], 'Modules.Itstoreblog.Admin'));
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $fields = [
            ['type' => 'text', 'label' => $this->trans('Block title', [], 'Modules.Itstoreblog.Admin'), 'name' => 'ITSTORE_BL_TITLE'],
            ['type' => 'text', 'label' => $this->trans('“View all” link', [], 'Modules.Itstoreblog.Admin'), 'name' => 'ITSTORE_BL_ALL'],
        ];
        for ($i = 1; $i <= self::N; $i++) {
            $fields[] = ['type' => 'text', 'label' => sprintf($this->trans('Post %d — tag', [], 'Modules.Itstoreblog.Admin'), $i), 'name' => 'ITSTORE_BL_' . $i . '_TAG'];
            $fields[] = ['type' => 'text', 'label' => sprintf($this->trans('Post %d — title', [], 'Modules.Itstoreblog.Admin'), $i), 'name' => 'ITSTORE_BL_' . $i . '_TITLE'];
            $fields[] = ['type' => 'text', 'label' => sprintf($this->trans('Post %d — image URL', [], 'Modules.Itstoreblog.Admin'), $i), 'name' => 'ITSTORE_BL_' . $i . '_IMG'];
            $fields[] = ['type' => 'text', 'label' => sprintf($this->trans('Post %d — link', [], 'Modules.Itstoreblog.Admin'), $i), 'name' => 'ITSTORE_BL_' . $i . '_LINK'];
        }

        $form = ['form' => [
            'legend' => ['title' => $this->trans('Blog preview', [], 'Modules.Itstoreblog.Admin'), 'icon' => 'icon-rss'],
            'input' => $fields,
            'submit' => ['title' => $this->trans('Save', [], 'Modules.Itstoreblog.Admin'), 'name' => 'submitItstoreBl'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreBl';
        $values = [];
        foreach (array_keys($this->defaults()) as $k) {
            $values[$k] = Configuration::get($k);
        }
        $helper->fields_value = $values;

        return $helper->generateForm([$form]);
    }
}
