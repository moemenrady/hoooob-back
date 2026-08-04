<?php

namespace Modules\AdminModule\Http\Controllers\Web\New\Admin;

use App\Http\Controllers\BaseController;
use App\Service\BaseServiceInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\BusinessManagement\Service\Interface\SupportSavedReplyServiceInterface;
use Modules\ChattingManagement\Entities\ChannelConversation;
use Modules\ChattingManagement\Service\Interface\ChannelConversationServiceInterface;
use Modules\ChattingManagement\Service\Interface\ChannelListServiceInterface;
use Modules\ChattingManagement\Service\Interface\ChannelUserServiceInterface;
use Modules\TransactionManagement\Service\Interface\TransactionServiceInterface;
use Modules\TripManagement\Entities\TripRequest;
use Modules\TripManagement\Service\Interface\TripRequestServiceInterface;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Service\Interface\CustomerServiceInterface;
use Modules\UserManagement\Service\Interface\DriverServiceInterface;
use Modules\UserManagement\Service\Interface\EmployeeServiceInterface;
use Modules\UserManagement\Service\Interface\UserAccountServiceInterface;
use Modules\ZoneManagement\Service\Interface\ZoneServiceInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends BaseController
{
    use AuthorizesRequests;
    protected $zoneService;
    protected $tripRequestService;
    protected $transactionService;
    protected $userAccountService;
    protected $driverService;
    protected $customerService;
    protected $employeeService;

    protected $channelListService;

    protected $channelUserService;

    protected $supportSavedReplyService;

    protected $channelConversationService;

    public function __construct(ZoneServiceInterface              $zoneService, TripRequestServiceInterface $tripRequestService,
                                TransactionServiceInterface       $transactionService, UserAccountServiceInterface $userAccountService,
                                DriverServiceInterface            $driverService, CustomerServiceInterface $customerService, EmployeeServiceInterface $employeeService,
                                ChannelListServiceInterface       $channelListService, ChannelUserServiceInterface $channelUserService,
                                SupportSavedReplyServiceInterface $supportSavedReplyService, ChannelConversationServiceInterface $channelConversationService
    )
    {
        parent::__construct($zoneService);
        $this->zoneService = $zoneService;
        $this->tripRequestService = $tripRequestService;
        $this->transactionService = $transactionService;
        $this->userAccountService = $userAccountService;
        $this->driverService = $driverService;
        $this->customerService = $customerService;
        $this->employeeService = $employeeService;
        $this->channelListService = $channelListService;
        $this->channelUserService = $channelUserService;
        $this->supportSavedReplyService = $supportSavedReplyService;
        $this->channelConversationService = $channelConversationService;
    }

    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        $zones = $this->zoneService->getBy(criteria: [
            'is_active' => 1
        ]);

        $totalTripsEarningCriteria = [
            'type' => RIDE_REQUEST,
            'payment_status' => PAID
        ];
        $totalParcelsEarningCriteria = [
            'type' => PARCEL,
            'payment_status' => PAID
        ];
        $whereHasRelations = [];

        // Add criteria for the `fee` relationship to filter by `cancelled_by` being either `null` or `CUSTOMER`
        $whereHasRelations['fee'] = function ($query) {
            $query->whereNull('cancelled_by')
                ->orWhere('cancelled_by', '=', 'CUSTOMER'); // Handle `null` or `CUSTOMER`
        };
        $transactions = $this->transactionService->getBy(criteria: ['user_id' => \auth()->user()->id], orderBy: ['created_at' => 'desc'])->take(7);
        $superAdmin = $this->employeeService->findOneBy(criteria: ['user_type' => 'super-admin']);
        $superAdminAccount = $this->userAccountService->findOneBy(criteria: ['user_id' => $superAdmin?->id]);
        $customers = $this->customerService->getBy(criteria: ['user_type' => CUSTOMER, 'is_active' => true])->count();
        $drivers = $this->driverService->getBy(criteria: ['user_type' => DRIVER, 'is_active' => true])->count();
        $totalCouponAmountGiven = $this->tripRequestService->getBy(criteria: ['payment_status' => PAID])->SUM('coupon_amount');
        $totalDiscountAmountGiven = $this->tripRequestService->getBy(criteria: ['payment_status' => PAID])->SUM('discount_amount');
        $totalTrips = $this->tripRequestService->getBy(criteria: ['type' => RIDE_REQUEST])->count();
        $totalParcels = $this->tripRequestService->getBy(criteria: ['type' => PARCEL])->count();
        $totalEarning = $this->tripRequestService->getBy(criteria: ['payment_status' => PAID], whereHasRelations: $whereHasRelations, relations: ['fee'])->sum('fee.admin_commission');
        $totalTripsEarning = $this->tripRequestService->getBy(criteria: $totalTripsEarningCriteria, whereHasRelations: $whereHasRelations, relations: ['fee'])->sum('fee.admin_commission');
        $totalParcelsEarning = $this->tripRequestService->getBy(criteria: $totalParcelsEarningCriteria, whereHasRelations: $whereHasRelations, relations: ['fee'])->sum('fee.admin_commission');


        return view('adminmodule::dashboard', compact('zones', 'transactions', 'superAdminAccount', 'customers',
            'drivers', 'totalDiscountAmountGiven', 'totalCouponAmountGiven', 'totalTrips', 'totalParcels', 'totalEarning', 'totalTripsEarning', 'totalParcelsEarning'));
    }

    public function recentTripActivity()
    {
        $trips = $this->tripRequestService->getBy(relations: ['customer', 'vehicle', 'vehicleCategory'], orderBy: ['created_at' => 'desc'], limit: 5, offset: 1);
        return response()->json(view('adminmodule::partials.dashboard._recent-trip-activity', compact('trips'))->render());
    }

    public function leaderBoardDriver(Request $request)
    {
        $request->merge(['user_type' => DRIVER]);
        $leadDriver = $this->tripRequestService->getLeaderBoard($request->all(), limit: 20);
        return response()->json(view('adminmodule::partials.dashboard._leader-board-driver', compact('leadDriver'))->render());
    }

    public function leaderBoardCustomer(Request $request)
    {
        $request->merge(['user_type' => CUSTOMER]);
        $leadCustomer = $this->tripRequestService->getLeaderBoard($request->all(), limit: 20);
        return response()->json(view('adminmodule::partials.dashboard._leader-board-customer', compact('leadCustomer'))->render());
    }

    public function adminEarningStatistics(Request $request)
    {
        $data = $this->tripRequestService->getAdminZoneWiseEarning($request->all());
        return response()->json($data);
    }


    public function zoneWiseStatistics(Request $request)
    {
        $data = $this->tripRequestService->getAdminZoneWiseStatistics(data: $request->all());
        return response()
            ->json(view('adminmodule::partials.dashboard._areawise-statistics', ['trips' => $data['zoneTripsByDate'], 'totalCount' => $data['totalTrips']])
                ->render());
    }

    public function heatMap(?Request $request)
    {
        $whereBetweenCriteria = [];
        if (array_key_exists('date_range', $request->all()) && $request['date_range']) {
            $date = getCustomDateRange($request['date_range']);
            $whereBetweenCriteria = [
                'created_at' => [$date['start'], $date['end']],
            ];
            $withCountQuery = [
                'tripRequest as ride_request' => [
                    ['type', '=', RIDE_REQUEST],
                    ['created_at', '>=', $date['start']], // Add your date range start
                    ['created_at', '<=', $date['end']],
                ],
                'tripRequest as parcel_request' => [
                    ['type', '=', PARCEL],
                    ['created_at', '>=', $date['start']], // Add your date range start
                    ['created_at', '<=', $date['end']],
                ]];
        } else {
            $withCountQuery = [
                'tripRequest as ride_request' => [
                    ['type', '=', RIDE_REQUEST]
                ],
                'tripRequest as parcel_request' => [
                    ['type', '=', PARCEL]
                ]];
        }

        $zones = $this->zoneService->index(criteria: $request?->all(), withCountQuery: $withCountQuery);
        $totalRideRequests = $zones->sum('ride_request');
        $totalParcelRequests = $zones->sum('parcel_request');
        $tripWhereInCriteria = [
            'zone_id' => $zones->pluck('id')->toArray(),
        ];
        $trips = $this->tripRequestService->getBy(whereInCriteria: $tripWhereInCriteria, whereBetweenCriteria: $whereBetweenCriteria, relations: ['coordinate', 'zone']);
        $markers = $trips->map(function ($trip) {
            return [
                'position' => [
                    'lat' => $trip?->coordinate?->pickup_coordinates?->latitude ?? 0, // Default to 0 if not defined
                    'lng' => $trip?->coordinate?->pickup_coordinates?->longitude ?? 0, // Default to 0 if not defined
                ],
                'title' => "Trip Id #" . $trip?->ref_id,
            ];
        });
        $polygons = json_encode(formatZoneCoordinates($zones));

        $markers = json_encode($markers);
        // Calculate center lat/lng
        $latSum = 0;
        $lngSum = 0;
        $totalPoints = 0;

        foreach ($zones as $zone) {
            $latSum += trim(explode(' ', $zone->center)[1], 'POINT()');
            $lngSum += trim(explode(' ', $zone->center)[0], 'POINT()');
            $totalPoints++;
        }

        $centerLat = $latSum / ($totalPoints == 0 ? 1 : $totalPoints);
        $centerLng = $lngSum / ($totalPoints == 0 ? 1 : $totalPoints);
        return view('adminmodule::heat-map', compact('zones', 'totalRideRequests', 'totalParcelRequests', 'markers', 'polygons', 'centerLat', 'centerLng'));
    }

    public function heatMapOverview(Request $request)
    {
        $whereBetweenCriteria = [];
        if (array_key_exists('date_range', $request->all()) && $request['date_range']) {
            $date = getCustomDateRange($request['date_range']);
            $whereBetweenCriteria = [
                'created_at' => [$date['start'], $date['end']],
            ];
        }
        $whereInCriteria = [
            'id' => $request['zone_ids'] ?? []
        ];
        $zones = $this->zoneService->getBy(whereInCriteria: $whereInCriteria);
        $tripWhereInCriteria = [
            'zone_id' => $zones->pluck('id')->toArray(),
        ];
        $trips = $this->tripRequestService->getBy(whereInCriteria: $tripWhereInCriteria, whereBetweenCriteria: $whereBetweenCriteria, relations: ['coordinate', 'zone']);
        $markers = $trips->map(function ($trip) {
            return [
                'position' => [
                    'lat' => $trip?->coordinate?->pickup_coordinates?->latitude ?? 0, // Default to 0 if not defined
                    'lng' => $trip?->coordinate?->pickup_coordinates?->longitude ?? 0, // Default to 0 if not defined
                ],
                'title' => "Trip Id #" . $trip?->ref_id,
            ];
        });
        $polygons = json_encode(formatZoneCoordinates($zones));
        $markers = json_encode($markers);
        // Calculate center lat/lng
        $latSum = 0;
        $lngSum = 0;
        $totalPoints = 0;
        foreach ($zones as $zone) {
            $latSum += trim(explode(' ', $zone->center)[1], 'POINT()');
            $lngSum += trim(explode(' ', $zone->center)[0], 'POINT()');
            $totalPoints++;
        }

        $centerLat = $latSum / ($totalPoints == 0 ? 1 : $totalPoints);
        $centerLng = $lngSum / ($totalPoints == 0 ? 1 : $totalPoints);
        return response()
            ->json(view('adminmodule::partials.heat-map._overview-map', compact('polygons', 'markers', 'centerLat', 'centerLng'))
                ->render());
    }

    public function heatMapCompare(Request $request)
    {
        $allZones = $this->zoneService->getAll();
        if (array_key_exists('zone_id', $request->all()) && $request['zone_id']) {
            $zone = $this->zoneService->findOne(id: $request['zone_id']);
        } else {
            $zone = count($allZones) ? $this->zoneService->findOne(id: $allZones[0]->id) : null;
        }
        if (array_key_exists('date_range', $request->all()) && $request['date_range']) {
            $dateRange = $request['date_range'];
            $date = getCustomDateRange($request['date_range']);
        } else {
            $firstTripRequest = $this->tripRequestService->findOneBy(criteria: ['zone_id' => $zone?->id], orderBy: ['created_at' => 'asc']);
            $todayStart = $zone ? Carbon::parse($firstTripRequest?->created_at)->format('m/d/Y') : Carbon::today()->format('m/d/Y'); // Start of today
            $todayEnd = Carbon::today()->format('m/d/Y');
            $dateRange = "{$todayStart} - {$todayEnd}";
            $date = getCustomDateRange("{$todayStart} - {$todayEnd}");
        }
        $startDate = $date['start'];
        $endDate = $date['end'];
        $whereBetweenCriteria = [
            'created_at' => [$startDate, $endDate],
        ];

        $tripCount = $this->tripRequestService->getBy(criteria: ['zone_id' => $zone?->id], whereBetweenCriteria: $whereBetweenCriteria, relations: ['coordinate', 'zone'])->count();
        $dateWiseTrips = $this->tripRequestService->getTripHeatMapCompareDataBy(data: ['zone_id' => $zone?->id, 'date_range' => $dateRange]);
        if ($dateWiseTrips->isNotEmpty()) {
            $markers = [];
            foreach ($dateWiseTrips as $dateWiseTrip) {
                if (isset($dateWiseTrip->month) && isset($dateWiseTrip->year)) {
                    $markerKey = $dateWiseTrip->month;
                    if ($startDate->month < $dateWiseTrip->month && $endDate->month == $dateWiseTrip->month) {
                        $showStartDate = Carbon::createFromDate($dateWiseTrip->year, $dateWiseTrip->month, 1)->startOfDay();
                        $showEndDate = Carbon::create($endDate);
                        $whereMarkerBetweenCriteria = [
                            'created_at' => [Carbon::createFromDate($dateWiseTrip->year, $dateWiseTrip->month, 1)->startOfDay(), Carbon::create($endDate)],
                        ];
                    } elseif ($startDate->month < $dateWiseTrip->month && $endDate->month > $dateWiseTrip->month) {
                        $showStartDate = Carbon::createFromDate($dateWiseTrip->year, $dateWiseTrip->month, 1)->startOfDay();
                        $showEndDate = Carbon::createFromDate($dateWiseTrip->year, $dateWiseTrip->month, 1)->endOfMonth()->endOfDay();
                        $whereMarkerBetweenCriteria = [
                            'created_at' => [Carbon::createFromDate($dateWiseTrip->year, $dateWiseTrip->month, 1)->startOfDay(), Carbon::createFromDate($dateWiseTrip->year, $dateWiseTrip->month, 1)->endOfMonth()->endOfDay()],
                        ];
                    } elseif ($startDate->month == $dateWiseTrip->month && $endDate->month > $dateWiseTrip->month) {
                        $showStartDate = Carbon::create($startDate);
                        $showEndDate = Carbon::createFromDate($dateWiseTrip->year, $dateWiseTrip->month, 1)->endOfMonth()->endOfDay();
                        $whereMarkerBetweenCriteria = [
                            'created_at' => [Carbon::create($startDate), Carbon::createFromDate($dateWiseTrip->year, $dateWiseTrip->month, 1)->endOfMonth()->endOfDay()],
                        ];
                    } else {
                        $showStartDate = Carbon::create($startDate);
                        $showEndDate = Carbon::create($endDate);
                        $whereMarkerBetweenCriteria = [
                            'created_at' => [Carbon::create($startDate), Carbon::create($endDate)],
                        ];
                    }
                } elseif (isset($dateWiseTrip->year)) {
                    $markerKey = $dateWiseTrip->year;

                    if ($startDate->year < $dateWiseTrip->year && $endDate->year == $dateWiseTrip->year) {
                        $showStartDate = Carbon::createFromDate($dateWiseTrip->year, 1, 1)->startOfDay();
                        $showEndDate = Carbon::create($endDate);
                        $whereMarkerBetweenCriteria = [
                            'created_at' => [Carbon::createFromDate($dateWiseTrip->year, 1, 1)->startOfDay(), Carbon::create($endDate)],
                        ];
                    } elseif ($startDate->year < $dateWiseTrip->year && $endDate->year > $dateWiseTrip->year) {

                        $showStartDate = Carbon::createFromDate($dateWiseTrip->year, 1, 1)->startOfDay();
                        $showEndDate = Carbon::createFromDate($dateWiseTrip->year, 12, 31)->endOfDay();
                        $whereMarkerBetweenCriteria = [
                            'created_at' => [Carbon::createFromDate($dateWiseTrip->year, 1, 1)->startOfDay(), Carbon::createFromDate($dateWiseTrip->year, 12, 31)->endOfDay()],
                        ];

                    } elseif ($startDate->year == $dateWiseTrip->year && $endDate->year > $dateWiseTrip->year) {
                        $showStartDate = Carbon::create($startDate);
                        $showEndDate = Carbon::createFromDate($dateWiseTrip->year, 12, 31)->endOfDay();
                        $whereMarkerBetweenCriteria = [
                            'created_at' => [Carbon::create($startDate), Carbon::createFromDate($dateWiseTrip->year, 12, 31)->endOfDay()],
                        ];
                    } else {
                        $showStartDate = Carbon::create($startDate);
                        $showEndDate = Carbon::create($endDate);
                        $whereMarkerBetweenCriteria = [
                            'created_at' => [Carbon::create($startDate), Carbon::create($endDate)],
                        ];
                    }
                } elseif (isset($dateWiseTrip->hour)) {
                    $showStartDate = Carbon::create($dateWiseTrip->date)->setTime($dateWiseTrip->hour, 0);
                    $showEndDate = $showStartDate->copy()->addMinutes(59)->addSeconds(59);
                    $markerKey = $dateWiseTrip->hour;
                    $whereMarkerBetweenCriteria = [
                        'created_at' => [$showStartDate, $showEndDate],
                    ];
                } else {
                    $markerKey = $dateWiseTrip->date;
                    $showStartDate = Carbon::create($dateWiseTrip->date)->startOfDay();
                    $showEndDate = Carbon::create($dateWiseTrip->date)->endOfDay();
                    $whereMarkerBetweenCriteria = [
                        'created_at' => [Carbon::create($dateWiseTrip->date)->startOfDay(), Carbon::create($dateWiseTrip->date)->endOfDay()],
                    ];

                }
                $dateWiseTrip->startDate = $showStartDate;
                $dateWiseTrip->endDate = $showEndDate;
                $dateWiseTrip->markerKey = $markerKey;
                $trips = $this->tripRequestService->getBy(criteria: ['zone_id' => $zone?->id], whereBetweenCriteria: $whereMarkerBetweenCriteria, relations: ['coordinate', 'zone']);
                $mappedMarkers = $trips->map(function ($trip) {
                    return [
                        'position' => [
                            'lat' => $trip?->coordinate?->pickup_coordinates?->latitude ?? 0, // Default to 0 if not defined
                            'lng' => $trip?->coordinate?->pickup_coordinates?->longitude ?? 0, // Default to 0 if not defined
                        ],
                        'title' => "Trip Id #" . $trip?->ref_id,
                    ];
                });
                $markers[$markerKey] = $mappedMarkers;
            }
        } else {
            $markers = [];
        }
        // Calculate center lat/lng
        $latSum = 0;
        $lngSum = 0;
        $totalPoints = 0;
        $polygons = $zone ? json_encode([formatCoordinates(json_decode($zone?->coordinates[0]->toJson(), true)['coordinates'])]) : json_encode([[]]);
        if ($zone) {
            foreach (formatCoordinates(json_decode($zone?->coordinates[0]->toJson(), true)['coordinates']) as $point) {
                $latSum += $point->lat;
                $lngSum += $point->lng;
                $totalPoints++;
            }
        }
        $centerLat = $latSum / ($totalPoints == 0 ? 1 : $totalPoints);
        $centerLng = $lngSum / ($totalPoints == 0 ? 1 : $totalPoints);
        $tripStatisticsData = $this->tripRequestService->getTripHeatMapCompareZoneDateWiseEarningStatistics(data: ['zone_id' => $zone?->id, 'date_range' => $dateRange]);
        return view('adminmodule::heat-map-compare',
            compact('allZones', 'zone',
                'dateRange', 'tripCount', 'polygons', 'markers', 'centerLat',
                'centerLng', 'dateWiseTrips', 'tripStatisticsData'));

    }

    public function chatting(Request $request)
    {
        $this->authorize('chatting_view');
        $channelUsers = $this->channelUserService->getBy(criteria: ['user_id' => \auth()->user()->id]);
        $whereHasRelations = [
            'user' => ['user_type' => DRIVER],
            'conversations' => [['id', '!=', null]]
        ];
        $driverChannels = $this->channelUserService->getBy(whereInCriteria: ['channel_id' => $channelUsers->pluck('channel_id')], whereHasRelations: $whereHasRelations,
            relations: ['user', 'conversations'], orderBy: ['created_at' => 'desc']);
//        return $driverChannels;
        $savedReplies = $this->supportSavedReplyService->getBy(criteria: ['is_active' => 1]);

        return view('adminmodule::chatting', compact('driverChannels', 'savedReplies'));

    }

    public function getDriverConversation($channelId, Request $request)
    {
        $this->channelUserService->updatedBy(criteria: ['channel_id' => $channelId, 'user_id' => $request->driverId, 'is_read' => 0], data: ['is_read' => 1]);
        $this->channelConversationService->updatedBy(criteria: ['channel_id' => $channelId, 'user_id' => $request->driverId, 'is_read' => 0], data: ['is_read' => 1]);
        $conversations = $this->channelConversationService->getBy(criteria: ['channel_id' => $channelId], relations: ['user', 'conversation_files'], orderBy: ['id' => 'desc']);
        $driver = $this->driverService->findOneBy(criteria: ['id' => $request->driverId, 'user_type' => DRIVER], withTrashed: true);
        return response()
            ->json(view('adminmodule::partials.chatting._conversation', compact('conversations', 'driver', 'channelId'))
                ->render());
    }

    public function searchDriversList(Request $request)
    {

        $searchCriteria = [];
        if (array_key_exists('search', $request->all())) {
            $searchCriteria = [
                'relations' => [
                    'user' => ['full_name', 'first_name', 'last_name'],
                ],
                'value' => $request->search,
            ];
        }
        $channelUsers = $this->channelUserService->getBy(criteria: ['user_id' => \auth()->user()->id]);
        $whereHasRelations = [
            'user' => ['user_type' => DRIVER],
            'conversations' => [['id', '!=', null]]
        ];
        $driverChannels = $this->channelUserService->getBy(searchCriteria: $searchCriteria, whereInCriteria: ['channel_id' => $channelUsers->pluck('channel_id')], whereHasRelations: $whereHasRelations,
            relations: ['user', 'conversations'], orderBy: ['created_at' => 'desc']);

        return response()
            ->json(view('adminmodule::partials.chatting._search-drivers', compact('driverChannels'))
                ->render());
    }

    public function searchSavedTopicAnswer(Request $request)
    {

        $searchCriteria = [];
        if (array_key_exists('search', $request->all())) {
            $searchCriteria = [
                'fields' => ['topic'],
                'value' => $request->search,
            ];
        }

        $savedReplies = $this->supportSavedReplyService->getBy(criteria: ['is_active' => 1], searchCriteria: $searchCriteria);

        return response()
            ->json(view('adminmodule::partials.chatting._saved-answer', compact('savedReplies'))
                ->render());
    }

    public function sendMessageToDriver(Request $request)
    {
        $fileImage = [];
        if ($request->has('file')) {
            $fileImage = array_merge($fileImage, $request->file('file'));
        }
        if ($request->has('image')) {
            $fileImage = array_merge($fileImage, $request->file('image'));
        }
        $data = [
            'channel_id' => $request->channelId,
            'user_id' => auth()->user()->id,
            'message' => $request->message,
            'is_read' => 0,
            'files' => $fileImage,
        ];
        $this->channelConversationService->create(data: $data);
        $channelDriver = $this->channelUserService->findOneBy(criteria: ['channel_id' => $request->channelId, 'user_id' => $request->driverId], relations: ['user']);
//        dd($channelDriver?->user);
//        $push = getNotification('someone_used_your_code');
        sendDeviceNotification(fcm_token: $channelDriver?->user?->fcm_token,
            title: translate("New Message"),
            description: translate("You have a new message from Admin"),
            status: 1,
            ride_request_id: $request->driverId,
            type: $request->channelId,
            action: 'admin_message',
            user_id: $request->driverId
        );

        $channelId = $request->channelId;
        $this->channelUserService->updatedBy(criteria: ['channel_id' => $channelId, 'user_id' => $request->driverId, 'is_read' => 0], data: ['is_read' => 1]);
        $this->channelConversationService->updatedBy(criteria: ['channel_id' => $channelId, 'user_id' => $request->driverId, 'is_read' => 0], data: ['is_read' => 1]);
        $conversations = $this->channelConversationService->getBy(criteria: ['channel_id' => $channelId], relations: ['user', 'conversation_files'], orderBy: ['created_at' => 'desc']);
        $driver = $this->driverService->findOneBy(criteria: ['id' => $request->driverId, 'user_type' => DRIVER], withTrashed: true);


        return response()
            ->json(view('adminmodule::partials.chatting._conversation', compact('conversations', 'driver', 'channelId'))
                ->render());

    }

    public function fleetMap(?Request $request, $type = null)
    {
        $zones = $this->zoneService->getAll();
        if (array_key_exists('zone_id', $request->all()) && $request['zone_id']) {
            $zone = $this->zoneService->findOne(id: $request['zone_id']);
        } else {
            $zone = count($zones) ? $this->zoneService->findOne(id: $zones[0]->id) : null;
        }
        // Calculate center lat/lng
        $latSum = 0;
        $lngSum = 0;
        $totalPoints = 0;
        $polygons = $zone ? json_encode([formatCoordinates(json_decode($zone?->coordinates[0]->toJson(), true)['coordinates'])]) : json_encode([[]]);
        if ($zone) {
            foreach (formatCoordinates(json_decode($zone?->coordinates[0]->toJson(), true)['coordinates']) as $point) {
                $latSum += $point->lat;
                $lngSum += $point->lng;
                $totalPoints++;
            }
        }
        $centerLat = $latSum / ($totalPoints == 0 ? 1 : $totalPoints);
        $centerLng = $lngSum / ($totalPoints == 0 ? 1 : $totalPoints);
        if ($zone) {
            $data = $this->fleetCommon($type, $zone, $request->all());

            $drivers = $data['drivers'];
            $markers = $data['markers'];
            return view('adminmodule::fleet-map', compact('drivers', 'zones', 'type', 'markers', 'polygons', 'centerLat', 'centerLng'));
        }
        $drivers =[];
        $markers = json_encode([[]]);

        return view('adminmodule::fleet-map', compact('drivers', 'zones', 'type', 'markers', 'polygons', 'centerLat', 'centerLng'));
    }

    public function fleetMapDriverDetails($id, Request $request)
    {
        $driverRelations = [
            'vehicle.model', 'lastLocations', 'userAccount', 'receivedReviews', 'driverTrips', 'driverDetails'
        ];
        $driver = $this->driverService->findOneBy(criteria: ['user_type' => DRIVER, 'id' => $id], relations: $driverRelations);
        return response()
            ->json(view('adminmodule::partials.fleet-map._fleet-map-driver-details', compact('driver'))
                ->render());
    }

    public function fleetMapViewUsingAjax(Request $request)
    {
        $type = $request->type;
        $zones = $this->zoneService->getAll();
        if (array_key_exists('zone_id', $request->all()) && $request['zone_id']) {
            $zone = $this->zoneService->findOne(id: $request['zone_id']);
        } else {
            $zone = count($zones) ? $this->zoneService->findOne(id: $zones[0]->id) : null;
        }
        // Calculate center lat/lng
        $latSum = 0;
        $lngSum = 0;
        $totalPoints = 0;
        $polygons = $zone ? json_encode([formatCoordinates(json_decode($zone?->coordinates[0]->toJson(), true)['coordinates'])]) : json_encode([[]]);
        if ($zone) {
            foreach (formatCoordinates(json_decode($zone?->coordinates[0]->toJson(), true)['coordinates']) as $point) {
                $latSum += $point->lat;
                $lngSum += $point->lng;
                $totalPoints++;
            }
        }
        $centerLat = $latSum / ($totalPoints == 0 ? 1 : $totalPoints);
        $centerLng = $lngSum / ($totalPoints == 0 ? 1 : $totalPoints);
        if ($zone) {
            $data = $this->fleetCommon($type, $zone, $request->all());
            $drivers = $data['drivers'];
            $markers = $data['markers'];
            return response()
                ->json(['markers' => $markers, 'polygons' => $polygons, 'centerLat' => $centerLat, 'centerLng' => $centerLng]);

        }
        $markers = json_encode([[]]);
        return response()
            ->json(['markers' => $markers, 'polygons' => $polygons, 'centerLat' => $centerLat, 'centerLng' => $centerLng]);
    }

    public function fleetMapViewSingleDriver($id, Request $request)
    {
        $driverRelations = [
            'vehicle.model', 'lastLocations', 'userAccount', 'receivedReviews', 'driverTrips', 'driverDetails'
        ];
        $driver = $this->driverService->findOneBy(criteria: ['user_type' => DRIVER, 'id' => $id], relations: $driverRelations);

        $zones = $this->zoneService->getAll();
        if (array_key_exists('zone_id', $request->all()) && $request['zone_id']) {
            $zone = $this->zoneService->findOne(id: $request['zone_id']);
        } else {
            $zone = count($zones) ? $this->zoneService->findOne(id: $zones[0]->id) : null;
        }
        // Calculate center lat/lng
        $latSum = 0;
        $lngSum = 0;
        $totalPoints = 0;
        $polygons = $zone ? json_encode([formatCoordinates(json_decode($zone?->coordinates[0]->toJson(), true)['coordinates'])]) : json_encode([[]]);
        if ($zone) {
            foreach (formatCoordinates(json_decode($zone?->coordinates[0]->toJson(), true)['coordinates']) as $point) {
                $latSum += $point->lat;
                $lngSum += $point->lng;
                $totalPoints++;
            }
        }
        $centerLat = $latSum / ($totalPoints == 0 ? 1 : $totalPoints);
        $centerLng = $lngSum / ($totalPoints == 0 ? 1 : $totalPoints);
        if ($zone) {
            $trip = $driver?->driverTrips()?->whereIn('current_status', [ACCEPTED, ONGOING])->where('type', RIDE_REQUEST)->first();

            $markers = [
                [
                    'id' => $driver?->id,
                    'position' => [
                        'lat' => $driver?->lastLocations?->latitude ? (double)$driver?->lastLocations?->latitude : 0, // Default to 0 if not defined
                        'lng' => $driver?->lastLocations?->longitude ? (double)$driver?->lastLocations?->longitude : 0, // Default to 0 if not defined
                    ],
                    'title' => $driver?->full_name ?? ($driver?->first_name ? $driver?->first_name . ' ' . $driver?->last_name : "N/A"),
                    'subtitle' => $trip ? $trip->ref_id : null,
                    'driver' => $driver?->id ? route('admin.driver.show', ['id' => $driver?->id]) : '#',
                    'trip' => $trip ? route('admin.trip.show', ['type' => ALL, 'id' => $trip->id, 'page' => 'summary']) : '#',
                    'icon' => $trip ? asset('/public/assets/admin-module/img/maps/customer.png') : asset('/public/assets/admin-module/img/maps/driver.png'),
                ]
            ];
            $markers = json_encode($markers);
            return response()
                ->json(['markers' => $markers, 'polygons' => $polygons, 'centerLat' => $centerLat, 'centerLng' => $centerLng]);
        }
        $markers = json_encode([[]]);
        return response()
            ->json(['markers' => $markers, 'polygons' => $polygons, 'centerLat' => $centerLat, 'centerLng' => $centerLng]);
    }


    private function fleetCommon($type, $zone, $request)
    {
        $searchCriteria = [];
        if (array_key_exists('search', $request)) {
            $searchCriteria = [
                'fields' => ['full_name', 'first_name', 'last_name', 'phone'],
                'value' => $request['search']
            ];
        }
        if ($type == ALL_DRIVER) {
            $driverCriteria = [
                'user_type' => DRIVER,
                'is_active' => 1,
            ];
            $driverRelations = [
                'vehicle.model', 'lastLocations', 'userAccount', 'receivedReviews', 'driverTrips', 'driverDetails'
            ];
            $driverWhereHasRelations = [
                'driverDetails' => ['is_online' => true],
                'lastLocations' => ['zone_id' => $zone->id],
            ];

            $drivers = $this->driverService->getBy(criteria: $driverCriteria, searchCriteria: $searchCriteria, whereHasRelations: $driverWhereHasRelations, relations: $driverRelations);
        } elseif ($type == DRIVER_ON_TRIP) {
            $driverCriteria = [
                'user_type' => DRIVER,
                'is_active' => 1,
            ];
            $driverRelations = [
                'vehicle.model', 'lastLocations', 'userAccount', 'receivedReviews', 'driverTrips', 'driverDetails'
            ];
            $driverWhereHasRelations = [
                'driverDetails' => ['is_online' => true],
                'lastLocations' => ['zone_id' => $zone->id],
                'driverTrips' => [
                    'type' => RIDE_REQUEST,
                    'current_status' => [ACCEPTED, ONGOING],
                ],
            ];
            $drivers = $this->driverService->getBy(criteria: $driverCriteria, searchCriteria: $searchCriteria, whereHasRelations: $driverWhereHasRelations, relations: $driverRelations);
        } elseif ($type == DRIVER_IDLE) {
            $driverCriteria = [
                'user_type' => DRIVER,
                'is_active' => 1,
            ];
            $driverRelations = [
                'vehicle.model', 'lastLocations', 'userAccount', 'receivedReviews', 'driverTrips', 'driverDetails'
            ];
            $driverWhereHasRelations = [
                'driverDetails' => ['is_online' => true],
                'lastLocations' => ['zone_id' => $zone->id],
            ];
            $drivers = $this->driverService->getBy(criteria: $driverCriteria, searchCriteria: $searchCriteria, whereHasRelations: $driverWhereHasRelations, relations: $driverRelations);
            $drivers = $drivers->filter(function ($driver) {
                return $driver->driverTrips
                        ->whereIn('current_status', [ACCEPTED, ONGOING])
                        ->where('type', RIDE_REQUEST)
                        ->count() < 1;
            })->values();
        } else {
            abort(404);
        }
        $markers = $drivers->map(function ($driver) {
            $trip = $driver?->driverTrips()?->whereIn('current_status', [ACCEPTED, ONGOING])->where('type', RIDE_REQUEST)->first();
            return [
                'id' => $driver?->id,
                'position' => [
                    'lat' => $driver?->lastLocations?->latitude ? (double)$driver?->lastLocations?->latitude : 0, // Default to 0 if not defined
                    'lng' => $driver?->lastLocations?->longitude ? (double)$driver?->lastLocations?->longitude : 0, // Default to 0 if not defined
                ],
                'title' => $driver->full_name ?? ($driver?->first_name ? $driver?->first_name . ' ' . $driver?->last_name : "N/A"),
                'subtitle' => $trip ? $trip->ref_id : null,
                'driver' => $driver?->id ? route('admin.driver.show', ['id' => $driver?->id]) : '#',
                'trip' => $trip ? route('admin.trip.show', ['type' => ALL, 'id' => $trip->id, 'page' => 'summary']) : '#',
                'icon' => $trip ? asset('/public/assets/admin-module/img/maps/customer.png') : asset('/public/assets/admin-module/img/maps/driver.png'),
            ];
        });
        $markers = json_encode($markers);

        return [
            'drivers' => $drivers,
            'markers' => $markers,
        ];
    }

}
