<?php
    require_once '../lib/db.php';

    consoleLog($_POST, 'POST Data');
    consoleLog($_FILES, 'FILES Data');

    if(empty($_POST) && empty($_FILES)) {
        consoleError('Image upload problem');
        die ('There was a problem uploading the file (probably too large)');
    }

    // Get image data and type of uploaded file
    [
        'data' => $imageData,
        'type' => $imageType
    ] = uploadedImageData($_FILES['image']);

    // Get the data from the form
    $name        = $_POST['name'];
    $species     = $_POST['species'];
    $dob         = $_POST['dob'];
    $description = $_POST['description'];

    // Connect to the database
    $db = connectToDB();

    // Setup and run the query to add the pet
    $query = 'INSERT INTO pets (name, species, dob, description, image_type, image_data) 
                VALUES (?, ?, ?, ?, ?, ?)';

    // Attempt to run the query
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([$name, $species, $dob, $description, $imageType, $imageData]);
    }
    catch (PDOException $e) {
        consoleError($e->getMessage(), 'DB INSERT');
        die('There was an error adding pet to the database');
    }

    // If we got here, it worked
    header('location: index.php');
?>
