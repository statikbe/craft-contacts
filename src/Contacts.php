<?php

namespace statikbe\contacts;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\elements\User;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\models\FieldLayout;
use craft\services\Elements;
use craft\web\UrlManager;
use statikbe\contacts\elements\Contact;
use statikbe\contacts\models\Settings;
use statikbe\contacts\services\FilterService;
use statikbe\contacts\services\ExportService;
use yii\base\Event;

/**
 * Contacts plugin
 *
 * @method static Contacts getInstance()
 * @method Settings getSettings()
 * @author Statik.be <support@statik.be>
 * @copyright Statik.be
 * @license MIT
 * @property-read FilterService $filterService
 * @property-read ExportService $exportService
 */
class Contacts extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    /**
     * Returns the plugin's configuration
     *
     * @return array
     */
    public static function config(): array
    {
        return [
            'components' => [
                'filterService' => FilterService::class,
                'exportService' => ExportService::class,
            ],
        ];
    }

    /**
     * Initializes the plugin
     *
     * @return void
     */
    public function init(): void
    {
        parent::init();
        $this->attachEventHandlers();

    }

    /**
     * Creates and returns the model used to store the plugin's settings
     *
     * @return Model|null
     */
    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    /**
     * Returns the rendered settings HTML
     *
     * @return string|null
     */
    protected function settingsHtml(): ?string
    {
        $layout = Craft::$app->getFields()->getLayoutByType(User::class);
        $tabs = collect($layout->getTabs())->map(function ($tab) {
            return [
                'label' => $tab->name,
                'value' => $tab->uid,
            ];
        })->values()->all();

        $groups = collect(Craft::$app->getUserGroups()->getAllGroups())
            ->map(function ($group) {
                return [
                    'label' => $group->name,
                    'value' => $group->id,
                ];
            })->values()->all();


        return Craft::$app->view->renderTemplate('contacts/_settings.twig', [
            'plugin' => $this,
            'tabs' => $tabs,
            'groups' => $groups,
            'settings' => $this->getSettings(),
        ]);
    }

    /**
     * Returns the CP nav item for this plugin
     *
     * @return array
     */
    public function getCpNavItem(): array
    {
        $navItem = parent::getCpNavItem();
        $navItem['label'] = Craft::t('contacts', 'Contacts');
        $navItem['subnav'] = [
            'allContacts' => [
                'url' => 'contacts',
                'label' => Craft::t('app', 'All Contacts'),
            ],
            'filters' => [
                'url' => 'contacts/filters',
                'label' => Craft::t('app', 'Filters'),
            ],
        ];
        return $navItem;
    }


    /**
     * Attaches event handlers for the plugin
     *
     * @return void
     */
    private function attachEventHandlers(): void
    {


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
                if ($layout->getErrors()) {
                    return;
                }
                // Check if we're dealing with the User element field layout
                if ($layout->type !== User::class) {
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
            $event->rules['contacts/new'] = 'contacts/contacts/new';
            $event->rules['contacts/<elementId:\\d+>'] = 'contacts/contacts/edit';
            $event->rules['contacts/filters'] = 'contacts/filter/index';
            $event->rules['contacts/filters/data'] = 'contacts/filter/data';
            $event->rules['contacts/filters/edit'] = 'contacts/filter/edit';
            $event->rules['contacts/filters/edit/<filterId:\\d+>'] = 'contacts/filter/edit';
            $event->rules['contacts/filters/save'] = 'contacts/filter/save';
            $event->rules['contacts/filters/delete'] = 'contacts/filter/delete';
            $event->rules['contacts/contacts/export-xlsx'] = 'contacts/contacts/export-xlsx';
        });
    }
}
