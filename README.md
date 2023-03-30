# php-project-library

A set of PHP functions to help my students with their project work, particularly with MySQL DB access and debugging

The library provides functions for:

- Connect to MySQL server databases:
     - connectToDB()

- Run queries to obtain or modify data in a MySQL DB
     - getRecords()
     - modifyRecords()

- Handle errors with the MySQL operations gracefully:
     - checkQueryAndData()

- Upload files / images to the server:
     - uploadFile()
     - uploadImage()

- Check for a valid URL
     - urlExists()

- Configure file download output streams:
     - prepareDownload()
     - finaliseDownload()

- Convert plain text to HTML paragraphs:
     - text2paras()

- Format and manage dates and times in various ways:
     - niceDate()
     - niceTime()
     - daysFromToday()
     - isToday()
     - isInPast()

- Show status messages:
     - showStatus()
     - showErrorAndDie()

- Add a JS delayed redirect (e.g. after a status message):
     - addRedirect()

- Display debug info for $_SESSION, $_GET, $_POST, etc.:
    - showDebugInfo()


Included are files for a simple demo application that shows the library functions in use.
