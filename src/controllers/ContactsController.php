<?php

namespace statikbe\contacts\controllers;

use Craft;
use craft\elements\User;
use craft\helpers\Cp;
use craft\web\assets\cp\CpAsset;
use craft\web\Controller;
use statikbe\contacts\Contacts;
use statikbe\contacts\elements\Contact;
use yii\web\Response;

/**
 * Contacts controller
 */
class ContactsController extends Controller
{
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    /**
     * Displays the contacts index page
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        return $this->asCpScreen()
            ->contentTemplate('contacts/_index')
            ->title(Craft::t('contacts', 'Contacts'));
    }

    /**
     * Displays the contact edit page
     *
     * @param int|null $elementId The contact ID to edit
     * @return Response
     */
    public function actionEdit(int $elementId = null): Response
    {
        $settings = Contacts::getInstance()->getSettings();
        $element = User::find()->id($elementId)->status(null)->one();

        $title = Craft::$app->getView()->renderObjectTemplate($settings->contactTitleFormat, $element);

        // Register CP assets for tab functionality
        Craft::$app->getView()->registerAssetBundle(CpAsset::class);

        $meta = Cp::metadataHtml($element->getMetadata());
        $layout = Craft::$app->getFields()->getLayoutByType(User::class);
        $allowedTabs = collect($layout->tabs)->filter(function ($tab) use ($settings) {
            return in_array($tab->uid, $settings->visibleTabs);
        })->all();

        $layout->setTabs($allowedTabs);
        $form = $layout->createForm($element);

        $variables = [
            'element' => $element,
            'form' => $form,
        ];

        return $this->asCpScreen()
            ->contentTemplate('contacts/contacts/_detail', $variables)
            ->action('contacts/contacts/save')
            ->tabs($form->getTabMenu())
            ->addAltAction(Craft::t('app', 'Save and continue editing'), [
                'redirect' => "contacts/{$element->id}",
                'shortcut' => true,
                'retainScroll' => true,
            ])
            ->metaSidebarTemplate('contacts/contacts/_sidebar', ['element' => $element, 'meta' => $meta])
            ->title($title);
    }

    /**
     * Saves a contact
     *
     * @return Response
     */
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

    /**
     * Displays the new contact page
     *
     * @return Response
     */
    public function actionNew(): Response
    {
        return $this->asCpScreen()
            ->contentTemplate('contacts/contacts/_new')
            ->title(Craft::t('contacts', 'New Contact'));
    }

    /**
     * Creates a new contact
     *
     * @return Response
     */
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

    /**
     * Convert an inactive contact to an active user
     */
    public function actionConvertToUser(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        
        $userId = $this->request->getBodyParam('contactId');
        if (!$userId) {
            return $this->asFailure(Craft::t('contacts', 'Contact ID is required.'));
        }

        // Get the contact
        $user = User::find()->id($userId)->status(null)->one();
        if (!$user) {
            return $this->asFailure(Craft::t('contacts', 'Contact not found.'));
        }

        // Check if user is already active
        if ($user->active) {
            return $this->asFailure(Craft::t('contacts', 'This contact is already an active user.'));
        }

            // Set user to pending status (they'll be activated when they complete the activation process)
            $user->pending = true;
            $user->active = false; // Ensure they're not active until they complete activation
            // Save the user
            if (!Craft::$app->getElements()->saveElement($user)) {
                $errors = implode(', ', $user->getErrorSummary(true));
                return $this->asFailure(Craft::t('contacts', 'Could not prepare user for activation: {errors}', ['errors' => $errors]));
            }

            // Assign to default user group if configured
            $settings = Contacts::getInstance()->getSettings();
            if ($settings->defaultUserGroup) {
                Craft::$app->getUsers()->assignUserToGroups($user->id, [$settings->defaultUserGroup]);
            }

            // Send activation email
            $emailSent = Craft::$app->getUsers()->sendActivationEmail($user);

            if (!$emailSent) {
                return $this->asFailure(Craft::t('contacts', 'User was prepared for activation but the activation email could not be sent. Check your email settings.'));
            }

            return $this->asSuccess(Craft::t('contacts', 'Contact successfully converted. An activation email has been sent to {email}.', ['email' => $user->email]));

        try {
        } catch (\Exception $e) {
            return $this->asFailure(Craft::t('contacts', 'An error occurred while converting the contact: {error}', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Export contacts to XLSX
     */
    public function actionExportXlsx(): Response
    {
        $this->requirePostRequest();
        
        // Get the contact IDs from the request
        $contactIds = Craft::$app->getRequest()->getBodyParam('contactId', []);
        
        if (empty($contactIds)) {
            throw new \yii\web\BadRequestHttpException('No contacts selected for export.');
        }

        // Get the contacts
        $contacts = Contact::find()
            ->id($contactIds)
            ->status(null)
            ->all();

        if (empty($contacts)) {
            throw new \yii\web\NotFoundHttpException('No contacts found for export.');
        }

        // Use the export service to generate and download the file
        $exportService = Contacts::getInstance()->exportService;
        $success = $exportService->exportAndDownload($contacts);

        if (!$success) {
            throw new \yii\web\ServerErrorHttpException('Failed to generate export file.');
        }

        // The response is sent by the service, so we don't need to return anything
        return $this->response;
    }
}
