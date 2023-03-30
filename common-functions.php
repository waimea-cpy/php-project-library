<?php
/*=============================================================
 *
 * Waimea College Standard PHP Library
 * Steve Copley
 * Digital Technologies Dept.
 *
 * Version: 2.8 (March 2023)
 *
 * Functions to:
 *
 *   - Connect to MySQL server databases:
 *        connectToDB()
 *
 *   - Run queries to obtain or modify data in a MySQL DB
 *        getRecords()
 *        modifyRecords()
 *
 *   - Handle errors with the MySQL operations gracefully:
 *        checkQueryAndData()
 *
 *   - Upload files / images to the server:
 *        uploadFile()
 *        uploadImage()
 *
 *   - Check for a valid URL
 *        urlExists()
 *
 *   - Configure file download output streams:
 *        prepareDownload()
 *        finaliseDownload()
 *
 *   - Convert plain text to HTML paragraphs:
 *        text2paras()
 *
 *   - Format and manage dates and times in various ways:
 *        niceDate()
 *        niceTime()
 *        daysFromToday()
 *        isToday()
 *        isInPast()
 *
 *   - Show status messages:
 *        showStatus()
 *        showErrorAndDie()
 *
 *   - Add a JS delayed redirect (e.g. after a status message):
 *        addRedirect()
 *
 *   - Display debug info for $_SESSION, $_GET, $_POST, etc.:
 *        showDebugInfo()
 *
 *-------------------------------------------------------------
 * History:
 *
 *  2.8 (2023-03-30) - Function to check if a given date is in the past
 *                   - Function to check is a given date is today
 *  2.7 (2023-03-01) - Functions to convert dates to user-friendly formats
 *                   - Function to convert text with line-breaks to HTML paras
 *                   - Fixed tiny layout bug with showInfo panel
 *  2.6 (2022-08-10) - More robust error checking / feedback for get/update
 *                     records. Plus can now just pass a single data variable
 *                     without needing to place it into an array
 *  2.5 (2022-06-22) - Added functions to support file downloads
 *  2.4 (2022-06-20) - Image uploading now allows SVGs
 *  2.3 (2022-03-21) - Added a function to check if a given URL is valid
 *  2.2 (2022-03-15) - Added check for folder trailing slash in file upload
 *  2.1 (2022-03-03) - Added session name to session info display
 *  2.0 (2022-02-16) - Code cleanup, new DB config file format, more defaults
 *
 *  1.7 (2021-08-23) - Fixed some CSS bugs in the debug panel
 *  1.6 (2021-07-06) - Fixed a bug in the modifyRecords function
 *  1.5 (2021-07-28) - Fixed a bug in the redirect function for GET URLS
 *  1.4 (2021-06-23) - Moved demo to its own folder
 *                   - Fixed bug with DEBUG arrays / strings
 *  1.3 (2021-06-15) - Fixed a debug tag issue with vertical text in Chrome
 *                   - Tweaked debug info funt and newlines in output
 *                   - Added default to image upload for non-random names
 *                   - Simplified theme for demo. Added pets details
 *  1.2 (2021-06-15) - Added redirect function and staus message styling
 *  1.1 (2021-06-14) - Added a basic search example
 *  1.0 (2021-06-14) - Initial version
 *=============================================================*/


/*-------------------------------------------------------------
 * Connect to MySQL database
 *
 * Requires: The host, username, password and database details in
 *           a config .ini file with the following fields...
 *             host="_______"  (the db host, e.g. localhost)
 *             user="_______"  (the MySQL username)
 *             pass="_______"  (the MySQL password)
 *             name="_______"  (the database to connect to)
 *
 * Argument: $iniFile - filename of the config .ini file
 *                      defaults to .db.ini within same directory
 *
 * Returns: the mysqli database connection object
 *-------------------------------------------------------------*/
function connectToDB( $iniFile='.db.ini' ) {

    $config = parse_ini_file( $iniFile, true );  // Load config values from file

    return new mysqli( $config['host'],
                       $config['user'],
                       $config['pass'],
                       $config['name'] );
}


