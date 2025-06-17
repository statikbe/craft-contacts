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
        $filters = Contacts::getInstance()->filterService->getAllFiltersForUser(true);
        
        return $this->renderTemplate('contacts/filters/_index', [
            'filters' => $filters,
            'title' => Craft::t('contacts', 'Filters'),
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
