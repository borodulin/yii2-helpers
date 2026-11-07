<?php
/**
 * @link https://github.com/borodulin/yii2-helpers
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-helpers/blob/master/LICENSE
 */

namespace conquer\helpers;

use yii\helpers\BaseJson;
use yii\web\JsExpression;

/**
 * JSON encoder that turns strings with the "js:" prefix into JavaScript expressions:
 *
 * ```php
 * Json::encode(['onClick' => 'js:function () { alert(1); }']);
 * ```
 *
 * WARNING: every string in the value is checked, at any depth. Never pass data that may come
 * from users (database records, request parameters) through this class: a value like "js:alert(1)"
 * would be executed. Encode such data with yii\helpers\Json::htmlEncode() instead,
 * or disable the prefix with `Json::$jsPrefix = null`.
 *
 * By default "<" and ">" are escaped (JSON_HEX_TAG), so the result is safe to put into a <script> block.
 *
 * @package conquer\helpers
 * @author Andrey Borodulin
 */
class Json extends BaseJson
{
    /**
     * Prefix that marks a string as a JavaScript expression. Set to null or '' to disable.
     * @var string|null
     */
    public static $jsPrefix = 'js:';

    /**
     * @inheritdoc
     */
    public static function encode($value, $options = 321) // JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG
    {
        $prefix = static::$jsPrefix;
        if (is_array($value) && $prefix !== null && $prefix !== '') {
            $length = strlen($prefix);
            array_walk_recursive($value, function (&$item) use ($prefix, $length) {
                if (is_string($item) && (strncasecmp($item, $prefix, $length) === 0)) {
                    $item = new JsExpression(substr($item, $length));
                }
            });
        }
        return parent::encode($value, $options);
    }
}