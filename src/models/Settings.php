<?php

namespace statikbe\contacts\models;

use craft\base\Model;

/**
 * Contacts settings model
 *
 * @property string|null $contentTemplate Content template path
 * @property string|null $sidebarTemplate Sidebar template path  
 * @property string|null $contactTitleFormat Contact title format
 * @property array $visibleTabs Visible tab UIDs
 * @property array $userGroups User group IDs
 * @property int|null $defaultUserGroup Default user group ID
 */
class Settings extends Model
{
    public string|null $contentTemplate = '';

    public string|null $sidebarTemplate = '';

    public string|null $contactTitleFormat = '{user.email}';

    public array $visibleTabs = [];

    public array $userGroups = [];

    public int|null $defaultUserGroup = null;
}
