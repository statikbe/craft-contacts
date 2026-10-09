<?php

namespace statikbe\contacts\elements\db;

use craft\elements\db\UserQuery;
use craft\elements\User;
use statikbe\contacts\elements\Contact;
use statikbe\contacts\Contacts;

/**
 * Contact element query class
 *
 * Extends UserQuery to provide filtering capabilities for Contact elements.
 * Supports applying saved filter conditions to modify query results.
 */
class ContactQuery extends UserQuery
{
    /**
     * @var int|null The ID of the filter to apply to this query
     */
    public ?int $filter = null;

    /**
     * Sets the filter ID for this query
     *
     * @param int|null $filterId The filter ID to apply, or null to clear
     * @return self
     */
    public function filter(?int $filterId): self
    {
        $this->filter = $filterId;
        return $this;
    }

    /**
     * Returns the cache tags for this query
     *
     * Contacts are users, so cached contact queries (e.g. the element index)
     * must be invalidated whenever a user is saved, whether it was saved as a
     * User or as a Contact.
     *
     * @return array
     */
    protected function cacheTags(): array
    {
        return [
            sprintf('element::%s::*', User::class),
            sprintf('element::%s::*', Contact::class),
        ];
    }

    /**
     * Applies the filter condition before preparing the query
     *
     * This method is called automatically by Craft before the query is executed.
     * If a filter ID is set, it retrieves the corresponding filter and applies
     * its condition rules to modify the query.
     *
     * @return bool
     */
    protected function beforePrepare(): bool
    {
        // Apply usergroup filtering from plugin settings
        $settings = Contacts::getInstance()->getSettings();
        if (!empty($settings->userGroups)) {
            $groupIds = $settings->userGroups;
            $contactGroupId = $settings->getContactUserGroupId();
            if ($contactGroupId !== null) {
                $groupIds[] = $contactGroupId;
            }
            $this->groupId(array_unique($groupIds));
        }

        if ($this->filter) {
            $filterService = Contacts::getInstance()->filterService;
            $filterService->applyFilterById($this, $this->filter);
        }

        return parent::beforePrepare();
    }
}
