<?php
/**
 * IT Store — Admin hub.
 *
 * Installs a single "IT Store" back-office tab (AdminItstore) that groups every
 * itstore* module in one dashboard: install/active status, one-click configure
 * links and at-a-glance counters pulled from the modules' data tables (pending
 * reviews, unanswered questions, open quotes, waiting stock alerts, active
 * subscriptions, blog posts).
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstoreadmin extends Module
{
    public function __construct()
    {
        $this->name = 'itstoreadmin';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('IT Store Admin Hub', [], 'Modules.Itstoreadmin.Admin');
        $this->description = $this->trans('One back-office dashboard grouping every IT Store module, its status and its key counters.', [], 'Modules.Itstoreadmin.Admin');
    }

    public function install()
    {
        return parent::install() && $this->installTab();
    }

    public function uninstall()
    {
        return $this->uninstallTab() && parent::uninstall();
    }

    protected function installTab()
    {
        $tab = new Tab();
        $tab->class_name = 'AdminItstore';
        $tab->module = $this->name;
        $tab->active = 1;
        $tab->icon = 'storefront';

        $idParent = (int) Tab::getIdFromClassName('AdminParentModulesSf');
        if (!$idParent) {
            $idParent = (int) Tab::getIdFromClassName('AdminParentModules');
        }
        $tab->id_parent = $idParent;

        $tab->name = [];
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = 'IT Store';
        }

        try {
            return (bool) $tab->add();
        } catch (Exception $e) {
            return false;
        }
    }

    protected function uninstallTab()
    {
        $idTab = (int) Tab::getIdFromClassName('AdminItstore');
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

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminItstore'));
    }
}