/*-------------------------------------------------------------
 * Runs a given SELECT query on the MySQL database
 *
 * Arguments: $sql - an SQL query string
 *            $format - an optional format string (e.g. 'ssii')
 *            $params - an optional data value or  array of data
 *                      to bind into the query, matching the
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
    if( $link->connect_error ) showErrorAndDie( 'Connecting to the database: '.$link->connect_error );

    // Setup the DB query to gather summative data
    $query = $link->prepare( $sql );
    if( !$query ) showErrorAndDie( 'Preparing database query: '.$link->error );

    // Check SQL, format and data all match up
    checkQueryAndData( $sql, $format, $params );

    // Do we have data and a format for the prepared statement?
    if( $format && $params ) {
        // Have we got an array of data? If so, decompose the array and bind in
        if( is_array( $params ) ) $query->bind_param( $format, ...$params );
        // Otherwise just bind in the single data value
        else $query->bind_param( $format, $params );
    }

    // Run the query
    $query->execute();
    if( $query->error ) showErrorAndDie( 'Running the database query: '.$query->error );

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
 *            $params - an optional data value or  array of data
 *                      to bind into the query, matching the
 *                      format string above
 *
 * Returns: the new ID if an INSERT query, otherwise null
 *-------------------------------------------------------------*/
function modifyRecords( $sql, $format=null, $params=null ) {

    // Connect to the DB
    $link = connectToDB();
    if( $link->connect_error ) showErrorAndDie( 'Connecting to the database: '.$link->connect_error );

    // Setup the DB query to gather summative data
    $query = $link->prepare( $sql );
    if( !$query ) showErrorAndDie( 'Preparing database query: '.$link->error );

    // Check SQL, format and data all match up
    checkQueryAndData( $sql, $format, $params );

    // Do we have data and a format for the prepared statement?
    if( $format && $params ) {
        // Have we got an array of data? If so, decompose the array and bind in
        if( is_array( $params ) ) $query->bind_param( $format, ...$params );
        // Otherwise just bind in the single data value
        else $query->bind_param( $format, $params );
    }

    // Run the query
    $query->execute();
    if( $query->error ) showErrorAndDie( 'Running the database query: '.$query->error );

    // Get the new ID of any INSERT query with auto-inc. key (will be 0 otherwise)
    $newID = $link->insert_id;

    // Tidy up afterwards
    $query->close();
    $link->close();

    // Return the ID of nay INSERTed record
    return $newID;
}


/*-------------------------------------------------------------
 * Validates an SQL string, data format string and data value(s)
 * quitting if a disceprency is found between them
 *
 * Arguments: $sql - an SQL query string
 *            $format - a format string (e.g. 'ssii')
 *            $params - a data value or array of data values
 *-------------------------------------------------------------*/
