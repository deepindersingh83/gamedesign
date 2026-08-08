<?php
/**
 * IT Store — FAQ entry ObjectModel.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ItstoreFaq extends ObjectModel
{
    public $id_faq;
    public $id_shop;
    public $category;
    public $question;
    public $answer;
    public $position;
    public $active;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'itstore_faq',
        'primary' => 'id_faq',
        'fields' => [
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'category' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 128],
            'question' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 512],
            'answer' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    /**
     * Active entries for a shop (id_shop 0 = all), ordered by category then
     * position.
     */
    public static function getActive($idShop)
    {
        return Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'itstore_faq`
             WHERE active = 1 AND (id_shop = 0 OR id_shop = ' . (int) $idShop . ')
             ORDER BY category ASC, position ASC, id_faq ASC'
        ) ?: [];
    }

    public static function countActive($idShop)
    {
        return (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'itstore_faq`
             WHERE active = 1 AND (id_shop = 0 OR id_shop = ' . (int) $idShop . ')'
        );
    }
}
