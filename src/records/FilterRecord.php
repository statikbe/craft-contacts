<?php

namespace statikbe\contacts\records;

use craft\db\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * Filter Record for storing ElementCondition filters
 *
 * @property int $id
 * @property string $label
 * @property string|null $conditionConfig
 * @property int|null $ownerId
 * @property bool $shared

 */
class FilterRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%contacts_filters}}';
    }

    /**
     * Get the owner relation (User)
     */
    public function getOwner(): ActiveQuery
    {
        return $this->hasOne(\craft\records\User::class, ['id' => 'ownerId']);
    }

    public function rules(): array
    {
        return [
        ];
    }
}
