<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\contactform\models;

use craft\base\Model;
use craft\web\UploadedFile;

/**
 * Class Submission
 *
 * @package craft\contactform
 */
class Submission extends Model
{
    /**
     * @var string|null
     */
    public $fromCode;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'fromCode' => \Craft::t('contact-form', 'Your Code'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fromCode'], 'required']
        ];
    }
}
