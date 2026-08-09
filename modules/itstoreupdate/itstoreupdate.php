<?php
/**
 * IT Store — Auto Update.
 *
 * Keeps the IT Store theme and every itstore* module up to date from a GitHub
 * repository. Check + one-click "Update now" from the module's configure page,
 * or automatic updates via a token-protected cron endpoint. A timestamped
 * backup of the replaced files is kept under the module's backups/ folder.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'itstoreupdate/classes/ItstoreUpdater.php';

class Itstoreupdate extends Module
{
    public function __construct()
    {
        $this->name = 'itstoreupdate';
        $this->tab = 'administration';
        $this->version = '1.7.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('IT Store Auto Update', [], 'Modules.Itstoreupdate.Admin');
        $this->description = $this->trans('Fetches the IT Store theme + modules from GitHub and updates them in place (manual or via cron), with a backup.', [], 'Modules.Itstoreupdate.Admin');
    }

    protected function defaults()
    {
        return [
            'ITSTORE_UPD_OWNER' => 'deepindersingh83',
            'ITSTORE_UPD_REPO' => 'gamedesign',
            'ITSTORE_UPD_BRANCH' => 'main',
            'ITSTORE_UPD_CHANNEL' => 'branch',
            'ITSTORE_UPD_TOKEN' => '',
            'ITSTORE_UPD_AUTO' => 0,
            'ITSTORE_UPD_INSTALLED_VERSION' => $this->version,
        ];
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }
        foreach ($this->defaults() as $k => $v) {
            Configuration::updateValue($k, $v);
        }
        Configuration::updateValue('ITSTORE_UPD_CRON_TOKEN', Tools::passwdGen(24));
        $this->ensureThemeCacheDir();

        return true;
    }

    /**
     * Make sure the IT Store theme's CCC cache directory exists and is writable.
     *
     * PrestaShop's Combine/Compress/Cache writes the combined CSS/JS bundles to
     * themes/itstore/assets/cache/. Those files are fetched by the browser, so
     * they must stay under the web root — they cannot live in var/cache. If the
     * directory is missing, the core CccReducer tries to mkdir() it on the fly
     * and fatals with "Permission denied" when assets/ is not writable by PHP.
     *
     * We create the directory here (as the PHP/web-server user, so it is owned
     * correctly and writable) so no manual chmod/chown or vhost change is ever
     * needed on the live server. Shipping the folder in the theme package makes
     * this a belt-and-suspenders safety net for re-uploaded themes.
     */
    public function ensureThemeCacheDir()
    {
        $dir = rtrim(_PS_ALL_THEMES_DIR_, '/') . '/itstore/assets/cache';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (is_dir($dir) && !is_writable($dir)) {
            @chmod($dir, 0775);
        }
        $index = $dir . '/index.php';
        if (is_dir($dir) && !file_exists($index)) {
            @file_put_contents($index, "<?php\n/**\n * Silence is golden.\n */\nheader('Location: ../');\nexit;\n");
        }

        return is_dir($dir) && is_writable($dir);
    }

    public function uninstall()
    {
        foreach (array_keys($this->defaults()) as $k) {
            Configuration::deleteByName($k);
        }
        foreach (['ITSTORE_UPD_CRON_TOKEN', 'ITSTORE_UPD_LAST_CHECK', 'ITSTORE_UPD_LATEST_VERSION', 'ITSTORE_UPD_LAST_LOG', 'ITSTORE_UPD_LAST_RUN'] as $k) {
            Configuration::deleteByName($k);
        }

        return parent::uninstall();
    }

    public function cronUrl()
    {
        return $this->context->link->getModuleLink(
            $this->name,
            'cron',
            ['token' => Configuration::get('ITSTORE_UPD_CRON_TOKEN')],
            true
        );
    }

    public function getContent()
    {
        $output = '';
        $updater = new ItstoreUpdater();

        if (Tools::isSubmit('submitItstoreUpd')) {
            Configuration::updateValue('ITSTORE_UPD_OWNER', trim((string) Tools::getValue('ITSTORE_UPD_OWNER')));
            Configuration::updateValue('ITSTORE_UPD_REPO', trim((string) Tools::getValue('ITSTORE_UPD_REPO')));
            Configuration::updateValue('ITSTORE_UPD_BRANCH', trim((string) Tools::getValue('ITSTORE_UPD_BRANCH')));
            Configuration::updateValue('ITSTORE_UPD_CHANNEL', Tools::getValue('ITSTORE_UPD_CHANNEL') === 'release' ? 'release' : 'branch');
            Configuration::updateValue('ITSTORE_UPD_TOKEN', trim((string) Tools::getValue('ITSTORE_UPD_TOKEN')));
            Configuration::updateValue('ITSTORE_UPD_AUTO', (int) Tools::getValue('ITSTORE_UPD_AUTO'));
            $output .= $this->displayConfirmation($this->trans('Settings saved.', [], 'Modules.Itstoreupdate.Admin'));
            $updater = new ItstoreUpdater();
        } elseif (Tools::isSubmit('itstoreUpdCheck')) {
            $res = $updater->check();
            if ($res === false) {
                $output .= $this->displayError($this->trans('Could not reach GitHub. Check the settings and server connectivity.', [], 'Modules.Itstoreupdate.Admin')
                    . '<br><pre>' . htmlspecialchars(implode("\n", $updater->log)) . '</pre>');
            } elseif ($res['update']) {
                $output .= $this->displayWarning(sprintf($this->trans('Update available: v%1$s → v%2$s.', [], 'Modules.Itstoreupdate.Admin'), $res['current'], $res['latest']));
            } else {
                $output .= $this->displayConfirmation(sprintf($this->trans('You are up to date (v%s).', [], 'Modules.Itstoreupdate.Admin'), $res['current']));
            }
        } elseif (Tools::isSubmit('itstoreUpdRun')) {
            $ok = $updater->run((bool) Tools::getValue('force'));
            Configuration::updateValue('ITSTORE_UPD_LAST_LOG', implode("\n", $updater->log));
            Configuration::updateValue('ITSTORE_UPD_LAST_RUN', date('Y-m-d H:i:s'));
            if ($ok) {
                $output .= $this->displayConfirmation($this->trans('Update finished.', [], 'Modules.Itstoreupdate.Admin')
                    . '<br><pre>' . htmlspecialchars(implode("\n", $updater->log)) . '</pre>');
            } else {
                $output .= $this->displayError($this->trans('Update failed — no changes were kept beyond the backup.', [], 'Modules.Itstoreupdate.Admin')
                    . '<br><pre>' . htmlspecialchars(implode("\n", $updater->log)) . '</pre>');
            }
        }

        return $output . $this->renderStatus($updater) . $this->renderForm();
    }

    protected function renderStatus(ItstoreUpdater $updater)
    {
        $current = $updater->getLocalVersion();
        $latest = (string) Configuration::get('ITSTORE_UPD_LATEST_VERSION');
        $lastCheck = (string) Configuration::get('ITSTORE_UPD_LAST_CHECK');
        // Opening this page self-heals the theme's CCC cache directory so the
        // storefront never fatals on a missing themes/itstore/assets/cache/.
        $this->ensureThemeCacheDir();
        $w = ItstoreUpdater::writableReport();

        $token = Tools::getAdminTokenLite('AdminModules');
        $base = $this->context->link->getAdminLink('AdminModules', false)
            . '&token=' . $token . '&configure=' . $this->name
            . '&tab_module=' . $this->tab . '&module_name=' . $this->name;

        $warn = '';
        foreach (['modules' => 'modules/', 'themes' => 'themes/', 'self' => 'modules/itstoreupdate/'] as $k => $label) {
            if (empty($w[$k])) {
                $warn .= '<li><strong>' . htmlspecialchars($label) . '</strong> '
                    . $this->trans('is not writable by PHP — updates cannot be applied until it is.', [], 'Modules.Itstoreupdate.Admin') . '</li>';
            }
        }
        if (empty($w['theme_cache'])) {
            $warn .= '<li><strong>themes/itstore/assets/cache/</strong> '
                . $this->trans('is missing or not writable — the storefront Combine/Compress/Cache option needs it. Re-open this page to create it, or set it 0775.', [], 'Modules.Itstoreupdate.Admin') . '</li>';
        }

        $h = '<div class="panel"><div class="panel-heading"><i class="icon-cloud-download"></i> '
            . $this->trans('IT Store updates', [], 'Modules.Itstoreupdate.Admin') . '</div>';
        $h .= '<p><strong>' . $this->trans('Installed:', [], 'Modules.Itstoreupdate.Admin') . '</strong> v' . htmlspecialchars($current);
        if ($latest !== '') {
            $h .= ' &nbsp; <strong>' . $this->trans('Latest seen:', [], 'Modules.Itstoreupdate.Admin') . '</strong> v' . htmlspecialchars($latest);
            if (version_compare($latest, $current, '>')) {
                $h .= ' <span class="badge badge-warning">' . $this->trans('update available', [], 'Modules.Itstoreupdate.Admin') . '</span>';
            }
        }
        if ($lastCheck !== '') {
            $h .= '<br><small class="text-muted">' . $this->trans('Last check:', [], 'Modules.Itstoreupdate.Admin') . ' ' . htmlspecialchars($lastCheck) . '</small>';
        }
        $h .= '</p>';

        if ($warn !== '') {
            $h .= '<div class="alert alert-warning"><ul style="margin:0 0 0 18px">' . $warn . '</ul></div>';
        }

        $h .= '<a class="btn btn-default" href="' . $base . '&itstoreUpdCheck=1"><i class="icon-refresh"></i> '
            . $this->trans('Check for updates', [], 'Modules.Itstoreupdate.Admin') . '</a> ';
        $h .= '<a class="btn btn-primary" onclick="return confirm(\''
            . $this->trans('This will overwrite the IT Store theme and modules with the latest version (a backup is kept). Continue?', [], 'Modules.Itstoreupdate.Admin')
            . '\')" href="' . $base . '&itstoreUpdRun=1"><i class="icon-cloud-download"></i> '
            . $this->trans('Update now', [], 'Modules.Itstoreupdate.Admin') . '</a>';

        $h .= '<hr><p><strong>' . $this->trans('Automatic updates (cron):', [], 'Modules.Itstoreupdate.Admin') . '</strong> '
            . $this->trans('call this URL from your server scheduler (e.g. daily). It updates only when the switch below is on.', [], 'Modules.Itstoreupdate.Admin')
            . '</p><pre style="white-space:normal;word-break:break-all">' . htmlspecialchars($this->cronUrl()) . '</pre>';

        $lastLog = (string) Configuration::get('ITSTORE_UPD_LAST_LOG');
        if ($lastLog !== '') {
            $h .= '<p><strong>' . $this->trans('Last run:', [], 'Modules.Itstoreupdate.Admin') . '</strong> ' . htmlspecialchars((string) Configuration::get('ITSTORE_UPD_LAST_RUN')) . '</p>';
            $h .= '<pre style="max-height:220px;overflow:auto">' . htmlspecialchars($lastLog) . '</pre>';
        }

        $h .= '</div>';

        return $h;
    }

    protected function renderForm()
    {
        $form = ['form' => [
            'legend' => ['title' => $this->trans('Update source', [], 'Modules.Itstoreupdate.Admin'), 'icon' => 'icon-github'],
            'input' => [
                ['type' => 'text', 'label' => $this->trans('GitHub owner', [], 'Modules.Itstoreupdate.Admin'), 'name' => 'ITSTORE_UPD_OWNER', 'col' => 4],
                ['type' => 'text', 'label' => $this->trans('Repository', [], 'Modules.Itstoreupdate.Admin'), 'name' => 'ITSTORE_UPD_REPO', 'col' => 4],
                [
                    'type' => 'select', 'label' => $this->trans('Channel', [], 'Modules.Itstoreupdate.Admin'), 'name' => 'ITSTORE_UPD_CHANNEL',
                    'options' => ['query' => [
                        ['id' => 'branch', 'name' => $this->trans('Branch (latest commit)', [], 'Modules.Itstoreupdate.Admin')],
                        ['id' => 'release', 'name' => $this->trans('Latest release (tag)', [], 'Modules.Itstoreupdate.Admin')],
                    ], 'id' => 'id', 'name' => 'name'],
                ],
                ['type' => 'text', 'label' => $this->trans('Branch', [], 'Modules.Itstoreupdate.Admin'), 'name' => 'ITSTORE_UPD_BRANCH', 'col' => 4, 'desc' => $this->trans('Used when the channel is “Branch”. E.g. main.', [], 'Modules.Itstoreupdate.Admin')],
                ['type' => 'text', 'label' => $this->trans('GitHub token (optional)', [], 'Modules.Itstoreupdate.Admin'), 'name' => 'ITSTORE_UPD_TOKEN', 'col' => 6, 'desc' => $this->trans('Only needed for private repos or to avoid rate limits. Stored in your shop configuration.', [], 'Modules.Itstoreupdate.Admin')],
                [
                    'type' => 'switch', 'label' => $this->trans('Enable automatic updates (cron)', [], 'Modules.Itstoreupdate.Admin'), 'name' => 'ITSTORE_UPD_AUTO', 'is_bool' => true,
                    'desc' => $this->trans('When on, the cron endpoint applies available updates automatically.', [], 'Modules.Itstoreupdate.Admin'),
                    'values' => [
                        ['id' => 'auto_on', 'value' => 1, 'label' => $this->trans('Yes', [], 'Modules.Itstoreupdate.Admin')],
                        ['id' => 'auto_off', 'value' => 0, 'label' => $this->trans('No', [], 'Modules.Itstoreupdate.Admin')],
                    ],
                ],
            ],
            'submit' => ['title' => $this->trans('Save', [], 'Modules.Itstoreupdate.Admin'), 'name' => 'submitItstoreUpd'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreUpd';
        $values = [];
        foreach (array_keys($this->defaults()) as $k) {
            $values[$k] = Configuration::get($k);
        }
        $helper->fields_value = $values;

        return $helper->generateForm([$form]);
    }
}
