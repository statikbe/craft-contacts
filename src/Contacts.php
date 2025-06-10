<?php

namespace statikbe\contacts;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\elements\User;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\models\FieldLayout;
use craft\services\Elements;
use craft\web\twig\variables\Cp;
use craft\web\UrlManager;
use statikbe\contacts\elements\Contact;
use statikbe\contacts\models\Settings;
use yii\base\Event;

/**
 * Contacts plugin
 *
 * @method static Contacts getInstance()
 * @method Settings getSettings()
 * @author Statik.be <support@statik.be>
 * @copyright Statik.be
 * @license MIT
 */
class Contacts extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        $this->attachEventHandlers();

        // Any code that creates an element query or loads Twig should be deferred until
        // after Craft is fully initialized, to avoid conflicts with other plugins/modules
        Craft::$app->onInit(function () {

        });
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('contacts/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    private function attachEventHandlers(): void
    {
        Event::on(Cp::class, Cp::EVENT_REGISTER_CP_NAV_ITEMS, function (RegisterCpNavItemsEvent $event) {
            $event->navItems[] = [
                'url' => 'contacts',
                'label' => 'Contacts',
                'icon' => '@appicons/newspaper.svg',
            ];
        });

        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = Contact::class;
            });

        Event::on(
            FieldLayout::class,
            FieldLayout::EVENT_AFTER_VALIDATE,
            function (Event $event) {
                /* @var FieldLayout $layout */
                $layout = $event->sender;
                if($layout->getErrors()) {
                    return;
                }
                // Check if we're dealing with the User element field layout
                if($layout->type !== User::class) {
                    return;
                }

                $contactsLayout = Craft::$app->getFields()->getLayoutByType(Contact::class);
                if (!$contactsLayout) {
                    $contactsLayout = new FieldLayout();
                }
                $contactsLayout->setTabs($layout->getTabs());
                Craft::$app->getFields()->saveLayout($contactsLayout);
            });


        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function (RegisterUrlRulesEvent $event) {
            $event->rules['contacts'] = ['template' => 'contacts/contacts/_index.twig'];
            $event->rules['contacts/<elementId:\\d+>'] = 'contacts/contacts/edit';
        });

    }
}
