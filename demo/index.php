<?php
    require_once '../lib/db.php';
    require_once '../lib/text.php';
    require_once '../lib/date.php';
    
    include 'partials/top.php';

    // Connect to the database
    $db = connectToDB();

    // Get the pet records
    $query = 'SELECT id, name, species, dob, description
                FROM pets
                ORDER BY name ASC';

    // Attempt to run the query. We should get an array of records
    try {
        $stmt = $db->prepare($query);
        $stmt->execute();
        $pets = $stmt->fetchAll();
    }
    catch (PDOException $e) {
        consoleError($e->getMessage(), 'DB SELECT');
        die('There was an error getting pets from the database');
    }

    // Are there any pet records?
    if (count($pets) > 0) {

        echo '<section id="pet-list">';

        // Yes, so loop through them all
        foreach ($pets as $pet) {

            // Show the data for each one
            echo '<article>';
            echo   '<figure><img src="pet-image.php?id='.$pet['id'].'" alt="'.$pet['name'].'"></figure>';
            echo   '<h3>'.$pet['name'].' the '.$pet['species'].'</h3>';
            echo   '<p>Born: <strong>'.formattedDate($pet['dob']).'</strong> ('.ageInYears($pet['dob']).')</p>';
            echo   '<div class="clip">';
            echo     text2paras($pet['description']);
            echo   '</div>';
            echo   '<a href="show-pet.php?id='.$pet['id'].'"><button>Find Out More...</button></a>';
            echo '</article>';
        }

        echo '</section>';
    }
    else {
        // No records retuned
        echo '<p>There are no pets in the database.</p>';
    }

    include 'partials/bottom.php';
?>
