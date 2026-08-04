<div class="inbox_chat d-flex flex-column">
    <div class="list_filter">
        @forelse($driverChannels as $driverChannel)
            <div class="chat_list p-3 d-flex gap-2  bg-soft-secondary driver-conversation"
                 data-channel-id="{{ $driverChannel?->channel_id }}"
                 data-driver-id="{{ $driverChannel?->user_id }}">
                <div class="chat_people media gap-10 w-100" id="chat_people">
                    <div class="avatar avatar-sm chat_img rounded-circle position-relative">
                        <img src="{{ onErrorImage(
                                                    $driverChannel?->user?->profile_image,
                                                    asset('storage/app/public/driver/profile') . '/' . $driverChannel?->user?->profile_image,
                                                    asset('public/assets/admin-module/img/user.png'),
                                                    'driver/profile/',
                                                ) }}"
                             id="" class="avatar-img rounded-circle aspect-1"
                             alt="">
                        {{--                                            <span class="avatar-status avatar-status-success"></span> --}}
                    </div>
                    <div class="chat_ib media-body title-color">
                        <h6 class="mb-1 seller active-text fw-semibold" id=""
                            data-name="{{ $driverChannel?->user?->full_name ?? ($driverChannel?->user?->first_name ? $driverChannel?->user?->first_name . ' ' . $driverChannel?->user?->last_name : 'N/A') }}"
                            data-phone="{{ $driverChannel?->user?->phone }}">
                            {{ $driverChannel?->user?->full_name ?? ($driverChannel?->user?->first_name ? $driverChannel?->user?->first_name . ' ' . $driverChannel?->user?->last_name : 'N/A') }}
                            <span
                                class="fw-medium fs-10 float-end opacity-80">{{ formatCustomDate($driverChannel?->last_message?->created_at ?? $driverChannel?->created_at) }}</span>
                        </h6>
                        <div class="fs-12 opacity-50 d-block mb-2" id=""
                             data-name="Will Smith"
                             data-phone="{{ $driverChannel?->user?->phone }}">
                            {{ $driverChannel?->user?->phone }}</div>
                        <div class="d-flex justify-content-between align-items-center gap-10">
                                                    <span
                                                        class="fs-12 line--limit-1">{{ $driverChannel?->last_message?->message ?? translate('Shared file') }}
                                                    </span>
                           <span
                                    class="new-msg-count {{ $driverChannel?->is_unread_count > 0 ? '' : 'd-none' }}">{{ $driverChannel?->is_unread_count }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="d-flex justify-content-center mt-2" id="">
                <div class="d-flex justify-content-center align-items-center h-100">
                    <div class="d-flex flex-column align-items-center gap-20">
                        <img width="38"
                             src="{{ asset('/public/assets/admin-module/img/svg/driver-man.svg') }}"
                             alt="">
                        <p class="fs-12">{{ translate('no driver found') }}</p>
                    </div>
                </div>
            </div>

        @endforelse
    </div>
</div>
