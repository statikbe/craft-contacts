<?php

namespace statikbe\contacts\elements\actions;

use Craft;
use craft\base\ElementAction;

/**
 * CopyEmail represents a Copy Email element action for contacts.
 *
 * @author Statik.be
 * @since 1.0.0
 */
class CopyEmail extends ElementAction
{
    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('contacts', 'Copy Email');
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
        bulk: true,
        validateSelection: (selectedItems, elementIndex) => {
            // Check if at least one selected element has an email
            const elements = selectedItems.find('.element');
            for (let i = 0; i < elements.length; i++) {
                const email = $(elements[i]).data('email');
                if (email) {
                    return true;
                }
            }
            return false;
        },
        activate: (selectedItems, elementIndex) => {
            const elements = selectedItems.find('.element');
            const emails = [];
            
            // Collect all email addresses from selected elements
            for (let i = 0; i < elements.length; i++) {
                const email = $(elements[i]).data('email');
                if (email && email.trim()) {
                    emails.push(email.trim());
                }
            }
            
            // Remove duplicates and join with commas
            const uniqueEmails = [...new Set(emails)];
            const emailList = uniqueEmails.join(', ');
            
            const label = uniqueEmails.length === 1 
                ? Craft.t('contacts', 'Copy Email Address')
                : Craft.t('contacts', 'Copy Email Addresses ({count})', {count: uniqueEmails.length});
            
            Craft.ui.createCopyTextPrompt({
                label: label,
                value: emailList,
            });
        },
    });
})();
JS, [static::class]);

        return null;
    }
}