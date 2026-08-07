<?php
/**
 * IT Store — back-office dashboard controller.
 *
 * Renders the grouped "IT Store" admin page: KPI counters plus a card grid of
 * every itstore* module with its status and a configure link.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminItstoreController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function initContent()
    {
        parent::initContent();
        $this->context->smarty->assign('content', $this->content . $this->renderDashboard());
    }

    /**
     * Whether a table (already prefixed name minus prefix) exists.
     */
    protected function tableExists($name)
    {
        $rows = Db::getInstance()->executeS("SHOW TABLES LIKE '" . _DB_PREFIX_ . pSQL($name) . "'");

        return !empty($rows);
    }

    protected function count($sql)
    {
        return (int) Db::getInstance()->getValue($sql);
    }

    protected function kpis()
    {
        $k = [];

        if ($this->tableExists('itstore_review')) {
            $k[] = [
                'label' => $this->module->l('Reviews awaiting moderation', 'AdminItstore'),
                'value' => $this->count('SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'itstore_review` WHERE approved = 0'),
                'icon' => 'star',
                'module' => 'itstorereviews',
            ];
        }
        if ($this->tableExists('itstore_question')) {
            $k[] = [
                'label' => $this->module->l('Unanswered questions', 'AdminItstore'),
                'value' => $this->count('SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'itstore_question` WHERE answer IS NULL OR answer = ""'),
                'icon' => 'help',
                'module' => 'itstoreaskquestion',
            ];
        }
        if ($this->tableExists('itstore_quote')) {
            $k[] = [
                'label' => $this->module->l('Quote requests', 'AdminItstore'),
                'value' => $this->count('SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'itstore_quote`'),
                'icon' => 'request_quote',
                'module' => 'itstorefleetdeals',
            ];
        }
        if ($this->tableExists('itstore_stock_alert')) {
            $k[] = [
                'label' => $this->module->l('Waiting stock alerts', 'AdminItstore'),
                'value' => $this->count('SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'itstore_stock_alert` WHERE notified = 0'),
                'icon' => 'notifications',
                'module' => 'itstorestock',
            ];
        }
        if ($this->tableExists('itstore_subscription')) {
            $k[] = [
                'label' => $this->module->l('Active subscriptions', 'AdminItstore'),
                'value' => $this->count('SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'itstore_subscription` WHERE active = 1'),
                'icon' => 'autorenew',
                'module' => 'itstoreautoreorder',
            ];
        }
        if ($this->tableExists('itstore_blog_post')) {
            $k[] = [
                'label' => $this->module->l('Published blog posts', 'AdminItstore'),
                'value' => $this->count('SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'itstore_blog_post` WHERE active = 1'),
                'icon' => 'article',
                'module' => 'itstoreblog',
            ];
        }

        return $k;
    }

    protected function modules()
    {
        $rows = Db::getInstance()->executeS(
            "SELECT name, active FROM `" . _DB_PREFIX_ . "module` WHERE name LIKE 'itstore%' ORDER BY name"
        ) ?: [];

        $out = [];
        foreach ($rows as $r) {
            if ($r['name'] === 'itstoreadmin') {
                continue;
            }
            $instance = Module::getInstanceByName($r['name']);
            $out[] = [
                'name' => $r['name'],
                'display' => $instance ? $instance->displayName : $r['name'],
                'version' => $instance ? $instance->version : '',
                'active' => (int) $r['active'],
                'configure' => $this->context->link->getAdminLink('AdminModules', true, [], [
                    'configure' => $r['name'],
                    'tab_module' => $instance ? $instance->tab : 'front_office_features',
                    'module_name' => $r['name'],
                ]),
            ];
        }

        return $out;
    }

    protected function configureLink($moduleName)
    {
        return $this->context->link->getAdminLink('AdminModules', true, [], [
            'configure' => $moduleName,
            'module_name' => $moduleName,
        ]);
    }

    protected function renderDashboard()
    {
        $kpis = $this->kpis();
        $modules = $this->modules();

        $h = '<div class="itstore-bo">';
        $h .= '<style>
            .itstore-bo{margin-top:14px}
            .itstore-bo__kpis{display:flex;flex-wrap:wrap;gap:14px;margin-bottom:22px}
            .itstore-bo__kpi{flex:1 1 160px;min-width:160px;background:#fff;border:1px solid #e3e7ef;border-radius:12px;padding:16px 18px;box-shadow:0 1px 2px rgba(16,24,40,.04)}
            .itstore-bo__kpi a{color:inherit;text-decoration:none;display:block}
            .itstore-bo__kpi .v{font-size:30px;font-weight:800;color:#0b1220;line-height:1}
            .itstore-bo__kpi .l{color:#5a6478;font-size:12.5px;margin-top:6px}
            .itstore-bo__kpi.alert .v{color:#dc2626}
            .itstore-bo h2{font-size:17px;font-weight:800;color:#0b1220;margin:8px 0 12px}
            .itstore-bo__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px}
            .itstore-bo__card{background:#fff;border:1px solid #e3e7ef;border-radius:12px;padding:15px 16px;display:flex;flex-direction:column;gap:8px}
            .itstore-bo__card .top{display:flex;justify-content:space-between;align-items:center}
            .itstore-bo__card .nm{font-weight:700;color:#0b1220;font-size:14px}
            .itstore-bo__card .vr{font-family:ui-monospace,monospace;color:#8b94a8;font-size:11px}
            .itstore-bo__badge{font-size:11px;font-weight:700;padding:3px 9px;border-radius:999px}
            .itstore-bo__badge.on{background:#e7f6ec;color:#16a34a}
            .itstore-bo__badge.off{background:#f1f2f5;color:#8b94a8}
            .itstore-bo__card .cfg{margin-top:2px;font-weight:700;font-size:13px;color:#1d4ed8;text-decoration:none}
        </style>';

        // KPI row
        if ($kpis) {
            $h .= '<div class="itstore-bo__kpis">';
            foreach ($kpis as $kpi) {
                $alert = $kpi['value'] > 0 && in_array($kpi['icon'], ['star', 'help', 'notifications'], true) ? ' alert' : '';
                $link = $this->configureLink($kpi['module']);
                $h .= '<div class="itstore-bo__kpi' . $alert . '"><a href="' . htmlspecialchars($link) . '">'
                    . '<div class="v">' . (int) $kpi['value'] . '</div>'
                    . '<div class="l">' . htmlspecialchars($kpi['label']) . '</div></a></div>';
            }
            $h .= '</div>';
        }

        // Module grid
        $h .= '<h2>' . $this->module->l('Modules', 'AdminItstore') . ' (' . count($modules) . ')</h2>';
        $h .= '<div class="itstore-bo__grid">';
        foreach ($modules as $m) {
            $badge = $m['active']
                ? '<span class="itstore-bo__badge on">' . $this->module->l('Active', 'AdminItstore') . '</span>'
                : '<span class="itstore-bo__badge off">' . $this->module->l('Disabled', 'AdminItstore') . '</span>';
            $h .= '<div class="itstore-bo__card">'
                . '<div class="top"><span class="nm">' . htmlspecialchars($m['display']) . '</span>' . $badge . '</div>'
                . '<span class="vr">' . htmlspecialchars($m['name']) . ' · v' . htmlspecialchars($m['version']) . '</span>'
                . '<a class="cfg" href="' . htmlspecialchars($m['configure']) . '">' . $this->module->l('Configure', 'AdminItstore') . ' →</a>'
                . '</div>';
        }
        $h .= '</div>';

        $h .= '</div>';

        return $h;
    }
}
