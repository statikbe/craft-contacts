<?php

namespace statikbe\contacts\models;

use Craft;
use craft\base\Element;
use craft\base\Model;
use craft\elements\conditions\ElementCondition;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\conditions\users\UserCondition;
use craft\elements\User;
use craft\helpers\Json;

/**
 * Filter Model for saving ElementConditions
 */
class FilterModel extends Model
{
    public ?int $id = null;
    public ?string $label = null;
    public ?string $conditionConfig = null;
    public ?int $ownerId = null;
    public bool $shared = false;
    public $condition;

    private ?ElementConditionInterface $_condition = null;

    /**
     * Get the ElementCondition instance for this filter
     *
     * Creates and configures an ElementCondition from the stored configuration.
     * The condition is cached after first creation for performance.
     *
     * @return ElementConditionInterface|null The configured condition instance
     */
    public function getCondition(): ?ElementConditionInterface
    {
        if ($this->_condition === null) {
            if ($this->conditionConfig) {
                try {
                    $config = Json::decode($this->conditionConfig);
                    $this->_condition = $this->createCondition();

                    // Restore the condition state from stored configuration
                    if (is_array($config) && !empty($config)) {
                        // Set condition rules if they exist in the config
                        if (isset($config['conditionRules'])) {
                            $this->_condition->setConditionRules($config['conditionRules']);
                        }
                        
                        // Set other condition attributes
                        $this->_condition->setAttributes($config, false);
                    }
                } catch (\Exception $e) {
                    Craft::error('Failed to create condition from config: ' . $e->getMessage(), __METHOD__);
                    $this->_condition = $this->createCondition();
                }
            } else {
                $this->_condition = $this->createCondition();
            }
        }

        return $this->_condition;
    }

    /**
     * Set the ElementCondition instance
     */
    public function setCondition(?ElementConditionInterface $condition): void
    {
        $this->_condition = $condition;
        
        if ($condition) {
            $this->conditionConfig = Json::encode($condition->getConfig());
        } else {
            $this->conditionConfig = null;
        }
    }

    /**
     * Create a new UserCondition instance for Contact elements
     *
     * Creates a UserCondition which is compatible with Contact elements
     * since Contact extends User. This condition can then be configured
     * with specific rules and used to filter contact queries.
     *
     * @return UserCondition A new UserCondition instance
     */
    public function createCondition(): ElementCondition
    {
        return new UserCondition(User::class);
    }

    /**
     * Get the filter owner (User)
     */
    public function getOwner(): ?\craft\elements\User
    {
        if ($this->ownerId) {
            return Craft::$app->getUsers()->getUserById($this->ownerId);
        }
        return null;
    }

    /**
     * Set the filter owner
     */
    public function setOwner(?\craft\elements\User $user): void
    {
        $this->ownerId = $user?->id;
    }

    /**
     * Check if the current user can edit this filter
     */
    public function canEdit(?\craft\elements\User $user = null): bool
    {
        $user = $user ?? Craft::$app->getUser()->getIdentity();

        if (!$user) {
            return false;
        }

        // Owner can always edit
        if ($this->ownerId === $user->id) {
            return true;
        }

        // Admin can edit any filter
        if ($user->admin) {
            return true;
        }

        // Check if user has permission to manage shared filters
        return $this->shared && $user->can('manageSharedContactFilters');
    }

    /**
     * Check if the current user can delete this filter
     */
    public function canDelete(?\craft\elements\User $user = null): bool
    {
        return $this->canEdit($user);
    }

    /**
     * Get a summary of the condition for display
     */
    public function getConditionSummary(): string
    {
        $condition = $this->getCondition();
        if (!$condition) {
            return Craft::t('contacts', 'No conditions');
        }

        $rules = $condition->getConditionRules();
        $count = count($rules);

        if ($count === 0) {
            return Craft::t('contacts', 'No conditions');
        }

        if ($count === 1) {
            return Craft::t('contacts', '1 condition');
        }

        return Craft::t('contacts', '{count} conditions', ['count' => $count]);
    }

    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            [['label'], 'required'],
            [['label'], 'string', 'max' => 255],
            [['ownerId'], 'integer'],
            [['shared'], 'boolean'],
            [['conditionConfig'], 'string'],
        ]);
    }


    /**
     * Get the attributes that should be included when converting to array
     */
    public function fields(): array
    {
        $fields = parent::fields();
        $fields[] = 'conditionSummary';
        return $fields;
    }
}
