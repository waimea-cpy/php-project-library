<?php 

    require_once '../lib/db.php';
    require_once '../lib/file.php';
    require_once '../lib/date.php';

    // Get the pet ID from URL
    $petID = $_GET['id'] ?? null;
    if (!$petID) {
        consoleLog($petID, 'Pet ID');
        die('Unknown pet ID');
    }

    // ----------------------------------------------------------
    // Connect to the database
    $db = connectToDB();

    // Get the pet records
    $query = 'SELECT name, species, dob, description
                FROM pets 
                WHERE id=?';

    // Attempt to run the query. We should get a single record
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([$petID]);
        $pet = $stmt->fetch();
    }
    catch (PDOException $e) {
        consoleError($e->getMessage(), 'DB SELECT');
        die('There was an error getting pet from the database');
    }

    // Check we have a record
    if (!$pet) die('No pet found for given ID');

    // ----------------------------------------------------------
    // Now get the notes using the pet id
    $query = 'SELECT note, timestamp
                FROM notes
                WHERE pet=?
                ORDER BY timestamp DESC';

    // Attempt to run the query. We should get an array of records
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([$petID]);
        $notes = $stmt->fetchAll();
    }
    catch (PDOException $e) {
        consoleError($e->getMessage(), 'DB SELECT');
        die('There was an error getting pet notes from the database');
    }


    // ----------------------------------------------------------
    // Send the data to the user

    // Setup the output stream
    $filename = $pet['name'] . ' Info (' . date('Y-m-d H-i-s') . ')';
    $output = prepareDownload( $filename, 'txt' );

    // Add the pet info
    fputs( $output, 'Name: '    . $pet['name']    . PHP_EOL );
    fputs( $output, 'Species: ' . $pet['species'] . PHP_EOL );
    fputs( $output, 'Born: '    . formattedDate($pet['species']) . PHP_EOL );
    fputs( $output, 'Description: '     . PHP_EOL );
    fputs( $output, $pet['description'] . PHP_EOL );
    
    // And the notes
    fputs( $output, PHP_EOL );
    fputs( $output, 'Notes: ' . PHP_EOL );

    foreach( $notes as $note ) {
        fputs( $output, '   - '  . $note['note'] );
        fputs( $output, ' (posted ' . formattedDate($note['timestamp']) . ')' . PHP_EOL );
    }

    // And close the stream
    finaliseDownload( $output );

