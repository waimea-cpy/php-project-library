<?php
    include 'common-functions.php';
    include 'common-top.php';

    echo '<h2>Search Results</h2>';

    // Was a search term provided?
    if( !isset( $_GET['search'] ) || empty( $_GET['search'] ) ) showErrorAndDie( 'missing search term' );
    // Yes, so get it
    $search = $_GET['search'];

    echo '<p>Searching for '.$search.'...';

    // Add in wildcards
    $search = '%'.$search.'%';

    // Get the pet records
    $sql = 'SELECT id, name, species, description, image
            FROM pets
            WHERE name LIKE ? OR species LIKE ? OR description LIKE ?
            ORDER BY name ASC';

    $pets = getRecords( $sql, 'sss', [$search, $search, $search] );

    // Are there any?
    if( count( $pets ) > 0 ) {

        echo '<section id="pets">';

        // Yes, so loop through them all
        foreach( $pets as $pet ) {
            // Show the data for each one
            echo '<div class="pet">';
            echo   '<figure><img src="'.$pet['image'].'" alt="'.$pet['name'].'"></figure>';
            echo   '<h3>'.$pet['name'].' the '.$pet['species'].'</h3>';
            echo   '<p>'.$pet['description'].'</p>';
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
