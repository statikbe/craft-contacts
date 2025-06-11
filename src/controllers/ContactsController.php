<?php

namespace statikbe\contacts\controllers;

use Craft;
use craft\elements\User;
use craft\web\Controller;
use statikbe\contacts\Contacts;
use yii\web\Response;

/**
 * Contacts controller
 */
class ContactsController extends Controller
{
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    /**
     * contacts/contacts action
     */
    public function actionIndex(): Response
    {
        return $this->asCpScreen()
            ->contentTemplate('contacts/_index')
            ->title(Craft::t('contacts', 'Contacts'));
    }

    public function actionEdit(int $elementId = null): Response
    {
        $settings = Contacts::getInstance()->getSettings();
        $element = User::find()->id($elementId)->status(null)->one();

        $title = Craft::$app->getView()->renderObjectTemplate($settings->contactTitleFormat, $element);

        return $this->asCpScreen()
            ->contentTemplate($settings->contentTemplate ?? 'contacts/contacts/_detail', ['element' => $element])
            ->metaSidebarTemplate($settings->sidebarTemplate ?? 'contacts/contacts/_sidebar', ['element' => $element])
            ->title($title);
    }
}
