<?php

namespace Modules\BusinessManagement\Http\Controllers\Web\Admin\Configuration;

use App\Http\Controllers\BaseController;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Modules\BusinessManagement\Http\Requests\NotificationSetupStoreOrUpdateRequest;
use Modules\BusinessManagement\Service\Interface\BusinessSettingServiceInterface;
use Modules\BusinessManagement\Service\Interface\FirebasePushNotificationServiceInterface;
use Modules\BusinessManagement\Service\Interface\NotificationSettingServiceInterface;

class NotificationController extends BaseController
{
    use AuthorizesRequests;

    protected $notificationSettingService;
    protected $firebasePushNotificationService;
    protected $businessSettingService;

    public function __construct(NotificationSettingServiceInterface $notificationSettingService, FirebasePushNotificationServiceInterface $firebasePushNotificationService, BusinessSettingServiceInterface $businessSettingService)
    {
        parent::__construct($notificationSettingService);
        $this->notificationSettingService = $notificationSettingService;
        $this->firebasePushNotificationService = $firebasePushNotificationService;
        $this->businessSettingService = $businessSettingService;
    }

    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        $this->authorize('business_view');
        $notifications = $this->firebasePushNotificationService
            ->getAll();
        $notificationSettings = $this->notificationSettingService->getAll();

        return view('businessmanagement::admin.configuration.notification',
            compact('notifications', 'notificationSettings'));
    }

    public function firebaseConfiguration(): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        $this->authorize('business_view');
        $settings = $this->businessSettingService
            ->findOneBy(criteria: ['key_name' => SERVER_KEY, 'settings_type' => NOTIFICATION_SETTINGS]);

        return view('businessmanagement::admin.configuration.firebase-configuration',
            compact('settings'));
    }

    public function store(Request $request): Renderable|RedirectResponse
    {
        $this->authorize('business_edit');
        $notificationKey = $this->businessSettingService->findOneBy(criteria: ['key_name' => SERVER_KEY,
            'settings_type' => NOTIFICATION_SETTINGS]);
        $data = ['key_name' => SERVER_KEY,
            'settings_type' => NOTIFICATION_SETTINGS,
            'value' => $request['server_key']];

        if ($notificationKey) {
            $this->businessSettingService->update(id: $notificationKey->id, data: $data);
        } else {
            $this->businessSettingService->create(data: $data);
        }
        Toastr::success(BUSINESS_SETTING_UPDATE_200['message']);
        return back();
    }

    public function pushStore(Request $request)
    {
        foreach ($request['notification'] as $key => $notification) {
            $status = array_key_exists('status', $notification) ? 1 : 0;
            $notification['status'] = $status;
            $notification['name'] = $key;
            $firebaseNotification = $this->firebasePushNotificationService->findOneBy(criteria: ['name' => $key]);
            $this->firebasePushNotificationService->update(id: $firebaseNotification?->id, data: $notification);
        }
        Toastr::success(BUSINESS_SETTING_UPDATE_200['message']);
        return back();
    }

    public function updateNotificationSettings(NotificationSetupStoreOrUpdateRequest $request): JsonResponse
    {
        $this->authorize('business_edit');
        $notification = $this->notificationSettingService
            ->update(id: $request['id'], data: $request->validated());
        return response()->json($notification);
    }
}
