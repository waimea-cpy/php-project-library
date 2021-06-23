<?php
    include 'common-functions.php';
    include 'common-top.php';

    echo '<h2>Uploading Pet Image...</h2>';

    // Get the uploaded image file
    $image = $_FILES['image'];
    // And up load the image, getting the file path
    $imagePath = uploadImage( $image, 'images/' );
    // If we got here, it all went well
    showStatus( 'image uploaded successfully', 'success' );

    echo '<h2>Adding Pet to Database...</h2>';

    // Get the data from the form
    $name        = $_POST['name'];
    $species     = $_POST['species'];
    $description = $_POST['description'];

    // Setup and run the query to add the pet
    $sql = 'INSERT INTO pets (name, species, description, image) VALUES (?, ?, ?, ?)';
    modifyRecords( $sql, 'ssss', [$name, $species, $description, $imagePath] );
    // If we got here, it worked
    showStatus( $name.' the '.$species.' added', 'success' );
    addRedirect( 2000, 'index.php' );

    include 'common-bottom.php';
?>
