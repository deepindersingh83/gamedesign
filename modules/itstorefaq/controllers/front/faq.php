<?php
/**
 * IT Store — FAQ front page.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'itstorefaq/classes/ItstoreFaq.php';

class ItstorefaqFaqModuleFrontController extends ModuleFrontController
{
    public $php_self = 'module-itstorefaq-faq';

    public function initContent()
    {
        parent::initContent();

        $idShop = (int) $this->context->shop->id;
        $rows = ItstoreFaq::getActive($idShop);

        // Group by category (preserving order).
        $groups = [];
        $flat = [];
        foreach ($rows as $r) {
            $cat = $r['category'] !== '' ? $r['category'] : $this->trans('General', [], 'Modules.Itstorefaq.Shop');
            if (!isset($groups[$cat])) {
                $groups[$cat] = [];
            }
            $groups[$cat][] = ['question' => $r['question'], 'answer' => $r['answer']];
            $flat[] = ['question' => $r['question'], 'answer' => $r['answer']];
        }

        $grouped = [];
        foreach ($groups as $cat => $items) {
            $grouped[] = ['category' => $cat, 'items' => $items];
        }

        $title = Configuration::get('ITSTORE_FAQ_TITLE') ?: $this->trans('Frequently asked questions', [], 'Modules.Itstorefaq.Shop');

        $this->context->smarty->assign([
            'faq_title' => $title,
            'faq_groups' => $grouped,
            'faq_jsonld' => $flat ? $this->buildJsonLd($flat) : '',
        ]);

        $this->setTemplate('module:itstorefaq/views/templates/front/faq.tpl');
    }

    /**
     * FAQPage structured data for rich results.
     */
    protected function buildJsonLd(array $items)
    {
        $nodes = [];
        foreach ($items as $it) {
            $nodes[] = [
                '@type' => 'Question',
                'name' => $it['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => trim(strip_tags((string) $it['answer'])),
                ],
            ];
        }

        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $nodes,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = [
            'title' => Configuration::get('ITSTORE_FAQ_TITLE') ?: $this->trans('FAQ', [], 'Modules.Itstorefaq.Shop'),
            'url' => $this->context->link->getModuleLink('itstorefaq', 'faq', [], true),
        ];

        return $breadcrumb;
    }
}
