<!DOCTYPE html>
<html lang="{{ session()->has('locale') ? session('locale') : 'en' }}" dir="{{ session()->get('direction') ?? 'ltr' }}">
@php($logo = getSession('header_logo'))
@php($favicon = getSession('favicon'))
@php($preloader = getSession('preloader'))

<head>
    <!-- Page Title -->
    <title>@yield('title')</title>

    <!-- Meta Data -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="description" content=""/>
    <meta name="keywords" content=""/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    {{-- <link rel="shortcut icon" href="{{$favicon ? asset("storage/app/public/business/.$favicon"): " "}}"
          onerror="this.src='{{asset('public/assets/admin-module/img/favicon.png')}}'"/> --}}
    <link rel="shortcut icon"
          href="{{ onErrorImage(
            $favicon,
            asset('storage/app/public/business') . '/' . $favicon,
            asset('public/assets/admin-module/img/favicon.png'),
            'business/',
        ) }}"/>
    {{-- <link rel="shortcut icon" href="{{ $favicon ? asset("storage/app/public/business/{$favicon}") : '' }}"
        onerror="this.src='{{ asset('public/assets/admin-module/img/favicon.png') }}'" /> --}}
    <link rel="shortcut icon"
          href="{{ onErrorImage(
            $favicon,
            asset('storage/app/public/business') . '/' . $favicon,
            asset('public/assets/admin-module/img/favicon.png'),
            'business/',
        ) }}"/>
    <!-- Web Fonts -->
    <link rel="stylesheet" href="{{ asset('public/assets/admin-module/css/fonts/google.css') }}"/>

    <!-- ======= BEGIN GLOBAL MANDATORY STYLES ======= -->
    <link rel="stylesheet" href="{{ asset('public/assets/admin-module/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('public/assets/admin-module/css/bootstrap-icons.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('public/assets/admin-module/plugins/icon-set/style.css') }}"/>
    <link rel="stylesheet"
          href="{{ asset('public/assets/admin-module/plugins/perfect-scrollbar/perfect-scrollbar.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('public/assets/admin-module/css/toastr.css') }}"/>
    <!-- ======= END BEGIN GLOBAL MANDATORY STYLES ======= -->

    <!-- ======= BEGIN PAGE LEVEL PLUGINS STYLES ======= -->
    <link rel="stylesheet" href="{{ asset('public/assets/admin-module/plugins/apex/apexcharts.css') }}"/>
    <link rel="stylesheet" href="{{ asset('public/assets/admin-module/plugins/select2/select2.min.css') }}"/>
    <!-- ======= END BEGIN PAGE LEVEL PLUGINS STYLES ======= -->

    <link href="{{ asset('public/assets/admin-module/css/intlTelInput.min.css') }}" rel="stylesheet"/>

    <!-- ======= MAIN STYLES ======= -->
    <link rel="stylesheet" href="{{ asset('public/assets/admin-module/css/style.css') }}"/>
    <link rel="stylesheet" href="{{ asset('public/assets/admin-module/css/custom.css') }}"/>
    @include('adminmodule::layouts.css')
    <!-- ======= END MAIN STYLES ======= -->

    <!-- ======= FOR CUSTOM STYLE ======= -->
    @stack('css_or_js')
    @stack('css_or_js2')
</head>

