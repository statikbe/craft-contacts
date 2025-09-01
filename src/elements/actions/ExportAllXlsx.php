<?php

namespace statikbe\contacts\elements\actions;

use Craft;
use craft\base\ElementAction;

/**
 * ExportAllXlsx represents an Export All to XLSX element action for contacts.
 *
 * @author Statik.be
 * @since 1.0.0
 */
class ExportAllXlsx extends ElementAction
{
    /**
     * @inheritdoc
     */
    public static function isDownload(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('contacts', 'Export All to Excel');
    }

    /**
     * @inheritdoc
     */
    public function getTriggerHtml(): ?string
    {
        Craft::$app->getView()->registerJsWithVars(fn($type) => <<<JS
(() => {
    new Craft.ElementActionTrigger({
        type: $type,
        activate: (selectedItems, elementIndex) => {
            var \$form = Craft.createForm().appendTo(Garnish.\$bod);
            $(Craft.getCsrfInput()).appendTo(\$form);
            $('<input/>', {
                type: 'hidden',
                name: 'action',
                value: 'contacts/contacts/export-all-xlsx'
            }).appendTo(\$form);
            // Pass the current source and search parameters to maintain filters
            if (elementIndex.sourceKey) {
                $('<input/>', {
                    type: 'hidden',
                    name: 'source',
                    value: elementIndex.sourceKey
                }).appendTo(\$form);
            }
            if (elementIndex.searchText) {
                $('<input/>', {
                    type: 'hidden',
                    name: 'search',
                    value: elementIndex.searchText
                }).appendTo(\$form);
            }
            $('<input/>', {
                type: 'submit',
                value: 'Submit',
            }).appendTo(\$form);
            \$form.submit();
            \$form.remove();
        },
    });
})();
JS, [static::class]);

        return null;
    }
}