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
            'id_faq' => ['title' => $this->trans('ID', [], 'Modules.Itstorefaq.Admin'), 'align' => 'center', 'class' => 'fixed-width-xs'],
            'category' => ['title' => $this->trans('Category', [], 'Modules.Itstorefaq.Admin')],
            'question' => ['title' => $this->trans('Question', [], 'Modules.Itstorefaq.Admin')],
            'position' => ['title' => $this->trans('Position', [], 'Modules.Itstorefaq.Admin'), 'align' => 'center', 'class' => 'fixed-width-sm'],
            'active' => ['title' => $this->trans('Published', [], 'Modules.Itstorefaq.Admin'), 'active' => 'status', 'type' => 'bool', 'align' => 'center', 'class' => 'fixed-width-sm'],
        ];

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->trans('Delete selected', [], 'Modules.Itstorefaq.Admin'),
                'icon' => 'icon-trash',
                'confirm' => $this->trans('Delete selected items?', [], 'Modules.Itstorefaq.Admin'),
            ],
        ];
    }

    public function renderForm()
    {
        $this->fields_form = [
            'legend' => ['title' => $this->trans('FAQ entry', [], 'Modules.Itstorefaq.Admin'), 'icon' => 'icon-question-circle'],
            'input' => [
                ['type' => 'text', 'label' => $this->trans('Category', [], 'Modules.Itstorefaq.Admin'), 'name' => 'category', 'col' => 5, 'desc' => $this->trans('Entries are grouped under their category on the FAQ page.', [], 'Modules.Itstorefaq.Admin')],
                ['type' => 'text', 'label' => $this->trans('Question', [], 'Modules.Itstorefaq.Admin'), 'name' => 'question', 'required' => true, 'col' => 9],
                ['type' => 'textarea', 'label' => $this->trans('Answer', [], 'Modules.Itstorefaq.Admin'), 'name' => 'answer', 'autoload_rte' => true, 'rows' => 8, 'cols' => 60],
                ['type' => 'text', 'label' => $this->trans('Position', [], 'Modules.Itstorefaq.Admin'), 'name' => 'position', 'col' => 2],
                [
                    'type' => 'switch', 'label' => $this->trans('Published', [], 'Modules.Itstorefaq.Admin'), 'name' => 'active', 'is_bool' => true,
                    'values' => [
                        ['id' => 'active_on', 'value' => 1, 'label' => $this->trans('Yes', [], 'Modules.Itstorefaq.Admin')],
                        ['id' => 'active_off', 'value' => 0, 'label' => $this->trans('No', [], 'Modules.Itstorefaq.Admin')],
                    ],
                ],
            ],
            'submit' => ['title' => $this->trans('Save', [], 'Modules.Itstorefaq.Admin')],
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
