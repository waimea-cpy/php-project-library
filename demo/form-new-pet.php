<?php
    include 'partials/top.php';
?>

<article>
    
    <h2>New Pet</h2>
    
    <form method="post" action="add-new-pet.php" enctype="multipart/form-data">
    
        <label for="name">Name</label>
        <input type="text" name="name" required>
    
        <label for="species">Species</label>
        <input type="text" name="species" required>
    
        <label for="dob">Date of Birth</label>
        <input type="date" name="dob" max="<?= date('Y-m-d') ?>" required>
    
        <label for="description">Description</label>
        <textarea name="description" required></textarea>
    
        <label for="image">Image</label>
        <input type="file" name="image" accept="image/*" required>
    
        <input type="submit" value="Add Pet">
    
    </form>
    
</article>

<?php
    include 'partials/bottom.php';
?>