<body>
{{--    <div id="customModal" class="modal">--}}
{{--        <div class="modal-content">--}}
{{--            <span class="close">&times;</span>--}}
{{--            <div class="modal-body">--}}
{{--                <img src="path-to-your-image/level-up.png" alt="Level Up">--}}
{{--                <p>By Turning ON Level Feature</p>--}}
{{--                <p>If you turn ON level feature, driver will show this feature on app</p>--}}
{{--            </div>--}}
{{--            <div class="modal-footer">--}}
{{--                <button id="modalOkBtn" class="modal-btn">OK</button>--}}
{{--                <button id="modalCancelBtn" class="modal-btn">Cancel</button>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
<script>
    localStorage.theme && document.querySelector('body').setAttribute("theme", localStorage.theme);
    localStorage.dir && document.querySelector('html').setAttribute("dir", localStorage.dir);
</script>

<!-- Offcanval Overlay -->
<div class="offcanvas-overlay"></div>
<!-- Offcanval Overlay -->
<!-- Preloader -->
<div class="preloader" id="preloader">
    @if ($preloader)
        <img class="preloader-img" width="160" loading="eager"
             src="{{ $preloader ? asset('storage/app/public/business/' . $preloader) : '' }}" alt="">
    @else
        <div class="spinner-grow" role="status">
            <span class="visually-hidden">{{ translate('Loading...') }}</span>
        </div>
    @endif
</div>
<div class="resource-loader" id="resource-loader" style="display: none;">
    @if ($preloader)
        <img width="160" loading="eager"
             src="{{ asset('storage/app/public/business') }}/{{ $preloader ?? null }}" alt="">
    @else
        <div class="spinner-grow" role="status">
            <span class="visually-hidden">{{ translate('Loading...') }}</span>
        </div>
    @endif
</div>
<!-- End Preloader -->

<!-- Header -->
@include('adminmodule::partials._header')
<!-- End Header -->

<!-- Aside -->
@include('adminmodule::partials._sidebar')
<!-- End Aside -->

<!-- Settings Sidebar -->
@include('adminmodule::partials._settings')
<!-- End Settings Sidebar -->


<!-- Wrapper -->
<main class="main-area">
    @include('adminmodule::modal._custom-modal')

    <!-- Main Content -->
    @yield('content')
    <!-- End Main Content -->

    <!-- Footer -->
    @include('adminmodule::partials._footer')
    <!-- End Footer -->

</main>
<!-- End wrapper -->

<span class="system-default-country-code" data-value="{{ getSession('country_code') ?? 'us' }}"></span>

<script src="{{ asset('public/assets/admin-module/js/firebase.min.js') }}"></script>

<!-- ======= BEGIN GLOBAL MANDATORY SCRIPTS ======= -->
<script src="{{ asset('public/assets/admin-module/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('public/assets/admin-module/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('public/assets/admin-module/plugins/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('public/assets/admin-module/plugins/select2/select2.min.js') }}"></script>
{{-- TOASTR and SWEETALERT --}}
<script src="{{ asset('public/assets/admin-module/js/sweet_alert.js') }}"></script>
<script src="{{ asset('public/assets/admin-module/js/toastr.js') }}"></script>
<script src="{{ asset('public/assets/admin-module/js/dev.js') }}"></script>

<script src="{{ asset('public/assets/admin-module/js/intlTelInput.min.js') }}"></script>
<script src="{{ asset('public/assets/admin-module/js/country-picker-init.js') }}"></script>
<script src="{{ asset('public/assets/admin-module/js/main.js') }}"></script>
<!-- ======= BEGIN GLOBAL MANDATORY SCRIPTS ======= -->

{!! Toastr::message() !!}
@if ($errors->any())
    <script>
        "use strict";
        @foreach ($errors->all() as $error)
        toastr.error('{{ $error }}', Error, {
            CloseButton: true,
            ProgressBar: true,
        });
        @endforeach
    </script>
@endif
<script>
    "use strict";

    $(document).on('click', '.call-demo', function () {
        @if(env('APP_MODE') =='demo')
        toastr.info('{{ translate('Update option is disabled for demo!') }}', {
            CloseButton: true,
            ProgressBar: true
        });
        @endif
    });
    $(".status-change-level").on('change', function () {
        statusAlertNew(this);
    })

    function statusAlertNew(obj) {
        let url = $(obj).data('url');
        let iconContent = $(obj).data('icon');
        let titleContent = $(obj).data('title');
        let subTitleContent = $(obj).data('sub-title');
        let checked = $(obj).prop("checked");
        let status = checked === true ? 1 : 0;

        // Show custom modal
        let bootstrapModal = new bootstrap.Modal(document.getElementById("customModal"));
        bootstrapModal.show();

        if (iconContent) {
            $("#icon").attr('src', iconContent);
        }
        if (titleContent) {
            $("#title").html("");
            $("#title").html(titleContent);
        }
        if (subTitleContent) {
            $("#subTitle").html("");
            $("#subTitle").html(subTitleContent);
        }
        let confirmBtn = document.getElementById("modalConfirmBtn");
        let cancelBtn = document.getElementById("modalCancelBtn");


        // // When the user clicks on OK button
        confirmBtn.onclick = function () {
            $.ajax({
                url: url,
                _method: 'PUT',
                data: {
                    status: status,
                    id: obj.id
                },
                success: function () {
                    toastr.success("{{ translate('status_changed_successfully') }}");
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                },
                error: function () {
                    resetCheckbox();
                    toastr.error("{{ translate('status_change_failed') }}");
                }
            });
            bootstrapModal.hide();
        }

        // When the user clicks on Cancel button
        cancelBtn.onclick = function () {
            bootstrapModal.hide();
            resetCheckbox();
        }

        function resetCheckbox() {
            if (status === 1) {
                $('#' + obj.id).prop('checked', false);
            } else if (status === 0) {
                $('#' + obj.id).prop('checked', true);
            }
        }
    }


    $(".status-change").on('change', function () {
        statusAlert(this);
    })

    function statusAlert(obj) {
        let url = $(obj).data('url');
        let checked = $(obj).prop("checked");
        let status = checked === true ? 1 : 0;
        Swal.fire({
            title: '{{ translate('are_you_sure') }}?',
            text: '{{ translate('want_to_change_status') }}',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'var(--bs-primary)',
            cancelButtonColor: 'default',
            cancelButtonText: '{{ translate('no') }}',
            confirmButtonText: '{{ translate('yes') }}',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: url,
                    _method: 'PUT',
                    data: {
                        status: status,
                        id: obj.id
                    },
                    success: function () {
                        toastr.success("{{ translate('status_changed_successfully') }}");
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    },
                    error: function () {
                        if (status === 1) {
                            $('#' + obj.id + '.status-change').prop('checked', false)
                        } else if (status === 0) {
                            $('#' + obj.id + '.status-change').prop('checked', true)
                        }
                        toastr.error("{{ translate('status_change_failed') }}");
                    }
                });
            } else {
                if (status === 1) {
                    $('#' + obj.id + '.status-change').prop('checked', false)
                } else if (status === 0) {
                    $('#' + obj.id + '.status-change').prop('checked', true)
                }
            }
        })
    }

    $('.custom_status_change').on('change', function () {
        customStatusChange(this)
    })

    function customStatusChange(obj) {
        let url = $(obj).data('url');
        let iconContent = $(obj).data('icon');
        let titleContent = $(obj).data('title');
        let subTitleContent = $(obj).data('sub-title');
        let confirmBtnContent = $(obj).data('confirm-btn');
        let cancelBtnContent = $(obj).data('cancel-btn');

        let checked = $(obj).prop("checked");
        let status = checked === true ? 1 : 0;

        const modalElement = document.getElementById('customModalForStatus');
        let bootstrapModal = new bootstrap.Modal(modalElement);
        bootstrapModal.show();

        if (iconContent) {
            $("#iconForStatus").attr('src', iconContent);
            $('.swal2-animate-warning-icon').addClass('d-none');
            $('.swal2-animate-warning-icon').removeClass('d-flex');
        }
        if (titleContent) {
            $("#titleForStatus").html("");
            $("#titleForStatus").html(titleContent);
        }
        if (subTitleContent) {
            $("#subTitleForStatus").html("");
            $("#subTitleForStatus").html(subTitleContent);
        }
        if (confirmBtnContent) {
            $("#modalConfirmBtnForStatus").html("");
            $("#modalConfirmBtnForStatus").html(confirmBtnContent);
        }
        if (cancelBtnContent) {
            $("#modalCancelBtnForStatus").html("");
            $("#modalCancelBtnForStatus").html(cancelBtnContent);
        }


        let confirmBtn = document.getElementById("modalConfirmBtnForStatus");
        let cancelBtn = document.getElementById("modalCancelBtnForStatus");

        confirmBtn.onclick = function () {
            $.ajax({
                url: url,
                _method: 'PUT',
                data: {id: obj.id, status: status},
                success: function () {
                    toastr.success("{{ translate('status_changed_successfully') }}");
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                },
                error: function () {
                    resetCheckbox();
                    toastr.error("{{ translate('status_change_failed') }}");
                }
            });
            bootstrapModal.hide();
        }

        cancelBtn.onclick = function () {
            bootstrapModal.hide();
            resetCheckbox();
        }

        // $('.btn-close').on('click', function () {
        //     bootstrapModal.hide();
        //     resetCheckbox();
        // });

        modalElement.addEventListener('hidden.bs.modal', function () {
            resetCheckbox();
        });

        function resetCheckbox() {
            if (status === 1) {
                $('#' + obj.id).prop('checked', false);
            } else if (status === 0) {
                $('#' + obj.id).prop('checked', true);
            }
        }
    }


    $(".maintenance-off").on('change', function () {
        maintenanceOff(this);
    })

    function maintenanceOff(obj) {
        let url = $(obj).data('url');
        let checked = $(obj).prop("checked");
        let status = checked === true ? 1 : 0;
        Swal.fire({
            title: '{{ translate('are_you_sure') }}?',
            text: '{{ translate('Do you want to turn off Maintenance mode? Turning it off will activate all systems that were deactivated.') }}',
            imageUrl: "{{asset("public/assets/admin-module/img/maintenance_off.svg")}}",
            imageAlt: "Custom image",
            showCancelButton: true,
            confirmButtonColor: 'var(--bs-primary)',
            cancelButtonColor: 'default',
            cancelButtonText: '{{ translate('discard') }}',
            confirmButtonText: '{{ translate('yes') }}',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: url,
                    _method: 'PUT',
                    data: {
                        status: status,
                        id: obj.id
                    },
                    success: function () {
                        toastr.success("{{ translate('maintenance_mode_successfully_off') }}");
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    },
                    error: function () {
                        if (status === 1) {
                            $('#' + obj.id).prop('checked', false)
                        } else if (status === 0) {
                            $('#' + obj.id).prop('checked', true)
                        }
                        toastr.error("{{ translate('status_change_failed') }}");
                    }
                });
            } else {
                if (status === 1) {
                    $('#' + obj.id).prop('checked', false)
                } else if (status === 0) {
                    $('#' + obj.id).prop('checked', true)
                }
            }
        })
    }


    $('.update-business-setting').on('change', function () {
        updateBusinessSettingLevel(this)
    });


    function updateBusinessSettingLevel(obj) {
        if (!permission) {
            toastr.error('{{ translate('you_do_not_have_enough_permission_to_update_this_settings') }}');

            let checked = $(obj).prop("checked");
            let status = checked === true ? 1 : 0;
            if (status === 1) {
                $('#' + obj.id).prop('checked', false)

            } else if (status === 0) {
                $('#' + obj.id).prop('checked', true)
            }
            return;
        }
        let url = $(obj).data('url');
        let iconContent = $(obj).data('icon');
        let titleContent = $(obj).data('title');
        let subTitleContent = $(obj).data('sub-title');
        let confirmBtnContent = $(obj).data('confirm-btn');
        let cancelBtnContent = $(obj).data('cancel-btn');


        let value = $(obj).prop('checked') === true ? 1 : 0;
        let name = $(obj).data('name');
        let type = $(obj).data('type');
        let checked = $(obj).prop("checked");
        let status = checked === true ? 1 : 0;

        // Show custom modal
        const modalElement = document.getElementById('customModal');
        let bootstrapModal = new bootstrap.Modal(modalElement);
        bootstrapModal.show();

        if (iconContent) {
            $("#icon").attr('src', iconContent);
        }
        if (titleContent) {
            $("#title").html("");
            $("#title").html(titleContent);
        }
        if (subTitleContent) {
            $("#subTitle").html("");
            $("#subTitle").html(subTitleContent);
        }
        if (confirmBtnContent) {
            $("#modalConfirmBtn").html("");
            $("#modalConfirmBtn").html(confirmBtnContent);
        }
        if (cancelBtnContent) {
            $("#modalCancelBtn").html("");
            $("#modalCancelBtn").html(cancelBtnContent);
        }


        let confirmBtn = document.getElementById("modalConfirmBtn");
        let cancelBtn = document.getElementById("modalCancelBtn");


        // // When the user clicks on OK button
        confirmBtn.onclick = function () {
            $.ajax({
                url: url,
                _method: 'PUT',
                data: {value: value, name: name, type: type},
                success: function () {
                    toastr.success("{{ translate('status_changed_successfully') }}");
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                },
                error: function () {
                    resetCheckbox();
                    toastr.error("{{ translate('status_change_failed') }}");
                }
            });
            bootstrapModal.hide();
        }

        // When the user clicks on Cancel button
        cancelBtn.onclick = function () {
            bootstrapModal.hide();
            resetCheckbox();
        }
        modalElement.addEventListener('hidden.bs.modal', function () {
            resetCheckbox();
        });

        function resetCheckbox() {
            if (status === 1) {
                $('#' + obj.id).prop('checked', false);
            } else if (status === 0) {
                $('#' + obj.id).prop('checked', true);
            }
        }
    }


    $('.update-extra-fare-setting').on('change', function () {
        updateExtraFareSetting(this)
    })

    function updateExtraFareSetting(obj) {
        let url = $(obj).data('url');
        let iconContent = $(obj).data('icon');
        let titleContent = $(obj).data('title');
        let subTitleContent = $(obj).data('sub-title');
        let confirmBtnContent = $(obj).data('confirm-btn');
        let cancelBtnContent = $(obj).data('cancel-btn');

        let checked = $(obj).prop("checked");
        let status = checked === true ? 1 : 0;

        // Show custom modal
        const modalElement = document.getElementById('customModal');
        let bootstrapModal = new bootstrap.Modal(modalElement);
        bootstrapModal.show();

        if (iconContent) {
            $("#icon").attr('src', iconContent);
        }
        if (titleContent) {
            $("#title").html("");
            $("#title").html(titleContent);
        }
        if (subTitleContent) {
            $("#subTitle").html("");
            $("#subTitle").html(subTitleContent);
        }
        if (confirmBtnContent) {
            $("#modalConfirmBtn").html("");
            $("#modalConfirmBtn").html(confirmBtnContent);
        }
        if (cancelBtnContent) {
            $("#modalCancelBtn").html("");
            $("#modalCancelBtn").html(cancelBtnContent);
        }


        let confirmBtn = document.getElementById("modalConfirmBtn");
        let cancelBtn = document.getElementById("modalCancelBtn");


        // // When the user clicks on OK button
        confirmBtn.onclick = function () {
            $.ajax({
                url: url,
                _method: 'PUT',
                data: {
                    status: status,
                    id: obj.id
                },
                success: function () {
                    toastr.success("{{ translate('status_changed_successfully') }}");
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                },
                error: function () {
                    resetCheckbox();
                    toastr.error("{{ translate('status_change_failed') }}");
                }
            });
            bootstrapModal.hide();
        }

        // When the user clicks on Cancel button
        cancelBtn.onclick = function () {
            bootstrapModal.hide();
            resetCheckbox();
        }
        modalElement.addEventListener('hidden.bs.modal', function () {
            resetCheckbox();
        });

        function resetCheckbox() {
            if (status === 1) {
                $('#' + obj.id).prop('checked', false);
            } else if (status === 0) {
                $('#' + obj.id).prop('checked', true);
            }
        }
    }

    $(".default-status").on('change', function () {
        defaultStatusAlert(this);
    })

    function defaultStatusAlert(obj) {
        let url = $(obj).data('url');
        let checked = $(obj).prop("checked");
        let status = checked === true ? 1 : 0;
        Swal.fire({
            title: '{{ translate('are_you_sure') }}?',
            text: '{{ translate('want_to_change_default_status') }}',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'var(--bs-primary)',
            cancelButtonColor: 'default',
            cancelButtonText: '{{ translate('no') }}',
            confirmButtonText: '{{ translate('yes') }}',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: url,
                    _method: 'PUT',
                    data: {
                        status: status,
                        id: obj.id
                    },
                    success: function () {
                        toastr.success("{{ translate('default_status_changed_successfully') }}");
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    },
                    error: function () {
                        if (status === 1) {
                            $('#' + obj.id + '.default-status').prop('checked', false)
                        } else if (status === 0) {
                            $('#' + obj.id + '.default-status').prop('checked', true)
                        }
                        toastr.error("{{ translate('default_status_change_failed') }}");
                    }
                });
            } else {
                if (status === 1) {
                    $('#' + obj.id + '.default-status').prop('checked', false)
                } else if (status === 0) {
                    $('#' + obj.id + '.default-status').prop('checked', true)
                }
            }
        })
    }

    $(".form-alert").on('click', function () {
        let id = $(this).data('id');
        let message = $(this).data('message');
        formAlert(id, message)
    })

    function formAlert(id, message) {
        Swal.fire({
            title: '{{ translate('are_you_sure') }}?',
            text: message,
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: 'default',
            confirmButtonColor: 'var(--bs-danger)',
            cancelButtonText: '{{ translate('no') }}',
            confirmButtonText: '{{ translate('yes') }}',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                $('#' + id).submit()
            }
        })
    }

    $(".form-alert-approved-rejected").on('click', function () {
        let id = $(this).data('id');
        let message = $(this).data('message');
        formAlertApprovedRejected(id, message)
    })

    function formAlertApprovedRejected(id, message) {
        Swal.fire({
            title: '{{ translate('are_you_sure') }}?',
            text: message,
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: 'default',
            confirmButtonColor: 'var(--bs-danger)',
            cancelButtonText: '{{ translate('no') }}',
            confirmButtonText: '{{ translate('yes') }}',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                $('#' + id).submit()
            }
        })
    }

    $(".form-alert-warning").on('click', function () {
        let id = $(this).data('id');
        let message = $(this).data('message');
        formAlertWarning(id, message)
    })

    function formAlertWarning(id, message) {
        Swal.fire({
            title: '{{ translate('warning') }}!',
            imageUrl: '{{asset('public/assets/admin-module/img/warning.png')}}',
            text: message,
            showCloseButton: true,
            showConfirmButton: false
        })
    }

    $(".restore-data").on('click', function () {
        let route = $(this).data('route');
        let message = $(this).data('message');
        restoreData(route, message)
    })

    function restoreData(route, message) {
        Swal.fire({
            title: '{{ translate('are_you_sure') }}?',
            text: message,
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: 'default',
            confirmButtonColor: 'var(--bs-primary)',
            cancelButtonText: '{{ translate('no') }}',
            confirmButtonText: '{{ translate('yes') }}',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                window.location.href = route;
            }
        })
    }

    function loadPartialView(url, divId, data = null) {
        $.get({
            url: url,
            dataType: 'json',
            data: {
                data
            },
            beforeSend: function () {
                $('#resource-loader').show();
            },
            success: function (response) {
                $(divId).empty().html(response)
            },
            complete: function () {
                $('#resource-loader').hide();
            },
            error: function () {
                $('#resource-loader').hide();
                toastr.error('{{ translate('failed_to_load_data') }}')
            },
        });
    }

    function seenNotification(id) {
        $.get({
            url: '{{ route('admin.seen-notification') }}',
            dataType: 'json',
            data: {
                id: id
            },
            success: function (response) {
                $('#resource-loader').hide();
                if (id == 0) {
                    location.reload()
                }
            }
        });

    }

    function getNotifications() {
        $.get({
            url: '{{ route('admin.get-notifications') }}',
            dataType: 'json',
            success: function (response) {
                $('#notification').empty().html(response)
                commonFunctionRecall();
            },
            error: function (xhr, status, error) {
            },
        });
    }


    function commonFunctionRecall() {
        $('.seen-notification').on('click', function () {
            let id = $(this).data('value');
            seenNotification(id)
        })
    }

    getNotifications();
    setInterval(getNotifications, 15000);

    document.addEventListener("DOMContentLoaded", function () {
        let checkboxes = document.querySelectorAll(".dynamic-checkbox-toggle");
        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener("click", function (event) {
                event.preventDefault();
                const checkboxId = checkbox.getAttribute("data-id");
                const imageOn = checkbox.getAttribute("data-image-on");
                const imageOff = checkbox.getAttribute("data-image-off");
                const titleOn = checkbox.getAttribute("data-title-on");
                const titleOff = checkbox.getAttribute("data-title-off");
                const textOn = checkbox.getAttribute("data-text-on");
                const textOff = checkbox.getAttribute("data-text-off");
                const confirmButtonTextON = checkbox.getAttribute("data-button-on") ?? '{{translate('Ok')}}';
                const confirmButtonTextOFF = checkbox.getAttribute("data-button-off") ?? '{{translate('Ok')}}';

                const isChecked = checkbox.checked;

                if (isChecked) {
                    $("#toggle-title").empty().append(titleOn);
                    $("#toggle-message").empty().append(textOn);
                    $("#toggle-image").attr("src", imageOn);
                    $("#toggle-ok-button").html(confirmButtonTextON);
                    $("#toggle-ok-button").attr("toggle-ok-button", checkboxId);
                } else {
                    $("#toggle-title").empty().append(titleOff);
                    $("#toggle-message").empty().append(textOff);
                    $("#toggle-image").attr("src", imageOff);
                    $("#toggle-ok-button").html(confirmButtonTextOFF);
                    $("#toggle-ok-button").attr("toggle-ok-button", checkboxId);
                }

                $("#toggle-modal").modal("show");
            });
        });
    });
    $(document).on("click", ".confirm-Toggle", function () {
        let toggle_id = $("#toggle-ok-button").attr("toggle-ok-button");
        if ($("#" + toggle_id).is(":checked")) {
            $("#" + toggle_id).prop("checked", false);
        } else {
            $("#" + toggle_id).prop("checked", true);
        }
        $("#toggle-modal").modal("hide");
    });
    $("#approvalButtonParcelRefund, .approval-button-parcel-refund").on('click', function () {
        parcelRefundAction(this)
    })
    $("#deniedButtonParcelRefund, .denied-button-parcel-refund").on('click', function () {
        parcelRefundAction(this)
    })
    $("#parcelRefundButton, .parcel-refund-button").on('click', function () {
        parcelMakeRefundAction(this)
    })

    function parcelRefundAction(obj) {

        let url = $(obj).data('url');
        let iconContent = $(obj).data('icon');
        let titleContent = $(obj).data('title');
        let subTitleContent = $(obj).data('sub-title');
        let inputLabelContent = $(obj).data('input-title');
        let confirmBtnContent = $(obj).data('confirm-btn');
        let cancelBtnContent = $(obj).data('cancel-btn');

        // Show custom modal
        const modalElement = document.getElementById('parcelRefundModal');
        let bootstrapModal = new bootstrap.Modal(modalElement);
        bootstrapModal.show();
        if (url) {
            $('#parcelRefundForm').attr('action', '');
            $('#parcelRefundForm').attr('action', url);
        }
        if (iconContent) {
            $("#parcelRefundIcon").attr('src', iconContent);
        }
        if (titleContent) {
            $("#parcelRefundTitle").html("");
            $("#parcelRefundTitle").html(titleContent);
        }
        if (subTitleContent) {
            $("#parcelRefundSubTitle").html("");
            $("#parcelRefundSubTitle").html(subTitleContent);
        }
        if (inputLabelContent) {
            $("#inputLabelTitle").html("");
            $("#inputLabelTitle").html(inputLabelContent);
        }
        if (confirmBtnContent) {
            $("#parcelRefundModalConfirmBtn").html("");
            $("#parcelRefundModalConfirmBtn").html(confirmBtnContent);
        }
        if (cancelBtnContent) {
            $("#parcelRefundModalCancelBtn").html("");
            $("#parcelRefundModalCancelBtn").html(cancelBtnContent);
        }
    }

    function parcelMakeRefundAction(obj) {
        let url = $(obj).data('url');
        let amount = $(obj).data('amount');
        $("#refundAmount").val(amount);
        // Show custom modal
        const modalElement = document.getElementById('parcelMakeRefundModal');
        let bootstrapModal = new bootstrap.Modal(modalElement);
        bootstrapModal.show();
        if (url) {
            $('#parcelMakeRefundForm').attr('action', '');
            $('#parcelMakeRefundForm').attr('action', url);
        }
    }

    $(".approval-button-vehicle-request, .approval-button-vehicle-update").on('click', function () {
        vehicleRequestApprovalAction(this);
    });

    function vehicleRequestApprovalAction(obj) {
        let url = $(obj).data('url');
        let iconContent = $(obj).data('icon');
        let titleContent = $(obj).data('title');
        let subTitleContent = $(obj).data('sub-title');
        let confirmBtnContent = $(obj).data('confirm-btn');
        let cancelBtnContent = $(obj).data('cancel-btn');

        // Show custom modal
        const modalElement = document.getElementById('vehicleRequestApprovalModal');
        let bootstrapModal = new bootstrap.Modal(modalElement);
        bootstrapModal.show();
        if (url) {
            $('#vehicleRequestApprovalForm').attr('action', '');
            $('#vehicleRequestApprovalForm').attr('action', url);
        }
        if (iconContent) {
            $("#vehicleRequestApprovalIcon").attr('src', iconContent);
        }
        if (titleContent) {
            $("#vehicleRequestApprovalTitle").html("");
            $("#vehicleRequestApprovalTitle").html(titleContent);
        }
        if (subTitleContent) {
            $("#vehicleRequestApprovalSubTitle").html("");
            $("#vehicleRequestApprovalSubTitle").html(subTitleContent);
        }
        if (confirmBtnContent) {
            $("#vehicleRequestApprovalModalConfirmBtn").html("");
            $("#vehicleRequestApprovalModalConfirmBtn").html(confirmBtnContent);
        }
        if (cancelBtnContent) {
            $("#vehicleRequestApprovalModalCancelBtn").html("");
            $("#vehicleRequestApprovalModalCancelBtn").html(cancelBtnContent);
        }
    }


    $(".deny-button-vehicle-request").on('click', function () {
        vehicleRequestDenyAction(this);
    });
    $(".deny-button-vehicle-update").on('click', function () {
        vehicleUpdateDenyAction(this);
    });

    function vehicleUpdateDenyAction(obj) {
        let url = $(obj).data('url');
        let iconContent = $(obj).data('icon');
        let titleContent = $(obj).data('title');
        let subTitleContent = $(obj).data('sub-title');
        let confirmBtnContent = $(obj).data('confirm-btn');
        let cancelBtnContent = $(obj).data('cancel-btn');

        // Show custom modal
        const modalElement = document.getElementById('vehicleUpdateDenyModal');
        let bootstrapModal = new bootstrap.Modal(modalElement);
        bootstrapModal.show();
        if (url) {
            $('#vehicleUpdateDenyForm').attr('action', '');
            $('#vehicleUpdateDenyForm').attr('action', url);
        }
        if (iconContent) {
            $("#vehicleUpdateDenyIcon").attr('src', iconContent);
        }
        if (titleContent) {
            $("#vehicleUpdateDenyTitle").html("");
            $("#vehicleUpdateDenyTitle").html(titleContent);
        }
        if (subTitleContent) {
            $("#vehicleUpdateDenySubTitle").html("");
            $("#vehicleUpdateDenySubTitle").html(subTitleContent);
        }
        if (confirmBtnContent) {
            $("#vehicleUpdateDenyModalConfirmBtn").html("");
            $("#vehicleUpdateDenyModalConfirmBtn").html(confirmBtnContent);
        }
        if (cancelBtnContent) {
            $("#vehicleUpdateDenyModalCancelBtn").html("");
            $("#vehicleUpdateDenyModalCancelBtn").html(cancelBtnContent);
        }
    }

    function vehicleRequestDenyAction(obj) {
        let url = $(obj).data('url');
        let iconContent = $(obj).data('icon');
        let titleContent = $(obj).data('title');
        let subTitleContent = $(obj).data('sub-title');
        let confirmBtnContent = $(obj).data('confirm-btn');
        let cancelBtnContent = $(obj).data('cancel-btn');

        // Show custom modal
        const modalElement = document.getElementById('vehicleRequestDenyModal');
        let bootstrapModal = new bootstrap.Modal(modalElement);
        bootstrapModal.show();
        if (url) {
            $('#vehicleRequestDenyForm').attr('action', '');
            $('#vehicleRequestDenyForm').attr('action', url);
        }
        if (iconContent) {
            $("#vehicleRequestDenyIcon").attr('src', iconContent);
        }
        if (titleContent) {
            $("#vehicleRequestDenyTitle").html("");
            $("#vehicleRequestDenyTitle").html(titleContent);
        }
        if (subTitleContent) {
            $("#vehicleRequestDenySubTitle").html("");
            $("#vehicleRequestDenySubTitle").html(subTitleContent);
        }
        if (confirmBtnContent) {
            $("#vehicleRequestDenyModalConfirmBtn").html("");
            $("#vehicleRequestDenyModalConfirmBtn").html(confirmBtnContent);
        }
        if (cancelBtnContent) {
            $("#vehicleRequestDenyModalCancelBtn").html("");
            $("#vehicleRequestDenyModalCancelBtn").html(cancelBtnContent);
        }
    }

    // delete  modal for all
    $(".delete-button").on('click', function () {
        deleteAction(this);
    });

    //deleteAction
    function deleteAction(obj) {
        let url = $(obj).data('url');
        let iconContent = $(obj).data('icon');
        let titleContent = $(obj).data('title');
        let subTitleContent = $(obj).data('sub-title');
        let confirmBtnContent = $(obj).data('confirm-btn');
        let cancelBtnContent = $(obj).data('cancel-btn');

        // Show custom modal
        const modalElement = document.getElementById('deleteModal');
        let bootstrapModal = new bootstrap.Modal(modalElement);
        bootstrapModal.show();
        if (url) {
            $('#deleteForm').attr('action', '');
            $('#deleteForm').attr('action', url);
        }
        if (iconContent) {
            $("#deleteIcon").attr('src', iconContent);
        }
        if (titleContent) {
            $("#deleteTitle").html("");
            $("#deleteTitle").html(titleContent);
        }
        if (subTitleContent) {
            $("#deleteSubTitle").html("");
            $("#deleteSubTitle").html(subTitleContent);
        }
        if (confirmBtnContent) {
            $("#deleteModalConfirmBtn").html("");
            $("#deleteModalConfirmBtn").html(confirmBtnContent);
        }
        if (cancelBtnContent) {
            $("#deleteModalCancelBtn").html("");
            $("#deleteModalCancelBtn").html(cancelBtnContent);
        }
    }


