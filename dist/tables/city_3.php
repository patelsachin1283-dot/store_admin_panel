<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../examples/login.php");
    exit();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

include '../db.php';

// ── Handle product delete ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $id  = (int) $_POST['id'];
    $sql = "DELETE FROM products WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        mysqli_query($conn, "DELETE FROM product_images WHERE product_id = $id");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    }
    exit();
}

// ── Fetch products list with first image ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'fetch_products') {
    $sql = "
        SELECT p.id, p.name, p.price, p.stock,
               (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.id ASC LIMIT 1) AS first_image
        FROM products p
        ORDER BY p.id DESC
    ";
    $result   = mysqli_query($conn, $sql);
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    echo json_encode(['success' => true, 'products' => $products]);
    exit();
}

// ── Handle product insert ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_submit'])) {
    $name  = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $stock = mysqli_real_escape_string($conn, $_POST['stock']);

    $sql = "INSERT INTO products (name, price, stock) VALUES ('$name', '$price', '$stock')";
    if (mysqli_query($conn, $sql)) {
        $product_id = mysqli_insert_id($conn);
        echo json_encode(['success' => true, 'product_id' => $product_id]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    }
    exit();
}

// ── Handle image upload via Dropzone ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && isset($_FILES['file'])) {
    $product_id = (int) $_POST['product_id'];
    $upload_dir = '../uploads/products/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file    = $_FILES['file'];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'error' => 'Invalid file type']);
        exit();
    }

    $filename = uniqid('img_') . '.' . $ext;
    $dest     = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        $db_path = 'uploads/products/' . $filename;
        $sql     = "INSERT INTO product_images (product_id, image_path) VALUES ($product_id, '$db_path')";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true, 'path' => $db_path]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Upload failed']);
    }
    exit();
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>AdminLTE | Products</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        crossorigin="anonymous" media="print" onload="this.media='all'" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
        crossorigin="anonymous" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        crossorigin="anonymous" />
    <link rel="stylesheet" href="../css/adminlte.css" />
    <!-- Dropzone CSS -->
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
        #uploadStatus { display: none; }
    </style>
</head>

