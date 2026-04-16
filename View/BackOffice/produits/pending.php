<h2>Pending Products</h2>
<table border="1">
<tr>
    <th>Name</th>
    <th>Price</th>
    <th>Action</th>
</tr>

<?php while($row = $products->fetch_assoc()): ?>
<tr>
    <td><?= $row['name'] ?></td>
    <td><?= $row['price'] ?></td>

    <td>
        <a href="index.php?action=approve&id=<?= $row['id'] ?>">✅ Approve</a>
        <a href="index.php?action=delete&id=<?= $row['id'] ?>">❌ Reject</a>
    </td>
</tr>
<?php endwhile; ?>
</table>