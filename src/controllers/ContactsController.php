<?php

namespace statikbe\contacts\controllers;

use Craft;
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
//        return $this->renderTemplate('contacts/_index');
        return $this->asCpScreen()
            ->contentTemplate('contacts/_index')
            ->title(Craft::t('contacts', 'Contacts'));
    }

    public function actionEdit(int $elementId = null): Response
    {
        $settings = Contacts::getInstance()->getSettings();
        return $this->asCpScreen()
            ->contentTemplate($settings->contentTemplate)
            ->metaSidebarTemplate($settings->sidebarTemplate)
            ->title(Craft::t('contacts', 'Edit Contact'))
            ->variables([
                'elementId' => $elementId,
                'settings' => $settings,
            ]);

    }
}
