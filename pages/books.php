<?php
$query = "SELECT id, title, description, cover_image FROM books";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>
<div class="container mt-4">
    <h2 class="mb-4">Books</h2>
    <div class="row">
        <?php while ($book = $result->fetch_assoc()): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <?php if (!empty($book['cover_image'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($book['cover_image']); ?>"
                             class="card-img-top"
                             alt="<?php echo htmlspecialchars($book['title']); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                        <p class="card-text">
                            <?php echo htmlspecialchars(mb_strimwidth($book['description'], 0, 100, '…')); ?>
                        </p>
                    </div>
                    <div class="card-footer text-right">
                        <a href="view_book.php?id=<?php echo $book['id']; ?>"
                           class="btn btn-sm btn-primary">View Details</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