<body class="layout-fixed fixed-header sidebar-expand-lg sidebar-open bg-body-tertiary">
<div class="app-wrapper">

    <!-- ── Header ── -->
    <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                        <i class="bi bi-list"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-md-block"><a href="#" class="nav-link">Home</a></li>
                <li class="nav-item d-none d-md-block"><a href="#" class="nav-link">Contact</a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                        <i class="bi bi-search"></i>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link" data-bs-toggle="dropdown" href="#">
                        <i class="bi bi-chat-text"></i>
                        <span class="navbar-badge badge text-bg-danger">3</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                        <a href="#" class="dropdown-item">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <img src="../assets/img/user1-128x128.jpg" alt="User Avatar"
                                        class="img-size-50 rounded-circle me-3" />
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="dropdown-item-title">Brad Diesel
                                        <span class="float-end fs-7 text-danger"><i class="bi bi-star-fill"></i></span>
                                    </h3>
                                    <p class="fs-7">Call me whenever you can...</p>
                                    <p class="fs-7 text-secondary"><i class="bi bi-clock-fill me-1"></i> 4 Hours Ago</p>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
                    </div>
                </li>
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
                        <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                        <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                        <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display:none"></i>
                    </a>
                </li>
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <img src="../assets/img/user2-160x160.jpg"
                            class="user-image rounded-circle shadow" alt="User Image" />
                        <span class="d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                        <li class="user-header text-bg-primary">
                            <img src="../assets/img/user2-160x160.jpg" class="rounded-circle shadow" alt="User Image" />
                            <p>
                                <?php echo htmlspecialchars($_SESSION['username']); ?> - Web Developer
                                <small>Member since Nov. 2023</small>
                            </p>
                        </li>
                        <li class="user-body">
                            <div class="row">
                                <div class="col-4 text-center"><a href="#">Followers</a></div>
                                <div class="col-4 text-center"><a href="#">Sales</a></div>
                                <div class="col-4 text-center"><a href="#">Friends</a></div>
                            </div>
                        </li>
                        <li class="user-footer">
                            <a href="#" class="btn btn-default btn-flat">Profile</a>
                            <a href="../examples/logout.php" class="btn btn-danger btn-flat float-end">Sign out</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <!-- ── Sidebar ── -->
    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="../index.php" class="brand-link">
                <img src="../assets/img/AdminLTELogo.png" alt="AdminLTE Logo"
                    class="brand-image opacity-75 shadow" />
                <span class="brand-text fw-light">AdminLTE 4</span>
            </a>
        </div>

        <div class="text-center m-1 justify-content-center d-flex">
            <div class="card col-10 bg-secondary">
                <!-- FIX 1: removed invalid <li> tag inside a <div> -->
                <div class="text-light m-1">
                    <?php echo date('l, F j, Y') . "<br>"; ?>
                    <p id="clock" class="mb-0 text-center"></p>
                </div>
            </div>
        </div>

        <div class="sidebar-wrapper">
            <nav class="mt-2">
                <!-- FIX 2: corrected broken treeview structure (missing <li> wrappers on <a> tags) -->
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview"
                    role="navigation" aria-label="Main navigation" data-accordion="false">

                    <li class="nav-item">
                        <a href="#" class="nav-link active">
                            <i class="nav-icon bi bi-speedometer"></i>
                            <p>Dashboard <i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="#" class="nav-link"><i class="nav-icon bi bi-circle"></i><p>Dashboard 1</p></a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link"><i class="nav-icon bi bi-circle"></i><p>Dashboard 2</p></a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link"><i class="nav-icon bi bi-circle"></i><p>Dashboard 3</p></a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon bi bi-pencil-square"></i>
                            <p>Forms <i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="../forms/general.php" class="nav-link">
                                    <i class="nav-icon bi bi-circle"></i><p>General Elements</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon bi bi-table"></i>
                            <p>Tables <i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="user.php" class="nav-link"><i class="nav-icon bi bi-circle"></i><p>Users</p></a></li>
                            <li class="nav-item"><a href="country.php" class="nav-link"><i class="nav-icon bi bi-circle"></i><p>Country</p></a></li>
                            <li class="nav-item"><a href="state.php" class="nav-link"><i class="nav-icon bi bi-circle"></i><p>State</p></a></li>
                            <li class="nav-item"><a href="city.php" class="nav-link active"><i class="nav-icon bi bi-circle"></i><p>City</p></a></li>
                        </ul>
                    </li>

                    <li class="nav-header">EXAMPLES</li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon bi bi-box-arrow-in-right"></i>
                            <p>Auth <i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="../dist/examples/login.php" class="nav-link"><i class="nav-icon bi bi-circle"></i><p>Login</p></a></li>
                            <li class="nav-item"><a href="../dist/examples/register.html" class="nav-link"><i class="nav-icon bi bi-circle"></i><p>Register</p></a></li>
                            <li class="nav-item"><a href="../dist/examples/lockscreen.html" class="nav-link"><i class="nav-icon bi bi-circle"></i><p>Lockscreen</p></a></li>
                        </ul>
                    </li>

                </ul>
            </nav>
        </div>
    </aside>

    <!-- ── Main ── -->
    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid mb-3">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="callout callout-info">
                            <button type="button" class="btn btn-success w-25"
                                data-bs-toggle="modal" data-bs-target="#uploadmodal">
                                Add Product
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FIX 3: added "fade" class so Bootstrap modal animation works -->
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

                            <!-- Step 1 -->
                            <div id="step1">
                                <h6 class="text-muted mb-3">Step 1 — Product Details</h6>

                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" id="modal_name" class="form-control mb-3"
                                    placeholder="Enter product name">

                                <label class="form-label">Price <span class="text-danger">*</span></label>
                                <input type="number" id="modal_price" class="form-control mb-3"
                                    placeholder="Enter product price" min="0" step="0.01">

                                <label class="form-label">Stock <span class="text-danger">*</span></label>
                                <input type="number" id="modal_stock" class="form-control mb-3"
                                    placeholder="Enter product stock" min="0">

                                <button type="button" id="saveProductBtn" class="btn btn-primary w-100 mt-2">
                                    <i class="bi bi-arrow-right-circle me-1"></i> Save &amp; Upload Images
                                </button>
                            </div>

                            <!-- Step 2 -->
                            <div id="step2" style="display:none;">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="badge bg-success me-2"><i class="bi bi-check-lg"></i></span>
                                    <span id="savedProductName" class="fw-semibold text-success"></span>
                                    <span class="text-muted ms-2 small">saved successfully!</span>
                                </div>

                                <h6 class="text-muted mb-3">Step 2 — Upload Product Images</h6>
                                <p class="small text-muted">Drag &amp; drop or click. JPG, PNG, GIF, WEBP — max 5 MB each.</p>

                                <div id="myDropzone" class="dropzone dropzone-wrapper">
                                    <div class="dz-message">
                                        <i class="bi bi-cloud-upload"></i>
                                        Drag &amp; drop images here<br>
                                        <small class="fw-normal text-muted">or click to browse</small>
                                    </div>
                                </div>

                                <div id="uploadStatus" class="alert alert-success mt-3">
                                    <i class="bi bi-check-circle me-1"></i>
                                    <span id="uploadStatusText"></span>
                                </div>

                                <div class="d-flex gap-2 mt-3">
                                    <button type="button" id="addAnotherBtn" class="btn btn-outline-secondary">
                                        <i class="bi bi-plus-circle me-1"></i> Add Another Product
                                    </button>
                                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                                        <i class="bi bi-check2-all me-1"></i> Done
                                    </button>
                                </div>
                            </div>

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
                    <h2 class="text-light mb-0"><b>Products</b></h2>
                    <span id="productCount" class="badge bg-light text-success fs-6">0 products</span>
                </div>
                <div class="m-2">
                    <table class="table table-bordered table-hover text-center border-dark align-middle" id="productsTable">
                        <thead class="table-dark">
                            <tr>
                                <th>#ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Price (₹)</th>
                                <th>Stock</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="productTableBody">
                            <tr id="loadingRow">
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <div class="spinner-border spinner-border-sm me-2"></div> Loading products...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">Anything you want</div>
        <strong>Copyright &copy; 2014-2025&nbsp;
            <a href="https://adminlte.io" class="text-decoration-none">AdminLTE.io</a>.
        </strong>
        All rights reserved.
    </footer>

