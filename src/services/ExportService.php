<?php

namespace statikbe\contacts\services;

use Craft;
use craft\base\Component;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use statikbe\contacts\elements\Contact;

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

            // Add header row
            $headerRow = Row::fromValues(['Email', 'Full Name', 'Date Created', 'Date Updated']);
            $writer->addRow($headerRow);

            // Add contact data
            foreach ($contacts as $contact) {
                $dataRow = Row::fromValues([
                    $contact->email ?? '',
                    $contact->fullName ?? '',
                    $contact->dateCreated ? $contact->dateCreated->format('Y-m-d H:i:s') : '',
                    $contact->dateUpdated ? $contact->dateUpdated->format('Y-m-d H:i:s') : '',
                ]);
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