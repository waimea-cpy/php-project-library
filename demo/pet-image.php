<?php

    require_once '../lib/db.php';

    // ID of image should be in URL
    $id = $_GET['id'] ?? null;

    $db = connectToDB();

    // Get the image type and binary data
    $query = 'SELECT image_type, image_data FROM pets WHERE id=?';

    try {
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        $image = $stmt->fetch();

        // Failed to get an image back?
        if (!$image) throw new Exception();
    }
    catch (Exception $e) {
        // Failed, so 404
        http_response_code(404);
        die();
    }

    // Got here, so all went well. Pass back the image data as a response
    header('Content-type: ' . $image['image_type']);
    echo $image['image_data'];

?>