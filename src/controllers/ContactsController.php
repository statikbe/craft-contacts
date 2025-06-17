<?php

namespace statikbe\contacts\controllers;

use Craft;
use craft\elements\User;
use craft\helpers\Cp;
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

        // TODO: Add edit button
        // TODO: Permission level: edit access or view access?

        $allowedTabs = ['CRM'];

        $meta = Cp::metadataHtml($element->getMetadata());
        $layout = Craft::$app->getFields()->getLayoutByType(User::class);
        $form = $layout->createForm($element);

        $variables = [
            'element' => $element,
            'form' => $form,
        ];

        return $this->asCpScreen()
            ->contentTemplate($settings->contentTemplate ?? 'contacts/contacts/_detail', $variables)
            ->action('contacts/contacts/save')
            ->addAltAction(Craft::t('app', 'Save and continue editing'), [
                'redirect' => "contacts/{$element->id}",
                'shortcut' => true,
                'retainScroll' => true,
            ])
            ->metaSidebarTemplate($settings->sidebarTemplate ?? 'contacts/contacts/_sidebar', ['element' => $element, 'meta' => $meta])
            ->title($title);
    }

    public function actionSave(): Response
    {
        $this->requirePostRequest();
        $params = $this->request->getBodyParams();
        $elementId = $params['elementId'] ?? null;
        $user = User::find()->id($elementId)->status(null)->one();
        if (!$user) {
            return $this->asFailure(Craft::t('contacts', 'Contact not found.'));
        }

        $user->setFieldValues($params['fields'] ?? []);
        Craft::$app->getElements()->saveElement($user);
        return $this->redirectToPostedUrl();
    }
}
