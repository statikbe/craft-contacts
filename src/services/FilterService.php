<?php

namespace statikbe\contacts\services;

use Craft;
use craft\elements\conditions\users\UserCondition;
use craft\elements\User;
use craft\helpers\Json;
use statikbe\contacts\elements\db\ContactQuery;
use statikbe\contacts\models\FilterModel;
use statikbe\contacts\records\FilterRecord;
use yii\base\Component;
use yii\base\Exception;

/**
 * Filter Service for managing ElementCondition filters
 */
class FilterService extends Component
{
    /**
     * Get all filters for the current user
     */
    public function getAllFiltersForUser(bool $includeShared = true): array
    {
        $user = Craft::$app->getUser()->getIdentity();
        if (!$user) {
            return [];
        }

        $query = FilterRecord::find()
            ->where(['ownerId' => $user->id]);

        if ($includeShared) {
            $query->orWhere(['shared' => true]);
        }

        $query->orderBy(['label' => SORT_ASC]);

        $models = [];
        foreach ($query->all() as $record) {
            $models[] = $this->createModelFromRecord($record);
        }

        return $models;
    }

    /**
     * Get paginated filters for the current user with search and sorting
     *
     * @param int $page Current page number (1-based)
     * @param int $limit Number of items per page
     * @param string|null $search Search term to filter by label
     * @param string|null $sort Sort field and direction (e.g., 'label|asc', 'dateCreated|desc')
     * @param bool $includeShared Whether to include shared filters
     * @return array Array with 'models', 'total', and 'pagination' keys
     */
    public function getAllFiltersForUserPaginated(int $page = 1, int $limit = 20, ?string $search = null, ?string $sort = null, bool $includeShared = true): array
    {
        $user = Craft::$app->getUser()->getIdentity();
        if (!$user) {
            return ['models' => [], 'total' => 0, 'pagination' => null];
        }

        $query = FilterRecord::find()
            ->where(['ownerId' => $user->id]);

        if ($includeShared) {
            $query->orWhere(['shared' => true]);
        }

        // Apply search
        if ($search) {
            $query->andWhere(['like', 'label', $search]);
        }

        // Apply sorting
        if ($sort && strpos($sort, '|') !== false) {
            [$field, $direction] = explode('|', $sort);
            $direction = strtoupper($direction) === 'DESC' ? SORT_DESC : SORT_ASC;
            
            switch ($field) {
                case 'label':
                case 'dateCreated':
                case 'dateUpdated':
                    $query->orderBy([$field => $direction]);
                    break;
                default:
                    $query->orderBy(['label' => SORT_ASC]);
            }
        } else {
            $query->orderBy(['label' => SORT_ASC]);
        }

        // Get total count for pagination
        $total = $query->count();

        // Apply pagination
        $offset = ($page - 1) * $limit;
        $query->offset($offset)->limit($limit);

        // Convert records to models
        $models = [];
        foreach ($query->all() as $record) {
            $models[] = $this->createModelFromRecord($record);
        }

        // Calculate pagination info
        $lastPage = (int)ceil($total / $limit);
        $from = $offset + 1;
        $to = min($offset + $limit, $total);

        $pagination = [
            'total' => (int)$total,
            'per_page' => (int)$limit,
            'current_page' => (int)$page,
            'last_page' => $lastPage,
            'from' => $from > $total ? 0 : $from,
            'to' => (int)$to,
            'next_page_url' => $page < $lastPage ? '?page=' . ($page + 1) : null,
            'prev_page_url' => $page > 1 ? '?page=' . ($page - 1) : null,
        ];

        return [
            'models' => $models,
            'total' => $total,
            'pagination' => $pagination,
        ];
    }

    /**
     * Get all shared filters
     */
    public function getSharedFilters(): array
    {
        $query = FilterRecord::find()
            ->where(['shared' => true])
            ->orderBy(['label' => SORT_ASC]
            );

        $models = [];
        foreach ($query->all() as $record) {
            $models[] = $this->createModelFromRecord($record);
        }

        return $models;
    }

