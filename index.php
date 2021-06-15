<?php
    include 'common-functions.php';
    include 'common-top.php';

    // Get the pet records
    $sql = 'SELECT id, name, species, description, image
            FROM pets
            ORDER BY name ASC';

    // We should get an array of records
    $pets = getRecords( $sql );

    // Are there any pet records?
    if( count( $pets ) > 0 ) {

        echo '<section id="pets">';

        // Yes, so loop through them all
        foreach( $pets as $pet ) {

            // Show the data for each one
            echo '<a class="pet" href="show-pet.php?id='.$pet['id'].'">';
            echo   '<header>';
            echo     '<figure><img src="'.$pet['image'].'" alt="'.$pet['name'].'"></figure>';
            echo     '<h3>'.$pet['name'].' the '.$pet['species'].'</h3>';
            echo   '</header>';

            echo   '<div class="details">';
            echo     '<p>'.$pet['description'].'</p>';
            echo   '</div>';
            echo '</a>';
        }

        echo '</section>';

        $DEBUG = 'WE HAVE '.count( $pets ).' PETS!';
    }
    else {
        // No records retuned
        showStatus( 'There are no pets in the database' );
    }

    include 'common-bottom.php';
?>