</div>

<!-- ── Scripts ── -->
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
    crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
    crossorigin="anonymous"></script>
<script src="../js/adminlte.js"></script>

<!-- FIX 4: Dropzone JS must be loaded BEFORE any Dropzone code runs -->
<script src="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.js"
    crossorigin="anonymous"></script>

<script>
// ── OverlayScrollbars ──────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const sw = document.querySelector('.sidebar-wrapper');
    if (sw && OverlayScrollbarsGlobal?.OverlayScrollbars) {
        OverlayScrollbarsGlobal.OverlayScrollbars(sw, {
            scrollbars: { theme: 'os-theme-light', autoHide: 'leave', clickScroll: true }
        });
    }
});

// FIX 5: only ONE updateTime() function (original code had two — caused redeclaration)
function updateTime() {
    const opts = {
        timeZone: "Asia/Kolkata",
        hour: "2-digit", minute: "2-digit", second: "2-digit", hour12: true
    };
    document.getElementById("clock").innerHTML =
        new Intl.DateTimeFormat("en-IN", opts).format(new Date());
}
setInterval(updateTime, 1000);
updateTime();

// FIX 6: autoDiscover must be set BEFORE DOMContentLoaded, set it immediately
Dropzone.autoDiscover = false;

// ── State ──────────────────────────────────────────────────────────────────
let currentProductId = null;
let myDropzone       = null;
let uploadedCount    = 0;

