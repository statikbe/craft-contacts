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
            ->contentTemplate('contacts/contacts/_detail', $variables)
            ->action('contacts/contacts/save')
            ->addAltAction(Craft::t('app', 'Save and continue editing'), [
                'redirect' => "contacts/{$element->id}",
                'shortcut' => true,
                'retainScroll' => true,
            ])
            ->metaSidebarTemplate('contacts/contacts/_sidebar', ['element' => $element, 'meta' => $meta])
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

    public function actionNew(): Response
    {
        return $this->asCpScreen()
            ->contentTemplate('contacts/contacts/_new')
            ->title(Craft::t('contacts', 'New Contact'));
    }

    public function actionCreate(): Response
    {
        $this->requirePostRequest();
        $params = $this->request->getBodyParams();
        
        $email = $params['email'] ?? null;
        $fullName = $params['fullName'] ?? null;

        if (!$email || !$fullName) {
            return $this->asFailure(Craft::t('contacts', 'Email and full name are required.'));
        }

        // Check if user with this email already exists
        $existingUser = User::find()->email($email)->status(null)->one();
        if ($existingUser) {
            return $this->asFailure(Craft::t('contacts', 'A contact with this email address already exists.'));
        }

        // Create new inactive user
        $user = new User();
        $user->email = $email;
        $user->fullName = $fullName;
        $user->username = $email; // Use email as username
        
        if (Craft::$app->getElements()->saveElement($user)) {
            // Assign to default user group if configured
            $settings = Contacts::getInstance()->getSettings();
            if ($settings->defaultUserGroup) {
                Craft::$app->getUsers()->assignUserToGroups($user->id, [$settings->defaultUserGroup]);
            }
            
            return $this->asSuccess(Craft::t('contacts', 'Contact created successfully.'), [
                'redirect' => 'contacts/' . $user->id,
            ]);
        } else {
            return $this->asFailure(Craft::t('contacts', 'Could not create contact.'));
        }
    }
}
