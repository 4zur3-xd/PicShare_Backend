<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>
        @if (!$userData)
            Pic Share User
        @else
            {{ $userData->name }} - Pic Share User
        @endif
    </title>

    <!-- Custom fonts for this template-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Custom styles for this template-->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

</head>

<body>

    <!-- Page Wrapper -->
    <div id="wrapper">

        @if (auth()->user()->role == 'admin')
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

            <li class="nav-item">
                <a class="nav-link" href="{{ route('users_manage') }}">
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
        @else
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ url('/') }}">
                <div class="sidebar-brand-icon rotate-n-15">
                    <img src="{{ asset('images/pic_share_logo.png') }}" style="width: 55px; height: 55px;">
                </div>
                <div class="sidebar-brand-text mx-3">Pic Share Account</div>
            </a>

            <hr class="sidebar-divider my-0">

            <li class="nav-item active">
                <a class="nav-link" href="">
                    <i class="fas fa-fw fa-user"></i>
                    <span>Profile</span></a>
            </li>

            <hr class="sidebar-divider">

        </ul>
        <!-- End of Sidebar -->
        @endif


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
                                @if (auth()->user()->url_avatar)
                                <img class="img-profile rounded-circle" src="{{ auth()->user()->url_avatar }}">
                                @else
                                <img class="img-profile rounded-circle" src="{{ asset('images/blank-avatar.jpg') }}">
                                @endif
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
                        <h1 class="h3 mb-0 text-gray-800" style="font-weight: bold;">{{ $userData->name }}</h1>
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Created At
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                @if ($userData)
                                                    {{ $userData->created_at }}
                                                @else
                                                    No Data
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
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
                                                Total Posts
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                @if (isset($headData['total_posts']))
                                                    {{ $headData['total_posts'] }}
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
                                                Total Reports Sent
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                @if (isset($headData['total_rp_sent']))
                                                    {{ $headData['total_rp_sent'] }}
                                                @else
                                                    No Data
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-flag fa-2x text-gray-300"></i>
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
                                                Total Reports Received
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                @if (isset($headData['total_rp_recv']))
                                                    {{ $headData['total_rp_recv'] }}
                                                @else
                                                    No Data
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-flag fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->

                    <div class="row">

                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <!-- Card Header -->
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">User Overview</h6>
                                    @if ($userData->id == auth()->user()->id && auth()->user()->status == 1)
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-pencil fa-sm fa-fw"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editModal">Edit</a>
                                            @if (!auth()->user()->google_id)
                                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editpasswordModal">Change password</a>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                <!-- Card Body -->
                                <div class="card-body" @if ($userData->status == 0)
                                    style="background-color: rgba(255,0,0,0.15);"
                                @endif>
                                    <div>
                                        @if ($userData->status == 0)
                                        <h2 style="text-align: center; font-weight: bold;">This account has been banned!</h2>
                                        @endif
                                        <table class="table">
                                            <tr>
                                                <td style="width: 20%;">Fullname</td>
                                                <td><b>{{ $userData->name }}</b></td>
                                            </tr>
                                            <tr>
                                                <td>Email</td>
                                                <td><b>
                                                    @if ($userData->id == auth()->user()->id || auth()->user()->role == 'admin')
                                                    {{ $userData->email }}
                                                    @else
                                                    **********@*****.***
                                                    @endif
                                                </b></td>
                                            </tr>
                                            <tr>
                                                <td>Email Verification</td>
                                                <td><b>
                                                    @if ($userData->email_verified_at)
                                                        {{ $userData->email_verified_at }}
                                                    @else
                                                        Not verified
                                                    @endif
                                                </b></td>
                                            </tr>
                                            <tr>
                                                <td>User Code</td>
                                                <td><b>
                                                    @if ($userData->id == auth()->user()->id || auth()->user()->role == 'admin')
                                                    {{ $userData->user_code }}
                                                    @else
                                                    ******
                                                    @endif
                                                </b></td>
                                            </tr>
                                            <tr>
                                                <td>Last Update</td>
                                                <td><b>
                                                    {{ $userData->updated_at }}
                                                </b></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pie Chart -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <!-- Card Header -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="height: 57px;">
                                    <h6 class="m-0 font-weight-bold text-primary">User's Avatar</h6>
                                    @if ($userData->id == auth()->user()->id && auth()->user()->status == 1)
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-pencil fa-sm fa-fw"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editavatarModal">Edit avatar</a>
                                            @if (auth()->user()->url_avatar)
                                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteavatarModal">Delete avatar</a>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    @if ($userData->url_avatar)
                                    <img src="{{ $userData->url_avatar }}" style="height: 90%; width: 90%; display: block; margin: auto;">
                                    @else
                                    <img src="{{ asset('images/blank-avatar.jpg') }}" style="height: 90%; width: 90%; display: block; margin: auto;">
                                    @endif
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

    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Profile edit</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ url()->current().'/edit' }}" method="POST" id="edit-form">
                        @csrf
                        Name: <input type="text" name="name" class="form-control" value="{{ auth()->user()->name }}" required>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" form="edit-form">Submit</button>
                </div>
            </div>
        </div>
    </div>

    @if ($userData->id == auth()->user()->id && auth()->user()->status == 1 && !auth()->user()->google_id)
    <div class="modal fade" id="editpasswordModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit password</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ url()->current().'/edit-password' }}" method="POST" id="editpassword-form">
                        @csrf
                        Old password: <input type="password" name="old_password" class="form-control" required>
                        New password: <input type="password" name="password" class="form-control" required>
                        Password Confirmation: <input type="password" name="password_confirmation" class="form-control" required>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" form="editpassword-form">Submit</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="modal fade" id="editavatarModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit avatar</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ url()->current().'/edit-avatar' }}" method="POST" id="editavatar-form" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="url_avatar" required>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" form="editavatar-form">Submit</button>
                </div>
            </div>
        </div>
    </div>

    @if ($userData->id == auth()->user()->id && auth()->user()->status == 1 && auth()->user()->url_avatar)
    <div class="modal fade" id="deleteavatarModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete avatar?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body" style="display: none;">
                    <form action="{{ url()->current().'/delete-avatar' }}" method="POST" id="deleteavatar-form">
                        @csrf
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" form="deleteavatar-form">Delete</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Bootstrap core JavaScript-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>

    <!-- Swal -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('failMSG'))
    <script>
        Swal.fire({
            title: "Action Failed!",
            text: "{{ session('failMSG') }}",
            icon: "error"
        });
    </script>
    @endif

    @if (session('successMSG'))
    <script>
        Swal.fire({
            title: "{{ session('successMSG') }}",
            icon: "success"
        });
    </script>
    @endif

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</body>

</html>
