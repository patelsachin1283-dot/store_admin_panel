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

include '../db1.php';




if (isset($_POST['upload_submit'])) {
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $image = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $image = basename($_FILES['image']['name']);
        $tmp   = $_FILES['image']['tmp_name'];

        $allowed_mime = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        $file_mime = mime_content_type($tmp);
        if (!in_array($file_mime, $allowed_mime)) {
            die("File type not allowed. Detected: " . $file_mime);
        }

        // ✅ Save images INSIDE your project folder (no permission issues)
        $upload_dir = __DIR__ . "/images/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // ✅ Give unique name to avoid overwrite
        $image = time() . '_' . $image;
        $destination = $upload_dir . $image;

        if (!move_uploaded_file($tmp, $destination)) {
            die("Upload failed. Path tried: " . $destination);
        }
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO image (name, email, image) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $name, $email, $image);
    mysqli_stmt_execute($stmt);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
if (isset($_POST['editBtn'])) {
    $edit_id = $_POST['edit_id'];
    $name    = $_POST['name'];
    $email   = $_POST['email'];

    if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] === 0) {
        $image = basename($_FILES['edit_image']['name']);
        $tmp   = $_FILES['edit_image']['tmp_name'];

        $allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_mime = mime_content_type($tmp);
        if (!in_array($file_mime, $allowed_mime)) {
            die("File type not allowed. Detected: " . $file_mime);
        }

        $upload_dir = __DIR__ . "/images/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $image = time() . '_' . $image;

        if (!move_uploaded_file($tmp, $upload_dir . $image)) {
            die("Upload failed. Path: " . $upload_dir . $image .
                " | Writable: " . (is_writable($upload_dir) ? 'YES' : 'NO'));
        }

        $stmt = mysqli_prepare($conn, "UPDATE image SET name=?, email=?, image=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $image, $edit_id);
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE image SET name=?, email=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssi", $name, $email, $edit_id);
    }

    mysqli_stmt_execute($stmt);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = mysqli_prepare($conn, "DELETE FROM image WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $delete_id);
    mysqli_stmt_execute($stmt);
    header("Location: " . $_SERVER['PHP_SELF']);
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



    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->
    <!--begin::Primary Meta Tags-->
    <meta name="title" content="AdminLTE | Dashboard v2" />
    <meta name="author" content="ColorlibHQ" />
    <meta
        name="description"
        content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS. Fully accessible with WCAG 2.1 AA compliance." />
    <meta
        name="keywords"
        content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel, WCAG compliant" />
    <!--end::Primary Meta Tags-->
    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="../css/adminlte.css" as="style" />
    <!--end::Accessibility Features-->
    <!--begin::Fonts-->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
        crossorigin="anonymous"
        media="print"
        onload="this.media='all'" />
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
        crossorigin="anonymous" />
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        crossorigin="anonymous" />
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="../css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->
    <!-- apexcharts -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
        integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0="
        crossorigin="anonymous" />

    <style>
        .heading-font {
            font-family: 'Poppins', sans-serif;
        }

        .body-font {
            font-family: 'Roboto', sans-serif;
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
                        <a class="nav-link" data-bs-toggle="dropdown" href="#">
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
                            <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
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

                    <li class="nav-item d-none d-md-block text-light m-1">
                        <?php echo date('l, F j, Y') . "<br>";   ?>
                        <p id="clock" class="mb-0 text-center"></p>
                    </li>
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
                                <button type="button" class="btn btn-success w-25" data-bs-toggle="modal" data-bs-target="#uploadmodal">Add Person</button>

                            </div>
                        </div>

                    </div>
                    <!--end::Row-->
                </div>



                <!-- Country Modal   -->
                <div class="modal" id="uploadmodal">
                    <div class="modal-dialog">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h4 class="modal-title" id="modalTitle">Add Person</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="edit_id" id="edit_id" value="">
                                    <input type="hidden" name="delete_id" id="delete_id" value="">

                                    <label>Name</label>
                                    <input type="text" name="name" id="modal_name" class="form-control mb-3" placeholder="Enter Your Name">

                                    <label>Email</label>
                                    <input type="text" name="email" id="modal_email" class="form-control mb-3" placeholder="Enter Your Email">


                                    <div id="fileSection">
                                        <label>Image</label>
                                        <input type="file" name="image" class="form-control mb-3">
                                    </div>

                                    <div id="currentImageSection" style="display:none;" class="mb-3">
                                        <label>Current Image</label><br>
                                        <img id="currentImage" src="" width="80" height="80"
                                            style="object-fit:cover; border-radius:6px; border:1px solid #ccc;">
                                        <p class="text-muted mt-1" style="font-size:12px;">
                                            Upload new file to replace, or leave empty to keep current.
                                        </p>
                                        <input type="file" name="edit_image" class="form-control mt-2">
                                    </div>

                                    <button type="submit" name="upload_submit" id="addBtn" class="btn btn-primary w-100 mt-3">Submit</button>
                                    <button type="submit" name="editBtn" id="editBtn"
                                        class="btn btn-warning w-100" style="display:none;">Update</button>
                                </form>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-success">
                        <h2 class="text-light"><b>Upload Image</b></h2>
                    </div>
                    <div class="m-2">
                        <table class="table table-bordered text-center border-dark">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Image</th>
                                    <th>Action</th>
                                </tr>
                            </thead>


                            <tbody>
                                <?php
                                $sql = mysqli_query($conn, "SELECT * FROM image ORDER BY id ASC");

                                if (mysqli_num_rows($sql) > 0) {
                                    while ($row = mysqli_fetch_assoc($sql)) { ?>

                                        <tr>
                                            <td><?= $row['id'] ?></td>
                                            <td><?= $row['name'] ?></td>
                                            <td><?= $row['email'] ?></td>
                                            <td>
                                                <img src="images/<?= htmlspecialchars($row['image']) ?>"
                                                    width="50" height="50"
                                                    style="object-fit:cover; border-radius:6px;"
                                                    onclick="openImage(this.src)"
                                                    >
                                            </td>
                                            <td>
                                                <!-- ✅ Pass row data via data attributes -->
                                                <button type="button" data-bs-toggle="modal" data-bs-target="#uploadmodal"
                                                    class="btn btn-success btn-sm edit-btn"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-name="<?= htmlspecialchars($row['name']) ?>"
                                                    data-email="<?= htmlspecialchars($row['email']) ?>"
                                                    data-image="<?= htmlspecialchars($row['image']) ?>">
                                                    Edit
                                                </button>

                                                <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="if(confirm('Are you sure?')) window.location='?delete_id=<?= $row['id'] ?>'">
                                                    Delete
                                                </button>

                                            </td>
                                        </tr>
                                <?php
                                    }
                                }  ?>
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
    <!-- OPTIONAL SCRIPTS -->
    <!-- apexcharts -->
    <script
        src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
        integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8="
        crossorigin="anonymous"></script>
    <script>
        // NOTICE!! DO NOT USE ANY OF THIS JAVASCRIPT
        // IT'S ALL JUST JUNK FOR DEMO
        // ++++++++++++++++++++++++++++++++++++++++++

        /* apexcharts
         * -------
         * Here we will create a few charts using apexcharts
         */

        //-----------------------
        // - MONTHLY SALES CHART -
        //-----------------------

        const sales_chart_options = {
            series: [{
                    name: 'Digital Goods',
                    data: [28, 48, 40, 19, 86, 27, 90],
                },
                {
                    name: 'Electronics',
                    data: [65, 59, 80, 81, 56, 55, 40],
                },
            ],
            chart: {
                height: 180,
                type: 'area',
                toolbar: {
                    show: false,
                },
            },
            legend: {
                show: false,
            },
            colors: ['#0d6efd', '#20c997'],
            dataLabels: {
                enabled: false,
            },
            stroke: {
                curve: 'smooth',
            },
            xaxis: {
                type: 'datetime',
                categories: [
                    '2023-01-01',
                    '2023-02-01',
                    '2023-03-01',
                    '2023-04-01',
                    '2023-05-01',
                    '2023-06-01',
                    '2023-07-01',
                ],
            },
            tooltip: {
                x: {
                    format: 'MMMM yyyy',
                },
            },
        };

        const sales_chart = new ApexCharts(
            document.querySelector('#sales-chart'),
            sales_chart_options,
        );
        sales_chart.render();

        //---------------------------
        // - END MONTHLY SALES CHART -
        //---------------------------

        function createSparklineChart(selector, data) {
            const options = {
                series: [{
                    data
                }],
                chart: {
                    type: 'line',
                    width: 150,
                    height: 30,
                    sparkline: {
                        enabled: true,
                    },
                },
                colors: ['var(--bs-primary)'],
                stroke: {
                    width: 2,
                },
                tooltip: {
                    fixed: {
                        enabled: false,
                    },
                    x: {
                        show: false,
                    },
                    y: {
                        title: {
                            formatter() {
                                return '';
                            },
                        },
                    },
                    marker: {
                        show: false,
                    },
                },
            };

            const chart = new ApexCharts(document.querySelector(selector), options);
            chart.render();
        }

        const table_sparkline_1_data = [25, 66, 41, 89, 63, 25, 44, 12, 36, 9, 54];
        const table_sparkline_2_data = [12, 56, 21, 39, 73, 45, 64, 52, 36, 59, 44];
        const table_sparkline_3_data = [15, 46, 21, 59, 33, 15, 34, 42, 56, 19, 64];
        const table_sparkline_4_data = [30, 56, 31, 69, 43, 35, 24, 32, 46, 29, 64];
        const table_sparkline_5_data = [20, 76, 51, 79, 53, 35, 54, 22, 36, 49, 64];
        const table_sparkline_6_data = [5, 36, 11, 69, 23, 15, 14, 42, 26, 19, 44];
        const table_sparkline_7_data = [12, 56, 21, 39, 73, 45, 64, 52, 36, 59, 74];

        createSparklineChart('#table-sparkline-1', table_sparkline_1_data);
        createSparklineChart('#table-sparkline-2', table_sparkline_2_data);
        createSparklineChart('#table-sparkline-3', table_sparkline_3_data);
        createSparklineChart('#table-sparkline-4', table_sparkline_4_data);
        createSparklineChart('#table-sparkline-5', table_sparkline_5_data);
        createSparklineChart('#table-sparkline-6', table_sparkline_6_data);
        createSparklineChart('#table-sparkline-7', table_sparkline_7_data);

        //-------------
        // - PIE CHART -
        //-------------

        const pie_chart_options = {
            series: [700, 500, 400, 600, 300, 100],
            chart: {
                type: 'donut',
            },
            labels: ['Chrome', 'Edge', 'FireFox', 'Safari', 'Opera', 'IE'],
            dataLabels: {
                enabled: false,
            },
            colors: ['#0d6efd', '#20c997', '#ffc107', '#d63384', '#6f42c1', '#adb5bd'],
        };

        const pie_chart = new ApexCharts(document.querySelector('#pie-chart'), pie_chart_options);
        pie_chart.render();

        //-----------------
        // - END PIE CHART -
        //-----------------
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
        function updateTime() {
            // Create new date object
            let now = new Date();

            // Convert to IST using locale
            let options = {
                timeZone: "Asia/Kolkata",
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
                hour12: true
            };

            let istTime = new Intl.DateTimeFormat("en-IN", options).format(now);

            document.getElementById("clock").innerHTML = istTime;
        }

        // Run every second
        setInterval(updateTime, 1000);

        // Run immediately on load
        updateTime();
    </script>


    <script>
        // ✅ Edit button clicked — fill modal with existing data + show current image
        document.querySelectorAll('.edit-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('modalTitle').innerText = 'Edit Person';
                document.getElementById('edit_id').value = this.dataset.id;
                document.getElementById('modal_name').value = this.dataset.name;
                document.getElementById('modal_email').value = this.dataset.email;

                // ✅ Show current image preview
                document.getElementById('currentImage').src = 'images/' + this.dataset.image;
                document.getElementById('currentImageSection').style.display = 'block';

                // ✅ Hide add file section, show edit buttons
                document.getElementById('fileSection').style.display = 'none';
                document.getElementById('addBtn').style.display = 'none';
                document.getElementById('editBtn').style.display = 'block';
            });
        });

        // ✅ Add button clicked — reset modal to blank
        document.querySelector('[data-bs-target="#uploadmodal"]:not(.edit-btn)')
            .addEventListener('click', function() {
                document.getElementById('modalTitle').innerText = 'Add Person';
                document.getElementById('edit_id').value = '';
                document.getElementById('modal_name').value = '';
                document.getElementById('modal_email').value = '';
                document.getElementById('currentImageSection').style.display = 'none';
                document.getElementById('fileSection').style.display = 'block';
                document.getElementById('addBtn').style.display = 'block';
                document.getElementById('editBtn').style.display = 'none';
                // ✅ Also clear preview here
                const preview = document.getElementById('addImagePreview');
                if (preview) preview.remove();
            });


        // ✅ Add mode — image preview when file selected
        document.querySelector('#fileSection input[type="file"]')
            .addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Check if preview img exists, if not create it
                        let preview = document.getElementById('addImagePreview');
                        if (!preview) {
                            preview = document.createElement('img');
                            preview.id = 'addImagePreview';
                            preview.width = 80;
                            preview.height = 80;
                            preview.style = 'object-fit:cover; border-radius:6px; border:1px solid #ccc; margin-top:8px; display:block;';
                            document.getElementById('fileSection').appendChild(preview);
                        }
                        preview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });

        // ✅ Edit mode — image preview when new file selected
        document.querySelector('#currentImageSection input[type="file"]')
            .addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('currentImage').src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });

        // ✅ Clear add preview when modal resets
        document.querySelector('[data-bs-target="#uploadmodal"]:not(.edit-btn)')
            .addEventListener('click', function() {
                const preview = document.getElementById('addImagePreview');
                if (preview) preview.remove();
            });
    </script>
