<?php
/**
 * IT Store — blog post ObjectModel.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ItstoreBlogPost extends ObjectModel
{
    public $id_post;
    public $id_shop;
    public $title;
    public $slug;
    public $tag;
    public $excerpt;
    public $content;
    public $image;
    public $meta_title;
    public $meta_description;
    public $active;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'itstore_blog_post',
        'primary' => 'id_post',
        'fields' => [
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'title' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 255],
            'slug' => ['type' => self::TYPE_STRING, 'validate' => 'isLinkRewrite', 'size' => 255],
            'tag' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 128],
            'excerpt' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 1024],
            'content' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'],
            'image' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 1024],
            'meta_title' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255],
            'meta_description' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 512],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    /**
     * Derive a URL slug from the title when one is not supplied.
     */
    public function save($null_values = false, $auto_date = true)
    {
        if (!$this->slug && $this->title) {
            $this->slug = Tools::str2url($this->title);
        }
        if (!$this->slug) {
            $this->slug = 'post';
        }

        return parent::save($null_values, $auto_date);
    }

    public function add($auto_date = true, $null_values = false)
    {
        if (!$this->slug && $this->title) {
            $this->slug = Tools::str2url($this->title);
        }

        return parent::add($auto_date, $null_values);
    }

    /**
     * Fetch active posts for a shop (id_shop 0 = all shops), newest first.
     */
    public static function getActive($idShop, $limit = 0, $offset = 0)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'itstore_blog_post`
                WHERE active = 1 AND (id_shop = 0 OR id_shop = ' . (int) $idShop . ')
                ORDER BY date_add DESC';
        if ($limit > 0) {
            $sql .= ' LIMIT ' . (int) $offset . ', ' . (int) $limit;
        }

        return Db::getInstance()->executeS($sql) ?: [];
    }

    public static function countActive($idShop)
    {
        return (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'itstore_blog_post`
             WHERE active = 1 AND (id_shop = 0 OR id_shop = ' . (int) $idShop . ')'
        );
    }
}
