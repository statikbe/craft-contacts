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
use statikbe\contacts\elements\actions\ExportAllXlsx;
use yii\web\Response;

/**
 * Contact element type
 */
class Contact extends User
{
    /**
     * Returns the display name for this element type
     *
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('contacts', 'Contact');
    }

    /**
     * Returns the lowercase display name for this element type
     *
     * @return string
     */
    public static function lowerDisplayName(): string
    {
        return Craft::t('contacts', 'contact');
    }

    /**
     * Returns the plural display name for this element type
     *
     * @return string
     */
    public static function pluralDisplayName(): string
    {
        return Craft::t('contacts', 'Contacts');
    }

    /**
     * Returns the plural lowercase display name for this element type
     *
     * @return string
     */
    public static function pluralLowerDisplayName(): string
    {
        return Craft::t('contacts', 'contacts');
    }

    /**
     * Returns the reference handle for this element type
     *
     * @return string|null
     */
    public static function refHandle(): ?string
    {
        return 'contact';
    }

    /**
     * Returns whether elements of this type have URIs
     *
     * @return bool
     */
    public static function hasUris(): bool
    {
        return false;
    }

    /**
     * Creates a new element query for this element type
     *
     * @return UserQuery
     */
    public static function find(): UserQuery
    {
        return Craft::createObject(ContactQuery::class, [static::class]);
    }

    /**
     * Creates a new condition for this element type
     *
     * @return ElementConditionInterface
     */
    public static function createCondition(): ElementConditionInterface
    {
        return Craft::createObject(UserCondition::class, [static::class]);
    }

    /**
     * Defines the table attributes for this element type.
     *
     * @return array
     */
    protected static function defineTableAttributes(): array
    {
        return User::defineTableAttributes();
    }

    /**
     * Returns the field layouts for this element type.
     *
     * Overrides the base implementation so Craft's condition system (and other
     * callers of the static method) resolve to the User field layout instead of
     * looking for a non-existent layout stored under Contact::class.
     *
     * @return \craft\models\FieldLayout[]
     */
    public static function fieldLayouts(?string $context): array
    {
        $fieldLayout = Craft::$app->getFields()->getLayoutByType(User::class);
        return $fieldLayout !== null ? [$fieldLayout] : [];
    }

    /**
     * Returns the field layout for this element
     *
     * @return \craft\models\FieldLayout|null
     */
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

    /**
     * Defines the available actions for this element type
     *
     * @param string $source The source key
     * @return array
     */
    protected static function defineActions(string $source): array
    {
        // List any bulk element actions here
        return [
            CopyEmail::class,
            ExportXlsx::class,
            ExportAllXlsx::class,
        ];
    }


    /**
     * Returns the preview targets for this element
     *
     * @return array
     */
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

    /**
     * Returns whether the given user can view this element
     *
     * @param User $user
     * @return bool
     */
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
