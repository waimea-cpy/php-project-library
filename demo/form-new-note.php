<?php
    require_once '../lib/db.php';

    include 'partials/top.php';

    // Get the pet ID from URL
    $petID = $_GET['pet'] ?? null;
    if (!$petID) die('Unknown pet ID');

    consoleLog($petID, 'Pet ID');

    // Connect to the database
    $db = connectToDB();

    // Get the pet records
    $query = 'SELECT name, species FROM pets WHERE id=?';

    // Attempt to run the query. We should get an array of records
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([$petID]);
        $pet = $stmt->fetch();
    }
    catch (PDOException $e) {
        consoleError($e->getMessage(), 'DB SELECT');
        die('There was an error getting pet from the database');
    }

    // Check we have a record
    if (!$pet) die('No pet found for given ID');
?>


<article>
    <h2>New Note for <?= $pet['name'] ?> the <?= $pet['species'] ?></h2>
    
    <form method="post" action="add-new-note.php">
    
        <input type="hidden" name="id" value="<?php echo $petID ?>">
    
        <label for="name">Note</label>
        <input type="text" name="note" maxlength="100" required>
    
        <input type="submit" value="Add Note">
    
    </form>

</article>

<?php
    include 'partials/bottom.php' ;
?>