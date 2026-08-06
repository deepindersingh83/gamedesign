<?php
/**
 * IT Store — Ask a Question (product Q&A).
 *
 * The design's product-page Q&A: answered questions plus an "Ask a Question"
 * form. Questions are stored in `itstore_question`, answered and published from
 * the back office. Rendered as a product tab via displayProductExtraContent.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Product\ProductExtraContent;

class Itstoreaskquestion extends Module
{
    public function __construct()
    {
        $this->name = 'itstoreaskquestion';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('IT Store Ask a Question', [], 'Modules.Itstoreaskquestion.Admin');
        $this->description = $this->trans('Product Q&A tab with an “Ask a Question” form and back-office answers.', [], 'Modules.Itstoreaskquestion.Admin');
    }

    public function install()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'itstore_question` (
            `id_question` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_product` INT(10) UNSIGNED NOT NULL,
            `id_shop` INT(10) UNSIGNED NOT NULL DEFAULT 1,
            `email` VARCHAR(255) NOT NULL DEFAULT "",
            `question` TEXT NOT NULL,
            `answer` TEXT,
            `approved` TINYINT(1) NOT NULL DEFAULT 0,
            `date_add` DATETIME NOT NULL,
            PRIMARY KEY (`id_question`),
            KEY `id_product` (`id_product`, `approved`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        return parent::install()
            && Db::getInstance()->execute($sql)
            && $this->registerHook('displayProductExtraContent')
            && $this->registerHook('actionFrontControllerSetMedia');
    }

    public function uninstall()
    {
        Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'itstore_question`;');

        return parent::uninstall();
    }

    public function hookActionFrontControllerSetMedia()
    {
        if ($this->context->controller instanceof ProductController) {
            $this->context->controller->registerStylesheet(
                'itstore-askq',
                'modules/' . $this->name . '/views/css/askquestion.css',
                ['media' => 'all', 'priority' => 148]
            );
            $this->context->controller->registerJavascript(
                'itstore-askq',
                'modules/' . $this->name . '/views/js/askquestion.js',
                ['position' => 'bottom', 'priority' => 148]
            );
        }
    }

    public function hookDisplayProductExtraContent($params)
    {
        $idProduct = 0;
        if (isset($params['product'])) {
            $p = $params['product'];
            $idProduct = (int) (is_array($p) ? (isset($p['id_product']) ? $p['id_product'] : 0) : $p->id);
        }
        if ($idProduct <= 0) {
            return [];
        }

        $qas = Db::getInstance()->executeS(
            'SELECT question, answer FROM `' . _DB_PREFIX_ . 'itstore_question`
             WHERE id_product = ' . $idProduct . ' AND id_shop = ' . (int) $this->context->shop->id . '
               AND approved = 1 AND answer IS NOT NULL AND answer <> ""
             ORDER BY date_add DESC'
        ) ?: [];

        $this->smarty->assign([
            'aq_items' => $qas,
            'aq_id_product' => $idProduct,
            'aq_submit_url' => $this->context->link->getModuleLink($this->name, 'ask', [], true),
            'aq_token' => Tools::getToken('itstoreaskquestion' . $idProduct),
        ]);

        $html = $this->fetch('module:' . $this->name . '/views/templates/hook/askquestion.tpl');

        $extra = new ProductExtraContent();
        $extra->setTitle($this->trans('Q&A', [], 'Modules.Itstoreaskquestion.Admin') . ($qas ? ' (' . count($qas) . ')' : ''))
            ->setContent($html);

        return [$extra];
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('answerQ')) {
            $id = (int) Tools::getValue('id_question');
            Db::getInstance()->update('itstore_question', [
                'answer' => pSQL(Tools::getValue('answer'), true),
                'approved' => 1,
            ], 'id_question = ' . $id);
            $output .= $this->displayConfirmation($this->trans('Answer published.', [], 'Modules.Itstoreaskquestion.Admin'));
        } elseif (Tools::isSubmit('deleteQ')) {
            Db::getInstance()->delete('itstore_question', 'id_question = ' . (int) Tools::getValue('id_question'));
            $output .= $this->displayConfirmation($this->trans('Question deleted.', [], 'Modules.Itstoreaskquestion.Admin'));
        }

        return $output . $this->renderList();
    }

    protected function renderList()
    {
        $rows = Db::getInstance()->executeS(
            'SELECT q.*, pl.name FROM `' . _DB_PREFIX_ . 'itstore_question` q
             LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
               ON (pl.id_product = q.id_product AND pl.id_lang = ' . (int) $this->context->language->id . '
                   AND pl.id_shop = q.id_shop)
             ORDER BY q.approved ASC, q.date_add DESC'
        ) ?: [];

        $token = Tools::getAdminTokenLite('AdminModules');
        $base = $this->context->link->getAdminLink('AdminModules', false)
            . '&token=' . $token . '&configure=' . $this->name
            . '&tab_module=' . $this->tab . '&module_name=' . $this->name;

        $body = '';
        foreach (array_slice($rows, 0, 200) as $r) {
            $answer = htmlspecialchars((string) $r['answer']);
            $status = (int) $r['approved'] ? $this->trans('Published', [], 'Modules.Itstoreaskquestion.Admin') : '<strong>' . $this->trans('Pending', [], 'Modules.Itstoreaskquestion.Admin') . '</strong>';
            $body .= '<tr>'
                . '<td>' . htmlspecialchars((string) $r['name']) . '</td>'
                . '<td>' . htmlspecialchars($r['question']) . '<div class="help-block">' . $status . '</div></td>'
                . '<td>'
                . '<form method="post" action="' . $base . '">'
                . '<input type="hidden" name="id_question" value="' . (int) $r['id_question'] . '">'
                . '<textarea name="answer" class="form-control" rows="2">' . $answer . '</textarea>'
                . '<div style="margin-top:6px">'
                . '<button class="btn btn-success btn-xs" name="answerQ" value="1">' . $this->trans('Publish answer', [], 'Modules.Itstoreaskquestion.Admin') . '</button> '
                . '<button class="btn btn-danger btn-xs" name="deleteQ" value="1" onclick="return confirm(\'' . $this->trans('Delete?', [], 'Modules.Itstoreaskquestion.Admin') . '\')">' . $this->trans('Delete', [], 'Modules.Itstoreaskquestion.Admin') . '</button>'
                . '</div></form></td>'
                . '</tr>';
        }
        if ($body === '') {
            $body = '<tr><td colspan="3">' . $this->trans('No questions yet.', [], 'Modules.Itstoreaskquestion.Admin') . '</td></tr>';
        }

        return '<div class="panel"><div class="panel-heading"><i class="icon-question"></i> ' . $this->trans('Product questions', [], 'Modules.Itstoreaskquestion.Admin') . '</div>'
            . '<table class="table"><thead><tr><th>' . $this->trans('Product', [], 'Modules.Itstoreaskquestion.Admin') . '</th><th>' . $this->trans('Question', [], 'Modules.Itstoreaskquestion.Admin')
            . '</th><th>' . $this->trans('Answer / actions', [], 'Modules.Itstoreaskquestion.Admin') . '</th></tr></thead><tbody>' . $body . '</tbody></table></div>';
    }
}
