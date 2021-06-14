<?php
    include 'common-functions.php';
    include 'common-top.php';

    echo '<h2>Uploading Pet Image...</h2>';

    // Get the uploaded image file
    $image = $_FILES['image'];

    $imagePath = uploadImage( $image, 'images/' );

    showStatus( 'image uploaded successfully', 'success' );

    echo '<h2>Adding Pet to Database...</h2>';

    // Get the data from the form
    $name        = $_POST['name'];
    $species     = $_POST['species'];
    $description = $_POST['description'];

    $sql = 'INSERT INTO pets (name, species, description, image)
            VALUES (?, ?, ?, ?)';

    $petID = modifyRecords( $sql, 'ssss', [$name, $species, $description, $imagePath] );

    showStatus( $name.' the '.$species.' added', 'success' );

    include 'common-bottom.php';
?>
