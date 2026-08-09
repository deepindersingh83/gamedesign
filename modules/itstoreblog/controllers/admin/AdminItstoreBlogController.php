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
            'id_post' => ['title' => $this->trans('ID', [], 'Modules.Itstoreblog.Admin'), 'align' => 'center', 'class' => 'fixed-width-xs'],
            'title' => ['title' => $this->trans('Title', [], 'Modules.Itstoreblog.Admin')],
            'category' => ['title' => $this->trans('Category', [], 'Modules.Itstoreblog.Admin')],
            'author' => ['title' => $this->trans('Author', [], 'Modules.Itstoreblog.Admin')],
            'tag' => ['title' => $this->trans('Tag', [], 'Modules.Itstoreblog.Admin')],
            'active' => ['title' => $this->trans('Published', [], 'Modules.Itstoreblog.Admin'), 'active' => 'status', 'type' => 'bool', 'align' => 'center', 'class' => 'fixed-width-sm'],
            'date_add' => ['title' => $this->trans('Created', [], 'Modules.Itstoreblog.Admin'), 'type' => 'datetime'],
        ];

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->trans('Delete selected', [], 'Modules.Itstoreblog.Admin'),
                'icon' => 'icon-trash',
                'confirm' => $this->trans('Delete selected items?', [], 'Modules.Itstoreblog.Admin'),
            ],
        ];
    }

    public function renderForm()
    {
        $this->fields_form = [
            'legend' => ['title' => $this->trans('Blog post', [], 'Modules.Itstoreblog.Admin'), 'icon' => 'icon-rss'],
            'input' => [
                ['type' => 'text', 'label' => $this->trans('Title', [], 'Modules.Itstoreblog.Admin'), 'name' => 'title', 'required' => true, 'col' => 6],
                ['type' => 'text', 'label' => $this->trans('Slug', [], 'Modules.Itstoreblog.Admin'), 'name' => 'slug', 'col' => 6, 'desc' => $this->trans('Leave blank to auto-generate from the title.', [], 'Modules.Itstoreblog.Admin')],
                ['type' => 'text', 'label' => $this->trans('Tag (badge)', [], 'Modules.Itstoreblog.Admin'), 'name' => 'tag', 'col' => 4, 'desc' => $this->trans('Short label shown on the card.', [], 'Modules.Itstoreblog.Admin')],
                ['type' => 'text', 'label' => $this->trans('Category', [], 'Modules.Itstoreblog.Admin'), 'name' => 'category', 'col' => 4, 'desc' => $this->trans('Groups posts and drives related articles.', [], 'Modules.Itstoreblog.Admin')],
                ['type' => 'text', 'label' => $this->trans('Tags', [], 'Modules.Itstoreblog.Admin'), 'name' => 'tags', 'col' => 6, 'desc' => $this->trans('Comma-separated, e.g. gpu, gaming, benchmarks.', [], 'Modules.Itstoreblog.Admin')],
                ['type' => 'text', 'label' => $this->trans('Author', [], 'Modules.Itstoreblog.Admin'), 'name' => 'author', 'col' => 4],
                ['type' => 'text', 'label' => $this->trans('Image URL', [], 'Modules.Itstoreblog.Admin'), 'name' => 'image', 'col' => 8, 'desc' => $this->trans('Absolute URL or a path under your shop.', [], 'Modules.Itstoreblog.Admin')],
                ['type' => 'textarea', 'label' => $this->trans('Excerpt', [], 'Modules.Itstoreblog.Admin'), 'name' => 'excerpt', 'rows' => 3, 'desc' => $this->trans('Short summary shown in listings.', [], 'Modules.Itstoreblog.Admin')],
                ['type' => 'textarea', 'label' => $this->trans('Content', [], 'Modules.Itstoreblog.Admin'), 'name' => 'content', 'autoload_rte' => true, 'rows' => 15, 'cols' => 60],
                ['type' => 'text', 'label' => $this->trans('Meta title', [], 'Modules.Itstoreblog.Admin'), 'name' => 'meta_title', 'col' => 8],
                ['type' => 'text', 'label' => $this->trans('Meta description', [], 'Modules.Itstoreblog.Admin'), 'name' => 'meta_description', 'col' => 8],
                [
                    'type' => 'switch', 'label' => $this->trans('Published', [], 'Modules.Itstoreblog.Admin'), 'name' => 'active', 'is_bool' => true,
                    'values' => [
                        ['id' => 'active_on', 'value' => 1, 'label' => $this->trans('Yes', [], 'Modules.Itstoreblog.Admin')],
                        ['id' => 'active_off', 'value' => 0, 'label' => $this->trans('No', [], 'Modules.Itstoreblog.Admin')],
                    ],
                ],
            ],
            'submit' => ['title' => $this->trans('Save', [], 'Modules.Itstoreblog.Admin')],
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
