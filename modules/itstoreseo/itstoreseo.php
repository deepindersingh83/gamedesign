<?php
/**
 * IT Store — SEO structured data.
 *
 * Emits Organization + WebSite JSON-LD site-wide (with a sitelinks search box)
 * and a BreadcrumbList on pages that expose a breadcrumb. Complements the
 * Product/AggregateRating JSON-LD already emitted by itstorereviews.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstoreseo extends Module
{
    public function __construct()
    {
        $this->name = 'itstoreseo';
        $this->tab = 'seo';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IT Store SEO');
        $this->description = $this->l('Organization, WebSite and Breadcrumb JSON-LD structured data.');
    }

    protected function defaults()
    {
        return [
            'ITSTORE_SEO_SAMEAS' => '',
        ];
    }

    public function install()
    {
        if (!parent::install() || !$this->registerHook('displayHeader')) {
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

    public function hookDisplayHeader($params)
    {
        $shopName = Configuration::get('PS_SHOP_NAME');
        $base = $this->context->shop->getBaseURL(true);
        $logo = $base . 'themes/itstore/assets/img/logo.png';

        $graph = [];

        $org = [
            '@type' => 'Organization',
            '@id' => $base . '#organization',
            'name' => $shopName,
            'url' => $base,
            'logo' => $logo,
        ];
        $sameAs = array_filter(array_map('trim', explode(',', (string) Configuration::get('ITSTORE_SEO_SAMEAS'))));
        if ($sameAs) {
            $org['sameAs'] = array_values($sameAs);
        }
        $graph[] = $org;

        $graph[] = [
            '@type' => 'WebSite',
            '@id' => $base . '#website',
            'url' => $base,
            'name' => $shopName,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $base . 'search?s={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];

        $crumbs = $this->breadcrumb();
        if (!empty($crumbs)) {
            $items = [];
            $pos = 1;
            foreach ($crumbs as $c) {
                $items[] = [
                    '@type' => 'ListItem',
                    'position' => $pos++,
                    'name' => $c['name'],
                    'item' => $c['url'],
                ];
            }
            $graph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => $items];
        }

        $data = ['@context' => 'https://schema.org', '@graph' => $graph];

        return "\n" . '<script type="application/ld+json">'
            . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            . '</script>' . "\n";
    }

    /**
     * @return array [['name'=>..,'url'=>..], ...]
     */
    protected function breadcrumb()
    {
        $controller = $this->context->controller;
        if (!is_object($controller) || !method_exists($controller, 'getBreadcrumbLinks')) {
            return [];
        }
        $bc = $controller->getBreadcrumbLinks();
        if (empty($bc['links']) || !is_array($bc['links'])) {
            return [];
        }
        $out = [];
        foreach ($bc['links'] as $link) {
            if (isset($link['title'], $link['url'])) {
                $out[] = ['name' => $link['title'], 'url' => $link['url']];
            }
        }

        return $out;
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitItstoreSeo')) {
            Configuration::updateValue('ITSTORE_SEO_SAMEAS', Tools::getValue('ITSTORE_SEO_SAMEAS'));
            $output .= $this->displayConfirmation($this->l('Settings saved.'));
        }

        $form = ['form' => [
            'legend' => ['title' => $this->l('SEO'), 'icon' => 'icon-google'],
            'input' => [[
                'type' => 'text',
                'label' => $this->l('Social profile URLs'),
                'name' => 'ITSTORE_SEO_SAMEAS',
                'desc' => $this->l('Comma-separated (Facebook, LinkedIn, …) for Organization sameAs.'),
            ]],
            'submit' => ['title' => $this->l('Save'), 'name' => 'submitItstoreSeo'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreSeo';
        $helper->fields_value = ['ITSTORE_SEO_SAMEAS' => Configuration::get('ITSTORE_SEO_SAMEAS')];

        return $output . $helper->generateForm([$form]);
    }
}
