@extends('adminmodule::layouts.master')

@section('title', translate('notification'))

@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="fs-22 mb-4 text-capitalize">{{ translate('notifications') }}</h2>

            <div class="mb-4">
                <ul class="nav d-inline-flex nav--tabs p-1 rounded bg-white">
                    <li class="nav-item">
                        <a href="{{route('admin.business.configuration.notification.index')}}"
                           class="nav-link text-capitalize {{Request::is('admin/business/configuration/notification')? "active":""}}">{{ translate('notification_message') }}</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('admin.business.configuration.notification.firebase-configuration')}}"
                           class="nav-link text-capitalize {{Request::is('admin/business/configuration/notification/firebase-configuration')? "active":""}}">{{ translate('firebase_configuration') }}</a>
                    </li>
                </ul>
            </div>

            <div class="card border-0 mb-3">
                <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <div class="">
                        <h5 class="text-capitalize mb-2">{{ translate('push_notification_setup') }}</h5>
                        <div
                            class="fs-12">{{ translate('Here you can set sending notification On/Off based on the case.') }}</div>
                    </div>

                    <button
                        class="btn border rounded-pill bg-primary-light border-primary-light btn-link text-decoration-none"
                        data-bs-toggle="modal" data-bs-target="#how_it_work">
                        <i class="bi bi-question-circle"></i>
                        {{ translate('how_it_work') }}
                    </button>
                </div>

                <div class="card-body p-30">
                    <form action="{{ route('admin.business.configuration.notification.store') }}" method="post"
                          id="server_key_form">
                        @csrf
                        <div class="mb-4">
                            <label for="server_key" class="mb-3 fw-medium text-dark">
                                {{ translate('service_account_content') }}
                                <i class="bi bi-info-circle-fill text-primary tooltip-icon" data-bs-toggle="tooltip"
                                   data-bs-title="{{ translate('Select and copy all the service file content and add here') }}"></i>
                            </label>
                            <textarea name="server_key" id="server_key" placeholder="Type Here..."
                                      class="form-control color-border-focus {{ $settings?->value ? 'color-border' : '' }}"
                                      cols="30" rows="10"
                                      required {{ env('APP_MODE') == 'demo' ? 'disabled' : '' }}>{{ env("APP_MODE") != "demo" ? $settings?->value : "..................." }}</textarea>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-3">
                                <button class="btn btn-primary text-uppercase call-demo"
                                        type="{{ env('APP_MODE') != 'demo' ? 'submit' : 'button' }}">{{ translate('submit') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
    <!-- End Main Content -->

    <!-- Modal -->
    <div class="modal fade" id="how_it_work" tabindex="-1" aria-labelledby="instructionModal"
         aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-xl-5 pb-xl-5">
                    <div class="d-flex justify-content-center mb-4">
                        <img width="120" src="{{asset("public/assets/admin-module/img/bell.png")}}" alt="">
                    </div>
                    <h5 class="mb-3">{{translate('instructions')}}</h5>
                    <p>{{translate("For configuring OTP in the Firebase, you must create a Firebase project first. lf you haven't
                        created any project for your application yet, please create a project first.")}}</p>
                    <p class="mb-2">{{translate("Now go the")}} <a href="https://console.firebase.google.com/" class="btn-info">{{translate("Firebase Console")}}</a>
                        {{translate("And follow the instructions below")}} -</p>
                    <ol class="d-flex text-dark flex-column gap-1">
                        <li>{{translate('Go to your Firebase project.')}}</li>
                        <li>{{translate('Navigate to the Build menu from the left sidebar and select Authentication.')}}</li>
                        <li>{{translate('Get started the project and go to the Sign-in method tab.')}}</li>
                        <li>{{translate('From the Sign-in providers section, select the Phone option.')}}</li>
                        <li>{{translate('Ensure to enable the method Phone and press save.')}}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";

        let permission = false;
        @can('business_edit')
            permission = true;
        @endcan


        $('#notification_setup_form').on('submit', function (e) {
            if (!permission) {
                toastr.error('{{ translate('you_do_not_have_enough_permission_to_update_this_settings') }}');
                e.preventDefault();
            }
        });

        $('#server_key_form').on('submit', function (e) {
            if (!permission) {
                toastr.error('{{ translate('you_do_not_have_enough_permission_to_update_this_settings') }}');
                e.preventDefault();
            }
        });

        $('.switcher_input').on('click', function () {
            updateSettings(this)
        })

        function updateSettings(obj) {
            $.ajax({
                url: '{{ route('admin.business.configuration.notification.notification-settings') }}',
                _method: 'PUT',
                data: {
                    id: $(obj).data('id'),
                    type: $(obj).data('type'),
                    status: ($(obj).prop("checked")) === true ? 1 : 0
                },
                beforeSend: function () {
                    $('.preloader').removeClass('d-none');
                },
                success: function (d) {
                    $('.preloader').addClass('d-none');
                    toastr.success("{{ translate('status_successfully_changed') }}");
                },
                error: function () {
                    $('.preloader').addClass('d-none');
                    toastr.error("{{ translate('status_change_failed') }}");

                }
            });
        }
    </script>
@endpush
