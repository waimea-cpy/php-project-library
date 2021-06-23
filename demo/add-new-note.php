<?php
    include 'common-functions.php';
    include 'common-top.php';

    echo '<h2>Adding Note...</h2>';

    // Get the data from the form
    $pet  = $_POST['pet'];
    $note = $_POST['note'];

    // Setup and run the query to add the pet
    $sql = 'INSERT INTO notes (pet, note) VALUES (?, ?)';
    modifyRecords( $sql, 'is', [$pet, $note] );
    // If we got here, it worked
    showStatus( 'note added', 'success' );
    addRedirect( 2000, 'index.php' );

    include 'common-bottom.php';
?>
