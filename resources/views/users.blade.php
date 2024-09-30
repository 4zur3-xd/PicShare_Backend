<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Pic Share Admin - Dashboard</title>

    <!-- Custom fonts for this template-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Custom styles for this template-->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <style>
        #main-table td {
            vertical-align: middle;
        }
    </style>

</head>

<body>

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}">
                <div class="sidebar-brand-icon rotate-n-15">
                    <img src="{{ asset('images/pic_share_logo.png') }}" style="width: 55px; height: 55px;">
                </div>
                <div class="sidebar-brand-text mx-3">Pic Share Admin</div>
            </a>

            <hr class="sidebar-divider my-0">

            <li class="nav-item">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <hr class="sidebar-divider my-0">

            <li class="nav-item active">
                <a class="nav-link" href="">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Users Management</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('reports_manage') }}">
                    <i class="fas fa-fw fa-flag"></i>
                    <span>Reports Management</span></a>
            </li>

            <hr class="sidebar-divider">

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600">
                                    @if (auth()->user())
                                        {{ auth()->user()->name }}
                                    @endif
                                </span>
                                <img class="img-profile rounded-circle"
                                    src="@if (auth()->user()->url_avatar) {{ auth()->user()->url_avatar }}
                                    @else
                                        {{ asset('images/blank-avatar.jpg') }}
                                    @endif">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                @if (auth()->user())
                                <a class="dropdown-item" href="{{ route('user_info').'/'.auth()->user()->id }}">
                                @else
                                <a class="dropdown-item" href="">
                                @endif
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="" data-toggle="modal"
                                    data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Users Management</h1>
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Users
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                @if (isset($headData['total_users']))
                                                    {{ $headData['total_users'] }}
                                                @else
                                                    No Data
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                New Users (This week)
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                @if (isset($headData['new_users']))
                                                    {{ $headData['new_users'] }}
                                                @else
                                                    No Data
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Verified - Unverified Users
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                @if (isset($headData['veri_users']))
                                                    {{ $headData['veri_users'] }}
                                                @else
                                                    No Data
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clipboard fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Banned Users
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                @if (isset($headData['banned_users']))
                                                    {{ $headData['banned_users'] }}
                                                @else
                                                    No Data
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-ban fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->

                    <div class="row">

                        <div class="col-xl-12 col-lg-12">
                            <div class="card shadow mb-4">
                                <!-- Card Header -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary" href="">Users List</h6>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body" style="overflow: auto;">
                                    <form method="get">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" name="search" placeholder="Search for user..." required>
                                            <button class="btn btn-primary" type="button">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </form>
                                    @if (!empty($_GET['search']))
                                    <a class="btn btn-outline-danger" href="{{ route('users_manage') }}" style="display: inline-block; margin-bottom: 15px;">Clear search</a>
                                    @endif
                                    <table class="table" id="main-table">
                                        <tr>
                                            <th style="width: 50px;">ID</th>
                                            <th style="width: 50px;">Img</th>
                                            <th>Email</th>
                                            <th>Full Name</th>
                                            <th style="width: 50px;">Verified</th>
                                            <th style="width: 50px;">Status</th>
                                            <th>Role</th>
                                            <th>Created At</th>
                                            <th>Info</th>
                                        </tr>
                                        @foreach ($usersData['data'] as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>
                                            <td>
                                            @if (!$user->url_avatar)
                                                <img src="{{ asset('images/blank-avatar.jpg') }}" style="height: 48px; width: 48px;">
                                            @else
                                                <img src="{{ $user->url_avatar }}" style="height: 48px; width: 48px;">
                                            @endif
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>
                                            @if (!$user->email_verified_at)
                                                No
                                            @else
                                                <span style="color: violet;">Yes</span>
                                            @endif
                                            </td>
                                            <td>
                                            @if ($user->status)
                                                <span style="color: yellowgreen;">Active</span>
                                            @else
                                                <span style="color: red;">Banned</span>
                                            @endif
                                            </td>
                                            <td>{{ $user->role }}</td>
                                            <td>{{ $user->created_at }}</td>
                                            <td><a href="{{ route('user_info').'/'.$user->id }}">Info</a></td>
                                        </tr>
                                        @endforeach
                                    </table>
                                    <ul class="pagination justify-content-end">
                                    @if (empty($_GET['search']))
                                        @if ($usersData['page'] != 1)
                                        <li class="page-item"><a class="page-link" href="{{ route('users_manage').'/'.($usersData['page'] - 1) }}"><</a></li>
                                        @else
                                        <li class="page-item disabled"><a class="page-link" href=""><</a></li>
                                        @endif

                                        @for ($i = 1; $i <= $usersData['total_pages']; $i++)
                                            @if ($i == $usersData['page'])
                                        <li class="page-item disabled"><a class="page-link" href="">{{ $i }}</a></li>
                                            @else
                                        <li class="page-item"><a class="page-link" href="{{ route('users_manage').'/'.$i }}">{{ $i }}</a></li>
                                            @endif
                                        @endfor

                                        @if ($usersData['page'] != $usersData['total_pages'])
                                        <li class="page-item"><a class="page-link" href="{{ route('users_manage').'/'.($usersData['page'] + 1) }}">></a></li>
                                        @else
                                        <li class="page-item disabled"><a class="page-link" href="">></a></li>
                                        @endif
                                    @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; {{ date('Y') }} Pic Share - Group 17 Advanced Android Programming -
                            CT5-ACT</span>
                        <br>
                        <i>Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</i>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Are you sure?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href=""
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>

    <!-- Page level plugins -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</body>

</html>
