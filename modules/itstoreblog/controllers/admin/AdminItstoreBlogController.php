<?php
/**
 * IT Store — blog posts back-office CRUD.
 *
 * Standard ModuleAdminController list/add/edit/delete over ItstoreBlogPost.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'itstoreblog/classes/ItstoreBlogPost.php';

class AdminItstoreBlogController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'itstore_blog_post';
        $this->className = 'ItstoreBlogPost';
        $this->identifier = 'id_post';
        $this->lang = false;
        $this->bootstrap = true;
        $this->allow_export = false;
        $this->_defaultOrderBy = 'date_add';
        $this->_defaultOrderWay = 'DESC';

        parent::__construct();

        $this->fields_list = [
            'id_post' => ['title' => $this->l('ID'), 'align' => 'center', 'class' => 'fixed-width-xs'],
            'title' => ['title' => $this->l('Title')],
            'category' => ['title' => $this->l('Category')],
            'author' => ['title' => $this->l('Author')],
            'tag' => ['title' => $this->l('Tag')],
            'active' => ['title' => $this->l('Published'), 'active' => 'status', 'type' => 'bool', 'align' => 'center', 'class' => 'fixed-width-sm'],
            'date_add' => ['title' => $this->l('Created'), 'type' => 'datetime'],
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
            'legend' => ['title' => $this->l('Blog post'), 'icon' => 'icon-rss'],
            'input' => [
                ['type' => 'text', 'label' => $this->l('Title'), 'name' => 'title', 'required' => true, 'col' => 6],
                ['type' => 'text', 'label' => $this->l('Slug'), 'name' => 'slug', 'col' => 6, 'desc' => $this->l('Leave blank to auto-generate from the title.')],
                ['type' => 'text', 'label' => $this->l('Tag (badge)'), 'name' => 'tag', 'col' => 4, 'desc' => $this->l('Short label shown on the card.')],
                ['type' => 'text', 'label' => $this->l('Category'), 'name' => 'category', 'col' => 4, 'desc' => $this->l('Groups posts and drives related articles.')],
                ['type' => 'text', 'label' => $this->l('Tags'), 'name' => 'tags', 'col' => 6, 'desc' => $this->l('Comma-separated, e.g. gpu, gaming, benchmarks.')],
                ['type' => 'text', 'label' => $this->l('Author'), 'name' => 'author', 'col' => 4],
                ['type' => 'text', 'label' => $this->l('Image URL'), 'name' => 'image', 'col' => 8, 'desc' => $this->l('Absolute URL or a path under your shop.')],
                ['type' => 'textarea', 'label' => $this->l('Excerpt'), 'name' => 'excerpt', 'rows' => 3, 'desc' => $this->l('Short summary shown in listings.')],
                ['type' => 'textarea', 'label' => $this->l('Content'), 'name' => 'content', 'autoload_rte' => true, 'rows' => 15, 'cols' => 60],
                ['type' => 'text', 'label' => $this->l('Meta title'), 'name' => 'meta_title', 'col' => 8],
                ['type' => 'text', 'label' => $this->l('Meta description'), 'name' => 'meta_description', 'col' => 8],
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

        if (!($obj = $this->loadObject(true))) {
            return '';
        }

        return parent::renderForm();
    }

    /**
     * Default new posts to published + current shop.
     */
    public function processAdd()
    {
        if (!Tools::getValue('id_shop')) {
            $_POST['id_shop'] = (int) $this->context->shop->id;
        }

        return parent::processAdd();
    }
}
