<?php 

    require_once 'common-functions.php';

    // Was a pet ID provided?
    if( !isset( $_GET['pet'] ) || empty( $_GET['pet'] ) ) showErrorAndDie( 'Missing pet ID' );
    // Yes, so get it
    $petID = $_GET['pet'];

    // Get the pet record
    $sql = 'SELECT name, species, description, image
            FROM pets
            WHERE id=?';

    $pets = getRecords( $sql, 'i', [$petID] );

    // Did we get a record?
    if( count( $pets ) != 1 ) showErrorAndDie( 'Invalid pet ID' );

    // Yes, so get the pet info
    $pet = $pets[0];

    // Now get the notes using the pet id
    $sql = 'SELECT note
            FROM notes
            WHERE pet=?
            ORDER BY id DESC';

    $notes = getRecords( $sql, 'i', [$petID] );


    // ----------------------------------------------------------
    // Send the data to the user

    // Setup the output stream
    $output = prepareDownload( 'petdata', 'txt' );

    // Add the pet info
    fputs( $output, 'Name: '.$pet['name'].PHP_EOL );
    fputs( $output, 'Species: '.$pet['species'].PHP_EOL );
    fputs( $output, 'Description: '.$pet['description'].PHP_EOL );
    
    // And the notes
    fputs( $output, PHP_EOL );
    fputs( $output, 'Notes: '.PHP_EOL );

    foreach( $notes as $note ) {
        fputs( $output, ' --- '.$note['note'].PHP_EOL );
    }

    // And close the stream
    finaliseDownload( $output );

