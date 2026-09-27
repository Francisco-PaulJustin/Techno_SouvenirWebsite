# Souvenir Shop - E-commerce Website

A complete PHP + PostgreSQL (Supabase) + HTML + CSS + JavaScript e-commerce website for a souvenir shop.

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
├── login.php              # Login for customers and admins
├── signup.php             # User registration
├── logout.php             # Logout
│
├── admin/                 # Admin panel
│   ├── index.php          # Admin dashboard
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
├── api/                   # JSON endpoints called from assets/js
│   ├── add_to_cart.php
│   ├── update_quantity.php
│   ├── delete_from_cart.php
│   ├── get_cart_count.php
│   └── cancel_order.php
│
└── cart/                  # Cart management
    └── cart_session.php   # Cart session functions
```

## Installation

1. **Database Setup (Supabase PostgreSQL)**
   - Create a Supabase project
   - Run `sql/souvenir_shop.sql` in the Supabase SQL editor
   - Make sure the PHP `pdo_pgsql` extension is enabled

2. **Configuration**
   - Credentials are never committed. Copy `includes/config.local.example.php` to
     `includes/config.local.php` (gitignored) and fill in your Supabase details:
     ```php
     return [
         'DB_HOST' => 'aws-0-ap-southeast-1.pooler.supabase.com',
         'DB_USER' => 'postgres.your-project-ref',
         'DB_PASS' => 'your-database-password',
     ];
     ```
   - Or set the `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` environment variables instead

3. **File Permissions**
   - Ensure `admin/uploads/` and `uploads/profiles/` are writable for image uploads

4. **Web Server**
   - Place the project in your web server directory (e.g., `htdocs` for XAMPP)
   - Access via: `http://localhost/Techno_SouvenirWebsite/`

5. **Deployment**
   - The `Dockerfile` runs the site on Render (or any Docker host) with PHP 8.2 + Apache
   - Set the same `DB_*` values as environment variables on the host
   - `/health` returns `OK` for uptime monitors

## Admin Account

Admins log in through the normal `login.php` page and are sent to the admin panel.
Give a user the admin role from **Admin Panel → Users**. Never publish admin passwords here.

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

- **Backend**: PHP 8.x
- **Database**: PostgreSQL (Supabase)
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

