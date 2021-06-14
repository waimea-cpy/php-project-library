<?php
    require_once 'common-functions.php';
    require_once 'common-top.php';

    if( !isset( $_GET['pet'] ) || empty( $_GET['pet'] ) ) showErrorAndDie( 'unknown pet ID' );

    $petID = $_GET['pet'];
?>

<h2>Add a New Note</h2>

<form method="post" action="add-new-note.php">

    <input type="hidden" name="pet" value="<?php echo $petID ?>">

    <label for="name">Note</label>
    <input type="text" name="note" required>

    <input type="submit" value="Add Note">

</form>

<?php
    include('common-bottom.php');
?>