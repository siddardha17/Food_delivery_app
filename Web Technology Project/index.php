<?php
// Database connection and session start
include('db.php');
session_start();

// Check if user is logged in
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentUser = $result->fetch_assoc();
    $stmt->close();
}

// Initialize cart from session
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$appliedCoupon = isset($_SESSION['applied_coupon']) ? $_SESSION['applied_coupon'] : null;
$discountPercentage = isset($_SESSION['discount_percentage']) ? $_SESSION['discount_percentage'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delicious Bites | Fast Food Delivery</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <nav>
                <div class="logo">Delicious Bites</div>
                <div class="nav-links">
                    <a href="#home">Home</a>
                    <a href="#menu">Menu</a>
                    <a href="#how-it-works">How It Works</a>
                    <a href="#contact">Contact</a>
                    <?php if ($currentUser): ?>
                        <div class="user-info">
                            <span><?php echo htmlspecialchars($currentUser['name']); ?></span>
                            <button class="logout-btn" id="logout-btn">Logout</button>
                        </div>
                    <?php else: ?>
                        <button class="btn btn-outline" id="navbar-login-btn">Login</button>
                    <?php endif; ?>
                    <button class="btn cart-btn">
                        <span class="cart-icon">🛒</span>
                        <span id="cart-count"><?php echo count($cart); ?></span>
                    </button>
                </div>
            </nav>
        </div>

        <!-- Cart Modal -->
        <div class="cart-modal" id="cart-modal">
            <div class="cart-header">
                <h3>Your Cart</h3>
                <button class="close-cart" id="close-cart">&times;</button>
            </div>
            <div class="cart-items" id="cart-items">
                <?php if (empty($cart)): ?>
                    <p style="text-align: center; color: #999;">Your cart is empty</p>
                <?php else: ?>
                    <?php foreach ($cart as $item): ?>
                        <div class="cart-item">
                            <div class="cart-item-info">
                                <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="cart-item-price">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                                <div class="cart-item-quantity">
                                    <button class="quantity-btn minus" data-name="<?php echo htmlspecialchars($item['name']); ?>">-</button>
                                    <span><?php echo $item['quantity']; ?></span>
                                    <button class="quantity-btn plus" data-name="<?php echo htmlspecialchars($item['name']); ?>">+</button>
                                </div>
                            </div>
                            <button class="remove-item" data-name="<?php echo htmlspecialchars($item['name']); ?>">&times;</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="coupon-section">
                <h4>Apply Coupon</h4>
                <div class="coupon-input">
                    <input type="text" id="coupon-code" placeholder="Enter coupon code" value="<?php echo $appliedCoupon ? htmlspecialchars($appliedCoupon) : ''; ?>">
                    <button class="btn" id="apply-coupon">Apply</button>
                </div>
                <p id="coupon-message" style="margin-top: 8px; font-size: 14px; color: <?php echo $appliedCoupon ? '#4CAF50' : '#ff6b6b'; ?>">
                    <?php echo $appliedCoupon ? "Coupon applied: {$discountPercentage}% off!" : ''; ?>
                </p>
            </div>
            
            <div class="cart-totals">
                <?php
                    $subtotal = array_reduce($cart, function($total, $item) {
                        return $total + ($item['price'] * $item['quantity']);
                    }, 0);
                    $discount = ($subtotal * $discountPercentage) / 100;
                    $grandTotal = $subtotal - $discount;
                ?>
                <div class="cart-total-row">
                    <span>Subtotal:</span>
                    <span id="subtotal">₹<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="cart-total-row discount">
                    <span>Discount:</span>
                    <span id="discount">-₹<?php echo number_format($discount, 2); ?></span>
                </div>
                <div class="cart-total-row grand-total">
                    <span>Total:</span>
                    <span id="grand-total">₹<?php echo number_format($grandTotal, 2); ?></span>
                </div>
            </div>
            
            <button class="btn checkout-btn" style="width: 100%; margin-top: 15px;">Proceed to Checkout</button>
        </div>
    </header>

    <!-- Login Modal -->
    <div class="modal-overlay" id="login-overlay">
        <div class="login-modal">
            <h2 id="login-title">Login to Your Account</h2>
            <form id="login-form" method="POST" action="login.php">
                <div class="form-group">
                    <label for="login-email">Email</label>
                    <input type="email" id="login-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" name="password" required>
                </div>
                <div class="login-actions">
                    <button type="button" class="btn btn-outline" id="close-login">Cancel</button>
                    <button type="submit" class="btn">Login</button>
                </div>
            </form>
            <div class="login-toggle">
                Don't have an account? <a id="show-register">Register</a>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal-overlay" id="register-overlay">
        <div class="login-modal">
            <h2>Create New Account</h2>
            <form id="register-form" method="POST" action="register.php">
                <div class="form-group">
                    <label for="register-name">Full Name</label>
                    <input type="text" id="register-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="register-email">Email</label>
                    <input type="email" id="register-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="register-password">Password</label>
                    <input type="password" id="register-password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="register-confirm">Confirm Password</label>
                    <input type="password" id="register-confirm" name="confirm_password" required>
                </div>
                <div class="login-actions">
                    <button type="button" class="btn btn-outline" id="close-register">Cancel</button>
                    <button type="submit" class="btn">Register</button>
                </div>
            </form>
            <div class="login-toggle">
                Already have an account? <a id="show-login">Login</a>
            </div>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div class="modal-overlay" id="checkout-overlay">
        <div class="checkout-modal">
            <h2>Select Payment Method</h2>
            <div class="payment-option">
                <input type="radio" id="cod" name="payment" value="cod" checked>
                <label for="cod">Cash on Delivery</label>
            </div>
            <div class="payment-option">
                <input type="radio" id="card" name="payment" value="card">
                <label for="card">Credit/Debit Card</label>
            </div>
            <div class="payment-option">
                <input type="radio" id="upi" name="payment" value="upi">
                <label for="upi">UPI Payment</label>
            </div>
            <div class="login-actions">
                <button type="button" class="btn btn-outline" id="close-checkout">Cancel</button>
                <button type="button" class="btn" id="place-order">Place Order</button>
            </div>
        </div>
    </div>

    <!-- Order Confirmation Modal -->
    <div class="modal-overlay" id="confirmation-overlay">
        <div class="checkout-modal">
            <div class="order-confirmation">
                <h3>Order Placed Successfully!</h3>
                <p>Your order has been placed and will be delivered soon.</p>
                <p>Thank you for choosing Delicious Bites!</p>
                <button type="button" class="btn" id="close-confirmation">OK</button>
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="container">
            <div class="hero-text">
                <h1>Delicious Indian Food Delivered to Your Doorstep</h1>
                <p>Order from your favorite restaurants and enjoy authentic Indian flavors at home. Fast delivery, great prices, and amazing taste!</p>
                <a href="#menu" class="btn">Order Now</a>
            </div>
            <div class="hero-image">
                <img src="https://images.unsplash.com/photo-1631515243349-e0cb75fb8d3a?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                     alt="Delicious Indian Biryani" 
                     style="max-width: 100%; max-height: 400px; object-fit: cover; display: block; margin: 0 auto; border-radius: 15px;">
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="how-it-works">
        <div class="container">
            <h2>How It Works</h2>
            <div class="feature-cards">
                <div class="feature-card">
                    <img src="https://cdn-icons-png.flaticon.com/512/3652/3652191.png" alt="Choose Restaurant">
                    <h3>Choose Restaurant</h3>
                    <p>Browse hundreds of restaurants near you and pick your favorite.</p>
                </div>
                <div class="feature-card">
                    <img src="https://cdn-icons-png.flaticon.com/512/2738/2738730.png" alt="Select Food">
                    <h3>Select Food</h3>
                    <p>Customize your order and add items to your cart.</p>
                </div>
                <div class="feature-card">
                    <img src="https://cdn-icons-png.flaticon.com/512/3097/3097137.png" alt="Fast Delivery">
                    <h3>Fast Delivery</h3>
                    <p>Get your food delivered in under 30 minutes, guaranteed!</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Dishes -->
    <section class="popular-dishes" id="menu">
        <div class="container">
            <h2>Our Menu</h2>
            
            <div class="food-categories">
                <div class="food-category active" data-category="all">All Items</div>
                <div class="food-category" data-category="tiffins">Tiffins</div>
                <div class="food-category" data-category="biryanis">Biryanis</div>
                <div class="food-category" data-category="burgers">Burgers</div>
                <div class="food-category" data-category="fastfood">Fast Food</div>
            </div>
            
            <div class="dishes">
                <!-- Tiffins -->
                <div class="dish" data-category="tiffins">
                    <img src="https://images.unsplash.com/photo-1589301760014-d929f3979dbc?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Idli Sambar">
                    <div class="dish-info">
                        <h3>Idli Sambar</h3>
                        <p>Soft steamed rice cakes served with sambar and coconut chutney.</p>
                        <span class="price">₹120</span>
                        <button class="btn add-to-cart" data-name="Idli Sambar" data-price="120">Add to Cart</button>
                    </div>
                </div>
                
                <div class="dish" data-category="tiffins">
                    <img src="https://images.unsplash.com/photo-1631515243349-e0cb75fb8d3a?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Dosa">
                    <div class="dish-info">
                        <h3>Masala Dosa</h3>
                        <p>Crispy rice crepe stuffed with spiced potato filling.</p>
                        <span class="price">₹150</span>
                        <button class="btn add-to-cart" data-name="Masala Dosa" data-price="150">Add to Cart</button>
                    </div>
                </div>
                
                <div class="dish" data-category="tiffins">
                    <img src="https://images.unsplash.com/photo-1601050690597-df0568f70950?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Poha">
                    <div class="dish-info">
                        <h3>Poha</h3>
                        <p>Flattened rice cooked with onions, spices and peanuts.</p>
                        <span class="price">₹80</span>
                        <button class="btn add-to-cart" data-name="Poha" data-price="80">Add to Cart</button>
                    </div>
                </div>
                
                <!-- Biryanis -->
                <div class="dish" data-category="biryanis">
                    <img src="https://images.unsplash.com/photo-1633945274309-2c16c9682a8c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Hyderabadi Biryani">
                    <div class="dish-info">
                        <h3>Hyderabadi Biryani</h3>
                        <p>Fragrant basmati rice cooked with tender chicken and spices.</p>
                        <span class="price">₹280</span>
                        <button class="btn add-to-cart" data-name="Hyderabadi Biryani" data-price="280">Add to Cart</button>
                    </div>
                </div>
                
                <div class="dish" data-category="biryanis">
                    <img src="https://images.unsplash.com/photo-1601050690597-df0568f70950?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Veg Biryani">
                    <div class="dish-info">
                        <h3>Veg Dum Biryani</h3>
                        <p>Flavorful rice cooked with mixed vegetables and aromatic spices.</p>
                        <span class="price">₹220</span>
                        <button class="btn add-to-cart" data-name="Veg Dum Biryani" data-price="220">Add to Cart</button>
                    </div>
                </div>
                
                <div class="dish" data-category="biryanis">
                    <img src="https://images.unsplash.com/photo-1599043513900-ed6fe01d3833?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Mutton Biryani">
                    <div class="dish-info">
                        <h3>Mutton Biryani</h3>
                        <p>Rich and flavorful biryani with tender mutton pieces.</p>
                        <span class="price">₹350</span>
                        <button class="btn add-to-cart" data-name="Mutton Biryani" data-price="350">Add to Cart</button>
                    </div>
                </div>
                
                <!-- Burgers -->
                <div class="dish" data-category="burgers">
                    <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Chicken Burger">
                    <div class="dish-info">
                        <h3>Spicy Chicken Burger</h3>
                        <p>Juicy chicken patty with spicy mayo and fresh veggies.</p>
                        <span class="price">₹180</span>
                        <button class="btn add-to-cart" data-name="Spicy Chicken Burger" data-price="180">Add to Cart</button>
                    </div>
                </div>
                
                <div class="dish" data-category="burgers">
                    <img src="https://images.unsplash.com/photo-1571091718767-18b5b1457add?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Aloo Tikki Burger">
                    <div class="dish-info">
                        <h3>Aloo Tikki Burger</h3>
                        <p>Crispy potato patty with mint chutney and veggies.</p>
                        <span class="price">₹120</span>
                        <button class="btn add-to-cart" data-name="Aloo Tikki Burger" data-price="120">Add to Cart</button>
                    </div>
                </div>
                
                <div class="dish" data-category="burgers">
                    <img src="https://images.unsplash.com/photo-1551615593-ef5fe247e8f7?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Cheese Burger">
                    <div class="dish-info">
                        <h3>Cheese Burger</h3>
                        <p>Classic beef patty with melted cheese and special sauce.</p>
                        <span class="price">₹200</span>
                        <button class="btn add-to-cart" data-name="Cheese Burger" data-price="200">Add to Cart</button>
                    </div>
                </div>
                
                <!-- Fast Food -->
                <div class="dish" data-category="fastfood">
                    <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Pizza">
                    <div class="dish-info">
                        <h3>Tandoori Pizza</h3>
                        <p>Indian style pizza with tandoori chicken and paneer toppings.</p>
                        <span class="price">₹250</span>
                        <button class="btn add-to-cart" data-name="Tandoori Pizza" data-price="250">Add to Cart</button>
                    </div>
                </div>
                
                <div class="dish" data-category="fastfood">
                    <img src="https://images.unsplash.com/photo-1601050690597-df0568f70950?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Pav Bhaji">
                    <div class="dish-info">
                        <h3>Pav Bhaji</h3>
                        <p>Spicy mashed vegetables served with buttered buns.</p>
                        <span class="price">₹150</span>
                        <button class="btn add-to-cart" data-name="Pav Bhaji" data-price="150">Add to Cart</button>
                    </div>
                </div>
                
                <div class="dish" data-category="fastfood">
                    <img src="https://images.unsplash.com/photo-1631515242807-497c3f8f29c1?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Samosa">
                    <div class="dish-info">
                        <h3>Samosa Chaat</h3>
                        <p>Crispy samosas topped with chutneys, yogurt and spices.</p>
                        <span class="price">₹90</span>
                        <button class="btn add-to-cart" data-name="Samosa Chaat" data-price="90">Add to Cart</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Hungry? Order Now!</h2>
            <p>Use coupon code <strong>WELCOME20</strong> for 20% off your first order!</p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <a href="#" class="btn">Download App</a>
                <a href="#menu" class="btn btn-outline">View Menu</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact">
        <div class="container">
            <div class="footer-links">
                <a href="#">Home</a>
                <a href="#">About Us</a>
                <a href="#">Menu</a>
                <a href="#">Contact</a>
                <a href="#">Privacy Policy</a>
            </div>
            <div class="social-icons">
                <a href="#">FB</a>
                <a href="#">IG</a>
                <a href="#">TW</a>
            </div>
            <p class="copyright">© 2023 Delicious Bites. All rights reserved.</p>
        </div>
    </footer>

    <script>
// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Cart Functionality
    const cartButton = document.querySelector('.cart-btn');
    const cartModal = document.getElementById('cart-modal');
    const closeCart = document.getElementById('close-cart');
    const cartItemsContainer = document.getElementById('cart-items');
    const cartCount = document.getElementById('cart-count');
    const subtotalElement = document.getElementById('subtotal');
    const discountElement = document.getElementById('discount');
    const grandTotalElement = document.getElementById('grand-total');
    const couponCodeInput = document.getElementById('coupon-code');
    const applyCouponButton = document.getElementById('apply-coupon');
    const couponMessage = document.getElementById('coupon-message');
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    const checkoutButton = document.querySelector('.checkout-btn');
    const logoutBtn = document.getElementById('logout-btn');
    const navbarLoginBtn = document.getElementById('navbar-login-btn');
    const foodCategories = document.querySelectorAll('.food-category');
    const dishes = document.querySelectorAll('.dish');

    // Login/Register Elements
    const loginOverlay = document.getElementById('login-overlay');
    const registerOverlay = document.getElementById('register-overlay');
    const closeLogin = document.getElementById('close-login');
    const closeRegister = document.getElementById('close-register');
    const showRegister = document.getElementById('show-register');
    const showLogin = document.getElementById('show-login');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    // Checkout Elements
    const checkoutOverlay = document.getElementById('checkout-overlay');
    const closeCheckout = document.getElementById('close-checkout');
    const placeOrderBtn = document.getElementById('place-order');
    const confirmationOverlay = document.getElementById('confirmation-overlay');
    const closeConfirmation = document.getElementById('close-confirmation');

    // Available coupons (code: discount percentage)
    const coupons = {
        'WELCOME20': 20,
        'FOODIE15': 15,
        'HUNGRY10': 10
    };

    // Initialize cart from PHP session
    let cart = <?php echo json_encode($cart); ?>;
    let appliedCoupon = <?php echo json_encode($appliedCoupon); ?>;
    let discountPercentage = <?php echo $discountPercentage; ?>;

    // Food category filtering
    foodCategories.forEach(category => {
        category.addEventListener('click', function() {
            // Remove active class from all categories
            foodCategories.forEach(cat => cat.classList.remove('active'));
            // Add active class to clicked category
            this.classList.add('active');
            
            const category = this.getAttribute('data-category');
            
            dishes.forEach(dish => {
                if (category === 'all' || dish.getAttribute('data-category') === category) {
                    dish.style.display = 'block';
                } else {
                    dish.style.display = 'none';
                }
            });
        });
    });

    // Toggle Cart Modal
    cartButton.addEventListener('click', function(e) {
        e.stopPropagation();
        cartModal.classList.toggle('active');
    });

    closeCart.addEventListener('click', function() {
        cartModal.classList.remove('active');
    });

    // Close cart when clicking outside
    document.addEventListener('click', function(e) {
        if (!cartModal.contains(e.target) && e.target !== cartButton) {
            cartModal.classList.remove('active');
        }
    });

    // Navbar Login Button
    if (navbarLoginBtn) {
        navbarLoginBtn.addEventListener('click', function() {
            loginOverlay.classList.add('active');
        });
    }

    // Add to Cart with login check
    addToCartButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            <?php if (!$currentUser): ?>
                loginOverlay.classList.add('active');
                alert('Please login to add items to your cart');
                return;
            <?php endif; ?>
            
            const name = this.getAttribute('data-name');
            const price = parseFloat(this.getAttribute('data-price'));

            const existingItem = cart.find(function(item) {
                return item.name === name;
            });

            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    name: name,
                    price: price,
                    quantity: 1
                });
            }

            updateCart();
            cartModal.classList.add('active');
            
            // Show added animation
            const originalText = this.textContent;
            this.textContent = 'Added!';
            this.style.backgroundColor = '#4CAF50';
            setTimeout(function() {
                button.textContent = originalText;
                button.style.backgroundColor = '#ff9e3f';
            }, 1000);
        });
    });

    // Handle quantity changes and item removal
    document.addEventListener('click', function(e) {
        // Handle plus button
        if (e.target.classList.contains('plus')) {
            const name = e.target.getAttribute('data-name');
            const item = cart.find(item => item.name === name);
            if (item) {
                item.quantity += 1;
                updateCart();
            }
        }
        
        // Handle minus button
        if (e.target.classList.contains('minus')) {
            const name = e.target.getAttribute('data-name');
            const item = cart.find(item => item.name === name);
            if (item) {
                if (item.quantity > 1) {
                    item.quantity -= 1;
                } else {
                    // Remove item if quantity becomes 0
                    cart = cart.filter(item => item.name !== name);
                }
                updateCart();
            }
        }
        
        // Handle remove item button
        if (e.target.classList.contains('remove-item')) {
            const name = e.target.getAttribute('data-name');
            cart = cart.filter(item => item.name !== name);
            updateCart();
        }
    });

    // Apply Coupon
    applyCouponButton.addEventListener('click', function() {
        const code = couponCodeInput.value.trim().toUpperCase();
        
        if (coupons[code]) {
            discountPercentage = coupons[code];
            appliedCoupon = code;
            couponMessage.textContent = `Coupon applied: ${discountPercentage}% off!`;
            couponMessage.style.color = '#4CAF50';
            updateCart();
        } else {
            discountPercentage = 0;
            appliedCoupon = null;
            couponMessage.textContent = 'Invalid coupon code';
            couponMessage.style.color = '#ff6b6b';
            updateCart();
        }
    });

    // Checkout functionality
    checkoutButton.addEventListener('click', function() {
        if (cart.length === 0) {
            alert('Your cart is empty. Please add items before checkout.');
            return;
        }
        
        cartModal.classList.remove('active');
        checkoutOverlay.classList.add('active');
    });

    closeCheckout.addEventListener('click', function() {
        checkoutOverlay.classList.remove('active');
    });

    placeOrderBtn.addEventListener('click', function() {
        // In a real app, you would process payment here
        // For this demo, we'll just show confirmation
        
        checkoutOverlay.classList.remove('active');
        confirmationOverlay.classList.add('active');
        
        // Clear the cart after order is placed
        cart = [];
        appliedCoupon = null;
        discountPercentage = 0;
        updateCart();
    });

    closeConfirmation.addEventListener('click', function() {
        confirmationOverlay.classList.remove('active');
    });

    // Login/Register Modal Functionality
    closeLogin.addEventListener('click', function() {
        loginOverlay.classList.remove('active');
    });

    closeRegister.addEventListener('click', function() {
        registerOverlay.classList.remove('active');
    });

    showRegister.addEventListener('click', function() {
        loginOverlay.classList.remove('active');
        registerOverlay.classList.add('active');
    });

    showLogin.addEventListener('click', function() {
        registerOverlay.classList.remove('active');
        loginOverlay.classList.add('active');
    });

    // Login Form Submission with AJAX
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;
        
        fetch('login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
        })
        .then(response => response.text())
        .then(data => {
            if (data === "Login successful!") {
                loginOverlay.classList.remove('active');
                alert('Login successful! Welcome to Delicious Bites.');
                window.location.href = 'index.php';
            } else {
                alert(data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Login failed. Please try again.');
        });
    });

    // Register Form Submission with AJAX
    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const name = document.getElementById('register-name').value;
        const email = document.getElementById('register-email').value;
        const password = document.getElementById('register-password').value;
        const confirm = document.getElementById('register-confirm').value;
        
        if (password !== confirm) {
            alert('Passwords do not match!');
            return;
        }
        
        fetch('register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
        })
        .then(response => response.text())
        .then(data => {
            if (data === "Registered successfully!") {
                registerOverlay.classList.remove('active');
                alert('Registration successful! You can now login.');
                loginOverlay.classList.add('active');
            } else {
                alert(data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Registration failed. Please try again.');
        });
    });

    // Logout Functionality
	if (logoutBtn) {
    logoutBtn.addEventListener('click', function() {
        fetch('logout.php')
        .then(response => {
            if (response.ok) {
                return response.text();
            }
            throw new Error('Network response was not ok');
        })
        .then(data => {
            if (data.includes("successfully")) {
                window.location.href = 'index.php';
            } else {
                alert('Logout failed: ' + data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Logout failed. Please try again.');
        });
    });
}

    // Update Cart Display and sync with server
    function updateCart() {
        // Calculate totals
        const subtotal = cart.reduce(function(total, item) {
            return total + (item.price * item.quantity);
        }, 0);
        const discount = (subtotal * discountPercentage) / 100;
        const grandTotal = subtotal - discount;

        // Update displayed totals (with ₹ symbol)
        subtotalElement.textContent = `₹${subtotal.toFixed(2)}`;
        discountElement.textContent = `-₹${discount.toFixed(2)}`;
        grandTotalElement.textContent = `₹${grandTotal.toFixed(2)}`;

        // Update cart count
        const totalItems = cart.reduce(function(total, item) {
            return total + item.quantity;
        }, 0);
        cartCount.textContent = totalItems;

        // Update cart items display
        if (cart.length === 0) {
            cartItemsContainer.innerHTML = '<p style="text-align: center; color: #999;">Your cart is empty</p>';
        } else {
            cartItemsContainer.innerHTML = cart.map(item => `
                <div class="cart-item">
                    <div class="cart-item-info">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">₹${(item.price * item.quantity).toFixed(2)}</div>
                        <div class="cart-item-quantity">
                            <button class="quantity-btn minus" data-name="${item.name}">-</button>
                            <span>${item.quantity}</span>
                            <button class="quantity-btn plus" data-name="${item.name}">+</button>
                        </div>
                    </div>
                    <button class="remove-item" data-name="${item.name}">&times;</button>
                </div>
            `).join('');
        }

        // Sync with server
        fetch('update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cart: cart,
                applied_coupon: appliedCoupon,
                discount_percentage: discountPercentage
            })
        })
        .catch(error => {
            console.error('Error updating cart:', error);
        });
    }
});z
</script> 
</body>
</html>