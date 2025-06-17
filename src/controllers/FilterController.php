<?php

namespace statikbe\contacts\controllers;

use Craft;
use craft\web\Controller;
use statikbe\contacts\Contacts;
use statikbe\contacts\models\FilterModel;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Contacts controller
 */
class FilterController extends Controller
{
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    /**
     * Filter index action
     */
    public function actionIndex(): Response
    {
        return $this->renderTemplate('contacts/filters/_index', [
            'title' => Craft::t('contacts', 'Filters'),
        ]);
    }

    /**
     * Data API endpoint for VueAdminTable
     * 
     * Returns paginated filter data in JSON format for the VueAdminTable component.
     * Supports search and sorting functionality.
     */
    public function actionData(): Response
    {
        $this->requireAcceptsJson();
        
        $request = Craft::$app->getRequest();
        
        // Get pagination parameters
        $page = (int)$request->getQueryParam('page', 1);
        $perPage = (int)$request->getQueryParam('per_page', 20);
        $search = $request->getQueryParam('search');
        $sort = $request->getQueryParam('sort');
        
        // Get paginated data from service
        $result = Contacts::getInstance()->filterService->getAllFiltersForUserPaginated(
            $page,
            $perPage,
            $search,
            $sort,
            true
        );
        
        // Format data for VueAdminTable
        $data = [];
        foreach ($result['models'] as $filter) {
            $editUrl = \craft\helpers\UrlHelper::cpUrl('contacts/filters/edit/' . $filter->id);
            $owner = $filter->getOwner();
            
            $data[] = [
                'id' => $filter->id,
                'name' => $filter->label, // VueAdminTable uses 'name' for delete confirmations
                'title' => $filter->label,
                'url' => $editUrl,
                // 'status' => removed to hide status indicator in title column
                'label' => $filter->label,
                'shared' => $filter->shared,
                'owner' => $owner ? $owner->fullName : Craft::t('contacts', 'System'),
                'canEdit' => $filter->canEdit(),
                'canDelete' => $filter->canDelete(),
            ];
        }
        
        return $this->asJson([
            'pagination' => $result['pagination'],
            'data' => $data
        ]);
    }

    /**
     * Edit/create filter action
     */
    public function actionEdit(?int $filterId = null): Response
    {
        if ($filterId) {
            $filter = Contacts::getInstance()->filterService->getFilterById($filterId);
            if (!$filter) {
                throw new NotFoundHttpException('Filter not found');
            }
            
            // Check permissions
            if (!$filter->canEdit()) {
                throw new ForbiddenHttpException('User not permitted to edit this filter');
            }
            $title = $filter->label;
        } else {
            $filter = new FilterModel();
            $filter->shared = true; // Default to shared
            $filter->setCondition($filter->createCondition());
            $title = Craft::t('contacts', 'Create a new filter');
        }

        return $this->renderTemplate('contacts/filters/_edit', [
            'filter' => $filter,
            'isNew' => !$filterId,
            'title' => $title,
        ]);
    }

    /**
     * Save filter action
     */
    public function actionSave(): Response
    {
        $this->requirePostRequest();
        
        $request = $this->request;
        $filterId = $request->getBodyParam('filterId');

        if ($filterId) {
            $filter = Contacts::getInstance()->filterService->getFilterById($filterId);
            if (!$filter) {
                throw new NotFoundHttpException('Filter not found');
            }
        } else {
            $filter = new FilterModel();
            $filter->shared = true; // Default to shared for new filters
        }

        // Set attributes from request
        $filter->label = $request->getBodyParam('label');
        $filter->shared = (bool)$request->getBodyParam('shared');
        
        // Handle condition
        $conditionConfig = $request->getBodyParam('condition');

        if ($conditionConfig) {
            $condition = $filter->createCondition();
            $condition->setConditionRules($conditionConfig['conditionRules']);
            $condition->setAttributes($conditionConfig, false);
            $filter->setCondition($condition);
        } else {
            // If no condition config, make sure we still have a condition
            if (!$filter->getCondition()) {
                $filter->setCondition($filter->createCondition());
            }
        }

        // Save the filter
        if (Contacts::getInstance()->filterService->saveFilter($filter)) {
            $this->setSuccessFlash(Craft::t('contacts', 'Filter saved.'));
            return $this->redirectToPostedUrl($filter);
        }

        $this->setFailFlash(Craft::t('contacts', 'Couldn\'t save filter.'));
        
        return $this->renderTemplate('contacts/filters/_edit', [
            'filter' => $filter,
            'isNew' => !$filterId,
            'title' => $filter->label ?: Craft::t('contacts', 'Create a new filter'),
        ]);
    }

    /**
     * Delete filter action
     */
    public function actionDelete(): Response
    {
        $this->requirePostRequest();
        
        $request = Craft::$app->getRequest();
        
        // VueAdminTable can send either 'id' or 'ids' (for bulk delete)
        $filterId = $request->getBodyParam('id');
        $filterIds = $request->getBodyParam('ids');
        
        if ($filterId) {
            // Single delete
            $ids = [$filterId];
        } elseif ($filterIds) {
            // Bulk delete
            $ids = is_array($filterIds) ? $filterIds : [$filterIds];
        } else {
            throw new \yii\web\BadRequestHttpException('No filter ID provided');
        }
        
        $deletedCount = 0;
        $errors = [];
        
        foreach ($ids as $id) {
            $filter = Contacts::getInstance()->filterService->getFilterById($id);
            if (!$filter) {
                $errors[] = "Filter with ID {$id} not found";
                continue;
            }
            
            if (!$filter->canDelete()) {
                $errors[] = "Not permitted to delete filter '{$filter->label}'";
                continue;
            }

            if (Contacts::getInstance()->filterService->deleteFilter($filter)) {
                $deletedCount++;
            } else {
                $errors[] = "Failed to delete filter '{$filter->label}'";
            }
        }

        if ($request->getAcceptsJson()) {
            if (count($errors) > 0) {
                return $this->asJson([
                    'success' => false,
                    'errors' => $errors,
                    'deleted' => $deletedCount
                ]);
            }
            return $this->asJson(['success' => true, 'deleted' => $deletedCount]);
        }
        
        if ($deletedCount > 0) {
            $this->setSuccessFlash(Craft::t('contacts', '{count} filter(s) deleted.', ['count' => $deletedCount]));
        }
        
        if (count($errors) > 0) {
            $this->setFailFlash(implode(', ', $errors));
        }
        
        return $this->redirectToPostedUrl();
    }
}