// ── Init Dropzone ──────────────────────────────────────────────────────────
function initDropzone() {
    if (myDropzone) {
        myDropzone.destroy();
        myDropzone = null;
    }

    myDropzone = new Dropzone("#myDropzone", {
        url: window.location.pathname,
        paramName: "file",
        maxFilesize: 5,
        acceptedFiles: "image/*",
        addRemoveLinks: true,
        dictRemoveFile: "✕ Remove",
        dictDefaultMessage: "",
        autoProcessQueue: true,
        parallelUploads: 3,

        sending: function (file, xhr, formData) {
            formData.append("product_id", currentProductId);
        },

        success: function (file, response) {
            // FIX 7: safe JSON parse — server may return string or object
            let res = response;
            if (typeof response === 'string') {
                try { res = JSON.parse(response); }
                catch (e) { res = { success: false, error: 'Invalid server response' }; }
            }
            if (res.success) {
                uploadedCount++;
                document.getElementById("uploadStatus").style.display = "block";
                document.getElementById("uploadStatusText").textContent =
                    uploadedCount + " image(s) uploaded successfully.";
            } else {
                file.previewElement.classList.add("dz-error");
                const errSpan = file.previewElement.querySelector(".dz-error-message span");
                if (errSpan) errSpan.textContent = res.error || "Upload failed";
                console.error("Upload error:", res.error);
            }
        },

        error: function (file, message) {
            console.error("Dropzone error:", message);
        },

        queuecomplete: function () {
            if (uploadedCount > 0) {
                document.getElementById("uploadStatus").style.display = "block";
                document.getElementById("uploadStatusText").textContent =
                    "All done! " + uploadedCount + " image(s) uploaded.";
            }
        }
    });
}

// ── Save Product ───────────────────────────────────────────────────────────
document.getElementById("saveProductBtn").addEventListener("click", function () {
    const name  = document.getElementById("modal_name").value.trim();
    const price = document.getElementById("modal_price").value.trim();
    const stock = document.getElementById("modal_stock").value.trim();

    if (!name || !price || !stock) {
        alert("Please fill in all fields.");
        return;
    }

    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

    const formData = new FormData();
    formData.append("name",          name);
    formData.append("price",         price);
    formData.append("stock",         stock);
    formData.append("upload_submit", "1");

    fetch(window.location.pathname, { method: "POST", body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                currentProductId = data.product_id;
                uploadedCount    = 0;

                document.getElementById("step1").style.display          = "none";
                document.getElementById("step2").style.display          = "block";
                document.getElementById("savedProductName").textContent = name;
                document.getElementById("uploadStatus").style.display   = "none";

                initDropzone();
            } else {
                alert("Error saving product: " + (data.error || "Unknown error"));
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-right-circle me-1"></i> Save &amp; Upload Images';
            }
        })
        .catch(err => {
            alert("Network error: " + err.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-right-circle me-1"></i> Save &amp; Upload Images';
        });
});

// ── Reset ──────────────────────────────────────────────────────────────────
document.getElementById("uploadmodal").addEventListener("hidden.bs.modal", resetModal);
document.getElementById("addAnotherBtn").addEventListener("click", resetModal);

function resetModal() {
    currentProductId = null;
    uploadedCount    = 0;

    document.getElementById("modal_name").value  = "";
    document.getElementById("modal_price").value = "";
    document.getElementById("modal_stock").value = "";

    document.getElementById("step1").style.display        = "block";
    document.getElementById("step2").style.display        = "none";
    document.getElementById("uploadStatus").style.display = "none";

    const btn = document.getElementById("saveProductBtn");
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-arrow-right-circle me-1"></i> Save &amp; Upload Images';

    if (myDropzone) {
        myDropzone.destroy();
        myDropzone = null;
    }
}