    /**
     * Get a filter by ID
     */
    public function getFilterById(int $id): ?FilterModel
    {
        $record = FilterRecord::findOne($id);
        if (!$record) {
            return null;
        }

        return $this->createModelFromRecord($record);
    }

    /**
     * Save a filter
     */
    public function saveFilter(FilterModel $model): bool
    {
        $isNew = !$model->id;
        
        if ($isNew) {
            $record = new FilterRecord();
        } else {
            $record = FilterRecord::findOne($model->id);
            if (!$record) {
                throw new Exception('Filter not found');
            }
        }

        // Set current user as owner if not specified
        if (!$model->ownerId) {
            $currentUser = Craft::$app->getUser()->getIdentity();
            if ($currentUser) {
                $model->ownerId = $currentUser->id;
            }
        }

        $record->setAttribute('label', $model->label);
        $record->setAttribute('conditionConfig', $model->conditionConfig);
        $record->setAttribute('ownerId', Craft::$app->getUser()->getIdentity()->id);
        $record->setAttribute('shared', $model->shared);
        $record->validate();


        if (!$model->validate()) {
            return false;
        }

        if (!$record->save()) {
            $model->addErrors($record->getErrors());
            return false;
        }

        if ($isNew) {
            $model->id = $record->id;
        }

        return true;
    }

    /**
     * Delete a filter
     */
    public function deleteFilter(FilterModel $model): bool
    {
        if (!$model->id) {
            return false;
        }

        $record = FilterRecord::findOne($model->id);
        if (!$record) {
            return false;
        }

        try {
            return (bool)$record->delete();
        } catch (\Exception $e) {
            Craft::error('Failed to delete filter: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Delete a filter by ID
     */
    public function deleteFilterById(int $id): bool
    {
        $model = $this->getFilterById($id);
        if (!$model) {
            return false;
        }

        return $this->deleteFilter($model);
    }

    /**
     * Apply a filter's condition to an element query
     *
     * Retrieves the filter's condition and applies its rules to modify the query.
     * This method handles the actual query modification using Craft's condition system.
     *
     * @param mixed $query The element query to modify
     * @param FilterModel $filter The filter whose condition should be applied
     * @return mixed The modified query
     */
    public function applyFilter($query, FilterModel $filter)
    {
        $condition = $filter->getCondition();
        if ($condition) {
            $condition->modifyQuery($query);
        }

        return $query;
    }

    /**
     * Apply a filter to an element query by filter ID
     *
     * Convenience method that retrieves a filter by ID and applies its condition
     * to the provided query. Used primarily by ContactQuery.beforePrepare().
     *
     * @param mixed $query The element query to modify
     * @param int $filterId The ID of the filter to apply
     * @return mixed The modified query
     */
    public function applyFilterById($query, int $filterId)
    {
        $filter = $this->getFilterById($filterId);
        if ($filter) {
            $this->applyFilter($query, $filter);
        }

        return $query;
    }

    /**
     * Check if a user can manage a filter
     */
    public function canManageFilter(FilterModel $filter, \craft\elements\User $user = null): bool
    {
        $user = $user ?? Craft::$app->getUser()->getIdentity();
        return $filter->canEdit($user);
    }

    /**
     * Create a FilterModel from a FilterRecord
     *
     * Converts a database record into a FilterModel instance with all attributes
     * properly transferred. The model will handle condition restoration from the
     * stored configuration when getCondition() is called.
     *
     * @param FilterRecord $record The database record to convert
     * @return FilterModel The configured FilterModel instance
     */
    private function createModelFromRecord(FilterRecord $record): FilterModel
    {
        $model = new FilterModel();
        $model->setAttributes($record->getAttributes(), true);
        $condition = new UserCondition(User::class);
        $config = Json::decodeIfJson($record->conditionConfig);
        
        // Safely handle condition rules
        if (is_array($config) && isset($config['conditionRules'])) {
            $condition->setConditionRules($config['conditionRules']);
        } else {
            $condition->setConditionRules([]);
        }
        
        $model->condition = $condition;

        // Explicitly ensure the ID is transferred from the record
        $model->id = $record->id;
        
        return $model;
    }
}
