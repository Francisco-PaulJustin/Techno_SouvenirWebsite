<?php
function getFeaturedProducts($limit = 6) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.featured = TRUE AND p.stock > 0 ORDER BY p.id DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getProductsByCategory($category_id, $limit = null) {
    global $pdo;
    if ($limit) {
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? AND p.stock > 0 ORDER BY p.name LIMIT ?");
        $stmt->execute([$category_id, $limit]);
    } else {
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? AND p.stock > 0 ORDER BY p.name");
        $stmt->execute([$category_id]);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProducts($search = '', $category = '', $sort = 'name', $limit = null, $offset = 0) {
    global $pdo;
    
    $where = ["p.stock > 0"];
    $params = [];
    
    if (!empty($search)) {
        $where[] = "(p.name ILIKE ? OR p.description ILIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    if (!empty($category)) {
        $where[] = "p.category_id = ?";
        $params[] = $category;
    }
    
    $where_clause = implode(' AND ', $where);
    
    $order_by = 'p.name ASC';
    if ($sort == 'price_asc') {
        $order_by = 'p.price ASC';
    } elseif ($sort == 'price_desc') {
        $order_by = 'p.price DESC';
    }
    
    $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $where_clause ORDER BY $order_by";
    
    if ($limit) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductCount($search = '', $category = '') {
    global $pdo;
    
    $where = ["stock > 0"];
    $params = [];
    
    if (!empty($search)) {
        $where[] = "(name ILIKE ? OR description ILIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    if (!empty($category)) {
        $where[] = "category_id = ?";
        $params[] = $category;
    }
    
    $where_clause = implode(' AND ', $where);
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE $where_clause");
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'];
}
?>
