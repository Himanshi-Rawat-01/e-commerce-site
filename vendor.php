<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'vendor') {
    header("Location: index.html");
    exit;
}

include "Backend/db.php";

// Handle product deletion
if (isset($_GET['delete'])) {
    $pid = $_GET['delete'];
    $conn->query("DELETE FROM products WHERE id = $pid AND vendor_id = " . $_SESSION['id']);
}

// Handle new product submission
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $desc = $_POST['description'];
    $image_name = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $target = "uploads/" . basename($image_name);

    if (move_uploaded_file($image_tmp, $target)) {
        $stmt = $conn->prepare("INSERT INTO products (vendor_id, name, price, image, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdss", $_SESSION['id'], $name, $price, $image_name, $desc);
        $stmt->execute();
    }
}

// Fetch vendor's products
$vendor_id = $_SESSION['id'];
$res = $conn->query("SELECT * FROM products WHERE vendor_id = $vendor_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Vendor Dashboard</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="container">
    <h1>Hello, <?php echo $_SESSION['username']; ?> (Vendor)</h1>
    <h2>Add New Product</h2>

    <form method="POST" enctype="multipart/form-data">
      <input type="text" name="name" placeholder="Product Name" required />
      <input type="number" step="0.01" name="price" placeholder="Price" required />
      <textarea name="description" placeholder="Product Description" required></textarea>
      <input type="file" name="image" accept="image/*" required />
      <button type="submit" name="add_product">Add Product</button>
    </form>

    <h2>Your Products</h2>
    <div class="product-list">
      <?php while ($row = $res->fetch_assoc()) { ?>
        <div class="product-card">
          <img src="uploads/<?php echo $row['image']; ?>" alt="Product Image" />
          <h3><?php echo $row['name']; ?></h3>
          <p>₹<?php echo $row['price']; ?></p>
          <p><?php echo $row['description']; ?></p>
          <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this product?')">❌ Delete</a>
        </div>
      <?php } ?>
    </div>
  </div>
</body>
</html>