window.addEventListener("pageshow", function (e) {
    if (e.persisted) window.location.reload();
});
</script>
<!-- ── Image Preview Modal ── -->
<div class="modal fade" id="imgPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Product Image</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-2">
                <img id="previewImg" src="" alt="Product" class="img-fluid rounded" style="max-height:300px;">
            </div>
        </div>
    </div>
</div>

<script>
// ── Load Products Table ────────────────────────────────────────────────────
function loadProducts() {
    fetch(window.location.pathname + '?action=fetch_products')
        .then(r => r.json())
        .then(data => {
            const tbody      = document.getElementById('productTableBody');
            const countBadge = document.getElementById('productCount');

            if (!data.success || data.products.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-box-seam fs-1 d-block mb-2 opacity-25"></i>
                            No products found. Click <b>Add Product</b> to get started.
                        </td>
                    </tr>`;
                countBadge.textContent = '0 products';
                return;
            }

            countBadge.textContent = data.products.length + ' product' + (data.products.length !== 1 ? 's' : '');

            tbody.innerHTML = data.products.map(p => {
                const imgHtml = p.first_image
                    ? `<img src="../${p.first_image}" alt="${escHtml(p.name)}"
                            class="rounded shadow-sm product-thumb"
                            style="width:56px;height:56px;object-fit:cover;cursor:pointer;"
                            data-src="../${p.first_image}"
                            title="Click to enlarge">`
                    : `<span class="badge bg-secondary"><i class="bi bi-image me-1"></i>No image</span>`;

                const stockBadge = p.stock > 10
                    ? `<span class="badge bg-success">${p.stock}</span>`
                    : p.stock > 0
                        ? `<span class="badge bg-warning text-dark">${p.stock}</span>`
                        : `<span class="badge bg-danger">Out of stock</span>`;

                return `
                    <tr>
                        <td><span class="badge bg-secondary">#${p.id}</span></td>
                        <td>${imgHtml}</td>
                        <td class="fw-semibold text-start">${escHtml(p.name)}</td>
                        <td>&#8377;${parseFloat(p.price).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                        <td>${stockBadge}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary me-1"
                                onclick="editProduct(${p.id})" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger"
                                onclick="deleteProduct(${p.id}, this)" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>`;
            }).join('');

            // Thumbnail click → image preview modal
            document.querySelectorAll('.product-thumb').forEach(img => {
                img.addEventListener('click', function () {
                    document.getElementById('previewImg').src = this.dataset.src;
                    new bootstrap.Modal(document.getElementById('imgPreviewModal')).show();
                });
            });
        })
        .catch(err => {
            document.getElementById('productTableBody').innerHTML =
                `<tr><td colspan="6" class="text-center text-danger py-3">
                    <i class="bi bi-exclamation-triangle me-1"></i> Failed to load products.
                </td></tr>`;
            console.error(err);
        });
}

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function editProduct(id) {
    alert('Edit product #' + id + ' — wire up your edit logic here.');
}

function deleteProduct(id, btn) {
    if (!confirm('Delete product #' + id + '? This cannot be undone.')) return;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    const fd = new FormData();
    fd.append('delete_product', '1');
    fd.append('id', id);

    fetch(window.location.pathname, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                loadProducts();
            } else {
                alert('Delete failed: ' + (data.error || 'Unknown error'));
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-trash"></i>';
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-trash"></i>';
        });
}

// Reload table after product modal closes (new product may have been added)
document.getElementById("uploadmodal").addEventListener("hidden.bs.modal", loadProducts);

// Initial load on page ready
loadProducts();
</script>

</body>
</html>
