<?php
    require_once 'common-functions.php';
    require_once 'common-top.php';
?>

<h2>Add a New Pet</h2>

<form method="post" action="add-new-pet.php" enctype="multipart/form-data">

    <label for="name">Name</label>
    <input type="text" name="name" required>

    <label for="species">Species</label>
    <input type="text" name="species" required>

    <label for="description">Description</label>
    <input type="text" name="description" required>

    <label for="image">Image</label>
    <input type="file" name="image" required>

    <input type="submit" value="Add Pet">

</form>

<?php
    include('common-bottom.php');
?>