<?php

namespace statikbe\contacts\elements\db;

use Craft;
use craft\elements\db\ElementQuery;

/**
 * Contact query
 */
class ContactQuery extends ElementQuery
{
    protected function beforePrepare(): bool
    {
        return parent::beforePrepare();
    }
}
