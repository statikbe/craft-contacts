<?php

namespace statikbe\contacts\services;

use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
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
     * Export contacts to XLSX format
     *
     * @param Contact[] $contacts Array of contact elements
     * @param string $filename The filename for the export
     * @return string|false The path to the temporary file, or false on failure
     */
    public function exportContactsToXlsx(array $contacts, string $filename = null): string|false
    {
        if (empty($contacts)) {
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
            $headers = ['Email', 'Full Name', 'Date Created', 'Date Updated'];
            foreach ($customFields as $field) {
                $headers[] = $field->name;
            }
            
            $headerRow = Row::fromValues($headers);
            $writer->addRow($headerRow);

            // Add contact data
            foreach ($contacts as $contact) {
                $rowData = [
                    $contact->email ?? '',
                    $contact->fullName ?? '',
                    $contact->dateCreated ? $contact->dateCreated->format('Y-m-d H:i:s') : '',
                    $contact->dateUpdated ? $contact->dateUpdated->format('Y-m-d H:i:s') : '',
                ];

                // Add custom field values
                foreach ($customFields as $field) {
                    $value = $contact->getFieldValue($field->handle);
                    $rowData[] = $this->formatFieldValueForExport($value);
                }

                $dataRow = Row::fromValues($rowData);
                $writer->addRow($dataRow);
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
     * @param Contact[] $contacts Array of contact elements
     * @param string $filename The filename for the download
     * @return bool Success status
     */
    public function exportAndDownload(array $contacts, string $filename = null): bool
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