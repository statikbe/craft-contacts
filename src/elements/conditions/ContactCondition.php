<?php

namespace statikbe\contacts\elements\conditions;

use Craft;
use craft\elements\conditions\ElementCondition;

/**
 * Contact condition
 */
class ContactCondition extends ElementCondition
{
    protected function selectableConditionRules(): array
    {
        return parent::conditionRuleTypes();

    }
}
