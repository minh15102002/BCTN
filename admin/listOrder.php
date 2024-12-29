<?php
    require_once "inc/sidebar.php";
    require_once "inc/header.php";
    require_once "../class/Order.php";

    $conn =  new Database();
    $pdo = $conn->getConnect();

    if(empty($_GET['page']))
        $page = 1;
    else
        $page = $_GET['page'];
    $ppp = 5; // sản phẩm trên 1 trang

    $limit = $ppp;
    $offset = ($page-1)*$ppp; // tính lấy 4 sp tiếp theo
    $data_order = Order::getAll($pdo, $limit, $offset);

    $max = Order::countAllOrder($pdo);
    $maxPages = ceil($max / $ppp);

    $a = "Đang chờ xác nhận";
    $b = "Đã xác nhận";
?>
    <div class="container-fluid">
        <!-- <h1 class="h3 mb-2 text-gray-800">Danh sách đơn hàng</h1> -->

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Danh sách đơn hàng</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center" id="dataTable" width="100%" cellspacing="0">
                        <thead class="table-secondary">
                            <tr>
                                <th>Mã đơn hàng</th>
                                <th>Email người đặt</th>
                                <th>Ngày lập</th>
                                <th>Tổng tiền</th>
                                <th>Hình thức thanh toán</th>
                                <th>Trạng thái</th>
                                <th colspan="3">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($data_order as $order): ?>
                                <tr>
                                    <td><?=$order->order_id?></td>
                                    <td><?=$order->email?></td>
                                    <td><?=$order->order_date?></td>
                                    <td><?= number_format($order->total_price, 0, ',', '.')?> VNĐ</td>
                                    <td><?= $order->payment_method?></td>
                                    <td><?= $order->status==0?$a:$b?></td>
                                    <td align="center">
                                        <a href="listOrderDetail.php?order_id=<?=$order->order_id?>">Xem & xác nhận</a>
                                        <!-- <a href="orderDetail.php?order_id=<?=$order->order_id?>">Xem chi tiết</a> -->
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $page == 1 ? '#' : 'listOrder.php?page='.($page-1) ?>" aria-disabled="<?= $page == 1 ? 'true' : 'false' ?>">Previous</a>
                </li>

                <?php for ($i = 1; $i <= $maxPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="listOrder.php?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= $page == $maxPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $page == $maxPages ? '#' : 'listOrder.php?page='.($page+1) ?>" aria-disabled="<?= $page == $maxPages ? 'true' : 'false' ?>">Next</a>
                </li>
            </ul>
        </nav>

    </div>

</div>

<!-- Footer -->
<?php
    require "inc/footer.php";
?>
