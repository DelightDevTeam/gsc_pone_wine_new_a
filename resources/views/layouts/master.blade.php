<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PoneWine 20X | Dashboard</title>

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/jqvmap/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    {{-- @vite(['resources/js/app.js']) --}}


    <style>
        .dropdown-menu {
            z-index: 1050 !important;
        }
    </style>

    @yield('style')


</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">



        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light sticky-top">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('home') }}" class="nav-link">Home</a>
                </li>
            </ul>



            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">

                <!--begin::Messages Dropdown Menu-->
                @can('deposit')
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle position-relative" href="#" id="notificationDropdown"
                            role="button" data-toggle="dropdown">
                            <i class="bi bi-bell"></i>
                            <span class="navbar-badge badge bg-danger text-white rounded-circle" id="notificationCount">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow-lg p-3 mb-5 bg-white rounded"
                            aria-labelledby="notificationDropdown">
                            @forelse (auth()->user()->unreadNotifications as $notification)
                                <li class="notification-item">
                                    <a href="#" class="dropdown-item d-flex align-items-start p-3"
                                        style="background-color: #ffeeba; border-left: 4px solid #ff6f00; border-radius: 5px;">
                                        <div class="flex-grow-1">
                                            <h6 class="dropdown-item-title fw-bold text-dark">
                                                {{ $notification->data['player_name'] }}
                                            </h6>
                                            <p class="fs-7 text-dark mb-1">{{ $notification->data['message'] }}</p>
                                            <p class="fs-7 text-muted">
                                                <i class="bi bi-clock-fill me-1"></i>
                                                {{ $notification->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                            @empty
                                <li class="dropdown-item text-center text-muted">No new notifications</li>
                            @endforelse

                            <li>
                                <a href="#" class="dropdown-item dropdown-footer text-center text-primary fw-bold">See
                                    All Notifications</a>
                            </li>
                        </ul>
                    </li>

                    <!-- Add the audio element -->
                    <button id="enableSound" style="display: none">Enable Notification Sound</button>
                    <audio id="notificationSound" src="{{ asset('sounds/noti.wav') }}" preload="auto"></audio>
                @endcan
                <!--end::Messages Dropdown Menu-->
                <li class="nav-item">
                    <a class="nav-link"
                        href="{{ route('admin.changePassword', \Illuminate\Support\Facades\Auth::id()) }}">
                        {{ auth()->user()->name }}
                        @if ( auth()->user()->referral_code )
                        | {{ auth()->user()->referral_code }}
                 @endif
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        | Balance: {{ number_format(auth()->user()->wallet->balanceFloat, 2) }}
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link" href="#"
                        onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>

                </li>

            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            {{-- <a href="{{ route('home') }}" class="brand-link">
            <img src="{{ asset('img/slot_maker.jpg') }}" alt="AdminLTE Logo"
                class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">GoldenJack</span>
            </a> --}}
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="brand-link">
                <img src="{{ $adminLogo }}" alt="Admin Logo" class="brand-image img-circle elevation-3"
                    style="opacity: .8">
                {{-- <span class="brand-text font-weight-light">GoldenJack</span> --}}
                <span class="brand-text font-weight-light">{{ $siteName }}</span>
            </a>


            <!-- Sidebar -->
            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <li class="nav-item menu-open">
                            <a href="{{ route('home') }}"
                                class="nav-link {{ Route::current()->getName() == 'home' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>
                                    Dashboard
                                </p>
                            </a>
                        </li>

                        @can('owner_index')
                            <li class="nav-item">
                                <a href="{{ route('admin.owner.index') }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.owner.index' ? 'active' : '' }}">
                                    <i class="fas fa-users"></i>
                                    <p>
                                        Owner List
                                    </p>
                                </a>
                            </li>
                        @endcan
                        @can('super_index')
                            <li class="nav-item">
                                <a href="{{ route('admin.super.index') }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.super.index' ? 'active' : '' }}">
                                    <i class="fas fa-users"></i>
                                    <p>
                                        Super List
                                    </p>
                                </a>
                            </li>
                        @endcan
                        @can('senior_index')
                            <li class="nav-item">
                                <a href="{{ route('admin.senior.index') }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.senior.index' ? 'active' : '' }}">
                                    <i class="fas fa-users"></i>
                                    <p>
                                        Senior List
                                    </p>
                                </a>
                            </li>
                        @endcan
                        @can('master_index')
                            <li class="nav-item">
                                <a href="{{ route('admin.master.index') }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.master.index' ? 'active' : '' }}">
                                    <i class="fas fa-users"></i>
                                    <p>
                                        Master List
                                    </p>
                                </a>
                            </li>
                        @endcan
                        @can('agent_index')
                            <li class="nav-item">
                                <a href="{{ route('admin.agent.index') }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.agent.index' ? 'active' : '' }}">
                                    <i class="fas fa-users"></i>
                                    <p>
                                        Agent List
                                    </p>
                                </a>
                            </li>
                        @endcan
                        @can('player_index')
                            <li class="nav-item">
                                <a href="{{ route('admin.player.index') }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.player.index' ? 'active' : '' }}">
                                    <i class="far fa-user"></i>
                                    <p>
                                        Player List
                                    </p>
                                </a>
                            </li>
                        @endcan
                        @can('contact')
                            <li class="nav-item">
                                <a href="{{ route('admin.contact.index') }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.contact.index' ? 'active' : '' }}">
                                    <i class="fas fa-address-book"></i>
                                    <p>
                                        Contact
                                    </p>
                                </a>
                            </li>
                        @endcan
                        @can('bank')
                            <li class="nav-item">
                                <a href="{{ route('admin.bank.index') }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.bank.index' ? 'active' : '' }}">
                                    <i class="fas fa-university"></i>
                                    <p>
                                        Bank
                                    </p>
                                </a>
                            </li>
                        @endcan
                        @can('withdraw')
                            <li class="nav-item">
                                <a href="{{ route('admin.agent.withdraw') }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.agent.withdraw' ? 'active' : '' }}">
                                    <i class="fas fa-comment-dollar"></i>
                                    <p>
                                        WithDraw Request
                                    </p>
                                </a>
                            </li>
                        @endcan
                        @can('deposit')
                            <li class="nav-item">
                                <a href="{{ route('admin.agent.deposit') }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.agent.deposit' ? 'active' : '' }}">
                                    <i class="fab fa-dochub"></i>
                                    <p>
                                        Deposit Request
                                    </p>
                                </a>
                            </li>
                        @endcan
                        <li class="nav-item">
                            <a href="{{ route('admin.transferLog') }}"
                                class="nav-link {{ Route::current()->getName() == 'admin.transferLog' ? 'active' : '' }}">
                                <i class="fas fa-exchange-alt"></i>
                                <p>
                                    Transaction Log
                                </p>
                            </a>
                        </li>
                        @can('agent_access')
                            <li class="nav-item">
                                <a href="{{ route('admin.subacc.index') }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.subacc.index' ? 'active' : '' }}">
                                    <i class="fas fa-user-plus"></i>
                                    <p>
                                        Sub Account
                                    </p>
                                </a>
                            </li>
                        @endcan
                        @can('senior_owner_access')
                            <li class="nav-item">
                                <a href="{{ route('admin.roles.index') }}"
                                    class="nav-link {{ Route::current()->getName() == 'admin.roles.index' ? 'active' : '' }}">
                                    <i class="far fa-registered"></i>
                                    <p>
                                        Role
                                    </p>
                                </a>
                            </li>

                            <li
                                class="nav-item {{ in_array(Route::currentRouteName(), ['admin.gameLists.index', 'admin.gametypes.index']) ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link">
                                    <i class="fas fa-tools"></i>
                                    <p>
                                        GSC Settings
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.gameLists.index') }}"
                                            class="nav-link {{ Route::current()->getName() == 'admin.gameLists.index' ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>GSC GameList</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.gametypes.index') }}"
                                            class="nav-link {{ Route::current()->getName() == 'admin.gametypes.index' ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>GSC GameProvider</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endcan
                        <li
                            class="nav-item {{ in_array(Route::currentRouteName(), ['admin.report.index', 'admin.report.ponewine']) ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link">
                                <i class="fas fa-file-invoice"></i>
                                <p>
                                    Reports
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item menu-open">
                                    <a href="{{ route('admin.report.index') }}"
                                        class="nav-link {{ Route::current()->getName() == 'admin.report.index' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            Win/Lose Report
                                        </p>
                                    </a>
                                </li>
                                <li class="nav-item menu-open">
                                    <a href="{{ route('admin.report.ponewine') }}"
                                        class="nav-link {{ Route::current()->getName() == 'admin.report.ponewine' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            PoneWine Report
                                        </p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @can('owner_access')
                            <li
                                class="nav-item {{ in_array(Route::currentRouteName(), ['admin.text.index', 'admin.banners.index', 'admin.adsbanners.index', 'admin.promotions.index']) ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link">
                                    <i class="fas fa-tools"></i>
                                    <p>
                                        General Settings
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.text.index') }}"
                                            class="nav-link {{ Route::current()->getName() == 'admin.text.index' ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>BannerText</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.banners.index') }}"
                                            class="nav-link {{ Route::current()->getName() == 'admin.banners.index' ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Banner</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.adsbanners.index') }}"
                                            class="nav-link {{ Route::current()->getName() == 'admin.adsbanners.index' ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Banner Ads</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.promotions.index') }}"
                                            class="nav-link {{ Route::current()->getName() == 'admin.promotions.index' ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Promotions</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endcan

                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <div class="content-wrapper">

            @yield('content')
        </div>
        <footer class="main-footer">
            <strong>Copyright &copy; 2025 <a href="">PoneWine20X</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 3.2.2
            </div>
        </footer>

        <aside class="control-sidebar control-sidebar-dark">
        </aside>
    </div>

    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <script>
        // $.widget.bridge('uibutton', $.ui.button)
    </script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('js/adminlte.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('plugins/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <!-- DataTables JS -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

    @yield('script')
    <script>
        var errorMessage = @json(session('error'));
        var successMessage = @json(session('success'));

        @if (session()->has('success'))
            toastr.success(successMessage)
        @elseif (session()->has('error'))
            toastr.error(errorMessage)
        @endif
    </script>
    <script>
        $(function() {
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });
            $('#ponewineTable').DataTable();
            $('#slotTable').DataTable();

            $("#mytable").DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "order": true,
                "pageLength": 10,
            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
            var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl)
            })
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#notificationDropdown').on('click', function() {
                $.ajax({
                    url: "{{ route('admin.markNotificationsRead') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function() {
                        $('#notificationCount').text(0);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error marking notifications as read:', error);
                    }
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notificationSound = document.getElementById('notificationSound');
            const enableSoundButton = document.getElementById('enableSound');
            let soundEnabled = false;

            // Enable sound when the user clicks the button
            enableSoundButton.addEventListener('click', function() {
                notificationSound.play().then(() => {
                    notificationSound.pause();
                    notificationSound.currentTime = 0;
                    soundEnabled = true; // Mark sound as enabled
                    enableSoundButton.style.display =
                        'none'; // Hide the button after enabling sound
                }).catch(error => {
                    console.error('Error enabling sound:', error);
                });
            });

            // Function to play notification sound
            function playNotificationSound() {
                if (soundEnabled) {
                    notificationSound.play().catch(error => {
                        console.error('Error playing notification sound:', error);
                    });
                }
            }

            // Use playNotificationSound() in your Pusher event handler
            channel.bind('deposit.notify', function(data) {
                console.log('New deposit notification received:', data);
                playNotificationSound(); // Play sound automatically
            });
        });
    </script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        Pusher.logToConsole = true;

        // Initialize Pusher
        // var pusher = new Pusher('29b71b17d47621df4504', {
        //     cluster: 'ap1',
        //     encrypted: true
        // });

        // // Dynamically subscribe to the agent's channel
        // var agentId = "{{ auth()->user()->id }}"; // Replace with the dynamic agent ID
        // var channel = pusher.subscribe('agent.' + agentId);

        // console.log('Subscribed to channel: agent.' + agentId);

        // // Bind to the event
        // channel.bind('deposit.notify', function(data) {
        //     console.log('New deposit notification received:', data);

        //     // Update the notification count
        //     var notificationCount = parseInt($('#notificationCount').text());
        //     $('#notificationCount').text(notificationCount + 1);

        //     // Prepend the new notification to the dropdown
        //     var newNotification = `
    //     <li class="notification-item">
    //         <a href="#" class="dropdown-item d-flex align-items-start p-3" style="background-color: #ffeeba; border-left: 4px solid #ff6f00; border-radius: 5px;">
    //             <div class="flex-grow-1">
    //                 <h6 class="dropdown-item-title fw-bold text-dark">
    //                     ${data.player_name}
    //                 </h6>
    //                 <p class="fs-7 text-dark mb-1">${data.message}</p>
    //                 <p class="fs-7 text-muted">
    //                     <i class="bi bi-clock-fill me-1"></i>
    //                     Just now
    //                 </p>
    //             </div>
    //         </a>
    //     </li>
    //     <li>
    //         <hr class="dropdown-divider">
    //     </li>
    // `;

        //     // Append the new notification to the dropdown
        //     $('.dropdown-menu').prepend(newNotification);

        //     // Remove the "No new notifications" message if it exists
        //     $('.dropdown-item.text-center.text-muted').remove();

        //     // Play the notification sound
        //     var notificationSound = document.getElementById('notificationSound');
        //     if (notificationSound) {
        //         notificationSound.play().catch(error => {
        //             console.error('Error playing notification sound:', error);
        //         });
        //     }
        // });

        // // Log Pusher connection status
        // pusher.connection.bind('state_change', function(states) {
        //     console.log('Pusher connection state changed:', states.current);
        // });

        // pusher.connection.bind('error', function(error) {
        //     console.error('Pusher connection error:', error);
        // });
        // Initialize Pusher
        var pusher = new Pusher('29b71b17d47621df4504', {
            cluster: 'ap1',
            encrypted: true
        });

        // Dynamically subscribe to the agent's channel
        var agentId = "{{ auth()->user()->id }}"; // Replace with the dynamic agent ID
        var channel = pusher.subscribe('agent.' + agentId);

        console.log('Subscribed to channel: agent.' + agentId);

        // Bind to the event
        channel.bind('deposit.notify', function(data) {
            console.log('New deposit notification received:', data);

            // Update the notification count
            var notificationCount = parseInt($('#notificationCount').text());
            $('#notificationCount').text(notificationCount + 1);

            // Prepend the new notification to the dropdown
            var newNotification = `
        <li class="notification-item">
            <a href="#" class="dropdown-item d-flex align-items-start p-3" style="background-color: #ffeeba; border-left: 4px solid #ff6f00; border-radius: 5px;">
                <div class="flex-grow-1">
                    <h6 class="dropdown-item-title fw-bold text-dark">
                        ${data.player_name}
                    </h6>
                    <p class="fs-7 text-dark mb-1">${data.message}</p>
                    <p class="fs-7 text-muted">
                        <i class="bi bi-clock-fill me-1"></i>
                        Just now
                    </p>
                </div>
            </a>
        </li>
        <li>
            <hr class="dropdown-divider">
        </li>
    `;

            // Append the new notification to the dropdown
            $('.dropdown-menu').prepend(newNotification);

            // Remove the "No new notifications" message if it exists
            $('.dropdown-item.text-center.text-muted').remove();

            // Play the notification sound
            playNotificationSound();
        });
    </script>

</body>

</html>
