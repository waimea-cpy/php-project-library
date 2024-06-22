<?php
    require_once '../lib/db.php';

    consoleLog($_POST, 'POST Data');

    // Get the data from the form
    $petID = $_POST['id'];
    $note  = $_POST['note'];

    // Connect to the database
    $db = connectToDB();

    // Setup and run the query to add the note
    $query = 'INSERT INTO notes (pet, note) VALUES (?, ?)';

    // Attempt to run the query. We should get an array of records
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([$petID, $note]);
    }
    catch (PDOException $e) {
        consoleError($e->getMessage(), 'DB INSERT');
        die('There was an error adding note to the database');
    }

    // If we got here, it worked, so go back to pet page
    header('location: show-pet.php?id=' . $petID);
?>
