# PHP Utility Library

A set of PHP library functions to help students with their project work, particularly with MySQL DB access and debugging

Current version: **4.0** (see [lib/version.md](lib/version.md))

## Demo

Included are files for a simple demo application that shows the library functions in use.

An SQL dump of a MySQL database for the demo can be found in the db folder.

## Library Files

### db.php

Connect to MySQL server databases: **connectToDB**()

Example of use...

```php
// Connect to the database using credentials in _db.ini
$db = connectToDB();

$query = 'SELECT * FROM items';

try {
    $stmt = $db->prepare($query);
    $stmt->execute();
    $items = $stmt->fetchAll();
}
catch (PDOException $e) {
    consoleError($e->getMessage(), 'Items Fetch');
    die('There was an error getting item data');
}

consoleLog($items);

foreach($items as $item) {
    echo $item['name'];
}
```

Obtain data about uploaded images for insert into DB: **uploadedImageData**()

When uploading an image via a multi-part form...

```html
<form 
    method="post" 
    action="..." 
    enctype="multipart/form-data"
>
    <label>Image</label>
    <input 
        type="file" 
        name="image" 
        accept="image/*" 
        required
    >

    <input type="submit" value="Upload Image">
</form>
```

The uploaded image's information is validated and returned, ready to be added to a database...

```php
if(empty($_FILES)) die ('Problem uploading the image (probably too large)');

// Get image data and type of uploaded file from $_FILES
[
    'data' => $imageData,
    'type' => $imageType
] = uploadedImageData($_FILES['image']);

$db = connectToDB();

$query = 'INSERT INTO items (image_type, image_data) 
          VALUES (?, ?)';

// Run the query, passing along the image data
try { 
    $stmt = $db->prepare($query);
    $stmt->execute([$imageType, $imageData]);
}
catch (PDOException $e) {
    consoleError($e->getMessage(), 'Image Insert');
    die('There was an error adding image to the database');
}
```

---

### debug.php

Show info in the JS console to aid in PHP debugging:
- consoleLog()
- consoleLError()
- consoleBeginGroup()
- consoleEndGroup()
- consoleDivider()

Example of use...

```php
consoleGroupStart('NEW REQUEST');

    consoleLog($url,    'Request URL');
    consoleLog($method, 'Method');
    consoleLog($type,   'Type');
    consoleLog($route,  'Route');

    consoleDivider();

    if ($params)   consoleLog($params,   'Parameters');
    if ($_GET)     consoleLog($_GET,     'GET Data');
    if ($_POST)    consoleLog($_POST,    'POST Data');
    if ($_FILES)   consoleLog($_FILES,   'FILES Data');
    if ($_SESSION) consoleLog($_SESSION, 'SESSION Data');

consoleGroupEnd();
```

Show a pop-out debug panel at the bottom-right of the screen:
- ShowDebugInfo()

---

### file.php

Configure file download output streams:
- prepareDownload()
- finaliseDownload()

Example of use...

```php
    // Setup the output stream
    $output = prepareDownload( 'info', 'txt' );
    // Add the info
    fputs( $output, 'Name: '.$thing['name'].PHP_EOL );
    fputs( $output, 'Desc: '.$thing['desc'].PHP_EOL );
    // And close the stream
    finaliseDownload( $output );
```

---

### text.php

Convert plain text to HTML paragraphs:
- text2paras()

Example of use...

```php
echo text2paras($thing['description']);
```

---

### date.php

Format and manage dates and times in various ways:
- formattedDate()
- formattedTime()
- isToday()
- isInPast()
- daysFromToday()
- ageInYears()

Example of use...

```php
echo 'Born: '.formattedDate($user['dob']);
echo ' ('.ageInYears($user['dob']).')';

echo 'Posted at ' . formattedTime($post['timestamp']);
echo ' on ' . formattedDate($post['timestamp']);
echo ' (' . daysFromToday($post['timestamp']) . ')';
```
---

