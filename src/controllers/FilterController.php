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
            
            $data[] = [
                'id' => $filter->id,
                'title' => $filter->label,
                'url' => $editUrl,
                'status' => true, // Always enabled for title column
                'label' => $filter->label,
                'shared' => $filter->shared,
                'canEdit' => $filter->canEdit(),
                'canDelete' => $filter->canDelete(),
                'menu' => $filter->canEdit() ? [
                    'showItems' => true,
                    'menuBtnTitle' => Craft::t('app', 'Actions'),
                    'label' => Craft::t('app', 'Actions'),
                    'items' => [
                        [
                            'label' => Craft::t('app', 'Edit'),
                            'url' => $editUrl
                        ]
                    ]
                ] : null,
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
        $filterId = $request->getRequiredBodyParam('id');
        
        $filter = Contacts::getInstance()->filterService->getFilterById($filterId);
        if (!$filter) {
            throw new NotFoundHttpException('Filter not found');
        }
        
        if (!$filter->canDelete()) {
            throw new ForbiddenHttpException('User not permitted to delete this filter');
        }

        if (Contacts::getInstance()->filterService->deleteFilter($filter)) {
            if ($request->getAcceptsJson()) {
                return $this->asJson(['success' => true]);
            }
            
            $this->setSuccessFlash(Craft::t('contacts', 'Filter deleted.'));
            return $this->redirectToPostedUrl();
        }

        if ($request->getAcceptsJson()) {
            return $this->asJson(['success' => false]);
        }
        
        $this->setFailFlash(Craft::t('contacts', 'Couldn\'t delete filter.'));
        return $this->redirectToPostedUrl();
    }
}
