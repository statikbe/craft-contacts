<?php

namespace statikbe\contacts\models;

use craft\base\Model;

/**
 * Contacts settings
 */
class Settings extends Model
{
    public string $contentTemplate = 'contacts/_content/_detail';

    public string $sidebarTemplate = 'contacts/_sidebar/_sidebar';
}
