<?php
    require_once 'common-functions.php';
    require_once 'common-top.php';
?>

<h2>Search Pets</h2>

<form method="get" action="show-matching-pets.php">

    <label for="search">Search Term</label>
    <input type="text" name="search" required>

    <input type="submit" value="Search Pets">

</form>

<?php
    include('common-bottom.php');
?>