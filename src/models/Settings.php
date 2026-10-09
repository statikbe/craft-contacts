<?php

namespace statikbe\contacts\models;

use Craft;
use craft\base\Model;

/**
 * Contacts settings model
 *
 * @property string|null $contentTemplate Content template path
 * @property string|null $sidebarTemplate Sidebar template path  
 * @property string|null $contactTitleFormat Contact title format
 * @property array $visibleTabs Visible tab UIDs
 * @property array $userGroups User group IDs
 * @property int|null $defaultUserGroup User group ID for contacts that are converted to users
 * @property string|null $contactUserGroup User group UID for contacts that are not users
 */
class Settings extends Model
{
    public string|null $contentTemplate = '';

    public string|null $sidebarTemplate = '';

    public string|null $contactTitleFormat = '{user.email}';

    public array $visibleTabs = [];

    public array $userGroups = [];

    public int|null $defaultUserGroup = null;

    public string|null $contactUserGroup = null;

    /**
     * Returns the ID of the user group for contacts that are not users
     *
     * @return int|null
     */
    public function getContactUserGroupId(): ?int
    {
        if (!$this->contactUserGroup) {
            return null;
        }

        return Craft::$app->getUserGroups()->getGroupByUid($this->contactUserGroup)?->id;
    }
}
