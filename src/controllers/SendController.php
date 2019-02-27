<?php

namespace craft\contactform\controllers;

use Craft;
use craft\contactform\models\Submission;
use craft\contactform\Plugin;
use craft\web\Controller;
use craft\web\UploadedFile;
use yii\web\Response;

class SendController extends Controller
{
    // Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public $allowAnonymous = true;

    // Public Methods
    // =========================================================================

    /**
     * Sends a contact form submission.
     *
     * @return Response|null
     */
    public function actionIndex()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $plugin = Plugin::getInstance();
        $settings = $plugin->getSettings();

        $submission = new Submission();
        $submission->fromCode = $request->getBodyParam('fromCode');
        $fromCode = $request->getBodyParam('fromCode');

        if ($request->getBodyParam('fromCode')) {

            if (is_array($fromCode)) {
                $submission->fromCode = array_filter($fromCode, function($value) {
                    return $value !== '';
                });
            } else {
                $submission->fromCode = $fromCode;
            } 

            $rows = (new \yii\db\Query())
                ->select(['code','used_count','status'])
                ->from('presale_code')
                ->where(['code' => $fromCode])
                ->one();

            if ($rows["code"] && $rows["status"] == 1 &&  $rows["used_count"] <= 6 ) {

                $connection = (new \craft\db\Query());
                if ($rows["used_count"] == 6) {
                    $update = $connection->createCommand()
                    ->update('presale_code', 
                        array(
                            'status'=>0,
                        ),
                        'code=:id',
                        array(':id'=>$fromCode)
                    )->execute();
                    Craft::$app->getSession()->setError(Craft::t('contact-form', 'Code is already used.'));
                    return $this->redirectToPostedUrl($submission);

                }else{
                    $update = $connection->createCommand()
                        ->update('presale_code', 
                            array(
                                'used_count'=>$rows["used_count"] + 1,
                            ),
                            'code=:id',
                            array(':id'=>$fromCode)
                        )->execute();
                    if ($request->getAcceptsJson()) {
                        return $this->asJson(['success' => true]);
                    }
                    Craft::$app->getSession()->setNotice($settings->successFlashMessage);
                    return $this->redirectToPostedUrl($submission);
                }
            }else{                                

                if ($request->getAcceptsJson()) {
                    return $this->asJson(['errors' => $submission->getErrors()]);
                }

                Craft::$app->getSession()->setError(Craft::t('contact-form', 'Code not available.'));

                Craft::$app->getUrlManager()->setRouteParams([
                    'variables' => ['message' => $submission]
                ]);

                return null;

            }
        } else {
            Craft::$app->getSession()->setError(Craft::t('contact-form', 'Code is require.'));
            return $this->redirectToPostedUrl($submission);
        }

        Craft::$app->getSession()->setNotice($settings->successFlashMessage);
        return $this->redirectToPostedUrl($submission);
    }
}
