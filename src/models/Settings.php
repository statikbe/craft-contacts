<?php

namespace statikbe\contacts\models;

use craft\base\Model;

/**
 * Contacts settings
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
