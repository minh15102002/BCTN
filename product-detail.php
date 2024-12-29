<?php
require_once "inc/header.php";
require_once "class/Database.php";
require_once "class/Product.php";
require_once "class/Cart.php";
require_once "class/Comment.php";

// Kết nối cơ sở dữ liệu
$conn = new Database();
$pdo = $conn->getConnect();

// Lấy ID sản phẩm từ URL
$product_id = $_GET["product_id"] ?? null;
if (!$product_id) {
    die("Sản phẩm không tồn tại.");
}

// Lấy các tham số bộ lọc
$cat_id = $_GET["cat_id"] ?? null;
$brand_id = $_GET["brand_id"] ?? null;
$limit = 4;

// Lấy chi tiết sản phẩm và các sản phẩm liên quan
$product = Product::getOneProductByID($pdo, $product_id);
$related_pro = Product::getRelatedProduct($pdo, $product_id, $cat_id, $brand_id, $limit);

// Xử lý khi người dùng thêm sản phẩm vào giỏ
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $auth = new Auth();
    $auth->restrictAccess();  // Kiểm tra quyền người dùng

    $product_id = $_POST['product_id'] ?? null;
    $customer_id = $_SESSION['user_id'] ?? null;
    $price = $_POST["price"] ?? null;
    $quantity = $_POST["quantity"] ?? null;

    if ($customer_id && $product_id && $quantity && $price) {
        Cart::updateCartItem($pdo, $customer_id, $product_id, $quantity, $price);
    }
}

// Xử lý khi người dùng gửi bình luận
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'], $_POST['comment_text'])) {
    // session_start();
    $user_id = $_SESSION['user_id'] ?? null;
    $rating = $_POST['rating'];
    $comment_text = $_POST['comment_text'];
    $product_id = $_GET["product_id"] ?? null;
    if ($user_id && $rating && $comment_text) {
        Comment::addComment($pdo, $product_id, $user_id, $rating, $comment_text);
        header("Location: product-detail.php?product_id=$product_id");
        exit();
    } else {
        $error = "Vui lòng điền đầy đủ thông tin.";
    }
}

// Lấy bình luận của sản phẩm
$order = $_GET['order'] ?? 'DESC';
$comments = Comment::getAllComments($pdo, $product_id, 5, $order);
?>

<body id="product-detail">
    <div class="main-content">
        <div class="container">
            <div class="content">
                <div class="row">
                    <div class="col-12">
                        <div class="main-product-detail" style="padding: 20px 0;">
                            <h2>Chi tiết sản phẩm</h2>
                            <div class="product-single row">
                                <div class="product-detail col-md-5">
                                    <div class="page-content" id="content">
                                        <div class="images-container">
                                            <div class="js-qv-mask mask tab-content border">
                                                <div id="item1" class="tab-pane fade active show">
                                                    <img src="assets/images/img_pro/<?=$product->product_image?>" alt="img" class="img-fluid">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="product-info col-md-7">
                                    <div class="detail-description">
                                        <h2><?=$product->product_name?></h2>
                                        <p class="description"><?=$product->product_description?></p>
                                        <div class="price-del">
                                            <span class="price fs-4"><?=number_format($product->product_price, 0, ',', '.')?> ₫</span>
                                        </div>
                                        <div class="cart-area" style="padding: 20px 0;">
                                            <div class="product-quantity">
                                                <form method="post">
                                                    <div class="quantity d-flex align-items-center">
                                                        <span class="control-label fs-6 float-start me-2">Số lượng : </span>
                                                        <div class="quantity buttons_added">
                                                            <input type="button" value="-" class="minus fs-6">
                                                            <input type="number" name="quantity" value="1" class="input-text qty text fs-6 text-center" readonly style="width: 70px;">
                                                            <input type="button" value="+" class="plus fs-6">
                                                        </div>

                                                        <div class="add py-3">
                                                            <input type="hidden" name="product_id" value="<?=$product->product_id?>">
                                                            <input type="hidden" name="price" value="<?=$product->product_price?>">
                                                            <button class="btn btn-primary add-to-cart" type="submit">
                                                                <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                                                                <span>Thêm vào giỏ hàng</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="content">
                                            <p>Categories : <a href="#"><?=$product->category_name?></a></p>
                                            <p>Brand : <a href="#"><?=$product->brand_name?></a></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab content -->
                            <div class="review">
                                <ul class="nav nav-tabs">
                                    <li class="active">
                                        <a data-toggle="tab" href="#description">MÔ TẢ SẢN PHẨM</a>
                                    </li>
                                    <li>
                                        <a data-toggle="tab" href="#tag">VỀ THƯƠNG HIỆU</a>
                                    </li>
                                </ul>

                                <div class="tab-content">
                                    <div id="description" class="tab-pane fade in active show">
                                        <p><?=$product->product_description?></p>
                                    </div>
                                    <div id="tag" class="tab-pane fade in">
                                        <p><?=$product->brand_desc?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Lọc Bình Luận -->
                            <h5 style="margin-top: 30px;">Bình Luận</h5>
                            <form method="get" class="mb-3">
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_id) ?>">
                                <select name="order" class="form-select w-25 d-inline" onchange="this.form.submit()">
                                    <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>Mới nhất</option>
                                    <option value="ASC" <?= $order === 'ASC' ? 'selected' : '' ?>>Cũ nhất</option>
                                </select>
                            </form>

                            <!-- Hiển Thị Bình Luận -->
                            <div class="comments-section">
                                <?php if (!empty($comments)): ?>
                                    <?php foreach ($comments as $comment): ?>
                                        <div class="comment-item border rounded p-3 mb-3">
                                            <p><strong><?= htmlspecialchars($comment['name']) ?></strong> - 
                                                <span class="rating">⭐ <?= $comment['rating'] ?>/5</span>
                                            </p>
                                            <p><?= htmlspecialchars($comment['comment_text']) ?></p>
                                            <small><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>Chưa có bình luận nào.</p>
                                <?php endif; ?>
                            </div>

                            <!-- Form Gửi Bình Luận -->
                            
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>
                            <form method="post">
                                <div class="form-group mb-2">
                                    <label for="rating">Đánh giá:</label>
                                    <select name="rating" id="rating" class="form-select" required>
                                        <option value="5">5 sao</option>
                                        <option value="4">4 sao</option>
                                        <option value="3">3 sao</option>
                                        <option value="2">2 sao</option>
                                        <option value="1">1 sao</option>
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <textarea name="comment_text" class="form-control" placeholder="Viết bình luận..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Gửi Bình Luận</button>
                            </form>

                            <!-- Sản Phẩm Liên Quan -->
                            <div class="related">
                                <div class="title-tab-content text-center">
                                    <h2>SẢN PHẨM LIÊN QUAN</h2>
                                </div>
                                <div class="row d-flex justify-content-center">
                                    <?php foreach ($related_pro as $related_product): ?>
                                        <div class="card card-product m-2" style="width: 16rem;">
                                            <a href="product-detail.php?product_id=<?=$related_product->product_id?>">
                                                <img src="assets/images/img_pro/<?=$related_product->product_image?>" class="card-img-top" alt="...">
                                            </a>
                                            <div class="card-body">
                                                <h5 class="card-title"><?=$related_product->brand_name?></h5>
                                                <p class="card-text fs-6"><a href="product-detail.php?product_id=<?=$related_product->product_id?>"><?=$related_product->product_name?></a></p>
                                                <p class="fw-bold text-black fs-6"><?=number_format($related_product->product_price, 0, ',', '.')?> ₫</p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
require_once "inc/footer.php";
?>
