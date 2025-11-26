# Souvenir Shop - E-commerce Website

A complete PHP + MySQL + HTML + CSS + JavaScript e-commerce website for a souvenir shop.

## Features

- **User Authentication**: Login, signup, and logout functionality
- **Product Management**: Browse products, view details, search and filter
- **Shopping Cart**: Add, update, and remove items from cart
- **Checkout System**: Complete order processing with shipping information
- **Admin Panel**: Full admin dashboard for managing products, orders, and users
- **Responsive Design**: Mobile-friendly interface with modern animations
- **User Profile**: Full profile management with avatar uploads and password updates

## Project Structure

```
souvenir_shop/
├── index.php              # Homepage
├── about.php              # About page
├── contact.php            # Contact page
├── categories.php         # Product categories
├── products.php           # Product listing
├── product_view.php       # Product details
├── cart.php               # Shopping cart
├── checkout.php           # Checkout page
├── profile.php            # User profile & settings
├── login.php              # User login
├── signup.php             # User registration
├── logout.php             # Logout
│
├── admin/                 # Admin panel
│   ├── index.php          # Admin dashboard
│   ├── login.php          # Admin login
│   ├── add_product.php    # Add new product
│   ├── edit_product.php   # Edit product
│   ├── delete_product.php # Delete product
│   ├── manage_orders.php  # Manage orders
│   ├── manage_users.php   # Manage users
│   └── uploads/           # Product images
│
├── includes/              # Shared PHP files
│   ├── config.php         # Database configuration
│   ├── header.php         # Site header
│   ├── footer.php         # Site footer
│   ├── navbar.php         # Navigation bar
│   ├── auth.php           # Authentication functions
│   ├── helpers.php        # Helper functions
│   └── product_functions.php # Product functions
│
├── assets/                # Static assets
│   ├── css/               # Stylesheets (global, auth, product, cart, profile, admin)
│   ├── js/                # JavaScript files (main interactions, cart, product, auth, profile)
│   ├── images/            # Images
│   └── fonts/             # Font files
│
├── sql/                   # Database
│   └── souvenir_shop.sql  # Database structure
│
├── api/                   # API endpoints
│   ├── get_products.php
│   ├── get_product.php
│   ├── add_to_cart.php
│   ├── delete_from_cart.php
│   ├── update_quantity.php
│   └── process_checkout.php
│
└── cart/                  # Cart management
    └── cart_session.php   # Cart session functions
```

## Installation

1. **Database Setup**
   - Create a MySQL database
   - Import the SQL file: `sql/souvenir_shop.sql`
   - Update database credentials in `includes/config.php`

2. **Configuration**
   - Edit `includes/config.php` with your database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'souvenir_shop');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     ```

3. **File Permissions**
   - Ensure `admin/uploads/` directory is writable for image uploads

4. **Web Server**
   - Place the project in your web server directory (e.g., `htdocs` for XAMPP)
   - Access via: `http://localhost/souvenir_shop/`

## Default Admin Account

- **Email**: admin@souvenirshop.com
- **Password**: admin123

**Note**: Change the default admin password after first login!

## Features Overview

### Frontend
- Product browsing and search with live filtering
- Category filtering and featured collections
- Shopping cart functionality with smooth interactions
- User authentication & full profile management
- Responsive design with animations, gradients, and hover effects

### Backend
- Product management (CRUD operations)
- Order management
- User management
- Image upload handling
- Session management

### Database
- Users table
- Products table
- Categories table
- Orders table
- Order items table

## Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Server**: Apache (XAMPP/WAMP)

## Security Notes

- Passwords are hashed using PHP's `password_hash()` function
- SQL injection protection using prepared statements
- XSS protection using `htmlspecialchars()`
- Session-based authentication
- Admin panel access control

## Development

To customize the website:

1. **Styling**: Edit CSS files in `assets/css/`
2. **Functionality**: Modify PHP files as needed
3. **Database**: Update SQL schema if needed
4. **Images**: Add product images to `admin/uploads/` and reference them in products

## License

This project is provided as-is for educational and commercial use.

## Support

For issues or questions, please refer to the code comments or contact the development team.

