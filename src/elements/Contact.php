<?php

namespace statikbe\contacts\elements;

use Craft;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\conditions\users\UserCondition;
use craft\elements\db\UserQuery;
use craft\elements\User;
use craft\helpers\UrlHelper;
use craft\web\CpScreenResponseBehavior;
use statikbe\contacts\elements\db\ContactQuery;
use statikbe\contacts\elements\actions\CopyEmail;
use statikbe\contacts\elements\actions\ExportXlsx;
use yii\web\Response;

/**
 * Contact element type
 */
class Contact extends User
{
    public static function displayName(): string
    {
        return Craft::t('contacts', 'Contact');
    }

    public static function lowerDisplayName(): string
    {
        return Craft::t('contacts', 'contact');
    }

    public static function pluralDisplayName(): string
    {
        return Craft::t('contacts', 'Contacts');
    }

    public static function pluralLowerDisplayName(): string
    {
        return Craft::t('contacts', 'contacts');
    }

    public static function refHandle(): ?string
    {
        return 'contact';
    }

    public static function hasUris(): bool
    {
        return false;
    }

    public static function find(): UserQuery
    {
        return Craft::createObject(ContactQuery::class, [static::class]);
    }

    public static function createCondition(): ElementConditionInterface
    {
        return Craft::createObject(UserCondition::class, [static::class]);
    }

    protected static function defineTableAttributes(): array
    {
        return User::defineTableAttributes();
    }

    public function getFieldLayout(): ?\craft\models\FieldLayout
    {
        return Craft::$app->getFields()->getLayoutByType(User::class);
    }


    /**
     * Defines the sources that should be shown in the contact index sidebar
     *
     * Creates the default "All contacts" source and adds user-defined filter sources.
     * Filter sources are automatically generated from saved filters and appear
     * under a "Filters" heading in the sidebar.
     *
     * @param string $context The context where sources are being displayed
     * @return array Array of source definitions
     */
    protected static function defineSources(string $context): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('contacts', 'All contacts'),
            ],
        ];

        // Add filter sources from saved filters
        $filterService = \statikbe\contacts\Contacts::getInstance()->filterService;
        $currentUser = Craft::$app->getUser()->getIdentity();
        
        if ($currentUser) {
            // Get shared and non-shared filters separately
            $allFilters = $filterService->getAllFiltersForUser();
            $sharedFilters = [];
            $personalFilters = [];
            
            foreach ($allFilters as $filter) {
                if ($filter->shared) {
                    $sharedFilters[] = $filter;
                } else {
                    $personalFilters[] = $filter;
                }
            }

            // Add personal filters section
            if (!empty($personalFilters)) {
                $sources[] = ['heading' => Craft::t('contacts', 'My Filters')];
                
                foreach ($personalFilters as $filter) {
                    $sources[] = [
                        'key' => 'filter:' . $filter->id,
                        'label' => $filter->label,
                        'criteria' => [
                            'filter' => $filter->id,
                        ],
                        'defaultSort' => ['username', 'asc'],
                    ];
                }
            }

            // Add shared filters section
            if (!empty($sharedFilters)) {
                $sources[] = ['heading' => Craft::t('contacts', 'Shared Filters')];
                
                foreach ($sharedFilters as $filter) {
                    $sources[] = [
                        'key' => 'filter:' . $filter->id,
                        'label' => $filter->label,
                        'criteria' => [
                            'filter' => $filter->id,
                        ],
                        'defaultSort' => ['username', 'asc'],
                    ];
                }
            }
        }

        return $sources;
    }

    protected static function defineActions(string $source): array
    {
        // List any bulk element actions here
        return [
            CopyEmail::class,
            ExportXlsx::class,
        ];
    }


    protected function previewTargets(): array
    {
        $previewTargets = [];
        $url = $this->getUrl();
        if ($url) {
            $previewTargets[] = [
                'label' => Craft::t('app', 'Primary {type} page', [
                    'type' => self::lowerDisplayName(),
                ]),
                'url' => $url,
            ];
        }
        return $previewTargets;
    }

    public function canView(User $user): bool
    {
        if (parent::canView($user)) {
            return true;
        }
        // todo: implement user permissions
        return $user->can('viewContacts');
    }

    public function canSave(User $user): bool
    {
        if (parent::canSave($user)) {
            return true;
        }
        // todo: implement user permissions
        return $user->can('saveContacts');
    }

    public function canDuplicate(User $user): bool
    {
        if (parent::canDuplicate($user)) {
            return true;
        }
        // todo: implement user permissions
        return $user->can('saveContacts');
    }

    public function canDelete(User $user): bool
    {
        if (parent::canSave($user)) {
            return true;
        }
        // todo: implement user permissions
        return $user->can('deleteContacts');
    }

    public function canCreateDrafts(User $user): bool
    {
        return true;
    }

    protected function cpEditUrl(): ?string
    {
        return sprintf('contacts/%s', $this->getCanonicalId());
    }

    public function getPostEditUrl(): ?string
    {
        return UrlHelper::cpUrl('contacts');
    }

    public function prepareEditScreen(Response $response, string $containerId): void
    {
        /** @var Response|CpScreenResponseBehavior $response */
        $response->crumbs([
            [
                'label' => self::pluralDisplayName(),
                'url' => UrlHelper::cpUrl('contacts'),
            ],
        ]);
    }

    protected function htmlAttributes(string $context): array
    {
        $attributes = parent::htmlAttributes($context);
        
        // Add email as a data attribute for the copy email action
        $attributes['data']['email'] = $this->email;
        
        return $attributes;
    }

    public function afterSave(bool $isNew): void
    {
        if (!$this->propagating) {
            // todo: update the `contacts` table
        }

        parent::afterSave($isNew);
    }
}
