<?php
/**
 * IT Store — Product gallery zoom & lightbox.
 *
 * Progressive enhancement for the product page: hover-magnifier on the main
 * cover image (desktop) and a full-screen lightbox with keyboard/prev-next
 * navigation across every gallery image. Dependency-free; degrades to the
 * theme's stock gallery when disabled or on unsupported browsers.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstoregallery extends Module
{
    public function __construct()
    {
        $this->name = 'itstoregallery';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('IT Store Gallery Zoom', [], 'Modules.Itstoregallery.Admin');
        $this->description = $this->trans('Hover-magnifier and full-screen lightbox for product images.', [], 'Modules.Itstoregallery.Admin');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('actionFrontControllerSetMedia');
    }

    public function hookActionFrontControllerSetMedia()
    {
        if (!($this->context->controller instanceof ProductController)) {
            return;
        }
        $this->context->controller->registerStylesheet(
            'itstore-gallery',
            'modules/' . $this->name . '/views/css/gallery.css',
            ['media' => 'all', 'priority' => 149]
        );
        $this->context->controller->registerJavascript(
            'itstore-gallery',
            'modules/' . $this->name . '/views/js/gallery.js',
            ['position' => 'bottom', 'priority' => 149, 'attribute' => 'defer']
        );
    }
}
