<?php

namespace statikbe\contacts\models;

use craft\base\Model;

/**
 * Contacts settings
 */
class Settings extends Model
{
    public string|null $contentTemplate = 'contacts/_content/_detail';

    public string|null $sidebarTemplate = 'contacts/_sidebar/_sidebar';

    public string|null $contactTitleFormat = '{user.email}';
}
