<?php
session_start();

if (!isset($_SESSION['username'])) {
  header("Location: examples/login.php");
  exit();
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

include 'db1.php';

// Fetching Products for the dashboard
$conn_products = mysqli_connect("localhost", "root", "", "store");
if (!$conn_products) {
    // If store DB doesn't exist or connect fails, fallback to admin_panel
    $conn_products = $conn;
}

// Get filter values
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$where_clauses = [];
if ($status_filter !== 'all') {
    $where_clauses[] = "p.status = '" . mysqli_real_escape_string($conn_products, $status_filter) . "'";
}
if (!empty($start_date)) {
    $where_clauses[] = "p.created_date >= '" . mysqli_real_escape_string($conn_products, $start_date) . " 00:00:00'";
}
if (!empty($end_date)) {
    $where_clauses[] = "p.created_date <= '" . mysqli_real_escape_string($conn_products, $end_date) . " 23:59:59'";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

$sql_products = "
    SELECT p.id, p.name, p.price, p.stock, p.status, p.created_date,
           (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.id ASC LIMIT 1) AS first_image
    FROM products p
    $where_sql
    ORDER BY p.id DESC
";
$products_result = mysqli_query($conn_products, $sql_products);
$products_count = ($products_result) ? mysqli_num_rows($products_result) : 0;
?>


<!doctype html>
<html lang="en">
<!--begin::Head-->

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>AdminLTE v4 | Dashboard</title>
  <!--begin::Accessibility Meta Tags-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <meta name="color-scheme" content="light dark" />
  <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
  <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
  <!--end::Accessibility Meta Tags-->
  <!--begin::Primary Meta Tags-->
  <meta name="title" content="AdminLTE v4 | Dashboard" />
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
  <link rel="preload" href="./css/adminlte.css" as="style" />
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
  <link rel="stylesheet" href="./css/adminlte.css" />
  <!--end::Required Plugin(AdminLTE)-->
  <!-- apexcharts -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
    integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0="
    crossorigin="anonymous" />
  <!-- jsvectormap -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
    integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4="
    crossorigin="anonymous" />
  <style>
    .product-card {
      border-radius: 15px;
      overflow: hidden;
    }
    .product-card:hover {
      box-shadow: 0 10px 20px rgba(0,0,0,0.12), 0 4px 8px rgba(0,0,0,0.06) !important;
    }
    .product-image-container {
      position: relative;
      padding-top: 100%; /* 1:1 Aspect Ratio */
      overflow: hidden;
      background: #f8f9fa;
    }
    .product-image-container img {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .price-tag {
      font-size: 1.25rem;
      font-weight: 700;
      color: #28a745;
    }
    .stock-badge {
      font-size: 0.75rem;
      padding: 4px 8px;
      border-radius: 50px;
    }
    .card-footer {
      background: #fff !important;
      border-top: 1px solid rgba(0,0,0,0.05) !important;
    }
  </style>
</head>


<!--end::Head-->
<!--begin::Body-->

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
                      src="./assets/img/user1-128x128.jpg"
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
                      src="./assets/img/user8-128x128.jpg"
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
                      src="./assets/img/user3-128x128.jpg"
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
          <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
              <img
                src="./assets/img/user1-128x128.jpg"
                class="user-image rounded-circle shadow"
                alt="User Image" style="object-fit: cover;" />
              <span class="d-none d-md-inline"> <?php echo $_SESSION['username'];  ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
              <!--begin::User Image-->
              <li class="user-header text-bg-primary">
                <img
                  src="./assets/img/user1-128x128.jpg"
                  class="rounded-circle shadow"
                  alt="User Image" style="object-fit: cover;" />
                
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
                <a href="examples/logout.php" class="btn btn-danger btn-flat float-end">Log out</a>
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
        <a href="./index.php" class="brand-link">
          <!--begin::Brand Image-->
          <img
            src="./assets/img/AdminLTELogo.png"
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
                <li class="nav-item">
                  <a href="tables/upload.php" class="nav-link">
                    <i class="nav-icon bi bi-circle"></i>
                    <p>Upload Image</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="tables/dropzone.php" class="nav-link">
                    <i class="nav-icon bi bi-circle"></i>
                    <p>Dropzone</p>
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
                  <a href="./forms/general.php" class="nav-link">
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
                  <a href="tables/user.php" class="nav-link">
                    <i class="nav-icon bi bi-circle"></i>
                    <p>Users</p>
                  </a>
                </li>

                <li class="nav-item">
                  <a href="tables/country.php" class="nav-link">
                    <i class="nav-icon bi bi-circle"></i>
                    <p>Country</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="tables/state.php" class="nav-link">
                    <i class="nav-icon bi bi-circle"></i>
                    <p>State</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="tables/city.php" class="nav-link active">
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
                  <a href="../dist/examples/lockscreen.php" class="nav-link">
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
        <div class="container-fluid">
          <!--begin::Row-->
          <div class="row">
            <div class="col-sm-6">
              <h3 class="mb-0">Dashboard</h3>
            </div>
          </div>
          <!--end::Row-->
        </div>
        <!--end::Container-->
      </div>
      <!--end::App Content Header-->
      <!--begin::App Content-->
      <div class="app-content">
        <!--begin::Container-->
        <div class="container-fluid">
          <!--begin::Row-->
          <div class="row">
            <!--begin::Col-->
            <div class="col-lg-3 col-6">
              <!--begin::Small Box Widget 1-->
              <div class="small-box text-bg-primary">
                <div class="inner">
                  <h3> <?php
                        $sql = mysqli_query($conn, "SELECT COUNT(*) AS Count FROM user");
                        $row = mysqli_fetch_array($sql);
                        echo $row['Count'];  ?></h3>
                  <p>Total Users</p>
                </div>
                <svg
                  class="small-box-icon"
                  fill="currentColor"
                  viewBox="0 0 24 24"
                  xmlns="http://www.w3.org/2000/svg"
                  aria-hidden="true">
                  <path
                    d="M6.25 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM3.25 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM19.75 7.5a.75.75 0 00-1.5 0v2.25H16a.75.75 0 000 1.5h2.25v2.25a.75.75 0 001.5 0v-2.25H22a.75.75 0 000-1.5h-2.25V7.5z"></path>
                </svg>
                <a
                  href="tables/user.php"
                  class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                  More info <i class="bi bi-link-45deg"></i>
                </a>
              </div>
              <!--end::Small Box Widget 1-->
            </div>
            <!--end::Col-->
            <div class="col-lg-3 col-6">
              <!--begin::Small Box Widget 2-->
              <div class="small-box text-bg-success">
                <div class="inner">
                  <h3><?php
                      $sql = mysqli_query($conn, "SELECT COUNT(*) AS Count FROM country");
                      $row = mysqli_fetch_array($sql);
                      echo $row['Count'];  ?>
                  </h3>
                  <p>Total Country</p>
                </div>
                <svg
                  class="small-box-icon"
                  fill="currentColor"
                  viewBox="0 0 24 24"
                  xmlns="http://www.w3.org/2000/svg"
                  aria-hidden="true">
                  <path
                    d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm6.75 9h-3a15.75 15.75 0 00-.75-4.5 8.28 8.28 0 013.75 4.5zM12 3.75c.75 0 2.25 2.25 2.625 7.5h-5.25C9.75 6 11.25 3.75 12 3.75zM6.25 6.75A15.75 15.75 0 005.5 11.25h-3a8.28 8.28 0 013.75-4.5zM3.75 12.75h3c.075 1.65.375 3.15.75 4.5a8.28 8.28 0 01-3.75-4.5zm8.25 7.5c-.75 0-2.25-2.25-2.625-7.5h5.25c-.375 5.25-1.875 7.5-2.625 7.5zm4.5-3a15.75 15.75 0 00.75-4.5h3a8.28 8.28 0 01-3.75 4.5z" />
                </svg>
                <a
                  href="tables/country.php"
                  class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                  More info <i class="bi bi-link-45deg"></i>
                </a>
              </div>
              <!--end::Small Box Widget 2-->
            </div>
            <!--end::Col-->
            <div class="col-lg-3 col-6">
              <!--begin::Small Box Widget 3-->
              <div class="small-box text-bg-warning">
                <div class="inner">
                  <h3><?php
                      $sql = mysqli_query($conn, "SELECT COUNT(*) AS Count FROM state");
                      $row = mysqli_fetch_array($sql);
                      echo $row['Count'];  ?></h3>
                  <p>Total State</p>
                </div>
                <svg
                  class="small-box-icon"
                  fill="currentColor"
                  viewBox="0 0 24 24"
                  xmlns="http://www.w3.org/2000/svg"
                  aria-hidden="true">
                  <path
                    fill-rule="evenodd"
                    d="M21.75 2.25a.75.75 0 00-.62-.74l-6 1.2-6-1.2a.75.75 0 00-.26 0l-6 1.2a.75.75 0 00-.62.74v16.5a.75.75 0 00.88.74l5.74-1.15 6 1.2c.09.02.18.02.26 0l6-1.2a.75.75 0 00.62-.74V2.25zM15 4.06l4.5-.9v14.28l-4.5.9V4.06zm-1.5 14.28l-4.5-.9V3.16l4.5.9v14.28zM4.5 4.06l4.5-.9v14.28l-4.5.9V4.06z"
                    clip-rule="evenodd" />
                </svg>
                <a
                  href="tables/state.php"
                  class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
                  More info <i class="bi bi-link-45deg"></i>
                </a>
              </div>
              <!--end::Small Box Widget 3-->
            </div>
            <!--end::Col-->
            <div class="col-lg-3 col-6">
              <!--begin::Small Box Widget 4-->
              <div class="small-box text-bg-danger">
                <div class="inner">
                  <h3><?php
                      $sql = mysqli_query($conn, "SELECT COUNT(*) AS Count FROM city");
                      $row = mysqli_fetch_array($sql);
                      echo $row['Count'];  ?></h3>
                  <p>Total City</p>
                </div>
                <svg
                  class="small-box-icon"
                  fill="currentColor"
                  viewBox="0 0 24 24"
                  xmlns="http://www.w3.org/2000/svg"
                  aria-hidden="true">
                  <path
                    d="M3 21h18a.75.75 0 000-1.5h-1.5V4.5A1.5 1.5 0 0018 3h-4.5A1.5 1.5 0 0012 4.5v15H9V7.5A1.5 1.5 0 007.5 6H4.5A1.5 1.5 0 003 7.5v12H1.5A.75.75 0 001.5 21H3zm3-12h1.5V10.5H6V9zm0 3h1.5v1.5H6V12zm0 3h1.5v1.5H6V15zm4.5-6H12V10.5h-1.5V9zm0 3H12v1.5h-1.5V12zm0 3H12v1.5h-1.5V15zm4.5-6H15V10.5h-1.5V9zm0 3H15v1.5h-1.5V12zm0 3H15v1.5h-1.5V15z" />
                </svg>
                <a
                  href="tables/city.php"
                  class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                  More info <i class="bi bi-link-45deg"></i>
                </a>
              </div>
              <!--end::Small Box Widget 4-->
            </div>
            <!--end::Col-->
          </div>
          <!--end::Row-->

          <div class="row mb-4">
            <div class="col-12">
              <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                  <form method="GET" action="index.php" class="row g-3 align-items-end">
                    <div class="col-md-3">
                      <label for="status" class="form-label small fw-bold text-muted text-uppercase">Status</label>
                      <select name="status" id="status" class="form-select border-0 bg-light rounded-3">
                        <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label for="start_date" class="form-label small fw-bold text-muted text-uppercase">Start Date</label>
                      <input type="date" name="start_date" id="start_date" class="form-control border-0  bg-light rounded-3" value="<?php echo htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="col-md-3">
                      <label for="end_date" class="form-label small fw-bold text-muted text-uppercase">End Date</label>
                      <input type="date" name="end_date" id="end_date" class="form-control border-0 bg-light rounded-3" value="<?php echo htmlspecialchars($end_date); ?>">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                      <button type="submit" class="btn btn-primary rounded-3 flex-grow-1">
                        Apply Filters
                      </button>
                      <a href="index.php" class="btn btn-light rounded-3">
                        <i class="bi bi-arrow-counterclockwise"></i>
                      </a>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <div class="row mb-5">
            <div class="col-12 mb-3 d-flex justify-content-between align-items-center">
              <h4 class="fw-bold"><i class="bi bi-box-seam me-2"></i>Products</h4>
              <span class="badge bg-primary rounded-pill"><?php echo $products_count; ?> Products Found</span>
            </div>
            
            <?php if ($products_count > 0): ?>
              <?php while ($p = mysqli_fetch_assoc($products_result)): ?>
                <div class="col-lg-2 col-sm-3 mb-4">
                  <div class="card h-100 product-card border-0 shadow-sm" onclick="window.location.href='product_details.php?id=<?php echo $p['id']; ?>'" style="cursor: pointer;">
                    <!-- Product Image in card-body -->
                    <div class="card-body p-0">
                      <div class="product-image-container">
                        <?php if (!empty($p['first_image'])): ?>
                          <img src="<?php echo htmlspecialchars($p['first_image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                        <?php else: ?>
                          <div class="d-flex align-items-center justify-content-center h-100 w-100">
                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                          </div>
                        <?php endif; ?>
                      </div>
                    </div>
                    
                    <!-- Product Details in card-footer -->
                    <div class="card-footer p-3">
                      <div class=" mb-2">
                        <h6 class="card-title fw-bold mb-0 text-truncate" style="max-width: 100%;"><?php echo htmlspecialchars($p['name']); ?></h6>
                        <br>
                        <span class="col-md-6 stock-badge <?php echo $p['stock'] > 10 ? 'bg-success-subtle text-success' : ($p['stock'] > 0 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger'); ?>">
                          <?php echo $p['stock'] > 0 ? 'In Stock: ' . $p['stock'] : 'Out of Stock'; ?>
                        </span>
                      </div>  
                      
                      <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="price-tag">₹<?php echo number_format((float)$p['price'], 2); ?></span>
                        
                      </div>
                      
                      <div class="mt-2">
                        <small class="text-muted"><i class="bi bi-calendar3 me-1"></i> Added: <?php echo date('M d, Y', strtotime($p['created_date'])); ?></small>
                      </div>
                      <button type="button" class="btn btn-sm btn-outline-success mt-2 w-100">Add to Cart</button>
                    </div>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="col-12 text-center py-5">
                <div class="bg-white rounded-4 shadow-sm p-5">
                  <i class="bi bi-box-seam text-muted" style="font-size: 4rem; opacity: 0.2;"></i>
                  <h5 class="mt-3 text-muted">No products available in the catalog.</h5>
                  <p class="text-muted small">Use the Product Management section to add some products.</p>
                </div>
              </div>
            <?php endif; ?>
          </div>

















          <!--begin::Row-->
          <div class="row">
            <!-- Start col -->
            <div class="col-lg-7 connectedSortable">
              <div class="card mb-4">
                <div class="card-header">
                  <h3 class="card-title">Sales Value</h3>
                </div>
                <div class="card-body">
                  <div id="revenue-chart"></div>
                </div>
              </div>
              <!-- /.card -->
              <!-- DIRECT CHAT -->
              <div class="card direct-chat direct-chat-primary mb-4">
                <div class="card-header">
                  <h3 class="card-title">Direct Chat</h3>
                  <div class="card-tools">
                    <span title="3 New Messages" class="badge text-bg-primary"> 3 </span>
                    <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                      <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                      <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                    </button>
                    <button
                      type="button"
                      class="btn btn-tool"
                      title="Contacts"
                      data-lte-toggle="chat-pane">
                      <i class="bi bi-chat-text-fill"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-lte-toggle="card-remove">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                  <!-- Conversations are loaded here -->
                  <div class="direct-chat-messages">
                    <!-- Message. Default to the start -->
                    <div class="direct-chat-msg">
                      <div class="direct-chat-infos clearfix">
                        <span class="direct-chat-name float-start"> Alexander Pierce </span>
                        <span class="direct-chat-timestamp float-end"> 23 Jan 2:00 pm </span>
                      </div>
                      <!-- /.direct-chat-infos -->
                      <img
                        class="direct-chat-img"
                        src="./assets/img/user1-128x128.jpg"
                        alt="message user image" />
                      <!-- /.direct-chat-img -->
                      <div class="direct-chat-text">
                        Is this template really for free? That's unbelievable!
                      </div>
                      <!-- /.direct-chat-text -->
                    </div>
                    <!-- /.direct-chat-msg -->
                    <!-- Message to the end -->
                    <div class="direct-chat-msg end">
                      <div class="direct-chat-infos clearfix">
                        <span class="direct-chat-name float-end"> Sarah Bullock </span>
                        <span class="direct-chat-timestamp float-start"> 23 Jan 2:05 pm </span>
                      </div>
                      <!-- /.direct-chat-infos -->
                      <img
                        class="direct-chat-img"
                        src="./assets/img/user3-128x128.jpg"
                        alt="message user image" />
                      <!-- /.direct-chat-img -->
                      <div class="direct-chat-text">You better believe it!</div>
                      <!-- /.direct-chat-text -->
                    </div>
                    <!-- /.direct-chat-msg -->
                    <!-- Message. Default to the start -->
                    <div class="direct-chat-msg">
                      <div class="direct-chat-infos clearfix">
                        <span class="direct-chat-name float-start"> Alexander Pierce </span>
                        <span class="direct-chat-timestamp float-end"> 23 Jan 5:37 pm </span>
                      </div>
                      <!-- /.direct-chat-infos -->
                      <img
                        class="direct-chat-img"
                        src="./assets/img/user1-128x128.jpg"
                        alt="message user image" />
                      <!-- /.direct-chat-img -->
                      <div class="direct-chat-text">
                        Working with AdminLTE on a great new app! Wanna join?
                      </div>
                      <!-- /.direct-chat-text -->
                    </div>
                    <!-- /.direct-chat-msg -->
                    <!-- Message to the end -->
                    <div class="direct-chat-msg end">
                      <div class="direct-chat-infos clearfix">
                        <span class="direct-chat-name float-end"> Sarah Bullock </span>
                        <span class="direct-chat-timestamp float-start"> 23 Jan 6:10 pm </span>
                      </div>
                      <!-- /.direct-chat-infos -->
                      <img
                        class="direct-chat-img"
                        src="./assets/img/user3-128x128.jpg"
                        alt="message user image" />
                      <!-- /.direct-chat-img -->
                      <div class="direct-chat-text">I would love to.</div>
                      <!-- /.direct-chat-text -->
                    </div>
                    <!-- /.direct-chat-msg -->
                  </div>
                  <!-- /.direct-chat-messages-->
                  <!-- Contacts are loaded here -->
                  <div class="direct-chat-contacts">
                    <ul class="contacts-list">
                      <li>
                        <a href="#">
                          <img
                            class="contacts-list-img"
                            src="./assets/img/user1-128x128.jpg"
                            alt="User Avatar" />
                          <div class="contacts-list-info">
                            <span class="contacts-list-name">
                              Count Dracula
                              <small class="contacts-list-date float-end"> 2/28/2023 </small>
                            </span>
                            <span class="contacts-list-msg"> How have you been? I was... </span>
                          </div>
                          <!-- /.contacts-list-info -->
                        </a>
                      </li>
                      <!-- End Contact Item -->
                      <li>
                        <a href="#">
                          <img
                            class="contacts-list-img"
                            src="./assets/img/user7-128x128.jpg"
                            alt="User Avatar" />
                          <div class="contacts-list-info">
                            <span class="contacts-list-name">
                              Sarah Doe
                              <small class="contacts-list-date float-end"> 2/23/2023 </small>
                            </span>
                            <span class="contacts-list-msg"> I will be waiting for... </span>
                          </div>
                          <!-- /.contacts-list-info -->
                        </a>
                      </li>
                      <!-- End Contact Item -->
                      <li>
                        <a href="#">
                          <img
                            class="contacts-list-img"
                            src="./assets/img/user3-128x128.jpg"
                            alt="User Avatar" />
                          <div class="contacts-list-info">
                            <span class="contacts-list-name">
                              Nadia Jolie
                              <small class="contacts-list-date float-end"> 2/20/2023 </small>
                            </span>
                            <span class="contacts-list-msg"> I'll call you back at... </span>
                          </div>
                          <!-- /.contacts-list-info -->
                        </a>
                      </li>
                      <!-- End Contact Item -->
                      <li>
                        <a href="#">
                          <img
                            class="contacts-list-img"
                            src="./assets/img/user5-128x128.jpg"
                            alt="User Avatar" />
                          <div class="contacts-list-info">
                            <span class="contacts-list-name">
                              Nora S. Vans
                              <small class="contacts-list-date float-end"> 2/10/2023 </small>
                            </span>
                            <span class="contacts-list-msg"> Where is your new... </span>
                          </div>
                          <!-- /.contacts-list-info -->
                        </a>
                      </li>
                      <!-- End Contact Item -->
                      <li>
                        <a href="#">
                          <img
                            class="contacts-list-img"
                            src="./assets/img/user6-128x128.jpg"
                            alt="User Avatar" />
                          <div class="contacts-list-info">
                            <span class="contacts-list-name">
                              John K.
                              <small class="contacts-list-date float-end"> 1/27/2023 </small>
                            </span>
                            <span class="contacts-list-msg"> Can I take a look at... </span>
                          </div>
                          <!-- /.contacts-list-info -->
                        </a>
                      </li>
                      <!-- End Contact Item -->
                      <li>
                        <a href="#">
                          <img
                            class="contacts-list-img"
                            src="./assets/img/user8-128x128.jpg"
                            alt="User Avatar" />
                          <div class="contacts-list-info">
                            <span class="contacts-list-name">
                              Kenneth M.
                              <small class="contacts-list-date float-end"> 1/4/2023 </small>
                            </span>
                            <span class="contacts-list-msg"> Never mind I found... </span>
                          </div>
                          <!-- /.contacts-list-info -->
                        </a>
                      </li>
                      <!-- End Contact Item -->
                    </ul>
                    <!-- /.contacts-list -->
                  </div>
                  <!-- /.direct-chat-pane -->
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                 
                </div>
                <!-- /.card-footer-->
              </div>
              <!-- /.direct-chat -->
            </div>
            <!-- /.Start col -->
            <!-- Start col -->
            <div class="col-lg-5 connectedSortable">
              <div class="card text-white bg-primary bg-gradient border-primary mb-4">
                <div class="card-header border-0">
                  <h3 class="card-title">Sales Value</h3>
                  <div class="card-tools">
                    <button
                      type="button"
                      class="btn btn-primary btn-sm"
                      data-lte-toggle="card-collapse">
                      <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                      <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body">
                  <div id="world-map" style="height: 220px"></div>
                </div>
                <div class="card-footer border-0">
                  <!--begin::Row-->
                  <div class="row">
                    <div class="col-4 text-center">
                      <div id="sparkline-1" class="text-dark"></div>
                      <div class="text-white">Visitors</div>
                    </div>
                    <div class="col-4 text-center">
                      <div id="sparkline-2" class="text-dark"></div>
                      <div class="text-white">Online</div>
                    </div>
                    <div class="col-4 text-center">
                      <div id="sparkline-3" class="text-dark"></div>
                      <div class="text-white">Sales</div>
                    </div>
                  </div>
                  <!--end::Row-->
                </div>
              </div>
            </div>
            <!-- /.Start col -->
          </div>
          <!-- /.row (main row) -->
        </div>
        <!--end::Container-->
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
  <script src="./js/adminlte.js"></script>
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
  <!-- sortablejs -->
  <script
    src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"
    crossorigin="anonymous"></script>
  <!-- sortablejs -->
  <script>
    new Sortable(document.querySelector('.connectedSortable'), {
      group: 'shared',
      handle: '.card-header',
    });

    const cardHeaders = document.querySelectorAll('.connectedSortable .card-header');
    cardHeaders.forEach((cardHeader) => {
      cardHeader.style.cursor = 'move';
    });
  </script>
  <!-- apexcharts -->
  <script
    src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
    integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8="
    crossorigin="anonymous"></script>
  <!-- ChartJS -->
  <script>
    // NOTICE!! DO NOT USE ANY OF THIS JAVASCRIPT
    // IT'S ALL JUST JUNK FOR DEMO
    // ++++++++++++++++++++++++++++++++++++++++++

    
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
        height: 300,
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
      document.querySelector('#revenue-chart'),
      sales_chart_options,
    );
    sales_chart.render();
  </script>
  <!-- jsvectormap -->
  <script
    src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"
    integrity="sha256-/t1nN2956BT869E6H4V1dnt0X5pAQHPytli+1nTZm2Y="
    crossorigin="anonymous"></script>
  <script
    src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"
    integrity="sha256-XPpPaZlU8S/HWf7FZLAncLg2SAkP8ScUTII89x9D3lY="
    crossorigin="anonymous"></script>
  <!-- jsvectormap -->
  <script>
    // World map by jsVectorMap
    new jsVectorMap({
      selector: '#world-map',
      map: 'world',
    });

    const option_sparkline1 = {
      series: [{
        data: [1000, 1200, 920, 927, 931, 1027, 819, 930, 1021],
      }, ],
      chart: {
        type: 'area',
        height: 50,
        sparkline: {
          enabled: true,
        },
      },
      stroke: {
        curve: 'straight',
      },
      fill: {
        opacity: 0.3,
      },
      yaxis: {
        min: 0,
      },
      colors: ['#DCE6EC'],
    };

    const sparkline1 = new ApexCharts(document.querySelector('#sparkline-1'), option_sparkline1);
    sparkline1.render();

    const option_sparkline2 = {
      series: [{
        data: [515, 519, 520, 522, 652, 810, 370, 627, 319, 630, 921],
      }, ],
      chart: {
        type: 'area',
        height: 50,
        sparkline: {
          enabled: true,
        },
      },
      stroke: {
        curve: 'straight',
      },
      fill: {
        opacity: 0.3,
      },
      yaxis: {
        min: 0,
      },
      colors: ['#DCE6EC'],
    };

    const sparkline2 = new ApexCharts(document.querySelector('#sparkline-2'), option_sparkline2);
    sparkline2.render();

    const option_sparkline3 = {
      series: [{
        data: [15, 19, 20, 22, 33, 27, 31, 27, 19, 30, 21],
      }, ],
      chart: {
        type: 'area',
        height: 50,
        sparkline: {
          enabled: true,
        },
      },
      stroke: {
        curve: 'straight',
      },
      fill: {
        opacity: 0.3,
      },
      yaxis: {
        min: 0,
      },
      colors: ['#DCE6EC'],
    };

    const sparkline3 = new ApexCharts(document.querySelector('#sparkline-3'), option_sparkline3);
    sparkline3.render();
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

</body>
<!--end::Body-->

</html>