<?php
/*=============================================================
 * Waimea College Standard PHP Library 
 * Version: 1.0 (June 2021)
 * 
 * Functions to:
 *   - Displaying debug info in a small panel
 *   - Connect to MySQL server databases
 *   - Run queries to obtain or modify data in a MySQL DB
 *   - Upload files / images to the server
 *=============================================================*/


/*-------------------------------------------------------------
 * Display debug info at bottom right of window (shows on hover)
 * for the standard PHP arrays: GET / POST / FILE / SESSION, as
 * well as the contents of a global $DEBUG variable which can
 * be set to any value when debugging code.
 *-------------------------------------------------------------*/
function showDebugInfo() {
    global $DEBUG;

    $debugInfo = '<div style="background: rgba(0,0,0,0.8); color: #fff; font-size: 18px; line-height: 1rem; position: fixed; right: 0; bottom: 1rem; padding: 0.5rem 1rem 0.5rem 0.25rem; width: 1rem; max-width: 95vw; max-height: 90vh; border-radius: 0.5rem 0 0 0.5rem; display: flex; gap: 1rem; overflow-x: hidden; overflow-y: auto; z-index: 999; cursor: pointer;"
                       onclick="this.style.width= this.style.width==\'auto\' ? \'1rem\' : \'auto\';">';

    $debugInfo .= '<div style="writing-mode: sideways-lr; text-align: center; color: #ff0;">DEBUG INFO</div>';
    $debugInfo .= '<pre style="margin: 0; font-size: 0.8rem; line-height: 0.8rem; text-align: left; ">';

    if( isset( $DEBUG )                               ) $debugInfo .=   'DEBUG: '.print_r( $DEBUG,    True );
    if( isset( $_POST )    && sizeof( $_POST )    > 0 ) $debugInfo .=    'POST: '.print_r( $_POST,    True );
    if( isset( $_GET )     && sizeof( $_GET )     > 0 ) $debugInfo .=     'GET: '.print_r( $_GET,     True );
    if( isset( $_FILES )   && sizeof( $_FILES )   > 0 ) $debugInfo .=   'FILES: '.print_r( $_FILES,   True );
    if( isset( $_SESSION ) && sizeof( $_SESSION ) > 0 ) $debugInfo .= 'SESSION: '.print_r( $_SESSION, True );

    $debugInfo .= '</pre></div>';

    echo $debugInfo;
}


/*-------------------------------------------------------------
 * Connect to MySQL database
 *
 * Requires: The username, password and database details in 
 *           text files withon the same directory
 *
 * Returns: the mysqli database connection object
 *-------------------------------------------------------------*/
function connectToDB() {

    $user = file_get_contents( '.username.txt' ); // DB Username
    $pass = file_get_contents( '.password.txt' ); // DB Password
    $db   = file_get_contents( '.database.txt' ); // Database to connect to

    return new mysqli( 'localhost', $user, $pass, $db );       
}


/*-------------------------------------------------------------
 * Show a suitably formatted status message
 *
 * Argument: $message - text of message to display
 *           $type    - an optional message type (e.g. 'success')
 *                      This prefixes the message and is also
 *                      added to the message as a class for 
 *                      styling purposes
 *-------------------------------------------------------------*/
function showStatus( $message, $type=null ) {
    $fullMessage = '';
    if( $type ) $fullMessage = ucfirst( $type ).': ';
    $fullMessage .= $message;
    echo '<p class="status '.$type.'">'.$fullMessage.'</p>';
}


/*-------------------------------------------------------------
 * Show an error as script exits
 *
 * Argument: $error - text of error to display
 *-------------------------------------------------------------*/
function showErrorAndDie( $error ) {
    showStatus( $error, 'error' );
    include 'common-bottom.php';
    die();
}


/*-------------------------------------------------------------
 * Runs a given SELECT query on the MySQL database
 * 
 * Arguments: $sql - an SQL query string
 *            $format - an optional format string (e.g. 'ssii')
 *            $params - an optional array of data parameters to
 *                      bind into the query, matching the 
 *                      format string above
 *
 * Returns: the an array of records
 *
 * Note: this potentially could lead to out-of-memory issues 
 *       with large data sets, but for low-use, small, 
 *       text-based data sets, it should be fine
 *-------------------------------------------------------------*/
function getRecords( $sql, $format=null, $params=null ) {

    // Connect to the DB
    $link = connectToDB();
    if( $link->connect_error ) showErrorAndDie( 'connecting to the database: '.$link->connect_error );

    // Setup the DB query to gather summative data
    $query = $link->prepare( $sql );
    if( !$query ) showErrorAndDie( 'preparing database query: '.$link->error );

    // do we have data and a format for the prepared statement?
    if( $format && $params && strlen( $format ) == count( $params ) ) {
        // Yes, so add in the data to the query
        $query->bind_param( $format, ...$params );
    }

    // RUn the query
    $query->execute();
    if( $query->error ) showErrorAndDie( 'running the database query: '.$query->error );

    // Get the result set
    $result = $query->get_result();

    // Push data records into an array, one by one
    $records = array();
    while( $record = $result->fetch_assoc() ) {
        $records[] = $record;
    }

    // Tidy up afterwards
    $result->close();
    $query->close();
    $link->close();

    // Pass back the array of records
    return $records;
}


