<?php

namespace Modules\VehicleManagement\Http\Controllers\Api\New\Driver;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\VehicleManagement\Http\Requests\VehicleApiStoreUpdateRequest;
use Modules\VehicleManagement\Service\Interface\VehicleServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Add this import
use Modules\VehicleManagement\Entities\VehicleBrand;
use Modules\VehicleManagement\Entities\VehicleModel;
use Modules\VehicleManagement\Entities\VehicleCategory;

class VehicleController extends Controller
{
    protected $vehicleService;

    public function __construct(VehicleServiceInterface $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    public function store(VehicleApiStoreUpdateRequest $request)
    {
        try {
            if ($this->vehicleService->findOneBy(['driver_id' => $request['driver_id']])) {
                return response()->json(responseFormatter(VEHICLE_DRIVER_EXISTS_403), 403);
            }
            $driver_id = auth('api')->id();
            $data = array_merge($request->validated(), ['vehicle_request_status' => PENDING]);
            $data['driver_id'] = $driver_id;
            $this->vehicleService->create($data);
            Log::info('Vechele Data Created Successfully', [
                'driver_id' => $driver_id,
                'data' => $data
            ]);

            return response()->json(responseFormatter(VEHICLE_CREATE_200), 200);

        } catch (\Exception $e) {
            return response()->json([
                'response_code' => 'default_500',
                'message' => $e->getMessage(),
                'errors' => method_exists($e, 'getErrors') ? $e->getMessage() : [],
                'trace' => config('app.debug') ? $e->getTrace() : []
            ], 500);
        }
    }

    public function update(int|string $id, VehicleApiStoreUpdateRequest $request)
    {
        $vehicle = $this->vehicleService->updatedByDriver(id:$id, data: $request->validated());
        if ($vehicle?->vehicle_request_status == APPROVED && $vehicle?->draft) {
            return response()->json(responseFormatter(VEHICLE_REQUEST_200), 200);
        }
        return response()->json(responseFormatter(VEHICLE_UPDATE_200), 200);
    }

    public function list(Request $request): JsonResponse
    {
        $driverId = $request->user()->id;
        $categoryFilter = $request->query('category');

        // Get all vehicles for this driver first
        $allVehicles = $this->vehicleService->getBy(criteria: ['driver_id' => $driverId]);

        // If category filter is provided, filter the results
        if ($categoryFilter) {
            $category = null;
            $filterType = '';

            // First, try to find by ID (if it looks like a UUID)
            if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $categoryFilter)) {
                $category = VehicleCategory::find($categoryFilter);
                $filterType = 'ID';
            }
            
            // If not found by ID, try by name
            if (!$category) {
                $category = VehicleCategory::where('name', $categoryFilter)->first();
                $filterType = 'name (exact)';
                
                // If still not found, try partial name match
                if (!$category) {
                    $category = VehicleCategory::where('name', 'like', '%' . $categoryFilter . '%')->first();
                    $filterType = 'name (partial)';
                }
            }

            if ($category) {
                // Filter vehicles by category_id
                $vehicles = $allVehicles->filter(function ($vehicle) use ($category) {
                    return $vehicle->category_id == $category->id;
                });
            } else {
                // If category not found, return empty result
                return response()->json([
                    'status' => true,
                    'message' => 'Category "' . $categoryFilter . '" not found',
                    'data' => [],
                    'debug' => [
                        'category_filter' => $categoryFilter,
                        'filter_attempted' => 'ID and name matching',
                        'available_categories' => VehicleCategory::select('id', 'name')->get()->toArray(),
                        'driver_id' => $driverId,
                        'total_driver_vehicles' => $allVehicles->count()
                    ]
                ], 200);
            }
        } else {
            $vehicles = $allVehicles;
            $category = null;
            $filterType = 'none';
        }

        // Transform the collection to add related data
        $transformedVehicles = $vehicles->map(function ($vehicle) {
            // Fetch the brand, model, and category names
            $vehicle->brand_name = VehicleBrand::find($vehicle->brand_id)->name ?? null;
            $vehicle->model_name = VehicleModel::find($vehicle->model_id)->name ?? null;
            $vehicle->category_name = VehicleCategory::find($vehicle->category_id)->name ?? null;
            return $vehicle;
        });

        return response()->json([
            'status' => true,
            'message' => $transformedVehicles->isEmpty() 
                ? 'No vehicles found' . ($categoryFilter ? ' for category: ' . $categoryFilter : '')
                : 'Driver vehicles fetched successfully' . ($categoryFilter ? ' filtered by category: ' . $categoryFilter : ''),
            'data' => $transformedVehicles->values(),
            'debug' => config('app.debug') ? [
                'driver_id' => $driverId,
                'category_filter' => $categoryFilter,
                'filter_type' => $filterType,
                'category_found' => $category ? ['id' => $category->id, 'name' => $category->name] : null,
                'total_vehicles_before_filter' => $allVehicles->count(),
                'total_vehicles_after_filter' => $transformedVehicles->count()
            ] : []
        ], 200);
    }

    // Add this debug method
    public function debugCategories(Request $request): JsonResponse
    {
        $driverId = $request->user()->id;
        $categoryFilter = $request->query('category');
        
        // Get all vehicles for this driver
        $vehicles = $this->vehicleService->getBy(criteria: ['driver_id' => $driverId]);
        
        // Get all categories (including deleted ones)
        $allCategories = DB::table('vehicle_categories')->get();
        $activeCategories = VehicleCategory::where('is_active', 1)->whereNull('deleted_at')->get();
        
        // Check which categories your vehicles are referencing
        $vehicleCategoryCheck = $vehicles->map(function($vehicle) use ($allCategories) {
            $category = $allCategories->where('id', $vehicle->category_id)->first();
            return [
                'vehicle_id' => $vehicle->id,
                'vehicle_category_id' => $vehicle->category_id,
                'category_exists' => $category ? 'YES' : 'NO',
                'category_name' => $category->name ?? 'NOT FOUND',
                'category_active' => $category ? ($category->is_active ? 'YES' : 'NO') : 'N/A',
                'category_deleted' => $category ? ($category->deleted_at ? 'YES' : 'NO') : 'N/A'
            ];
        });
        
        return response()->json([
            'debug_info' => [
                'driver_id' => $driverId,
                'total_vehicles' => $vehicles->count(),
                'category_filter' => $categoryFilter,
                'vehicle_category_analysis' => $vehicleCategoryCheck,
                'all_categories_in_db' => $allCategories->map(function($cat) {
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'active' => $cat->is_active,
                        'deleted' => $cat->deleted_at
                    ];
                }),
                'active_categories_only' => $activeCategories->map(function($cat) {
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name
                    ];
                })
            ]
        ]);
    }
public function destroy(int|string $id): JsonResponse
{
    $vehicle = $this->vehicleService->findOneBy(['id' => $id]);

    if (!$vehicle) {
        return response()->json('Vehicle not found', 404);
    }

    $this->vehicleService->delete($id);

    return response()->json([
        'message' => 'Vehicle deleted successfully',
        'status' => true
    ], 200);
}



}