function checkQueryAndData( $sql, $format, $params ) {
    // Find number of data markers (?) in $sql
    $markerCount = substr_count( $sql, '?' );
    // Find length of data types, if present
    $formatCount = $format ? strlen( $format ) : 0;
    // Find number of data items, if present
    $dataCount = $params ? (is_array( $params ) ? count( $params ) : 1) : 0;

    // Check if everything matches up
    if( $markerCount != $formatCount || $markerCount != $dataCount ) showErrorAndDie( <<<EOD
        Mismatch between number of data markers in SQL ('?' count: $markerCount),
        length of format string (length of '$format': $formatCount),
        and number of data values provided (data values: $dataCount)
        EOD
    );
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
    if( $fileError == 1 || $fileSize > 2000000 ) showErrorAndDie( 'The file is too large (2MB max)' );

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

    // Check if folder has trailing slash and add one if not
    if( strcmp( $folder[-1], '/' ) !== 0 ) $folder .= '/';

    // Piece together the final save path
    $targetFilePath = $folder.$targetFilename;

    // Check if the file is already on server (possible if not a random filename)
    if( file_exists( $targetFilePath ) ) showErrorAndDie( 'A file with that name already exists' );

    // Attempt to save the file to the upload folder
    $uploadSuccess = move_uploaded_file( $fileTempName, $targetFilePath );
    if( !$uploadSuccess ) showErrorAndDie( 'Problem uploading file' );

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
 *            $random - random filename flag (default: false)
 *
 * Returns: the full path of the uploaded file
 *-------------------------------------------------------------*/
function uploadImage( $image, $folder, $random=false ) {

    // Seperate out the image info
    $imageFilename = $image['name'];
    $imageError    = $image['error'];
    $imageType     = $image['type'];
    $imageTempName = $image['tmp_name'];
    $imageSize     = $image['size'];

    // Check image file size is not too large (2MB max on server)
    if( $imageError == 1 || $imageSize > 2000000 ) showErrorAndDie( 'The image file is too large (2MB max)' );

    // Check if image is an actual image (excluding SVG which are text files)
    if( $imageType != 'image/svg+xml' ) {
        $validImage = getimagesize( $imageTempName );
        if( !$validImage ) showErrorAndDie( 'The file does not contain image data' );
    }

    // Check the image is of a suitable type
    if( $imageType != 'image/svg+xml' &&
        $imageType != 'image/png' &&
        $imageType != 'image/jpeg' &&
        $imageType != 'image/gif' &&
        $imageType != 'image/webp' ) showErrorAndDie( 'Only JPEG, JFIF, WEBP, PNG, GIF and SVG images are allowed' );

    return uploadFile( $image, $folder, $random );
}


/*-------------------------------------------------------------
 * Check if a URL exists or not
 *
 * Requires: The host, username, password and database details in
 *           the same config .ini file used by connectToDB
 *
 * Argument: $url      - The URL of the file to check
 *           $relative - true if URL is relative to current script
 *           $auth     - true if Basic Auth via user/pass required
 *           $iniFile  - filename of the config .ini file
 *                       defaults to .db.ini within same directory
 *
 * Returns: true if URL exists, false otherwise
 *-------------------------------------------------------------*/
function urlExists( $url, $relative=true, $auth=true, $iniFile='.db.ini' ) {

    $config = parse_ini_file( $iniFile, true );  // Load config values from file

    // Setup the access context with authentication if required
    stream_context_set_default( array(
        'http' => array(
            'method' => 'GET',
            'header' => $auth ? 'Authorization: Basic '.base64_encode( $config['user'].':'.$config['pass'] ) : ''
        )
    ) );

    if( $relative ) {
        // Get HTTP(S)
        $protocol = ((!empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
        // Work out URL base path (no filename)
        $path = $protocol.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
        // Add it to the front of the URL
        $url = $path.'/'.$url;
    }

    // Attempt to access URL
    $headers = @get_headers( $url );

    // Nothing back?
    if( $headers == false ) return false;

    // Check status code
    $status = substr( $headers[0], 9, 3 );

    // 200-399 is good
    return $status >= 200 && $status < 400;
}


/*-------------------------------------------------------------
 * Setup an output stream to write to for a file download
 *
 * Argument: $filename - The download filename, no extension
 *           $type     - The download file type, text-based
 *                       txt  - plain text
 *                       csv  - CSV data
 *                       json - JSON data
 *
 * Note: regardless of the type, you still have to output the
 *       actual data in the appropriate format using fputs(),
 *       fputcsv(), json_encode(), etc.
 *
 * Returns: the output stream file handle
 *-------------------------------------------------------------*/
function prepareDownload( $filename='data', $type='txt' ) {
    $type = strtolower( $type );

        if( $type == 'txt'  ) $mimetype = 'text/plain';
    elseif( $type == 'csv'  ) $mimetype = 'text/csv';
    elseif( $type == 'json' ) $mimetype = 'application/json';
    else showErrorAndDie( 'Invalid data type' );

    header( 'Content-Type: '.$mimetype.'; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename='.$filename.'.'.$type );
    header( 'Pragma: no-cache' );
    header( 'Expires: 0' );

    $handle = fopen( 'php://output', 'w' );

    return $handle;
}


/*-------------------------------------------------------------
 * Close an output stream for a file download
 *
 * Argument: $handle - The output stream handle
 *-------------------------------------------------------------*/
function finaliseDownload( $handle ) {
    fclose( $handle );
}



/*-------------------------------------------------------------
 * Convert any newlines in some given text to HTML <p>...</p>
 *
 * Argument: $text - the text to convert
 *
 * Returns: a string full of HTML paragraphs, if any were found
 *          a blank string otherwise
 *-------------------------------------------------------------*/
function text2paras( $text ) {
    $paragraphs = explode( "\n", $text );

    $paragraphsHTML = '';

    foreach( $paragraphs as $para ) {
        $trimmed = trim( $para );

        if( !empty( $trimmed ) ) {
            $paragraphsHTML .= '<p>'.$trimmed.'</p>';
        }
    }

    return $paragraphsHTML;
}



/*-------------------------------------------------------------
 * Convert a given timestamp in standard PHP/MySQL date/time
 * format (YYYY-MM-DD HH:MM:SS) into a nicely formated date
 *
 * Arguments: $timestamp - string containing timestamp
 *            $format - date format, defaults to D MMM YYYY
 *
 * Returns: date string, using the given format
 *-------------------------------------------------------------*/
function niceDate( $timestamp, $format='j M Y' ) {
    $date = new DateTime( $timestamp );
    return $date->format( $format );
}


/*-------------------------------------------------------------
 * Convert a given timestamp in standard PHP/MySQL date/time
 * format (YYYY-MM-DD HH:MM:SS) into a nicely formated time
 *
 * Arguments: $timestamp - string containing timestamp
 *            $format - time format, defaults to H:MMam/pm
 *
 * Returns: date string, using the given format
 *-------------------------------------------------------------*/
function niceTime( $timestamp, $format='h:ia' ) {
    $date = new DateTime( $timestamp );
    return $date->format( $format );
}


/*-------------------------------------------------------------
 * Check if a given timestamp in standard PHP/MySQL date/time
 * format (YYYY-MM-DD HH:MM:SS) is today
 *
 * Arguments: $timestamp - string containing timestamp
 *
 * Returns: true if is today, false otherwise
 *-------------------------------------------------------------*/
function isToday( $timestamp ) {
    $date = new DateTime( $timestamp );
    $today = new DateTime( 'today' );
    $diff = $today->diff( $date );

    return ($diff->days == 0);
}


/*-------------------------------------------------------------
 * Check if a given timestamp in standard PHP/MySQL date/time
 * format (YYYY-MM-DD HH:MM:SS) is in the past
 *
 * Arguments: $timestamp - string containing timestamp
 *
 * Returns: true if in past, false otherwise
 *-------------------------------------------------------------*/
function isInPast( $timestamp ) {
    $date = new DateTime( $timestamp );
    $today = new DateTime( 'today' );
    $diff = $today->diff( $date );

    return $diff->invert;
}


/*-------------------------------------------------------------
 * Convert a given timestamp in standard PHP/MySQL date/time
 * format (YYYY-MM-DD HH:MM:SS) into relative number of days:
 *    0 -> today
 *   -1 -> yesterday
 *   +1 -> tomorrow
 *   -n -> n days ago
 *   +n -> in n days
 *
 * Arguments: $timestamp - string containing timestamp
 *
 * Returns: relative date string
 *-------------------------------------------------------------*/
function daysFromToday( $timestamp ) {
    $date = new DateTime( $timestamp );
    $today = new DateTime( 'today' );
    $diff = $today->diff( $date );

    if( $diff->days == 0 )                  return 'today';
    if( $diff->invert && $diff->days == 1 ) return 'yesterday';
    if( $diff->invert )                     return $diff->days.' days ago';
    if( $diff->days == 1 )                  return 'tomorrow';
                                            return 'in '.$diff->days.' days';
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
 * Show an error as script exits. If an ending file is provided
 * and exists (i.e. to wrap up the HTML / page layout), then
 * this is first included
 *
 * Argument: $error  - text of error to display
 *           $ending - file to include after error text
 *                     (defaults to 'common-bottom.php')
 *-------------------------------------------------------------*/
function showErrorAndDie( $error, $ending='common-bottom.php' ) {
    showStatus( $error, 'error' );
    if( file_exists( $ending ) ) include $ending;
    die();
}


/*-------------------------------------------------------------
 * Adds a JS redirect to a given page after a given delay
 *
 * Argument: $delay    - an optional delay in ms (default 3000)
 *           $location - an optional location (default index.php)
 *-------------------------------------------------------------*/
function addRedirect( $delay=3000, $location='index.php' ) {
    // Strip off any GET parameters to get the filename
    $file = strtok( $location, '?' );

    if( !file_exists( $file ) ) $location = 'index.php';
    echo '<script>';
    echo   'setTimeout( function () {
                window.location.href = "'.$location.'";
            }, '.$delay.' );';
    echo '</script>';
}


/*-------------------------------------------------------------
 * Display debug info at bottom right of window (shows on hover)
 * for the standard PHP arrays: GET / POST / FILE / SESSION, as
 * well as the contents of a global $DEBUG variable which can
 * be set to any value when debugging code.
 *-------------------------------------------------------------*/
function showDebugInfo() {
    global $DEBUG;

    $havePost    = isset( $_POST )    && sizeof( $_POST )    > 0;
    $haveGet     = isset( $_GET )     && sizeof( $_GET )     > 0;
    $haveFiles   = isset( $_FILES )   && sizeof( $_FILES )   > 0;
    $haveSession = isset( $_SESSION ) && sizeof( $_SESSION ) > 0;
    $haveDebug   = isset( $DEBUG );

    $haveInfo = $havePost || $haveGet || $haveFiles || $haveSession || $haveDebug;

    $debugInfo  = '<div style="box-sizing: border-box; font-family: system-ui, sans-serif; background: rgba(0,0,0,0.8); color: #fff; font-size: 18px; line-height: 1em; position: fixed; right: 0; bottom: 20px; padding: 10px 30px 10px 5px; width: 40px; max-width: 95vw; max-height: 90vh; border-radius: 10px 0 0 10px; display: flex; gap: 20px; z-index: 999; overflow-x: hidden; box-shadow: 0 0 5px 1px #00000040;" ';
    $debugInfo .= 'onclick="this.style.width= this.style.width==\'auto\' ? \'40px\' : \'auto\';">';
    $debugInfo .= '<div style="box-sizing: inherit; padding: 0; writing-mode: vertical-lr; align-self: flex-end; cursor: pointer; color: ';
    $debugInfo .= $haveInfo ? '#ff0' : '#666';
    $debugInfo .= ';">DEBUG INFO</div>';
    $debugInfo .= '<pre style="margin: 0; font-size: 16px; line-height: 16px; text-align: left; ">';

    if( $haveInfo ) {
        if( $haveDebug   ) $debugInfo .=   'DEBUG: '.print_r( $DEBUG,    True ).PHP_EOL.PHP_EOL;
        if( $havePost    ) $debugInfo .=    'POST: '.print_r( $_POST,    True );
        if( $haveGet     ) $debugInfo .=     'GET: '.print_r( $_GET,     True );
        if( $haveFiles   ) $debugInfo .=   'FILES: '.print_r( $_FILES,   True );
        $debugInfo .= 'SESSION: ('.print_r( session_name(), True ).') ';
        if( $haveSession ) $debugInfo .=             print_r( $_SESSION, True );
    }
    else {
        $debugInfo .= 'NONE';
    }

    $debugInfo .= '</pre></div>';

    echo $debugInfo;
}



?>