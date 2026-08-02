<?php
/**
 * IT Store — Image Slot (hero slider) module.
 *
 * Displays a configurable, responsive hero slider on the home page using the
 * theme's `image-slot.js` component. Slides are stored in a dedicated table
 * and managed from the module's back-office configuration screen.
 *
 * @author    Syber Info <admin@syberinfo.com.au>
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstoreimageslot extends Module
{
    public function __construct()
    {
        $this->name = 'itstoreimageslot';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IT Store Image Slot');
        $this->description = $this->l('Responsive hero image slider for the IT Store home page.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the Image Slot? All slides will be removed.');
    }

    /**
     * Install: create the slides table, register hooks and seed demo slides.
     */
    public function install()
    {
        return parent::install()
            && $this->installDb()
            && $this->registerHook('displayHome')
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->installDefaultSlides();
    }

    public function uninstall()
    {
        return $this->uninstallDb() && parent::uninstall();
    }

    protected function installDb()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'itstore_slide` (
            `id_itstore_slide` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shop` INT(10) UNSIGNED NOT NULL DEFAULT 1,
            `image` VARCHAR(255) NOT NULL DEFAULT "",
            `title` VARCHAR(255) NOT NULL DEFAULT "",
            `subtitle` VARCHAR(255) NOT NULL DEFAULT "",
            `caption` TEXT,
            `btn_text` VARCHAR(128) NOT NULL DEFAULT "",
            `btn_link` VARCHAR(255) NOT NULL DEFAULT "",
            `position` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
            PRIMARY KEY (`id_itstore_slide`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        return Db::getInstance()->execute($sql);
    }

    protected function uninstallDb()
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'itstore_slide`;');
    }

    protected function installDefaultSlides()
    {
        $shopId = (int) $this->context->shop->id;
        $demo = [
            [
                'image' => 'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?auto=format&fit=crop&w=1600&q=80',
                'title' => $this->l('Build Your Dream Rig'),
                'subtitle' => $this->l('Custom PCs, components & peripherals'),
                'caption' => $this->l('Hand-picked hardware from the brands you trust.'),
                'btn_text' => $this->l('Shop components'),
                'btn_link' => $this->context->link->getPageLink('category', true, null, 'id_category=2'),
            ],
            [
                'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=1600&q=80',
                'title' => $this->l('Laptops for Work & Play'),
                'subtitle' => $this->l('Ultrabooks, gaming & business laptops'),
                'caption' => $this->l('Next-day delivery on in-stock machines.'),
                'btn_text' => $this->l('Browse laptops'),
                'btn_link' => $this->context->link->getPageLink('category', true, null, 'id_category=3'),
            ],
            [
                'image' => 'https://images.unsplash.com/photo-1591405351990-4726e331f141?auto=format&fit=crop&w=1600&q=80',
                'title' => $this->l('Networking & Smart Office'),
                'subtitle' => $this->l('Routers, switches & Wi-Fi 6E'),
                'caption' => $this->l('Set up a faster, more reliable network today.'),
                'btn_text' => $this->l('Explore networking'),
                'btn_link' => $this->context->link->getPageLink('category', true, null, 'id_category=4'),
            ],
        ];

        $position = 0;
        foreach ($demo as $slide) {
            Db::getInstance()->insert('itstore_slide', [
                'id_shop' => $shopId,
                'image' => pSQL($slide['image']),
                'title' => pSQL($slide['title']),
                'subtitle' => pSQL($slide['subtitle']),
                'caption' => pSQL($slide['caption']),
                'btn_text' => pSQL($slide['btn_text']),
                'btn_link' => pSQL($slide['btn_link']),
                'position' => (int) $position++,
                'active' => 1,
            ]);
        }

        return true;
    }

    /**
     * Load the slider assets on the front office.
     */
    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'itstore-imageslot',
            'modules/' . $this->name . '/views/css/imageslot.css',
            ['media' => 'all', 'priority' => 120]
        );
        $this->context->controller->registerJavascript(
            'itstore-imageslot',
            'modules/' . $this->name . '/views/js/image-slot.js',
            ['position' => 'bottom', 'priority' => 120]
        );
    }

    /**
     * Render the hero slider on the home page.
     */
    public function hookDisplayHome($params)
    {
        $slides = $this->getSlides();
        if (empty($slides)) {
            return '';
        }

        $this->smarty->assign([
            'itstore_slides' => $slides,
            'itstore_slider_interval' => (int) Configuration::get('ITSTORE_SLOT_INTERVAL', null, null, null, 6000),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/imageslot.tpl');
    }

    /**
     * @return array Active slides for the current shop, ordered by position.
     */
    protected function getSlides()
    {
        $shopId = (int) $this->context->shop->id;

        return Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'itstore_slide`
             WHERE `active` = 1 AND `id_shop` = ' . $shopId . '
             ORDER BY `position` ASC, `id_itstore_slide` ASC'
        ) ?: [];
    }

    /**
     * Back-office configuration: manage slides.
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitItstoreSlide')) {
            $output .= $this->processSlideForm();
        } elseif (Tools::isSubmit('deleteItstoreSlide')) {
            $id = (int) Tools::getValue('id_itstore_slide');
            Db::getInstance()->delete('itstore_slide', 'id_itstore_slide = ' . $id);
            $output .= $this->displayConfirmation($this->l('Slide deleted.'));
        } elseif (Tools::isSubmit('toggleItstoreSlide')) {
            $id = (int) Tools::getValue('id_itstore_slide');
            Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'itstore_slide`
                 SET `active` = 1 - `active` WHERE `id_itstore_slide` = ' . $id
            );
            $output .= $this->displayConfirmation($this->l('Slide updated.'));
        }

        return $output . $this->renderSlidesList() . $this->renderSlideForm();
    }

    protected function processSlideForm()
    {
        $image = trim(Tools::getValue('image'));
        $btnLink = trim(Tools::getValue('btn_link'));

        if ($image !== '' && !Validate::isCleanHtml($image)) {
            return $this->displayError($this->l('The image URL is not valid.'));
        }
        if ($btnLink !== '' && !Validate::isUrlOrEmpty($btnLink)) {
            return $this->displayError($this->l('The button link is not valid.'));
        }

        $data = [
            'id_shop' => (int) $this->context->shop->id,
            'image' => pSQL($image),
            'title' => pSQL(Tools::getValue('title')),
            'subtitle' => pSQL(Tools::getValue('subtitle')),
            'caption' => pSQL(Tools::getValue('caption'), true),
            'btn_text' => pSQL(Tools::getValue('btn_text')),
            'btn_link' => pSQL($btnLink),
            'position' => (int) Tools::getValue('position'),
            'active' => (int) Tools::getValue('active'),
        ];

        $id = (int) Tools::getValue('id_itstore_slide');
        if ($id > 0) {
            Db::getInstance()->update('itstore_slide', $data, 'id_itstore_slide = ' . $id);

            return $this->displayConfirmation($this->l('Slide saved.'));
        }

        Db::getInstance()->insert('itstore_slide', $data);

        return $this->displayConfirmation($this->l('Slide added.'));
    }

    protected function renderSlidesList()
    {
        $slides = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'itstore_slide`
             WHERE `id_shop` = ' . (int) $this->context->shop->id . '
             ORDER BY `position` ASC'
        ) ?: [];

        $token = Tools::getAdminTokenLite('AdminModules');
        $base = $this->context->link->getAdminLink('AdminModules', false)
            . '&token=' . $token
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;

        $rows = '';
        foreach ($slides as $s) {
            $rows .= '<tr>'
                . '<td>' . (int) $s['position'] . '</td>'
                . '<td><img src="' . htmlspecialchars($s['image']) . '" alt="" style="max-height:40px;border-radius:4px"></td>'
                . '<td>' . htmlspecialchars($s['title']) . '</td>'
                . '<td>' . ($s['active'] ? '<span class="badge badge-success">' . $this->l('On') . '</span>'
                    : '<span class="badge badge-danger">' . $this->l('Off') . '</span>') . '</td>'
                . '<td class="text-right">'
                . '<a class="btn btn-default btn-xs" href="' . $base . '&toggleItstoreSlide&id_itstore_slide=' . (int) $s['id_itstore_slide'] . '">'
                . '<i class="icon-refresh"></i> ' . $this->l('Toggle') . '</a> '
                . '<a class="btn btn-default btn-xs" href="' . $base . '&editItstoreSlide&id_itstore_slide=' . (int) $s['id_itstore_slide'] . '">'
                . '<i class="icon-pencil"></i> ' . $this->l('Edit') . '</a> '
                . '<a class="btn btn-danger btn-xs" onclick="return confirm(\'' . $this->l('Delete this slide?') . '\')" '
                . 'href="' . $base . '&deleteItstoreSlide&id_itstore_slide=' . (int) $s['id_itstore_slide'] . '">'
                . '<i class="icon-trash"></i> ' . $this->l('Delete') . '</a>'
                . '</td></tr>';
        }

        if ($rows === '') {
            $rows = '<tr><td colspan="5">' . $this->l('No slides yet — add one below.') . '</td></tr>';
        }

        return '<div class="panel"><div class="panel-heading"><i class="icon-picture"></i> '
            . $this->l('Hero slides') . '</div>'
            . '<table class="table"><thead><tr>'
            . '<th>' . $this->l('#') . '</th><th>' . $this->l('Image') . '</th><th>' . $this->l('Title') . '</th>'
            . '<th>' . $this->l('Status') . '</th><th class="text-right">' . $this->l('Actions') . '</th>'
            . '</tr></thead><tbody>' . $rows . '</tbody></table></div>';
    }

    protected function renderSlideForm()
    {
        $edit = [];
        if (Tools::isSubmit('editItstoreSlide')) {
            $id = (int) Tools::getValue('id_itstore_slide');
            $edit = Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'itstore_slide` WHERE `id_itstore_slide` = ' . $id
            ) ?: [];
        }

        $fields = [
            ['type' => 'hidden', 'name' => 'id_itstore_slide'],
            [
                'type' => 'text',
                'label' => $this->l('Image URL'),
                'name' => 'image',
                'desc' => $this->l('Full URL or path to the slide background image (recommended 1600×640).'),
            ],
            ['type' => 'text', 'label' => $this->l('Title'), 'name' => 'title'],
            ['type' => 'text', 'label' => $this->l('Subtitle'), 'name' => 'subtitle'],
            ['type' => 'textarea', 'label' => $this->l('Caption'), 'name' => 'caption'],
            ['type' => 'text', 'label' => $this->l('Button text'), 'name' => 'btn_text'],
            ['type' => 'text', 'label' => $this->l('Button link'), 'name' => 'btn_link'],
            ['type' => 'text', 'label' => $this->l('Position'), 'name' => 'position', 'class' => 'fixed-width-sm'],
            [
                'type' => 'switch',
                'label' => $this->l('Active'),
                'name' => 'active',
                'is_bool' => true,
                'values' => [
                    ['id' => 'active_on', 'value' => 1, 'label' => $this->l('Yes')],
                    ['id' => 'active_off', 'value' => 0, 'label' => $this->l('No')],
                ],
            ],
        ];

        $form = [
            'form' => [
                'legend' => [
                    'title' => empty($edit) ? $this->l('Add a slide') : $this->l('Edit slide'),
                    'icon' => 'icon-plus',
                ],
                'input' => $fields,
                'submit' => ['title' => $this->l('Save'), 'name' => 'submitItstoreSlide'],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreSlide';
        $helper->fields_value = [
            'id_itstore_slide' => isset($edit['id_itstore_slide']) ? (int) $edit['id_itstore_slide'] : 0,
            'image' => isset($edit['image']) ? $edit['image'] : '',
            'title' => isset($edit['title']) ? $edit['title'] : '',
            'subtitle' => isset($edit['subtitle']) ? $edit['subtitle'] : '',
            'caption' => isset($edit['caption']) ? $edit['caption'] : '',
            'btn_text' => isset($edit['btn_text']) ? $edit['btn_text'] : '',
            'btn_link' => isset($edit['btn_link']) ? $edit['btn_link'] : '',
            'position' => isset($edit['position']) ? (int) $edit['position'] : 0,
            'active' => isset($edit['active']) ? (int) $edit['active'] : 1,
        ];

        return $helper->generateForm([$form]);
    }
}
