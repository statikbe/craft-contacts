<?php

namespace statikbe\contacts\services;

use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\db\QueryBatcher;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use statikbe\contacts\elements\Contact;
use statikbe\contacts\Contacts;

/**
 * Export Service
 * 
 * Handles exporting contact data to various formats
 *
 * @author Statik.be
 * @since 1.0.0
 */
class ExportService extends Component
{
    /**
     * Number of contacts to fetch per batch when streaming an export query.
     */
    private const BATCH_SIZE = 100;

    /**
     * Export contacts to XLSX format
     *
     * Accepts either an element query or an array of contacts. When a query is
     * given, contacts are streamed in batches so that exporting a large contact
     * base does not exhaust memory by loading every element at once.
     *
     * @param ElementQueryInterface|Contact[] $contacts A contact element query or array of contact elements
     * @param string|null $filename The filename for the export
     * @return string|false The path to the temporary file, or false on failure
     */
    public function exportContactsToXlsx(ElementQueryInterface|array $contacts, ?string $filename = null): string|false
    {
        if (is_array($contacts) && empty($contacts)) {
            return false;
        }

        // Generate filename if not provided
        if (!$filename) {
            $filename = 'contacts_export_' . date('Y-m-d_H-i-s') . '.xlsx';
        }

        // Create a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'contacts_export_');

        try {
            // Create the XLSX writer
            $writer = new Writer();
            $writer->openToFile($tempFile);

            // Get field layout for users to determine columns
            $fieldLayout = Craft::$app->getFields()->getLayoutByType(User::class);
            $customFields = [];

            if ($fieldLayout) {
                // Get plugin settings to filter by visible tabs
                $settings = Contacts::getInstance()->getSettings();
                $visibleTabUids = $settings->visibleTabs ?? [];

                // Get all tabs and filter by visible ones
                $visibleTabs = collect($fieldLayout->getTabs())->filter(function ($tab) use ($visibleTabUids) {
                    return in_array($tab->uid, $visibleTabUids);
                });

                // Collect fields from visible tabs only
                foreach ($visibleTabs as $tab) {
                    foreach ($tab->getElements() as $element) {
                        if ($element instanceof \craft\fieldlayoutelements\CustomField) {
                            $customFields[] = $element->getField();
                        }
                    }
                }
            }

            // Build header row
            $headers = ['Email', 'First Name', 'Last Name', 'Date Created', 'Date Updated'];
            foreach ($customFields as $field) {
                $headers[] = $field->name;
            }

            $headerRow = Row::fromValues($headers);
            $writer->addRow($headerRow);

            // Write the contact rows. When given a query, stream it in batches so
            // memory usage stays flat regardless of how many contacts there are,
            // rather than materializing the whole result set up front (which is
            // what caused the export to exhaust memory for large contact bases).
            if ($contacts instanceof ElementQueryInterface) {
                // QueryBatcher fetches BATCH_SIZE populated elements at a time via
                // offset/limit, so each query only ever pulls BATCH_SIZE rows into
                // memory. This is deliberately used over Db::each(), which buffers
                // the whole result set client-side unless `useUnbufferedConnections`
                // is enabled (off by default). It relies on the query having an
                // orderBy clause, which the controllers set.
                $batcher = new QueryBatcher($contacts);
                $offset = 0;

                do {
                    $slice = $batcher->getSlice($offset, self::BATCH_SIZE);

                    foreach ($slice as $contact) {
                        $writer->addRow($this->buildContactRow($contact, $customFields));
                    }

                    $offset += self::BATCH_SIZE;

                    // Craft elements hold circular references to their field values,
                    // which PHP's refcounting can't free on its own. Drop the batch
                    // and run the cycle collector so memory doesn't creep up over a
                    // large export.
                    $count = count($slice);
                    unset($slice);
                    gc_collect_cycles();
                } while ($count === self::BATCH_SIZE);
            } else {
                foreach ($contacts as $contact) {
                    $writer->addRow($this->buildContactRow($contact, $customFields));
                }
            }

            $writer->close();

            return $tempFile;

        } catch (\Exception $e) {
            // Clean up the temporary file on error
            @unlink($tempFile);

            Craft::error('Error generating XLSX file: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Build an XLSX row for a single contact.
     *
     * @param Contact $contact The contact element
     * @param \craft\base\FieldInterface[] $customFields The custom fields to include as columns
     * @return Row
     */
    private function buildContactRow(Contact $contact, array $customFields): Row
    {
        $rowData = [
            $contact->email ?? '',
            $contact->firstName ?? '',
            $contact->lastName ?? '',
            $contact->dateCreated ? $contact->dateCreated->format('Y-m-d H:i:s') : '',
            $contact->dateUpdated ? $contact->dateUpdated->format('Y-m-d H:i:s') : '',
        ];

        // Add custom field values
        foreach ($customFields as $field) {
            $value = $contact->getFieldValue($field->handle);
            $rowData[] = $this->formatFieldValueForExport($value);
        }

        return Row::fromValues($rowData);
    }

    /**
     * Format field value for export
     *
     * @param mixed $value The field value
     * @return string The formatted value for export
     */
    private function formatFieldValueForExport(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        // Handle element queries (relations that haven't been executed yet)
        if ($value instanceof \craft\elements\db\ElementQuery) {
            $elements = $value->all();
            if (empty($elements)) {
                return '';
            }
            
            $formattedValues = [];
            foreach ($elements as $element) {
                $formattedValues[] = $element->title ?? $element->__toString();
            }
            return implode(', ', $formattedValues);
        }

        // Handle arrays (e.g., multi-select fields, checkboxes)
        if (is_array($value)) {
            $formattedValues = [];
            foreach ($value as $item) {
                if ($item instanceof ElementInterface) {
                    $formattedValues[] = $item->title ?? $item->__toString();
                } else {
                    $formattedValues[] = (string) $item;
                }
            }
            return implode(', ', $formattedValues);
        }

        // Handle single elements (relations)
        if ($value instanceof ElementInterface) {
            return $value->title ?? $value->__toString();
        }

        // Handle dates
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        // Convert to string
        return (string) $value;
    }

    /**
     * Send XLSX file as download response
     *
     * @param string $filePath Path to the XLSX file
     * @param string $downloadName The name for the downloaded file
     * @return bool Success status
     */
    public function sendXlsxDownload(string $filePath, string $downloadName): bool
    {
        try {
            $response = Craft::$app->getResponse();
            $response->sendFile($filePath, $downloadName, [
                'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'inline' => false,
            ]);

            // Clean up the temporary file
            @unlink($filePath);
            
            return true;
            
        } catch (\Exception $e) {
            // Clean up the temporary file on error
            @unlink($filePath);
            
            Craft::error('Error sending XLSX download: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Export contacts to XLSX and send as download
     *
     * @param ElementQueryInterface|Contact[] $contacts A contact element query or array of contact elements
     * @param string|null $filename The filename for the download
     * @return bool Success status
     */
    public function exportAndDownload(ElementQueryInterface|array $contacts, ?string $filename = null): bool
    {
        // Generate filename if not provided
        if (!$filename) {
            $filename = 'contacts_export_' . date('Y-m-d_H-i-s') . '.xlsx';
        }

        // Export to XLSX
        $tempFile = $this->exportContactsToXlsx($contacts, $filename);
        
        if (!$tempFile) {
            return false;
        }

        // Send as download
        return $this->sendXlsxDownload($tempFile, $filename);
    }
}