</script>
{{--Remove non-numeric characters from the input value  with type="tel" --}}
<script>
    document.addEventListener('input', function (event) {

        if (event.target.tagName === 'INPUT' && event.target.type === 'tel') {
            validateNumbers(event.target);
        }
    });

    function validateNumbers(input) {
        input.value = input.value.replace(/\D/g, '');
    }

    // Function to get the file icon
    function getFileIcon(fileName) {
        const asset = "{{ asset('public/assets/admin-module/img/file-format/svg') }}";
        const extension = fileName.split('.').pop().toLowerCase();
        switch (extension) {
            case 'pdf':
                return `${asset}/pdf.svg`;
            case 'cvc':
                return `${asset}/cvc.svg`;
            case 'csv':
                return `${asset}/csv.svg`;
            case 'doc':
            case 'docx':
                return `${asset}/doc.svg`;
            case 'jpg':
                return `${asset}/jpg.svg`;
            case 'jpeg':
                return `${asset}/jpeg.svg`;
            case 'webp':
                return `${asset}/webp.svg`;
            case 'png':
                return `${asset}/png.svg`;
            case 'xls':
                return `${asset}/xls.svg`;
            case 'xlsx':
                return `${asset}/xlsx.svg`;
            default:
                return "{{ asset('public/assets/admin-module/img/document-upload.png') }}";
        }
    }
    $("body").on("click", ".file__value", function () {
        $(this).remove();
    });

    /*============================================
12: Multiple file upload
==============================================*/

    let newSelectedFiles = [];

    // Event listener to handle file selection
    document.querySelector(".upload-file__input2").addEventListener("change", function (event) {
        // Add selected files to the selectedFiles array
        for (let i = 0; i < event.target.files.length; i++) {
            newSelectedFiles.push(event.target.files[i]);
        }
        newDisplaySelectedFiles();
    });

    // Function to display selected files
    function newDisplaySelectedFiles() {
        const container1 = document.getElementById("input-data");
        container1.innerHTML = "";
        newSelectedFiles.forEach((file, index) => {
            const input = document.createElement("input");
            input.type = "file";
            input.name = `other_documents[${index}]`;
            input.classList.add(`file-index${index}`);
            input.hidden = true;
            container1.appendChild(input);
            const blob = new Blob([file], {type: file.type});
            const file_obj = new File([file], file.name);
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file_obj);
            input.files = dataTransfer.files;
        });
        const container = document.getElementById("selected-files-container1");
        container.innerHTML = ""; // Clear existing content

        newSelectedFiles.forEach((file, index) => {
            // Create a new div for the file display
            const fileDiv = document.createElement("div");
            fileDiv.classList.add("show-image");

            // Create the file value container
            const fileValueDiv = document.createElement("div");
            fileValueDiv.classList.add("file__value", "bg-transparent", "border", "border-C5D2D2", "remove_outside");
            fileValueDiv.setAttribute("data-document", file.name);

            // Create the icon for the file (this assumes PDF files for now)
            const fileIcon = document.createElement("img");
            fileIcon.classList.add("file__value--icon");
            fileIcon.src = getFileIcon(file.name); // Set the icon based on file type

            // Create the text displaying the file name
            const fileText = document.createElement("div");
            fileText.classList.add("file__value--text");
            fileText.textContent = file.name;

            // Create the remove button
            const removeButton = document.createElement("div");
            removeButton.classList.add("file__value--remove", "fw-bold");
            removeButton.setAttribute("data-id", file.name);
            removeButton.innerHTML = `<img src="{{ asset('public/assets/admin-module/img/icons/close-circle.svg') }}" alt="">`;

            // Append everything to the file value div
            fileValueDiv.appendChild(fileIcon);
            fileValueDiv.appendChild(fileText);
            fileValueDiv.appendChild(removeButton);

            // Add the file value div to the container
            fileDiv.appendChild(fileValueDiv);
            container.appendChild(fileDiv);

            // Handle file removal
            removeButton.addEventListener("click", function () {
                // Remove file div from the DOM
                fileDiv.remove();

                // Remove the file from the selectedFiles array
                newSelectedFiles.splice(newSelectedFiles.indexOf(file), 1);

                // Optionally update the UI after removal
                newDisplaySelectedFiles();
            });
        });
    }

</script>
@stack('script')
@stack('script2')

</body>

</html>
