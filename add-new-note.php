<?php
    include 'common-functions.php';
    include 'common-top.php';

    echo '<h2>Adding Note...</h2>';

    // Get the data from the form
    $pet  = $_POST['pet'];
    $note = $_POST['note'];

    $sql = 'INSERT INTO notes (pet, note)
            VALUES (?, ?)';

    $petID = modifyRecords( $sql, 'is', [$pet, $note] );

    showStatus( 'note added', 'success' );

    include 'common-bottom.php';
?>
