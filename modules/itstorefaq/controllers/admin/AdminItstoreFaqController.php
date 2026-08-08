<?php
/**
 * IT Store — FAQ back-office CRUD.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'itstorefaq/classes/ItstoreFaqItem.php';

class AdminItstoreFaqController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'itstore_faq';
        $this->className = 'ItstoreFaqItem';
        $this->identifier = 'id_faq';
        $this->lang = false;
        $this->bootstrap = true;
        $this->allow_export = false;
        $this->_defaultOrderBy = 'category';
        $this->_defaultOrderWay = 'ASC';
        $this->position_identifier = 'id_faq';

        parent::__construct();

        $this->fields_list = [
            'id_faq' => ['title' => $this->l('ID'), 'align' => 'center', 'class' => 'fixed-width-xs'],
            'category' => ['title' => $this->l('Category')],
            'question' => ['title' => $this->l('Question')],
            'position' => ['title' => $this->l('Position'), 'align' => 'center', 'class' => 'fixed-width-sm'],
            'active' => ['title' => $this->l('Published'), 'active' => 'status', 'type' => 'bool', 'align' => 'center', 'class' => 'fixed-width-sm'],
        ];

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?'),
            ],
        ];
    }

    public function renderForm()
    {
        $this->fields_form = [
            'legend' => ['title' => $this->l('FAQ entry'), 'icon' => 'icon-question-circle'],
            'input' => [
                ['type' => 'text', 'label' => $this->l('Category'), 'name' => 'category', 'col' => 5, 'desc' => $this->l('Entries are grouped under their category on the FAQ page.')],
                ['type' => 'text', 'label' => $this->l('Question'), 'name' => 'question', 'required' => true, 'col' => 9],
                ['type' => 'textarea', 'label' => $this->l('Answer'), 'name' => 'answer', 'autoload_rte' => true, 'rows' => 8, 'cols' => 60],
                ['type' => 'text', 'label' => $this->l('Position'), 'name' => 'position', 'col' => 2],
                [
                    'type' => 'switch', 'label' => $this->l('Published'), 'name' => 'active', 'is_bool' => true,
                    'values' => [
                        ['id' => 'active_on', 'value' => 1, 'label' => $this->l('Yes')],
                        ['id' => 'active_off', 'value' => 0, 'label' => $this->l('No')],
                    ],
                ],
            ],
            'submit' => ['title' => $this->l('Save')],
        ];

        if (!$this->loadObject(true)) {
            return '';
        }

        return parent::renderForm();
    }

    public function processAdd()
    {
        if (!Tools::getValue('id_shop')) {
            $_POST['id_shop'] = (int) $this->context->shop->id;
        }

        return parent::processAdd();
    }
}