/*-------------------------------------------------------------
 * Runs a given INSERT / UPDATE query on the MySQL database
 *
 * Arguments: $sql - an SQL query string
 *            $format - an optional format string (e.g. 'ssii')
 *            $params - an optional array of data parameters to
 *                      bind into the query, matching the 
 *                      format string above
 *
 * Returns: the new ID if an INSERT query, otherwise null
 *-------------------------------------------------------------*/
function modifyRecords( $sql, $format=null, $params=null ) {

    // Connect to the DB
    $link = connectToDB();
    if( $link->connect_error ) showErrorAndDie( 'connecting to the database: '.$link->connect_error );

    // Setup the DB query to gather summative data
    $query = $link->prepare( $sql );
    if( !$query ) showErrorAndDie( 'preparing database query: '.$link->error );

    // Do we have data to bind into the prepared statement?
    if( !$format || !$params || strlen( $format ) != count( $params ) ) showErrorAndDie( 'mismatched data parameters' );
    
    // Yes, so add in the data to the query
    $query->bind_param( $format, ...$params );

    // Run the query
    $query->execute();
    if( $query->error ) showErrorAndDie( 'running the database query: '.$query->error );

    // Get the new ID of any INSERT query with auto-inc. key (will be 0 otherwise)
    $newID = $link->insert_id;

    // Tidy up afterwards
    $query->close();
    $link->close();

    // Return the ID of nay INSERTed record
    return $newID;
}


/*-------------------------------------------------------------
 * Uploads a given file object to the server. The file object
 * is created by a multi-part form with a file upload field
 *
 * If required, a random 32 character filename is generated 
 * for the file to avoid file collisions, and the original 
 * extension is added to this.
 *
 * Arguments: $file   - a file object from a multi-part form
 *            $folder - the folder name for the upload. Note
 *                      this must have appropriate permissions
 *                      and should end in a trailing /
 *            $random - an optional flag (default: false) to 
 *                      indicate if a random filename is wanted
 *
 * Returns: the full path of the uploaded file
 *-------------------------------------------------------------*/
function uploadFile( $file, $folder, $random=false ) {

    // Seperate out the file info
    $filename     = $file['name'];
    $fileError    = $file['error'];
    $fileType     = $file['type'];
    $fileTempName = $file['tmp_name'];
    $fileSize     = $file['size'];

    // Check image file size is not too large (2MB max on server)
    if( $fileError == 1 || $fileSize > 2000000 ) showErrorAndDie( 'the file is too large (2MB max)' );

    if( $random ) {
        // Build the path to save the file to
        $filenameParts = explode( '.', $filename );                  // Break apart the image filename
        $fileExtension = $filenameParts[sizeof($filenameParts) - 1]; // To get the image file extension

        $targetFilename = md5( $filename.rand( 1, 100000 ) );        // Generate a random 32 char filename
        $targetFilename = $targetFilename.'.'.$fileExtension;        // Add on file extension
        $targetFilename = strtolower( $targetFilename );             // Force to lowercase
    }
    else {
        $targetFilename = strtolower( $filename );
    }

    $targetFilePath = $folder.$targetFilename;  // Piece together the path

    // Check if the file is already on server (possible if not a random filename)
    if( file_exists( $targetFilePath ) ) showErrorAndDie( 'a file with that name already exists' );

    // Attempt to save the file to the upload folder
    $uploadSuccess = move_uploaded_file( $fileTempName, $targetFilePath );
    if( !$uploadSuccess ) showErrorAndDie( 'problem uploading file' );

    // Return the full path of the uploaded file
    return $targetFilePath;
}


/*-------------------------------------------------------------
 * Uploads a given image object to the server. The image object
 * is created by a multi-part form with a file upload field
 * 
 * See the uploadFile() function notes for other info
 *
 * Compared to a normal file upload, more image-specific
 * validation is done
 *
 * Arguments: $image  - a image object from a multi-part form
 *            $folder - the folder name for the upload
 *            $random - random filename flag (default: true)
 *
 * Returns: the full path of the uploaded file
 *-------------------------------------------------------------*/
function uploadImage( $image, $folder, $random=true ) {

    // Seperate out the image info
    $imageFilename = $image['name'];
    $imageError    = $image['error'];
    $imageType     = $image['type'];
    $imageTempName = $image['tmp_name'];
    $imageSize     = $image['size'];

    // Check image file size is not too large (2MB max on server)
    if( $imageError == 1 || $imageSize > 2000000 ) showErrorAndDie( 'the image file is too large (2MB max)' );

    // Check if image is an actual image
    $validImage = getimagesize( $imageTempName );
    if( !$validImage ) showErrorAndDie( 'the file does not contain image data' );

    // Check the image is of a suitable type
    if( $imageType != 'image/png' &&
        $imageType != 'image/jpeg' &&
        $imageType != 'image/gif' &&
        $imageType != 'image/webp' ) showErrorAndDie( 'only JPEG, JFIF, WEBP, PNG or GIF images are allowed' );

    return uploadFile( $image, $folder, $random );
}


?>