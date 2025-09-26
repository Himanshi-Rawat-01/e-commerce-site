<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'customer') {
    header("Location: index.html");
    exit;
}

include "Backend/db.php";

// Add to cart
if (isset($_GET['add_to_cart'])) {
    $pid = $_GET['add_to_cart'];
    $cid = $_SESSION['id'];

    // Check if already in cart
    $check = $conn->query("SELECT * FROM cart WHERE customer_id=$cid AND product_id=$pid");
    if ($check->num_rows > 0) {
        $conn->query("UPDATE cart SET quantity = quantity + 1 WHERE customer_id=$cid AND product_id=$pid");
    } else {
        $conn->query("INSERT INTO cart (customer_id, product_id, quantity) VALUES ($cid, $pid, 1)");
    }
}

// Remove from cart
if (isset($_GET['remove'])) {
    $cart_id = $_GET['remove'];
    $conn->query("DELETE FROM cart WHERE id = $cart_id");
}

// Place order (for now, just clear cart)
if (isset($_GET['place_order'])) {
    $cid = $_SESSION['id'];
    $conn->query("DELETE FROM cart WHERE customer_id = $cid");
    echo "<script>alert('Order placed successfully!'); window.location='customer.php';</script>";
}

// Fetch products
$products = $conn->query("SELECT * FROM products");

// Fetch cart
$cid = $_SESSION['id'];
$cart = $conn->query("SELECT cart.id AS cart_id, products.name, products.price, products.image, cart.quantity 
                      FROM cart JOIN products ON cart.product_id = products.id 
                      WHERE cart.customer_id = $cid");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Customer Dashboard</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="container">
    <h1>Welcome, <?php echo $_SESSION['username']; ?> (Customer)</h1>

    <h2>Available Products</h2>
    <div class="product-list">
      <?php while ($row = $products->fetch_assoc()) { ?>
        <div class="product-card">
          <img src="uploads/<?php echo $row['image']; ?>" alt="Product Image" />
          <h3><?php echo $row['name']; ?></h3>
          <p>₹<?php echo $row['price']; ?></p>
          <p><?php echo $row['description']; ?></p>
          <a href="?add_to_cart=<?php echo $row['id']; ?>">➕ Add to Cart</a>
        </div>
      <?php } ?>
    </div>

    <h2>Your Cart</h2>
    <div class="product-list">
      <?php 
      $total = 0;
      while ($item = $cart->fetch_assoc()) { 
        $subtotal = $item['price'] * $item['quantity'];
        $total += $subtotal;
      ?>
        <div class="product-card">
          <img src="uploads/<?php echo $item['image']; ?>" />
          <h3><?php echo $item['name']; ?></h3>
          <p>₹<?php echo $item['price']; ?> x <?php echo $item['quantity']; ?> = ₹<?php echo $subtotal; ?></p>
          <a href="?remove=<?php echo $item['cart_id']; ?>" onclick="return confirm('Remove from cart?')">🗑️ Remove</a>
        </div>
      <?php } ?>
    </div>

    <h3>Total: ₹<?php echo $total; ?></h3>
    <?php if ($total > 0) { ?>
      <a href="?place_order=true">
        <button style="margin-top: 10px;">🛍️ Place Order</button>
      </a>
    <?php } ?>
  </div>
</body>
</html>
