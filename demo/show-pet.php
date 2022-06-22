<?php
    include 'common-functions.php';
    include 'common-top.php';

    // Was a pet ID provided?
    if( !isset( $_GET['id'] ) || empty( $_GET['id'] ) ) showErrorAndDie( 'missing pet ID' );
    // Yes, so get it
    $petID = $_GET['id'];

    // Get the pet record
    $sql = 'SELECT name, species, description, image
            FROM pets
            WHERE id=?';

    $pets = getRecords( $sql, 'i', [$petID] );

    // Did we get a record?
    if( count( $pets ) > 0 ) {

        // Yes, so show the pet
        $pet = $pets[0];

        echo '<section id="pets">';

        echo '<div class="pet">';

        echo   '<header>';
        echo     '<figure><img src="'.$pet['image'].'" alt="'.$pet['name'].'"></figure>';
        echo     '<h3>'.$pet['name'].' the '.$pet['species'].'</h3>';
        echo   '</header>';

        echo   '<div class="details">';
        echo     '<p>'.$pet['description'].'</p>';

        echo     '<h4>Notes:</h4>';

        // Now get the records from the linked table using the pet id
        $sql = 'SELECT note
                FROM notes
                WHERE pet=?
                ORDER BY id DESC';

        $notes = getRecords( $sql, 'i', [$petID] );

        // Show them all
        echo     '<ul>';
        foreach( $notes as $note ) {
            echo '<li>'.$note['note'];
        }
        echo     '</ul>';

        echo   '</div>';

        echo   '<footer>';
        echo     '<a class="button" href="download-pet.php?pet='.$petID.'">Download</a>';
        echo     '<a class="button" href="form-new-note.php?pet='.$petID.'">New Note</a>';
        echo   '</footer>';

        echo '</div>';
        echo '</section>';
    }
    else {
        // No records retuned
        showStatus( 'No pet with the given ID could be found', 'error' );
    }

    include 'common-bottom.php';
?>
