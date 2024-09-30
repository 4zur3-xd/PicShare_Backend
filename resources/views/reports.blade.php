<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Pic Share Admin - Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

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

            <li class="nav-item">
                <a class="nav-link" href="{{ route('users_manage') }}">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Users Management</span></a>
            </li>

            <li class="nav-item active">
                <a class="nav-link" href="">
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
                        <h1 class="h3 mb-0 text-gray-800">Reports Management</h1>
                    </div>

                    <!-- Content Row -->

                    <div class="row">

                        <div class="col-xl-12 col-lg-12">
                            <div class="card shadow mb-4">
                                <!-- Card Header -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary" href="">Reports List</h6>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body" style="overflow: auto;">
                                    <table class="table" id="main-table">
                                        <tr>
                                            <th style="width: 50px;">ID</th>
                                            <th style="width: 150px;">Post</th>
                                            <th>Reason</th>
                                            <th style="width: 150px;">Targeted User</th>
                                            <th style="width: 150px;">User Reported</th>
                                            <th style="width: 150px;">Created At</th>
                                            <th style="width: 50px;">Action</th>
                                        </tr>
                                        @if ($reportsData['rp_num'] != 0)

                                        @foreach ($reportsData['data'] as $report)
                                            @foreach ($reportsData['reported_user_data'] as $reportedUser)
                                                @if ($report->reported_user == $reportedUser->id && $reportedUser->status == 1)
                                        <tr>
                                            <td>{{ $report->id }}</td>
                                            <td>
                                                <a href="" data-bs-toggle="modal" data-bs-target="#modalPost{{ $report->post_id }}" onclick="event.preventDefault()">
                                                    Post #{{ $report->post_id }}
                                                </a>
                                            </td>
                                            <td>{{ $report->reason }}</td>
                                            <td>
                                                @foreach ($reportsData['reported_user_data'] as $reportedUser)
                                                    @if ($report->reported_user == $reportedUser->id)
                                                        {{ $reportedUser->name }} (<a href="{{ route('user_info').'/'.$report->reported_user }}">#{{ $report->reported_user }}</a>)
                                                    @endif
                                                @endforeach
                                            </td>
                                            <td>
                                                @foreach ($reportsData['reporting_user_data'] as $reportingUser)
                                                    @if ($report->user_reporting == $reportingUser->id)
                                                        {{ $reportingUser->name }} (<a href="{{ route('user_info').'/'.$report->user_reporting }}">#{{ $report->user_reporting }}</a>)
                                                    @endif
                                                @endforeach
                                            </td>
                                            <td>{{ $report->created_at }}</td>
                                            <td>
                                                <button onclick="banUser{{ $report->reported_user }}()" class="btn btn-danger">BAN</button>
                                            </td>
                                        </tr>
                                                @elseif ($report->reported_user == $reportedUser->id && $reportedUser->status == 0)
                                        <tr>
                                            <td style="background-color: rgb(0, 0, 0, 0.15);">{{ $report->id }}</td>
                                            <td colspan="6" style="text-align: center; background-color: rgb(0, 0, 0, 0.15);">This user has been banned</td>
                                        </tr>
                                                @endif
                                            @endforeach
                                        @endforeach

                                        @else
                                        <tr>
                                            <td colspan="7" style="text-align: center;">There is no reports!</td>
                                        </tr>
                                        @endif
                                    </table>
                                    <ul class="pagination justify-content-end">
                                    @if ($reportsData['rp_num'] != 0)
                                        @if ($reportsData['page'] != 1)
                                        <li class="page-item"><a class="page-link" href="{{ route('reports_manage').'/'.($reportsData['page'] - 1) }}"><</a></li>
                                        @else
                                        <li class="page-item disabled"><a class="page-link" href=""><</a></li>
                                        @endif

                                        @for ($i = 1; $i <= $reportsData['total_pages']; $i++)
                                            @if ($i == $reportsData['page'])
                                        <li class="page-item disabled"><a class="page-link" href="">{{ $i }}</a></li>
                                            @else
                                        <li class="page-item"><a class="page-link" href="{{ route('reports_manage').'/'.$i }}">{{ $i }}</a></li>
                                            @endif
                                        @endfor

                                        @if ($reportsData['page'] != $reportsData['total_pages'])
                                        <li class="page-item"><a class="page-link" href="{{ route('reports_manage').'/'.($reportsData['page'] + 1) }}">></a></li>
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

    @foreach ($reportsData['post_data'] as $post)
    <div class="modal fade" id="modalPost{{ $post->id }}" tabindex="-1" aria-labelledby="customModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customModalLabel">Post #{{ $post->id }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img src="{{ asset($post->url_image) }}" style="display: block; margin:auto; width: 75%;">
                <p>
                    Caption: <b>{{ $post->caption }}</b>
                </p>
                <p><small>
                    Posted at <b>{{ $post->created_at }}</b>
                </small></p>
            </div>
          </div>
        </div>
    </div>
    @endforeach

    @foreach ($reportsData['reported_user_data'] as $reportedUser)
        @if ($reportedUser->status == 1)
            <form id="banForm{{ $reportedUser->id }}" action="{{ route('user_ban') }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" name="user_id" value="{{ $reportedUser->id }}">
            </form>
            <script>
                function banUser{{ $reportedUser->id }}(){
                    if (confirm('Are you sure you want to ban this user?')) {
                        document.getElementById('banForm{{ $reportedUser->id }}').submit();
                    }
                }
            </script>
        @endif
    @endforeach

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
