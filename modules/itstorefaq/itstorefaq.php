<?php
/**
 * IT Store — FAQ.
 *
 * A simple FAQ: entries stored in `itstore_faq`, managed from a back-office tab
 * (AdminItstoreFaq) and published on a front page grouped by category, with
 * FAQPage JSON-LD for rich results.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'itstorefaq/classes/ItstoreFaqItem.php';

class Itstorefaq extends Module
{
    public function __construct()
    {
        $this->name = 'itstorefaq';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('IT Store FAQ', [], 'Modules.Itstorefaq.Admin');
        $this->description = $this->trans('A categorised FAQ page with accordion answers and FAQPage structured data.', [], 'Modules.Itstorefaq.Admin');
    }

    public function install()
    {
        if (!parent::install()
            || !$this->installTable()
            || !$this->registerHook('actionFrontControllerSetMedia')
            || !$this->installTab()) {
            return false;
        }
        Configuration::updateValue('ITSTORE_FAQ_TITLE', $this->trans('Frequently asked questions', [], 'Modules.Itstorefaq.Admin'));
        $this->seed();

        return true;
    }

    public function uninstall()
    {
        $this->uninstallTab();
        Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'itstore_faq`;');
        Configuration::deleteByName('ITSTORE_FAQ_TITLE');

        return parent::uninstall();
    }

    public function installTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'itstore_faq` (
            `id_faq` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shop` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            `category` VARCHAR(128) NOT NULL DEFAULT "",
            `question` VARCHAR(512) NOT NULL,
            `answer` TEXT,
            `position` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_faq`),
            KEY `active` (`active`, `id_shop`, `category`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        return Db::getInstance()->execute($sql);
    }

    public function installTab()
    {
        $tab = new Tab();
        $tab->class_name = 'AdminItstoreFaq';
        $tab->module = $this->name;
        $tab->active = 1;
        $tab->icon = 'help_outline';

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
            $tab->name[$lang['id_lang']] = 'IT Store FAQ';
        }

        try {
            return (bool) $tab->add();
        } catch (Exception $e) {
            return false;
        }
    }

    public function uninstallTab()
    {
        $idTab = (int) Tab::getIdFromClassName('AdminItstoreFaq');
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

    protected function seed()
    {
        if (ItstoreFaqItem::countActive(0) > 0) {
            return;
        }
        $samples = [
            ['Orders & delivery', 'How fast is delivery?', 'In-stock items dispatched before 1pm on business days ship the same day; delivery times then depend on your location and chosen carrier.'],
            ['Orders & delivery', 'Do you offer business accounts?', 'Yes — business accounts unlock bulk pricing tiers, quotes and account-based ordering. Ask our team to set one up.'],
            ['Warranty & returns', 'What warranty do products carry?', 'Products carry the full manufacturer warranty; many desktops and servers include an extended business warranty. Check each product page for details.'],
            ['Warranty & returns', 'How do I return a faulty item?', 'Contact support with your order reference and we will arrange a replacement or repair under warranty.'],
        ];
        $pos = 0;
        foreach ($samples as $s) {
            $faq = new ItstoreFaqItem();
            $faq->id_shop = 0;
            $faq->category = $s[0];
            $faq->question = $s[1];
            $faq->answer = '<p>' . $s[2] . '</p>';
            $faq->position = $pos++;
            $faq->active = 1;
            try {
                $faq->save();
            } catch (Exception $e) {
                // ignore a failed seed row
            }
        }
    }

    public function hookActionFrontControllerSetMedia()
    {
        if (Tools::getValue('module') === $this->name) {
            $this->context->controller->registerStylesheet(
                'itstore-faq',
                'modules/' . $this->name . '/views/css/faq.css',
                ['media' => 'all', 'priority' => 130]
            );
        }
    }

    public function faqUrl()
    {
        return $this->context->link->getModuleLink($this->name, 'faq', [], true);
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitItstoreFaq')) {
            Configuration::updateValue('ITSTORE_FAQ_TITLE', Tools::getValue('ITSTORE_FAQ_TITLE'));
            $output .= $this->displayConfirmation($this->trans('Settings saved.', [], 'Modules.Itstorefaq.Admin'));
        }

        $manageUrl = $this->context->link->getAdminLink('AdminItstoreFaq');
        $output .= '<div class="panel"><div class="panel-heading"><i class="icon-question-circle"></i> '
            . $this->trans('FAQ', [], 'Modules.Itstorefaq.Admin') . '</div>'
            . '<p>' . $this->trans('Manage questions & answers here:', [], 'Modules.Itstorefaq.Admin') . '</p>'
            . '<a class="btn btn-primary" href="' . htmlspecialchars($manageUrl) . '"><i class="icon-edit"></i> '
            . $this->trans('Manage FAQ entries', [], 'Modules.Itstorefaq.Admin') . '</a> '
            . '<a class="btn btn-default" href="' . htmlspecialchars($this->faqUrl()) . '" target="_blank"><i class="icon-eye"></i> '
            . $this->trans('View FAQ page', [], 'Modules.Itstorefaq.Admin') . '</a></div>';

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $form = ['form' => [
            'legend' => ['title' => $this->trans('FAQ page', [], 'Modules.Itstorefaq.Admin'), 'icon' => 'icon-cogs'],
            'input' => [
                ['type' => 'text', 'label' => $this->trans('Page title', [], 'Modules.Itstorefaq.Admin'), 'name' => 'ITSTORE_FAQ_TITLE'],
            ],
            'submit' => ['title' => $this->trans('Save', [], 'Modules.Itstorefaq.Admin'), 'name' => 'submitItstoreFaq'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreFaq';
        $helper->fields_value = ['ITSTORE_FAQ_TITLE' => Configuration::get('ITSTORE_FAQ_TITLE')];

        return $helper->generateForm([$form]);
    }
}
