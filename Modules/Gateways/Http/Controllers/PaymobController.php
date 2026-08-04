<?php

namespace Modules\Gateways\Http\Controllers;


use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Modules\Gateways\Traits\Processor;
use Modules\Gateways\Entities\PaymentRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymobController extends Controller
{
    use Processor;

    private mixed $config_values;
   protected  $api_key ;
    protected  $iframe_id ;
    protected  $integration_id ;
    protected  $hmac ;
    private PaymentRequest $payment;
    private User $user;

    public function __construct(PaymentRequest $payment, User $user)
    {
         $this->api_key = "ZXlKaGJHY2lPaUpJVXpVeE1pSXNJblI1Y0NJNklrcFhWQ0o5LmV5SmpiR0Z6Y3lJNklrMWxjbU5vWVc1MElpd2ljSEp2Wm1sc1pWOXdheUk2TVRBMU5UTXhNQ3dpYm1GdFpTSTZJbWx1YVhScFlXd2lmUS5lYUFmd2x0cUlYX0ZrdjlubkRzandGa3NlanBZYm9uRFZRQTF3VThWeld1QVBpMnRaX0pHREVMeG85RUJ3UndRU2Fia1ZyN1JPbFNxaGhMSktneDBSdw==";

        $this->hmac = "BAFDACCB2FD0DFEFBCEC3A4CDEDA61AF";
 
      
        $this->payment = $payment;
        $this->user = $user;
    }

    protected function cURL($url, $json)
    {
        $ch = curl_init($url);

        $headers = array();
        $headers[] = 'Content-Type: application/json';

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $output = curl_exec($ch);

        curl_close($ch);
        return json_decode($output);
    }

 public function credit(Request $request)
{
    $secret = "egy_sk_live_7070aae6b18627a6746c53c5edf2e5d458be3341c9d3af8720c4c05a865c04b4";
    $public = "egy_pk_live_AfLEs69u7bqoB6HY7EugeRJ7EAunVWxY";

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
        "payment_methods" => [5305675],
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
        return view('Gateways::success');
    }

    return view('Gateways::fail');
}



  
  
  
public function webhook(Request $request)
{
    $data = $request->all();

    file_put_contents(
        storage_path('app/paymob_webhook_latest.json'),
        json_encode($data, JSON_PRETTY_PRINT)
    );

    $transaction = $data['transaction'] ?? null;

    if (!$transaction) {
        return response()->json(['message' => 'No transaction object'], 400);
    }

    if ($transaction['success'] !== true) {
        return response()->json(['message' => 'Transaction not successful'], 200);
    }

    // هنا بناخد special_reference من intention
    $specialReference = $data['intention']['special_reference'] ?? null;

    if (!$specialReference) {
        return response()->json(['message' => 'Missing special_reference'], 400);
    }

    $parts = explode('_', $specialReference);
    $attributeId = $parts[0] ?? null;

    if (!$attributeId) {
        return response()->json(['message' => 'Invalid special_reference'], 400);
    }

    $payment = PaymentRequest::where('attribute_id', $attributeId)->first();

    if (!$payment) {
        return response()->json(['message' => 'Payment not found'], 404);
    }

    if (!$payment->is_paid) {
        $payment->update([
            'is_paid' => 1,
            'transaction_id' => $transaction['id'],
            'payment_method' => 'paymob_accept'
        ]);
    }

    return response()->json(['message' => 'Payment updated successfully']);
}


}
