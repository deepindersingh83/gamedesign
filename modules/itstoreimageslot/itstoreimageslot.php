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

        $this->displayName = $this->trans('IT Store Image Slot', [], 'Modules.Itstoreimageslot.Admin');
        $this->description = $this->trans('Responsive hero image slider for the IT Store home page.', [], 'Modules.Itstoreimageslot.Admin');
        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall the Image Slot? All slides will be removed.', [], 'Modules.Itstoreimageslot.Admin');
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
                'title' => $this->trans('Build Your Dream Rig', [], 'Modules.Itstoreimageslot.Admin'),
                'subtitle' => $this->trans('Custom PCs, components & peripherals', [], 'Modules.Itstoreimageslot.Admin'),
                'caption' => $this->trans('Hand-picked hardware from the brands you trust.', [], 'Modules.Itstoreimageslot.Admin'),
                'btn_text' => $this->trans('Shop components', [], 'Modules.Itstoreimageslot.Admin'),
                'btn_link' => $this->context->link->getPageLink('category', true, null, 'id_category=2'),
            ],
            [
                'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=1600&q=80',
                'title' => $this->trans('Laptops for Work & Play', [], 'Modules.Itstoreimageslot.Admin'),
                'subtitle' => $this->trans('Ultrabooks, gaming & business laptops', [], 'Modules.Itstoreimageslot.Admin'),
                'caption' => $this->trans('Next-day delivery on in-stock machines.', [], 'Modules.Itstoreimageslot.Admin'),
                'btn_text' => $this->trans('Browse laptops', [], 'Modules.Itstoreimageslot.Admin'),
                'btn_link' => $this->context->link->getPageLink('category', true, null, 'id_category=3'),
            ],
            [
                'image' => 'https://images.unsplash.com/photo-1591405351990-4726e331f141?auto=format&fit=crop&w=1600&q=80',
                'title' => $this->trans('Networking & Smart Office', [], 'Modules.Itstoreimageslot.Admin'),
                'subtitle' => $this->trans('Routers, switches & Wi-Fi 6E', [], 'Modules.Itstoreimageslot.Admin'),
                'caption' => $this->trans('Set up a faster, more reliable network today.', [], 'Modules.Itstoreimageslot.Admin'),
                'btn_text' => $this->trans('Explore networking', [], 'Modules.Itstoreimageslot.Admin'),
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
            ['position' => 'bottom', 'priority' => 120, 'attribute' => 'defer']
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
            $output .= $this->displayConfirmation($this->trans('Slide deleted.', [], 'Modules.Itstoreimageslot.Admin'));
        } elseif (Tools::isSubmit('toggleItstoreSlide')) {
            $id = (int) Tools::getValue('id_itstore_slide');
            Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'itstore_slide`
                 SET `active` = 1 - `active` WHERE `id_itstore_slide` = ' . $id
            );
            $output .= $this->displayConfirmation($this->trans('Slide updated.', [], 'Modules.Itstoreimageslot.Admin'));
        }

        return $output . $this->renderSlidesList() . $this->renderSlideForm();
    }

    protected function processSlideForm()
    {
        $image = trim(Tools::getValue('image'));
        $btnLink = trim(Tools::getValue('btn_link'));

        if ($image !== '' && !Validate::isCleanHtml($image)) {
            return $this->displayError($this->trans('The image URL is not valid.', [], 'Modules.Itstoreimageslot.Admin'));
        }
        if ($btnLink !== '' && !Validate::isUrlOrEmpty($btnLink)) {
            return $this->displayError($this->trans('The button link is not valid.', [], 'Modules.Itstoreimageslot.Admin'));
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

            return $this->displayConfirmation($this->trans('Slide saved.', [], 'Modules.Itstoreimageslot.Admin'));
        }

        Db::getInstance()->insert('itstore_slide', $data);

        return $this->displayConfirmation($this->trans('Slide added.', [], 'Modules.Itstoreimageslot.Admin'));
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
                . '<td>' . ($s['active'] ? '<span class="badge badge-success">' . $this->trans('On', [], 'Modules.Itstoreimageslot.Admin') . '</span>'
                    : '<span class="badge badge-danger">' . $this->trans('Off', [], 'Modules.Itstoreimageslot.Admin') . '</span>') . '</td>'
                . '<td class="text-right">'
                . '<a class="btn btn-default btn-xs" href="' . $base . '&toggleItstoreSlide&id_itstore_slide=' . (int) $s['id_itstore_slide'] . '">'
                . '<i class="icon-refresh"></i> ' . $this->trans('Toggle', [], 'Modules.Itstoreimageslot.Admin') . '</a> '
                . '<a class="btn btn-default btn-xs" href="' . $base . '&editItstoreSlide&id_itstore_slide=' . (int) $s['id_itstore_slide'] . '">'
                . '<i class="icon-pencil"></i> ' . $this->trans('Edit', [], 'Modules.Itstoreimageslot.Admin') . '</a> '
                . '<a class="btn btn-danger btn-xs" onclick="return confirm(\'' . $this->trans('Delete this slide?', [], 'Modules.Itstoreimageslot.Admin') . '\')" '
                . 'href="' . $base . '&deleteItstoreSlide&id_itstore_slide=' . (int) $s['id_itstore_slide'] . '">'
                . '<i class="icon-trash"></i> ' . $this->trans('Delete', [], 'Modules.Itstoreimageslot.Admin') . '</a>'
                . '</td></tr>';
        }

        if ($rows === '') {
            $rows = '<tr><td colspan="5">' . $this->trans('No slides yet — add one below.', [], 'Modules.Itstoreimageslot.Admin') . '</td></tr>';
        }

        return '<div class="panel"><div class="panel-heading"><i class="icon-picture"></i> '
            . $this->trans('Hero slides', [], 'Modules.Itstoreimageslot.Admin') . '</div>'
            . '<table class="table"><thead><tr>'
            . '<th>' . $this->trans('#', [], 'Modules.Itstoreimageslot.Admin') . '</th><th>' . $this->trans('Image', [], 'Modules.Itstoreimageslot.Admin') . '</th><th>' . $this->trans('Title', [], 'Modules.Itstoreimageslot.Admin') . '</th>'
            . '<th>' . $this->trans('Status', [], 'Modules.Itstoreimageslot.Admin') . '</th><th class="text-right">' . $this->trans('Actions', [], 'Modules.Itstoreimageslot.Admin') . '</th>'
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
                'label' => $this->trans('Image URL', [], 'Modules.Itstoreimageslot.Admin'),
                'name' => 'image',
                'desc' => $this->trans('Full URL or path to the slide background image (recommended 1600×640).', [], 'Modules.Itstoreimageslot.Admin'),
            ],
            ['type' => 'text', 'label' => $this->trans('Title', [], 'Modules.Itstoreimageslot.Admin'), 'name' => 'title'],
            ['type' => 'text', 'label' => $this->trans('Subtitle', [], 'Modules.Itstoreimageslot.Admin'), 'name' => 'subtitle'],
            ['type' => 'textarea', 'label' => $this->trans('Caption', [], 'Modules.Itstoreimageslot.Admin'), 'name' => 'caption'],
            ['type' => 'text', 'label' => $this->trans('Button text', [], 'Modules.Itstoreimageslot.Admin'), 'name' => 'btn_text'],
            ['type' => 'text', 'label' => $this->trans('Button link', [], 'Modules.Itstoreimageslot.Admin'), 'name' => 'btn_link'],
            ['type' => 'text', 'label' => $this->trans('Position', [], 'Modules.Itstoreimageslot.Admin'), 'name' => 'position', 'class' => 'fixed-width-sm'],
            [
                'type' => 'switch',
                'label' => $this->trans('Active', [], 'Modules.Itstoreimageslot.Admin'),
                'name' => 'active',
                'is_bool' => true,
                'values' => [
                    ['id' => 'active_on', 'value' => 1, 'label' => $this->trans('Yes', [], 'Modules.Itstoreimageslot.Admin')],
                    ['id' => 'active_off', 'value' => 0, 'label' => $this->trans('No', [], 'Modules.Itstoreimageslot.Admin')],
                ],
            ],
        ];

        $form = [
            'form' => [
                'legend' => [
                    'title' => empty($edit) ? $this->trans('Add a slide', [], 'Modules.Itstoreimageslot.Admin') : $this->trans('Edit slide', [], 'Modules.Itstoreimageslot.Admin'),
                    'icon' => 'icon-plus',
                ],
                'input' => $fields,
                'submit' => ['title' => $this->trans('Save', [], 'Modules.Itstoreimageslot.Admin'), 'name' => 'submitItstoreSlide'],
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
