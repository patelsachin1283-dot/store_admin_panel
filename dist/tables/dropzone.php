<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../examples/login.php");
    exit();
}


header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

include '../db.php';

function sendProductEmail($productName) {
    global $conn;
    $mail = new PHPMailer(true);
    try {
        // Fetch the full product details for the email
        $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE name = ? ORDER BY id DESC LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $productName);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);

        if (!$product) return; // Product not found

        // Render the template using output buffering
        ob_start();
        $templatePath = __DIR__ . '/uiemail.php';
        if (file_exists($templatePath)) {
            include $templatePath;
        } else {
            echo "New product added: " . htmlspecialchars($productName);
        }
        $emailBody = ob_get_clean();

        //Server settings
        $mail->SMTPDebug = 0; 
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'patelsachin1283@gmail.com';
        $mail->Password   = 'pukg ipik xqrr jgiq'; // Replace with your 16-digit Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom('patelsachin1283@gmail.com', 'Sachin Patel');
        $mail->addAddress('sachinpatel9933@gmail.com', 'Product Mail');

        //Content
        $mail->isHTML(true);
        $mail->Subject = 'New Product Alert: ' . $productName;
        $mail->Body    = $emailBody;
        $mail->AltBody = "Product $productName has been uploaded. View it here: $dashboardUrl";

        $mail->send();
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $id = (int)$_POST['id'];

    // Delete
    $res = mysqli_query($conn, "SELECT image_path FROM product_images WHERE product_id = $id");
    while ($img = mysqli_fetch_assoc($res)) {
        $file = "../" . $img['image_path'];
        if (file_exists($file)) unlink($file);
    }

    mysqli_query($conn, "DELETE FROM product_images WHERE product_id = $id");
    mysqli_query($conn, "DELETE FROM products WHERE id = $id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetching
$sql_products = "
    SELECT p.id, p.name, p.price, p.stock, p.status, p.created_date,
           (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.id ASC LIMIT 1) AS first_image
    FROM products p
    ORDER BY p.id ASC
";
$products_result = mysqli_query($conn, $sql_products);
$products_list   = [];
while ($row = mysqli_fetch_assoc($products_result)) {
    $products_list[] = $row;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {

    $id    = isset($_POST['id']) && $_POST['id'] != '' ? (int)$_POST['id'] : 0;
    $name  = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $stock = mysqli_real_escape_string($conn, $_POST['stock']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $created_date = mysqli_real_escape_string($conn, $_POST['created_date']);

    if ($id > 0) {

        mysqli_query($conn, "
            UPDATE products 
            SET name='$name', price='$price', stock='$stock', status='$status', created_date='$created_date'
            WHERE id=$id
        ");
    } else {

        mysqli_query($conn, "
            INSERT INTO products (name, price, stock, status, created_date) 
            VALUES ('$name', '$price', '$stock', '$status', '$created_date')
        ");

        $id = mysqli_insert_id($conn);
        
        // Send email notification
        sendProductEmail($name);
    }


    // Image Upload
    if (isset($_FILES['file']) && count($_FILES['file']['name']) > 0 && $_FILES['file']['name'][0] != '') {


        $upload_dir = '../uploads/products/';

        foreach ($_FILES['file']['name'] as $key => $nameFile) {

            $tmp  = $_FILES['file']['tmp_name'][$key];
            $ext  = strtolower(pathinfo($nameFile, PATHINFO_EXTENSION));

            // Allow only images
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                continue;
            }

            $filename = uniqid('img_') . '.' . $ext;
            $dest     = $upload_dir . $filename;

            if (move_uploaded_file($tmp, $dest)) {

                $db_path = 'uploads/products/' . $filename;

                mysqli_query($conn, "
                INSERT INTO product_images (product_id, image_path) 
                VALUES ($id, '$db_path')
            ");
            }
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


//  GET PRODUCT IMAGES
if (isset($_GET['get_images'])) {
    $id = (int)$_GET['id'];

    $res = mysqli_query($conn, "SELECT id, image_path FROM product_images WHERE product_id = $id");

    $images = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $images[] = $row;
    }

    echo json_encode($images);
    exit();
}

if (isset($_GET['delete_image'])) {
    $id = (int)$_GET['id'];

    // Get file path
    $res = mysqli_query($conn, "SELECT image_path FROM product_images WHERE id = $id");
    $img = mysqli_fetch_assoc($res);

    if ($img) {
        $file = "../" . $img['image_path'];
        if (file_exists($file)) unlink($file);
    }



    mysqli_query($conn, "DELETE FROM product_images WHERE id = $id");
    exit();
}


?>





<!doctype html>
<html lang="en">
<!--begin::Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>AdminLTE | Dashboard v2</title>


    <link rel="stylesheet" href="lib/datatables/dataTables.css">



    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../css/adminlte.css" />

    <!--begin::Fonts-->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        crossorigin="anonymous" media="print" onload="this.media='all'" />
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />

    <link rel="stylesheet" href="../css/adminlte.css" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.css"
        crossorigin="anonymous" />


    <style>
        .dropzone-wrapper {
            border: 2px dashed #28a745;
            border-radius: 10px;
            background: #f8fff9;
            padding: 10px;
            min-height: 140px;
            transition: border-color .2s, background .2s;
        }

        .dropzone-wrapper:hover,
        .dropzone-wrapper.dz-drag-hover {
            border-color: #155724;
            background: #e9f7ec;
        }

        .dropzone-wrapper .dz-message {
            color: #28a745;
            font-weight: 600;
            font-size: 15px;
            margin: 18px 0;
        }

        .dropzone-wrapper .dz-message i {
            font-size: 2rem;
            display: block;
            margin-bottom: 6px;
        }
    </style>


</head>

<body class="layout-fixed fixed-header sidebar-expand-lg sidebar-open bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
        <!--begin::Header-->
        <nav class="app-header navbar navbar-expand bg-body">
            <!--begin::Container-->
            <div class="container-fluid">
                <!--begin::Start Navbar Links-->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                            <i class="bi bi-list"></i>
                        </a>
                    </li>
                    <li class="nav-item d-none d-md-block"><a href="#" class="nav-link">Home</a></li>
                    <li class="nav-item d-none d-md-block"><a href="#" class="nav-link">Contact</a></li>
                </ul>
                <!--end::Start Navbar Links-->
                <!--begin::End Navbar Links-->
                <ul class="navbar-nav ms-auto">
                    <!--begin::Navbar Search-->
                    <li class="nav-item">
                        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                            <i class="bi bi-search"></i>
                        </a>
                    </li>
                    <!--end::Navbar Search-->
                    <!--begin::Messages Dropdown Menu-->
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-bs-toggle="dropdown" href="uiemail.php">
                            <i class="bi bi-chat-text"></i>
                            <span class="navbar-badge badge text-bg-danger">3</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <a href="#" class="dropdown-item">
                                <!--begin::Message-->
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <img
                                            src="../assets/img/user1-128x128.jpg"
                                            alt="User Avatar"
                                            class="img-size-50 rounded-circle me-3" />
                                    </div>
                                    <div class="flex-grow-1">
                                        <h3 class="dropdown-item-title">
                                            Brad Diesel
                                            <span class="float-end fs-7 text-danger"><i class="bi bi-star-fill"></i></span>
                                        </h3>
                                        <p class="fs-7">Call me whenever you can...</p>
                                        <p class="fs-7 text-secondary">
                                            <i class="bi bi-clock-fill me-1"></i> 4 Hours Ago
                                        </p>
                                    </div>
                                </div>
                                <!--end::Message-->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <!--begin::Message-->
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <img
                                            src="../assets/img/user8-128x128.jpg"
                                            alt="User Avatar"
                                            class="img-size-50 rounded-circle me-3" />
                                    </div>
                                    <div class="flex-grow-1">
                                        <h3 class="dropdown-item-title">
                                            John Pierce
                                            <span class="float-end fs-7 text-secondary">
                                                <i class="bi bi-star-fill"></i>
                                            </span>
                                        </h3>
                                        <p class="fs-7">I got your message bro</p>
                                        <p class="fs-7 text-secondary">
                                            <i class="bi bi-clock-fill me-1"></i> 4 Hours Ago
                                        </p>
                                    </div>
                                </div>
                                <!--end::Message-->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <!--begin::Message-->
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <img
                                            src="../assets/img/user3-128x128.jpg"
                                            alt="User Avatar"
                                            class="img-size-50 rounded-circle me-3" />
                                    </div>
                                    <div class="flex-grow-1">
                                        <h3 class="dropdown-item-title">
                                            Nora Silvester
                                            <span class="float-end fs-7 text-warning">
                                                <i class="bi bi-star-fill"></i>
                                            </span>
                                        </h3>
                                        <p class="fs-7">The subject goes here</p>
                                        <p class="fs-7 text-secondary">
                                            <i class="bi bi-clock-fill me-1"></i> 4 Hours Ago
                                        </p>
                                    </div>
                                </div>
                                <!--end::Message-->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="uiemail.php" class="dropdown-item dropdown-footer">See All Messages</a>
                        </div>
                    </li>
                    <!--end::Messages Dropdown Menu-->
                    <!--begin::Notifications Dropdown Menu-->
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-bs-toggle="dropdown" href="#">
                            <i class="bi bi-bell-fill"></i>
                            <span class="navbar-badge badge text-bg-warning">15</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <span class="dropdown-item dropdown-header">15 Notifications</span>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="bi bi-envelope me-2"></i> 4 new messages
                                <span class="float-end text-secondary fs-7">3 mins</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="bi bi-people-fill me-2"></i> 8 friend requests
                                <span class="float-end text-secondary fs-7">12 hours</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="bi bi-file-earmark-fill me-2"></i> 3 new reports
                                <span class="float-end text-secondary fs-7">2 days</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer"> See All Notifications </a>
                        </div>
                    </li>
                    <!--end::Notifications Dropdown Menu-->
                    <!--begin::Fullscreen Toggle-->
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                            <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                            <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
                        </a>
                    </li>
                    <!--end::Fullscreen Toggle-->
                    <!--begin::User Menu Dropdown-->
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <img
                                src="../assets/img/user2-160x160.jpg"
                                class="user-image rounded-circle shadow"
                                alt="User Image" />
                            <span class="d-none d-md-inline"><?php echo $_SESSION['username'];  ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <!--begin::User Image-->
                            <li class="user-header text-bg-primary">
                                <img
                                    src="../assets/img/user2-160x160.jpg"
                                    class="rounded-circle shadow"
                                    alt="User Image" />
                                <p>
                                    <?php echo $_SESSION['username'];  ?> - Web Developer
                                    <small>Member since Nov. 2023</small>
                                </p>
                            </li>
                            <!--end::User Image-->
                            <!--begin::Menu Body-->
                            <li class="user-body">
                                <!--begin::Row-->
                                <div class="row">
                                    <div class="col-4 text-center"><a href="#">Followers</a></div>
                                    <div class="col-4 text-center"><a href="#">Sales</a></div>
                                    <div class="col-4 text-center"><a href="#">Friends</a></div>
                                </div>
                                <!--end::Row-->
                            </li>
                            <!--end::Menu Body-->
                            <!--begin::Menu Footer-->
                            <li class="user-footer">
                                <a href="#" class="btn btn-default btn-flat">Profile</a>
                                <a href="../examples/logout.php" class="btn btn-danger btn-flat float-end">Sign out</a>
                            </li>
                            <!--end::Menu Footer-->
                        </ul>
                    </li>
                    <!--end::User Menu Dropdown-->
                </ul>
                <!--end::End Navbar Links-->
            </div>
            <!--end::Container-->
        </nav>
        <!--end::Header-->
        <!--begin::Sidebar-->
        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
            <!--begin::Sidebar Brand-->
            <div class="sidebar-brand">
                <!--begin::Brand Link-->
                <a href="../index.php" class="brand-link">
                    <!--begin::Brand Image-->
                    <img
                        src="../assets/img/AdminLTELogo.png"
                        alt="AdminLTE Logo"
                        class="brand-image opacity-75 shadow" />
                    <!--end::Brand Image-->
                    <!--begin::Brand Text-->
                    <span class="brand-text fw-light">AdminLTE 4</span>
                    <!--end::Brand Text-->
                </a>
                <!--end::Brand Link-->
            </div>

            <div class="text-center m-1 justify-content-center d-flex">
                <div class="card col-10 bg-secondary">

                    <div class="text-light m-1">
                        <?php echo date('l, F j, Y') . "<br>"; ?>
                        <p id="clock" class="mb-0 text-center"></p>
                    </div>
                </div>
            </div>


            <!--end::Sidebar Brand-->
            <!--begin::Sidebar Wrapper-->
            <div class="sidebar-wrapper">
                <nav class="mt-2">
                    <!--begin::Sidebar Menu-->
                    <ul
                        class="nav sidebar-menu flex-column"
                        data-lte-toggle="treeview"
                        role="navigation"
                        aria-label="Main navigation"
                        data-accordion="false"
                        id="navigation">
                        <li class="nav-item">
                            <a href="#" class="nav-link active">
                                <i class="nav-icon bi bi-speedometer"></i>
                                <p>
                                    Dashboard
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li>
                                    <a href="#" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Dashboard 1</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Dashboard 2</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link active">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Dashboard 3</p>
                                    </a>
                                </li>
                            </ul>
                        </li>


                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-pencil-square"></i>
                                <p>
                                    Forms
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="../forms/general.php" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>General Elements</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-table"></i>
                                <p>
                                    Tables
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="user.php" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Users</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="country.php" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Country</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="state.php" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>State</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="city.php" class="nav-link active">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>City</p>
                                    </a>
                                </li>
                            </ul>
                        </li>


                        <li class="nav-header">EXAMPLES</li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-box-arrow-in-right"></i>
                                <p>
                                    Auth
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">

                                <li class="nav-item">
                                    <a href="../dist/examples/login.php" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Login</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="../dist/examples/register.html" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Register</p>
                                    </a>
                                </li>


                                <li class="nav-item">
                                    <a href="../dist/examples/lockscreen.html" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Lockscreen</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-header">MAILS</li>
                        <li class="nav-item">
                            <a href="uiemail.php" class="nav-link">
                                <i class="nav-icon bi bi-envelope"></i>
                                <p>Inbox</p>
                            </a>
                        </li>

                    </ul>
                    <!--end::Sidebar Menu-->
                </nav>
            </div>
            <!--end::Sidebar Wrapper-->
        </aside>
        <!--end::Sidebar-->
        <!--begin::App Main-->
        <main class="app-main">
            <!--begin::App Content Header-->
            <div class="app-content-header">
                <!--begin::Container-->

                <div class="container-fluid mb-3">
                    <!--begin::Row-->
                    <div class="row g-4">
                        <!--begin::Col-->
                        <div class="col-12">
                            <div class="callout callout-info">
                                <button type="button" class="btn btn-success w-25" data-bs-toggle="modal" data-bs-target="#uploadmodal">Add Product</button>

                            </div>
                        </div>

                    </div>
                    <!--end::Row-->
                </div>



                <div class="modal fade" id="uploadmodal" tabindex="-1"
                    aria-labelledby="modalTitle" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h4 class="modal-title" id="modalTitle">
                                    <i class="bi bi-box-seam me-2"></i>Add Product
                                </h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <form id="productForm" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="id" id="product_id">

                                    <label class="form-label"><b>Name</b></label>
                                    <input type="text" name="name" id="name" class="form-control mb-3" required>

                                    <div class="row">
                                        <div class="col-6">
                                            <label class="form-label"><b>Price</b></label>
                                            <input type="number" name="price" id="price" class="form-control mb-3" min="0" step="0.01" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label"><b>Stock</b></label>
                                            <input type="number" name="stock" id="stock" class="form-control mb-3" min="0" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <label class="form-label"><b>Status</b></label>
                                            <input type="text" name="status" id="status" class="form-control mb-3" min="0" step="0.01" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label"><b>Created date</b></label>
                                            <input type="date" name="created_date" id="created_date" class="form-control mb-3" min="0" required>
                                        </div>
                                    </div>


                                    <h6 class="text-muted mt-3">Existing Images</h6>
                                    <div id="existingImages" class="d-flex flex-wrap gap-2"></div>

                                    <h6 class="text-muted mt-3">Upload Images</h6>

                                    <div id="myDropzone" class="dropzone dropzone-wrapper">
                                        <div class="dz-message">
                                            <i class="bi bi-cloud-upload"></i>
                                            Drag & drop images here<br>
                                            <small class="fw-normal text-muted">or click to browse</small>
                                        </div>
                                    </div>

                                    <button type="submit" id="submitBtn" class="btn btn-success w-100 mt-3">
                                        <span class="spinner-border spinner-border-sm d-none" id="submitSpinner" role="status" aria-hidden="true"></span>
                                        <span id="submitBtnText">Save Product</span>
                                    </button>

                                </form>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="card">
                    <div class="card-header bg-success d-flex justify-content-between align-items-center">
                        <h2 class="text-light"><b>Products</b> </h2>
                        <span id="productCount" class="badge bg-light text-success fs-6">
                            <?php echo count($products_list); ?> product<?php echo count($products_list) !== 1 ? 's' : ''; ?>
                        </span>


                    </div>
                    <div class="m-2">
                        <table class="table table-bordered text-center border-dark" id="productsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Price (₹)</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Created Date</th>
                                    <th>Images</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-center align-middle">

                                <?php if (empty($products_list)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-box-seam fs-1 d-block mb-2 opacity-25"></i>
                                            No products found. Click <b>Add Product</b> to get started.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products_list as $p): ?>
                                        <tr>
                                            <td><?php echo $p['id']; ?></td>
                                            <td class="fw-semibold text-center"><?php echo htmlspecialchars($p['name']); ?></td>
                                            <td>&#8377;<?php echo number_format((float)$p['price'], 2); ?></td>
                                            <td>
                                                <?php if ($p['stock'] > 10): ?>
                                                    <span><b><?php echo $p['stock']; ?></b> <br>(Stock is avilable)</span>
                                                <?php elseif ($p['stock'] > 0): ?>
                                                    <span class="text-warning"><b><?php echo $p['stock']; ?></b> <br><b>(Stock is few)</b></span>
                                                <?php else: ?>
                                                    <span class="text-danger"><b><?php echo $p['stock']; ?></b> <br><b>Out of stock</b></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($p['status']); ?></td>
                                            <td><?php echo htmlspecialchars($p['created_date']); ?></td>

                                            <td>
                                                <?php if (!empty($p['first_image'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($p['first_image']); ?>"
                                                        alt="<?php echo htmlspecialchars($p['name']); ?>"
                                                        class="rounded shadow-sm product-thumb"
                                                        style="width:56px;height:56px;object-fit:cover;cursor:pointer;"
                                                        data-src="../<?php echo htmlspecialchars($p['first_image']); ?>"
                                                        title="Click to enlarge">
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="bi bi-image me-1"></i>No image
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <!-- Edit Button -->
                                                <button
                                                    class="btn btn-sm btn-outline-primary editBtn"
                                                    data-id="<?php echo $p['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($p['name']); ?>"
                                                    data-price="<?php echo $p['price']; ?>"
                                                    data-stock="<?php echo $p['stock']; ?>"
                                                    data-status="<?php echo $p['status']; ?>"
                                                    data-created_date="<?php echo $p['created_date']; ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                <!-- Delete Button -->
                                                <form method="POST" style="display:inline;"
                                                    onsubmit="return confirm('Delete product?');">
                                                    <input type="hidden" name="delete_product" value="1">
                                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                                    <button class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>

                        </table>
                    </div>
                </div>



                <!--end::App Content-->
        </main>
        <!--end::App Main-->
        <!--begin::Footer-->
        <footer class="app-footer">
            <!--begin::To the end-->
            <div class="float-end d-none d-sm-inline">Anything you want</div>
            <!--end::To the end-->
            <!--begin::Copyright-->
            <strong>
                Copyright &copy; 2014-2025&nbsp;
                <a href="https://adminlte.io" class="text-decoration-none">AdminLTE.io</a>.
            </strong>
            All rights reserved.
            <!--end::Copyright-->
        </footer>
        <!--end::Footer-->



    </div>
    <!--end::App Wrapper-->
    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
        src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
        crossorigin="anonymous"></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
        src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        crossorigin="anonymous"></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
        crossorigin="anonymous"></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="../js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
    <script>
        const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
        const Default = {
            scrollbarTheme: 'os-theme-light',
            scrollbarAutoHide: 'leave',
            scrollbarClickScroll: true,
        };
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
            if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined) {
                OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                    scrollbars: {
                        theme: Default.scrollbarTheme,
                        autoHide: Default.scrollbarAutoHide,
                        clickScroll: Default.scrollbarClickScroll,
                    },
                });
            }
        });
    </script>
    <!--end::OverlayScrollbars Configure-->

    </script>
    <!--end::Script-->
    <script>
        window.addEventListener("pageshow", function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>





    <script>
        // ── Clock ──────────────────────────────────────────────────────────────────
        function updateTime() {
            const opts = {
                timeZone: "Asia/Kolkata",
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
                hour12: true
            };
            document.getElementById("clock").innerHTML = new Intl.DateTimeFormat("en-IN", opts).format(new Date());
        }
        setInterval(updateTime, 1000);
        updateTime();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.js"
        crossorigin="anonymous"></script>

    <script>
        Dropzone.autoDiscover = false;

        let myDropzone = new Dropzone("#myDropzone", {
            url: window.location.pathname, // send to same PHP file
            autoProcessQueue: false,
            uploadMultiple: true,
            parallelUploads: 10,
            maxFilesize: 5,
            acceptedFiles: "image/*",
            addRemoveLinks: true,
            paramName: "file",

            init: function() {
                let dz = this;

                document.getElementById("productForm").addEventListener("submit", function(e) {
                    e.preventDefault();

                    let productId = document.getElementById("product_id").value;

                    if (dz.files.length === 0 && productId === "") {
                        alert("Please add at least one image");
                        return;
                    }

                    if (dz.files.length === 0 && productId !== "") {
                        toggleLoading(true);
                        document.getElementById("productForm").submit();
                        return;
                    }

                    toggleLoading(true);
                    dz.processQueue();
                });

                function toggleLoading(isLoading) {
                    const btn = document.getElementById("submitBtn");
                    const spinner = document.getElementById("submitSpinner");
                    const text = document.getElementById("submitBtnText");

                    if (isLoading) {
                        btn.disabled = true;
                        spinner.classList.remove("d-none");
                        text.innerText = " Saving...";
                    } else {
                        btn.disabled = false;
                        spinner.classList.add("d-none");
                        text.innerText = btn.dataset.mode === 'edit' ? "Update Product" : "Save Product";
                    }
                }

                this.on("totaluploadprogress", function(progress) {
                    // Progress bar removed as requested, using spinner on button instead
                });

                this.on("sendingmultiple", function(data, xhr, formData) {
                    formData.append("id", document.getElementById("product_id").value);

                    formData.append("name", document.getElementById("name").value);
                    formData.append("price", document.getElementById("price").value);
                    formData.append("stock", document.getElementById("stock").value);
                    formData.append("status", document.getElementById("status").value);
                    formData.append("created_date", document.getElementById("created_date").value);
                });
                this.on("successmultiple", function() {
                    location.reload();
                });

                this.on("errormultiple", function(file, response) {
                    toggleLoading(false);
                    alert("Upload failed: " + response);
                });
            }
        });
    </script>

    <script>
        let modal = new bootstrap.Modal(document.getElementById('uploadmodal'));

        // EDIT BUTTON CLICK
        document.querySelectorAll(".editBtn").forEach(btn => {
            btn.addEventListener("click", function() {

                let id = this.dataset.id;

                // Fill data
                document.getElementById("product_id").value = id;
                document.getElementById("name").value = this.dataset.name;
                document.getElementById("price").value = this.dataset.price;
                document.getElementById("stock").value = this.dataset.stock;
                document.getElementById("status").value = this.dataset.status;
                document.getElementById("created_date").value = this.dataset.created_date;

                document.getElementById("modalTitle").innerText = "Edit Product";
                document.getElementById("submitBtnText").innerText = "Update Product";
                document.getElementById("submitBtn").dataset.mode = 'edit';

                loadImages(id);
                modal.show();
            });
        });

        // RESET FORM WHEN OPENING ADD
        document.querySelector('[data-bs-target="#uploadmodal"]').addEventListener("click", function() {

            document.getElementById("productForm").reset();
            document.getElementById("product_id").value = "";
            document.getElementById("existingImages").innerHTML = ""; // 🔥 Clear old images

            myDropzone.removeAllFiles(true); // 🔥 clear old files

            document.getElementById("modalTitle").innerText = "Add Product";
            document.getElementById("submitBtnText").innerText = "Save Product";
            document.getElementById("submitBtn").dataset.mode = 'add';
        });

        function deleteImage(id) {
            if (!confirm("Delete this image?")) return;

            fetch(`?delete_image=1&id=${id}`)
                .then(() => {
                    // Remove image from UI without reload
                    loadImages(document.getElementById("product_id").value);
                });
        }

        function loadImages(productId) {
            fetch(`?get_images=1&id=${productId}`)
                .then(res => res.json())
                .then(data => {
                    let container = document.getElementById("existingImages");
                    container.innerHTML = "";

                    data.forEach(img => {
                        container.innerHTML += `
                    <div style="position:relative;">
                        <img src="../${img.image_path}" 
                             style="width:80px;height:80px;object-fit:cover;border-radius:6px;">
                        
                        <button type="button" onclick="deleteImage(${img.id})"
                            style="position:absolute;top:0;right:0;background:red;color:#fff;border:none;">
                            ×
                        </button>
                    </div>
                `;
                    });
                });
        }
    </script>



</body>
<!--end::Body-->

</html>