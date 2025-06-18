<?php

namespace statikbe\contacts\elements\actions;

use Craft;
use craft\base\ElementAction;

/**
 * ExportXlsx represents an Export to XLSX element action for contacts.
 *
 * @author Statik.be
 * @since 1.0.0
 */
class ExportXlsx extends ElementAction
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
        return Craft::t('contacts', 'Export to Excel');
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
                value: 'contacts/contacts/export-xlsx'
            }).appendTo(\$form);
            selectedItems.each(function() {
                $('<input/>', {
                    type: 'hidden',
                    name: 'contactId[]',
                    value: $(this).data('id')
                }).appendTo(\$form);
            });
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