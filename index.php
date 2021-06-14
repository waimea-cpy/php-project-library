<?php
    include 'common-functions.php';
    include 'common-top.php';

    // Get the pet records
    $sql = 'SELECT id, name, species, description, image
            FROM pets
            ORDER BY name ASC';

    $pets = getRecords( $sql );

    // Are there any?
    if( count( $pets ) > 0 ) {

        $DEBUG = 'WE HAVE '.count( $pets ).' PETS!';

        echo '<section id="pets">';

        // Yes, so loop through them all
        foreach( $pets as $pet ) {
            // Show the data for each one
            echo '<div class="pet">';
            echo   '<figure><img src="'.$pet['image'].'" alt="'.$pet['name'].'"></figure>';
            echo   '<h3>'.$pet['name'].' the '.$pet['species'].'</h3>';
            echo   '<p>'.$pet['description'].'</p>';

            echo   '<h4>Notes:</h4>';

            // Now get the records from the linked table using the pet id
            $id = $pet['id'];

            $sql = 'SELECT note
                    FROM notes
                    WHERE pet=?
                    ORDER BY id DESC';

            $notes = getRecords( $sql, 'i', [$id] );

            // Shpow them all
            echo   '<ul>';
            foreach( $notes as $note ) {
                echo '<li>'.$note['note'];
            }
            echo   '</ul>';

            echo   '<a class="button" href="form-new-note.php?pet='.$id.'">New Note</a>';

            echo '</div>';
        }

        echo '</section>';
    }
    else {
        // No records retuned
        showStatus( 'There are no pets in the database' );
    }

    include 'common-bottom.php';
?>