<!-- Image Preview Modal -->
<div id="imageModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); justify-content:center; align-items:center; z-index:9999;">
    
    <span onclick="closeImage()" style="position:absolute; top:20px; right:30px; font-size:30px; color:white; cursor:pointer;">&times;</span>
    
    <img id="modalImg" style="max-width:90%; max-height:90%; border-radius:10px;">
</div>


<script>
function openImage(src) {
    document.getElementById("imageModal").style.display = "flex";
    document.getElementById("modalImg").src = src;
}

function closeImage() {
    document.getElementById("imageModal").style.display = "none";
}
document.getElementById("imageModal").addEventListener("click", function(e) {
    if (e.target.id === "imageModal") {
        closeImage();
    }
});
</script>


<script>
let scale = 1;

const modalImg = document.getElementById("modalImg");

modalImg.addEventListener("wheel", function(e) {
    e.preventDefault();

    if (e.deltaY < 0) {
        scale += 0.1; // zoom in
    } else {
        scale -= 0.1; // zoom out
    }

    // limit zoom
    if (scale < 1) scale = 1;
    if (scale > 5) scale = 5;

    modalImg.style.transform = "scale(" + scale + ")";
});

// reset zoom when closing
function closeImage() {
    document.getElementById("imageModal").style.display = "none";
    scale = 1;
    modalImg.style.transform = "scale(1)";
}
</script>



</body>
<!--end::Body-->

</html>