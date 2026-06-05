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

if (isset($_POST['submit_btn'])) {
  $edit_id = (int)($_POST['user_id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');

  // Use country, state, city from standard form names 
  $selected_country = (int)($_POST['country'] ?? 0);
  $selected_state   = (int)($_POST['state'] ?? 0);
  $selected_city    = (int)($_POST['city'] ?? 0);

  if ($edit_id > 0) {
    // Edit User
    $update_query = "UPDATE user SET name = ?, email = ?, password = ?, country_id = ?, state_id = ?, city_id = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "sssiiii", $name, $email, $password, $selected_country, $selected_state, $selected_city, $edit_id);
    mysqli_stmt_execute($stmt);
  } else {
    // Add User
    $stmt = mysqli_prepare(
      $conn,
      "INSERT INTO user(name,email, password, country_id, state_id, city_id) VALUES(?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "sssiii", $name, $email, $password, $selected_country, $selected_state, $selected_city);
    mysqli_stmt_execute($stmt);
  }

  header("Location: " . $_SERVER['PHP_SELF']);
  exit();
}

$selected_country = $_POST['country'] ?? '';
$selected_state   = $_POST['state'] ?? '';
$selected_city    = $_POST['city'] ?? '';

$countries = [];
$country_query = mysqli_query($conn, "SELECT id, country_name FROM country ORDER BY country_name ASC");
if ($country_query) {
  while ($country_row = mysqli_fetch_assoc($country_query)) {
    $countries[] = $country_row;
  }
}

$state_query = null;
if (!empty($selected_country)) {
  $state_query = mysqli_query(
    $conn,
    "SELECT * FROM state WHERE country_id = $selected_country"
  );
}

$city_query = null;
if (!empty($selected_state)) {
  $city_query = mysqli_query(
    $conn,
    "SELECT * FROM city WHERE state_id = $selected_state"
  );
}

if (isset($_POST['delete_btn'])) {
  $delete_id = (int)($_POST['delete_id'] ?? 0);
  if ($delete_id > 0) {
    $stmt = mysqli_prepare($conn, "DELETE FROM user WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $delete_id);
    mysqli_stmt_execute($stmt);
  }

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
                src="../assets/img/user1-128x128.jpg"
                class="user-image rounded-circle shadow"
                alt="User Image" style="object-fit: cover;" />
              <span class="d-none d-md-inline"><?php echo $_SESSION['username'];  ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
              <!--begin::User Image-->
              <li class="user-header text-bg-primary">
                <img
                  src="../assets/img/user1-128x128.jpg"
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
                <li class="nav-item">
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

        <!--begin::Container-->
        <div class="container-fluid mb-3">
          <!--begin::Row-->
          <div class="row g-4">
            <!--begin::Col-->
            <div class="col-12">
              <div class="callout callout-info">
                <button type="button" class="btn btn-success w-25" data-bs-toggle="modal" data-bs-target="#usermodal">Add User</button>
              </div>
            </div>

          </div>
          <!--end::Row-->
        </div>
        <!--end::Container-->


        <!-- User Modal -->
        <div class="modal" id="usermodal">
          <div class="modal-dialog">
            <div class="modal-content">

              <div class="modal-header">
                <h4 class="modal-title" id="userModalTitle">Add User</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">
                <form method="POST" id="userForm" name="userForm">
                  <input type="hidden" name="user_id" id="userId">
                  <label>Name <span class="text-danger small ms-1" data-error-for="name"></span></label>
                  <input type="text" id="userName" name="name" class="form-control mb-3" placeholder="Enter Your Name">
                  <label>Email ID <span class="text-danger small ms-1" data-error-for="email"></span></label>
                  <input type="email" id="userEmail" name="email" class="form-control mb-3" placeholder="Enter Your Email ID">
                  <label>Password <span class="text-danger small ms-1" data-error-for="password"></span></label>
                  <input type="text" id="userPassword" name="password" class="form-control mb-3" placeholder="Enter Your Password">

                  <div class="row mb-4">
                    <div class="col-4">
                      <label>Country <span class="text-danger small ms-1" data-error-for="country"></span></label>
                      <select name="country" id="userCountrySelect" class="form-select">
                        <option value="">--Country--</option>

                        <?php foreach ($countries as $country) { ?>
                          <option value="<?= $country['id'] ?>" <?= ($selected_country == $country['id']) ? 'selected' : '' ?>>
                            <?= $country['country_name'] ?>
                          </option>
                        <?php } ?>
                      </select>
                    </div>

                    <div class="col-4">
                      <label>State <span class="text-danger small ms-1" data-error-for="state"></span></label>
                      <select name="state" id="userStateSelect" class="form-select" disabled>
                        <option value="">--State--</option>
                        <?php
                        if ($state_query) {
                          while ($row = mysqli_fetch_assoc($state_query)) { ?>
                            <option value="<?= $row['id'] ?>" <?= ($selected_state == $row['id']) ? 'selected' : '' ?>>
                              <?= $row['state_name'] ?>
                            </option>
                        <?php }
                        } ?>
                      </select>
                    </div>

                    <div class="col-4">
                      <label>City <span class="text-danger small ms-1" data-error-for="city"></span></label>
                      <select name="city" id="userCitySelect" class="form-select" disabled>
                        <option value="">--City--</option>
                        <?php
                        if ($city_query) {
                          while ($row = mysqli_fetch_assoc($city_query)) { ?>
                            <option value="<?= $row['id'] ?>" <?= ($selected_city == $row['id']) ? 'selected' : '' ?>>
                              <?= $row['city_name'] ?>
                            </option>
                        <?php }
                        } ?>
                      </select>
                    </div>
                  </div>
                  <button type="submit" name="submit_btn" id="submitBtn" class="btn btn-success w-100">Submit</button>
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
            <h2 class="text-light"><b class="heading-font">User Details</b></h2>
          </div>
          <div class="m-2">



            <table class="table table-hover border-dark text-center" id="myTable">
              <thead class="table-success">
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email ID</th>
                  <th>Password</th>
                  <th>City</th>
                  <th>State</th>
                  <th>Country</th>
                  <th>Action</th>
                </tr>
              </thead>


              <tbody></tbody>
            </table>
          </div>

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
    const userCountrySelect = document.getElementById('userCountrySelect');
    const stateSelect = document.getElementById('userStateSelect');
    const citySelect = document.getElementById('userCitySelect');

    const editCountrySelect = document.getElementById('editCountrySelect');
    const editStateSelect = document.getElementById('editStateSelect');
    const editCitySelect = document.getElementById('editCitySelect');

    function resetSelect(selectEl, placeholder) {
      selectEl.innerHTML = `<option value="">${placeholder}</option>`;
      selectEl.disabled = true;
    }

    async function loadStates(countryId, targetSelect, selectedStateId = '') {
      resetSelect(targetSelect, '--State--');
      if (!countryId) return;

      const res = await fetch('../get_states.php?country_id=' + encodeURIComponent(countryId));
      const data = await res.json();
      data.forEach(state => {
        targetSelect.innerHTML += `<option value="${state.id}">${state.state_name}</option>`;
      });
      targetSelect.disabled = false;
      if (selectedStateId) {
        targetSelect.value = String(selectedStateId);
      }
    }

    async function loadCities(stateId, targetSelect, selectedCityId = '') {
      resetSelect(targetSelect, '--City--');
      if (!stateId) return;

      const res = await fetch('../get_cities.php?state_id=' + encodeURIComponent(stateId));
      const data = await res.json();
      data.forEach(city => {
        targetSelect.innerHTML += `<option value="${city.id}">${city.city_name}</option>`;
      });
      targetSelect.disabled = false;
      if (selectedCityId) {
        targetSelect.value = String(selectedCityId);
      }
    }

    if (userCountrySelect && userStateSelect && userCitySelect) {
      userCountrySelect.addEventListener('change', function() {
        loadStates(this.value, userStateSelect);
        resetSelect(userCitySelect, '--City--');
      });

      userStateSelect.addEventListener('change', function() {
        loadCities(this.value, userCitySelect);
      });
    }

    document.addEventListener('click', async function(event) {
      // Add User Button
      if (event.target.closest('[data-bs-target="#usermodal"]') && !event.target.closest('.editBtn')) {
        document.getElementById('userModalTitle').innerText = 'Add User';
        document.getElementById('submitBtn').innerText = 'Add User';
        document.getElementById('userId').value = '';
        document.getElementById('userName').value = '';
        document.getElementById('userEmail').value = '';
        document.getElementById('userPassword').value = '';
        userCountrySelect.value = '';
        resetSelect(userStateSelect, '--State--');
        resetSelect(userCitySelect, '--City--');
        return;
      }

      // Edit User Button
      const editBtn = event.target.closest('.editBtn');
      if (editBtn) {
        document.getElementById('userModalTitle').innerText = 'Edit User';
        document.getElementById('submitBtn').innerText = 'Update User';
        document.getElementById('userId').value = editBtn.dataset.id || '';
        document.getElementById('userName').value = editBtn.dataset.name || '';
        document.getElementById('userEmail').value = editBtn.dataset.email || '';
        document.getElementById('userPassword').value = editBtn.dataset.password || '';

        const countryId = editBtn.dataset.countryId || '';
        const stateId = editBtn.dataset.stateId || '';
        const cityId = editBtn.dataset.cityId || '';

        userCountrySelect.value = countryId;

        // Load states and cities BEFORE Bootstrap opens the modal
        await loadStates(countryId, userStateSelect, stateId);
        await loadCities(stateId, userCitySelect, cityId);

        // Manually open the modal after data is ready
        const modal = new bootstrap.Modal(document.getElementById('usermodal'));
        modal.show();
        return;
      }

      // Add User Button — only runs if NOT an edit button
      if (event.target.closest('[data-bs-target="#usermodal"]')) {
        document.getElementById('userModalTitle').innerText = 'Add User';
        document.getElementById('submitBtn').innerText = 'Add User';
        document.getElementById('userId').value = '';
        document.getElementById('userName').value = '';
        document.getElementById('userEmail').value = '';
        document.getElementById('userPassword').value = '';
        userCountrySelect.value = '';

        resetSelect(userStateSelect, '--State--');
        resetSelect(userCitySelect, '--City--');
      }
    });
  </script>
  <script src="lib/jquery/jquery-4.0.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
  <script src="validator.min.js"></script>
  <script>
    $(document).ready(function () {
      var validator = $('#userForm').data('validator');
      if (validator && validator.settings.rules && validator.settings.rules.email) {
        var rule = validator.settings.rules.email;
        if (rule.remote) {
          if (typeof rule.remote === 'string') {
            rule.remote = { url: rule.remote };
          }
          rule.remote.data = $.extend({}, rule.remote.data, {
            edit_id: function () {
              return $('#userId').val() || 0;
            }
          });
        }
      }
    });
  </script>
  <script src="lib/datatables/dataTables.js"></script>


  <script>
    $(document).ready(function() {
      $('#myTable').DataTable({
        ajax: {
          url: '../get_users_table.php',
          dataSrc: 'data'
        },
        columns: [{
            data: 'id'
          },
          {
            data: 'name'
          },
          {
            data: 'email'
          },
          {
            data: 'password',
            render: function(data, type) {
              if (type !== 'display') {
                return data;
              }
              const length = typeof data === 'string' ? data.length : 0;
              return length > 0 ? '*'.repeat(length) : '';
            }
          },
          {
            data: 'city'
          },
          {
            data: 'state'
          },
          {
            data: 'country'
          },
          {
            data: 'actions'
          }
        ],
        ordering: true,
        columnDefs: [{
          targets: [2, 3, 7],
          orderable: false
        }]
      });
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