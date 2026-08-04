<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Models\Payment;
use App\Traits\Processor;
use App\Models\PaymentRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PaymobController extends Controller
{

    private $config_values;

    private $user;
   protected  $api_key ;
    protected  $iframe_id ;
    protected  $integration_id ;
    protected  $hmac ;

    public function __construct(PaymentRequest $payment, User $user)
    {
           $this->api_key = "ZXlKaGJHY2lPaUpJVXpVeE1pSXNJblI1Y0NJNklrcFhWQ0o5LmV5SmpiR0Z6Y3lJNklrMWxjbU5vWVc1MElpd2ljSEp2Wm1sc1pWOXdheUk2TVRBMU5UTXhNQ3dpYm1GdFpTSTZJbWx1YVhScFlXd2lmUS5lYUFmd2x0cUlYX0ZrdjlubkRzandGa3NlanBZYm9uRFZRQTF3VThWeld1QVBpMnRaX0pHREVMeG85RUJ3UndRU2Fia1ZyN1JPbFNxaGhMSktneDBSdw==";

        $this->hmac = "BAFDACCB2FD0DFEFBCEC3A4CDEDA61AF";
 
      
        $this->payment = $payment;
        $this->user = $user;
    }

public function credit(Request $request)
{
    $secret = "egy_sk_test_d111e72dd569fc43080d31317d857feaac0527d96125761dc205fc53c5b96c4e";
    $public = "egy_pk_test_2Z897l6Zr5LhbsFsoAahL1LiVAexGTcE";

    $validator = Validator::make($request->all(), [
        'payment_id' => 'required|uuid'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 400);
    }

    $payment = $this->payment::where('id', $request['payment_id'])->where('is_paid', 0)->first();
    if (!$payment) {
        return response()->json([
            'status' => 'error',
            'message' => 'Payment ID not found or already paid'
        ], 404);
    }

    $business_name = "my_business";
    if (!empty($payment->additional_data)) {
        $business = json_decode($payment->additional_data);
        $business_name = $business->business_name ?? $business_name;
    }

    $token = $this->getToken();

    $order = $this->createOrder($token, $payment, $business_name);
    if (!isset($order['id'])) {
        return response()->json(['error' => 'Failed to create Paymob order', 'raw' => $order], 500);
    }

    

    $payer = json_decode($payment->payer_information);
    $billingData = [
        "apartment" => "N/A",
        "email" => $payer->email ?? "noemail@example.com",
        "floor" => "N/A",
        "first_name" => $payer->name ?? "Guest",
        "street" => "N/A",
        "building" => "N/A",
        "phone_number" => $payer->phone ?? "N/A",
        "shipping_method" => "PKG",
        "postal_code" => "N/A",
        "city" => "N/A",
        "country" => "N/A",
        "last_name" => $payer->name ?? "Guest",
        "state" => "N/A",
    ];

    $res = Http::withHeaders([
        'Authorization' => "Token {$secret}",
        'Content-Type'  => 'application/json',
    ])->post('https://accept.paymob.com/v1/intention/', [
        "auth_token" => $token,
        "amount" => round($payment->payment_amount * 100),
        "currency" => $payment->currency_code,
        "order_id" => $order['id'],
        "billing_data" => $billingData,
      "special_reference" => $payment->attribute_id . '_' . time(),
        "payment_methods" => [5155796],
    ]);

    if (!$res->successful()) {
        return response()->json(['error' => $res->json()], 400);
    }

    $data = $res->json();
    $clientSecret = $data['client_secret'] ?? null;

    if (!$clientSecret) {
        return response()->json(['error' => 'no client secret returned', 'raw' => $data], 500);
    }

    $iframeUrl = "https://accept.paymob.com/unifiedcheckout/?publicKey={$public}&clientSecret={$clientSecret}";
   // return Redirect::away($iframeUrl);
   return response()->json([
       'status' => 'success',
        'iframe' => $iframeUrl,
    ]);
}


public function getToken()
{
    $response = Http::post('https://accept.paymob.com/api/auth/tokens', [
        'api_key' => $this->api_key
    ]);

    \Log::info('Paymob Token Response:', $response->json());

    return $response->json()['token'] ?? null;
}


  public function createOrder($token, $payment_data, $business_name)
{
    $items[] = [
        'name' => $business_name,
        'amount_cents' => round($payment_data->payment_amount * 100),
        'description' => 'payment ID :' . $payment_data->id,
        'quantity' => 1
    ];

    $data = [
        "auth_token" => $token,
        "delivery_needed" => false,
        "amount_cents" => round($payment_data->payment_amount * 100),
        "currency" => $payment_data->currency_code,
        "items" => $items
    ];

    $response = Http::post('https://accept.paymob.com/api/ecommerce/orders', $data);

    if (!$response->successful()) {
        return ['error' => 'Failed to create Paymob order', 'raw' => $response->json()];
    }

    return $response->json(); 
}

public function callback(Request $request)
{
    $success = $request->query('success');     

    if ($success === "true") {
        return view('success');
    } else {
        return view('fail');
    }
}


  
  
  
public function webhook(Request $request)
{
    Log::info('🔔 Paymob Webhook Hit!', $request->all());
    file_put_contents(
        storage_path('logs/paymob_webhook_latest.json'),
        json_encode($request->all(), JSON_PRETTY_PRINT)
    );
    $fullPayload = $request->all();
    $payload = $fullPayload['obj'] ?? [];

    if (!$payload) {
        return response()->json(['message' => 'Malformed request (no obj)'], 400);
    }

    $fields = [
        'amount_cents',
        'created_at',
        'currency',
        'error_occured',
        'has_parent_transaction',
        'id',
        'integration_id',
        'is_3d_secure',
        'is_auth',
        'is_capture',
        'is_refunded',
        'is_standalone_payment',
        'is_voided',
        'order',
        'owner',
        'pending',
        'source_data_pan',
        'source_data_sub_type',
        'source_data_type',
        'success',
    ];

    $connectedString = '';
    foreach ($fields as $field) {
        switch ($field) {
            case 'source_data_pan':
                $value = $payload['source_data']['pan'] ?? '';
                break;
            case 'source_data_sub_type':
                $value = $payload['source_data']['sub_type'] ?? '';
                break;
            case 'source_data_type':
                $value = $payload['source_data']['type'] ?? '';
                break;
            case 'order':
                $value = $payload['order']['id'] ?? '';
                break;
            default:
                $value = $payload[$field] ?? '';
        }
        $connectedString .= is_bool($value) ? ($value ? 'true' : 'false') : $value;
    }

    $generatedHmac = hash_hmac('sha512', $connectedString, $this->hmac);
    $incomingHmac  = $fullPayload['hmac'] ?? null;

    if ($incomingHmac && $generatedHmac !== $incomingHmac) {
        Log::warning('⚠️ HMAC verification failed');
        return response()->json(['message' => 'Invalid HMAC'], 400);
    }

    if ($fullPayload['type'] === 'TRANSACTION' && ($payload['success'] === true || $payload['success'] === 'true')) {
$merchantOrderId = $payload['order']['merchant_order_id'] ?? null;

if (!$merchantOrderId) {
    return response()->json(['message' => 'Missing merchant_order_id'], 400);
}

$parts = explode('_', $merchantOrderId, 2);
$attributeId = $parts[0] ?? null;

if (!$attributeId) {
    return response()->json(['message' => 'Invalid merchant_order_id format'], 400);
}

$payment = PaymentRequest::where('attribute_id', $attributeId)->first();

if (!$payment) {
    return response()->json(['message' => 'Payment record not found'], 404);
}


        if (!$payment->is_paid) {
            $payment->is_paid = 1;
            $payment->transaction_id = $payload['id'] ?? null; 
            $payment->payment_method = 'paymob_accept';
            $payment->save();

            Log::info("✅ Payment updated: {$payment->id}");

            if (!empty($payment->success_hook) && function_exists($payment->success_hook)) {
                call_user_func($payment->success_hook, $payment);
            }
        }

        return response()->json(['message' => 'Payment verified and updated successfully'], 200);
    }

    return response()->json(['message' => 'Webhook received but not a successful transaction'], 200);
}



  
}
