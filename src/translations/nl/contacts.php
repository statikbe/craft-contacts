<?php
/**
 * Contacts Translation (Dutch)
 *
 * @author    Statik.be
 * @package   Contacts
 * @since     1.0.0
 */

return [
    // ==============================================
    // PLUGIN CORE & NAVIGATION
    // ==============================================
    
    'Contacts' => 'Contacten',
    'Contact' => 'Contact',
    'contact' => 'contact',
    'contacts' => 'contacten',
    'New Contact' => 'Nieuw Contact',
    'All contacts' => 'Alle contacten',
    
    // ==============================================
    // CONTACT MANAGEMENT
    // ==============================================
    
    // Contact creation and editing
    'Email Address' => 'E-mailadres',
    'Enter the contact\'s email address' => 'Voer het e-mailadres van de contact in',
    'Full Name' => 'Volledige Naam',
    'Enter the contact\'s full name' => 'Voer de volledige naam van de contact in',
    'Create Contact' => 'Contact Aanmaken',
    
    // Contact status and conversion
    'User Status' => 'Gebruikersstatus',
    'Convert to User' => 'Converteren naar Gebruiker',
    'Are you sure you want to convert this contact to a user? They will be assigned to the default user group and sent an activation email with login instructions.' => 'Weet je zeker dat je dit contact wilt converteren naar een gebruiker? Ze worden toegewezen aan de standaard gebruikersgroep en ontvangen een activatie-e-mail met inloggegevens.',
    'Converting...' => 'Converteren...',
    
    // ==============================================
    // SUCCESS MESSAGES
    // ==============================================
    
    'Contact created successfully.' => 'Contact succesvol aangemaakt.',
    'Contact successfully converted. Activation email sent.' => 'Contact succesvol geconverteerd. Activatie-e-mail verzonden.',
    'Contact successfully converted. An activation email has been sent to {email}.' => 'Contact succesvol geconverteerd. Een activatie-e-mail is verzonden naar {email}.',
    'Filter saved.' => 'Filter opgeslagen.',
    '{count} filter(s) deleted.' => '{count} filter(s) verwijderd.',
    '"{name}" deleted.' => '"{name}" verwijderd.',
    
    // ==============================================
    // ERROR MESSAGES
    // ==============================================
    
    // Contact errors
    'Contact not found.' => 'Contact niet gevonden.',
    'Could not create contact.' => 'Kon contact niet aanmaken.',
    'Contact ID is required.' => 'Contact ID is vereist.',
    'This contact is already an active user.' => 'Dit contact is al een actieve gebruiker.',
    'An error occurred while converting the contact.' => 'Er is een fout opgetreden bij het converteren van het contact.',
    'An error occurred while converting the contact: {error}' => 'Er is een fout opgetreden bij het converteren van het contact: {error}',
    
    // Validation errors
    'Email and full name are required.' => 'E-mail en volledige naam zijn vereist.',
    'A contact with this email address already exists.' => 'Een contact met dit e-mailadres bestaat al.',
    'Could not prepare user for activation: {errors}' => 'Kon gebruiker niet voorbereiden voor activatie: {errors}',
    'User was prepared for activation but the activation email could not be sent. Check your email settings.' => 'Gebruiker werd voorbereid voor activatie maar de activatie-e-mail kon niet worden verzonden. Controleer je e-mailinstellingen.',
    
    // Filter errors
    'Couldn\'t save filter.' => 'Kon filter niet opslaan.',
    'Couldn\'t delete "{name}".' => 'Kon "{name}" niet verwijderen.',
    
    // ==============================================
    // FILTERING SYSTEM
    // ==============================================
    
    // Filter sections and navigation
    'Filters' => 'Filters',
    'Contact Filters' => 'Contact Filters',
    'My Filters' => 'Mijn Filters',
    'Shared Filters' => 'Gedeelde Filters',
    'Filter' => 'Filter',
    'New filter' => 'Nieuwe filter',
    'Create a new filter' => 'Maak een nieuwe filter aan',
    
    // Filter form labels
    'Filter Name' => 'Filter Naam',
    'Give your filter a descriptive name' => 'Geef je filter een beschrijvende naam',
    'Shared' => 'Gedeeld',
    'Allow other users to see and use this filter' => 'Sta andere gebruikers toe om deze filter te zien en gebruiken',
    'Filter Conditions' => 'Filter Voorwaarden',
    'Define the conditions that contacts must meet to be included in this filter.' => 'Definieer de voorwaarden waaraan contacten moeten voldoen om in deze filter te worden opgenomen.',
    'Create Filter' => 'Filter Aanmaken',
    'Save Filter' => 'Filter Opslaan',
    
    // Filter states and search
    'Search filters…' => 'Filters zoeken…',
    'No filters exist yet.' => 'Er bestaan nog geen filters.',
    'No filters found.' => 'Geen filters gevonden.',
    'Private' => 'Privé',
    'Owner' => 'Eigenaar',
    'System' => 'Systeem',
    
    // Filter condition summaries
    'No conditions' => 'Geen voorwaarden',
    '1 condition' => '1 voorwaarde',
    '{count} conditions' => '{count} voorwaarden',
    
    // Filter confirmation messages
    'Are you sure you want to delete "{name}"?' => 'Weet je zeker dat je "{name}" wilt verwijderen?',
    
    // ==============================================
    // BULK ACTIONS & EXPORT
    // ==============================================
    
    // Email actions
    'Copy Email' => 'E-mail Kopiëren',
    'Copy Email Address' => 'E-mailadres Kopiëren',
    'Copy Email Addresses ({count})' => 'E-mailadressen Kopiëren ({count})',
    
    // Export actions
    'Export to Excel' => 'Exporteren naar Excel',
    
    // ==============================================
    // PLUGIN SETTINGS
    // ==============================================
    
    // Settings form labels
    'User groups' => 'Gebruikersgroepen',
    'Default user group for new contacts' => 'Standaard gebruikersgroep voor nieuwe contacten',
    'New contacts will be automatically assigned to this user group.' => 'Nieuwe contacten worden automatisch toegewezen aan deze gebruikersgroep.',
    'None' => 'Geen',
    'Tabs' => 'Tabbladen',
    'Select which tabs should be visible in the contact detail view.' => 'Selecteer welke tabbladen zichtbaar moeten zijn in de contactdetailweergave.',
    'Main content template' => 'Hoofdinhoud sjabloon',
    'Sidebar template' => 'Zijbalk sjabloon',
    'Contact title format' => 'Contact titel formaat',
    'This format is used to display the title of the contact\'s detail page.' => 'Dit formaat wordt gebruikt om de titel van de contactdetailpagina weer te geven.',
];