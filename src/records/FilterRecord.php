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
    /**
     * Returns the table name for this record
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%contacts_filters}}';
    }

    /**
     * Get the owner relation (User)
     *
     * @return ActiveQuery
     */
    public function getOwner(): ActiveQuery
    {
        return $this->hasOne(\craft\records\User::class, ['id' => 'ownerId']);
    }

    /**
     * Returns the validation rules for this record
     *
     * @return array
     */
    public function rules(): array
    {
        return [
        ];
    }
}
