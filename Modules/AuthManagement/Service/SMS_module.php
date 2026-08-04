<?php

namespace Modules\AuthManagement\Service;

use Twilio\Rest\Client;

use Illuminate\Support\Facades\DB;


class SMS_module
{
    public static function send($receiver, $otp)
    {
        $config = self::get_settings('whysms');
        if (isset($config) && $config['status'] == 1) {
            return self::whysms($receiver, $otp);
        }

        $config = self::get_settings('smsmisr');
        if (isset($config) && $config['status'] == 1) {
            return self::smsmisr($receiver, $otp);
        }

        $config = self::get_settings('twilio');
        if (isset($config) && $config['status'] == 1) {
            return self::twilio($receiver, $otp);
        }

        $config = self::get_settings('nexmo');
        if (isset($config) && $config['status'] == 1) {
            return self::nexmo($receiver, $otp);
        }

        $config = self::get_settings('2factor');
        if (isset($config) && $config['status'] == 1) {
            return self::two_factor($receiver, $otp);
        }

        $config = self::get_settings('msg91');
        if (isset($config) && $config['status'] == 1) {
            return self::msg_91($receiver, $otp);
        }
        $config = self::get_settings('alphanet_sms');
        if (isset($config) && $config['status'] == 1) {
            return self::alphanet_sms($receiver, $otp);
        }



        return 'not_found';
    }

    public static function smsmisr($receiver, $otp): string
    {
        $config = self::get_settings('smsmisr');
        $response = 'error';

        // Log the config for debugging
        \Log::info('SMS Misr Config:', [
            'config_exists' => isset($config),
            'config_status' => isset($config['status']) ? $config['status'] : 'not set',
            'config_keys' => isset($config) ? array_keys($config) : []
        ]);

        if (isset($config) && $config['status'] == 1) {
            // Validate required fields
            $required_fields = ['environment', 'username', 'password', 'sender', 'template', 'base_url'];
            $missing_fields = [];
            foreach ($required_fields as $field) {
                if (!isset($config[$field]) || empty($config[$field])) {
                    $missing_fields[] = $field;
                }
            }

            if (!empty($missing_fields)) {
                \Log::error('SMS Misr: Missing required fields - ' . implode(', ', $missing_fields));
                return 'error';
            }

            try {
                $curl = curl_init();

                // Remove any spaces or special characters from the phone number
                $receiver = preg_replace('/[^0-9]/', '', $receiver);

                // Prepare POST data
                $postData = [
                    'environment' => $config['environment'],
                    'username' => $config['username'],
                    'password' => $config['password'],
                    'sender' => $config['sender'],
                    'mobile' => $receiver,
                    'template' => $config['template'],
                    'otp' => $otp
                ];

                // Log the request data (remove sensitive info)
                $logData = $postData;
                $logData['password'] = '****';
                \Log::info('SMS Misr Request:', $logData);

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $config['base_url'] . '/OTP/',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => http_build_query($postData),
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/x-www-form-urlencoded',
                        'Accept: application/json'
                    ),
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);

                // Log the response
                \Log::info('SMS Misr Response:', [
                    'http_code' => $httpCode,
                    'response' => $response,
                    'error' => $err
                ]);

                if (!$err) {
                    // The response is a JSON string that needs to be decoded
                    $result = json_decode($response, true);
                    if ($result && isset($result['Code'])) { // Note: Changed from 'code' to 'Code'
                        switch ($result['Code']) { // Note: Changed from 'code' to 'Code'
                            case '4901': // Success
                                $response = 'success';
                                break;
                            case '4903': // Invalid credentials
                                \Log::error('SMS Misr: Invalid credentials');
                                $response = 'error';
                                break;
                            case '4904': // Invalid sender
                                \Log::error('SMS Misr: Invalid sender ID');
                                $response = 'error';
                                break;
                            case '4905': // Invalid mobile
                                \Log::error('SMS Misr: Invalid mobile number');
                                $response = 'error';
                                break;
                            case '4906': // Insufficient credit
                                \Log::error('SMS Misr: Insufficient credit');
                                $response = 'error';
                                break;
                            case '4907': // Server under updating
                                \Log::error('SMS Misr: Server under updating');
                                $response = 'error';
                                break;
                            case '4908': // Invalid OTP
                                \Log::error('SMS Misr: Invalid OTP');
                                $response = 'error';
                                break;
                            case '4909': // Invalid template
                                \Log::error('SMS Misr: Invalid template');
                                $response = 'error';
                                break;
                            case '4912': // Invalid environment
                                \Log::error('SMS Misr: Invalid environment');
                                $response = 'error';
                                break;
                            default:
                                \Log::error('SMS Misr: Unknown error code - ' . $result['Code']);
                                $response = 'error';
                                break;
                        }
                    } else {
                        \Log::error('SMS Misr: Invalid response format - ' . $response);
                        $response = 'error';
                    }
                } else {
                    \Log::error('SMS Misr: CURL Error - ' . $err);
                    $response = 'error';
                }
            } catch (\Exception $exception) {
                \Log::error('SMS Misr Exception: ' . $exception->getMessage());
                $response = 'error';
            }
        } else {
            \Log::error('SMS Misr: Configuration not found or disabled');
        }
        return $response;
    }

    public static function twilio($receiver, $otp): string
    {
        $config = self::get_settings('twilio');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $message = str_replace("#OTP#", $otp, $config['otp_template']);
            $sid = $config['sid'];
            $token = $config['token'];
            try {
                $twilio = new Client($sid, $token);
                $twilio->messages
                    ->create($receiver, // to
                        array(
                            "messagingServiceSid" => $config['messaging_service_sid'],
                            "body" => $message
                        )
                    );
                $response = 'success';
            } catch (\Exception $exception) {
                $response = 'error';
            }
        }
        return $response;
    }

    public static function nexmo($receiver, $otp): string
    {
        $config = self::get_settings('nexmo');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $message = str_replace("#OTP#", $otp, $config['otp_template']);
            try {
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, 'https://rest.nexmo.com/sms/json');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "from=".$config['from']."&text=".$message."&to=".$receiver."&api_key=".$config['api_key']."&api_secret=".$config['api_secret']);

                $headers = array();
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo 'Error:' . curl_error($ch);
                }
                curl_close($ch);
                $response = 'success';
            } catch (\Exception $exception) {
                $response = 'error';
            }
        }
        return $response;
    }

    public static function whysms($receiver, $otp): string
{
    \Log::info('WhySMS: Starting SMS send process', ['receiver' => $receiver, 'otp' => $otp]);

    $config = self::get_settings('whysms');
    $response = 'error';

    if (!isset($config)) {
        \Log::warning('WhySMS: No config found');
        return $response;
    }

    if ($config['status'] != 1) {
        \Log::warning('WhySMS: Config is disabled', ['config_status' => $config['status']]);
        return $response;
    }

    \Log::info('WhySMS: Config loaded', ['config' => $config]);

    // prepare message
    $message = str_replace("#OTP#", $otp, $config['otp_template'] ?? 'Your verification code is #OTP#');
    $receiver = preg_replace('/[^0-9]/', '', $receiver); // sanitize

    \Log::info('WhySMS: Final message prepared', ['message' => $message, 'receiver' => $receiver]);

    $postData = [
        'token' => trim($config['api_token']),
        'sender_id' => $config['sender_id'],
        'recipient' => $receiver,
        'type' => 'plain',
        'message' => $message,
    ];

$user = auth()->user();
$token = null;

if ($user && method_exists($user, 'token')) {
    $token = $user->token();
} elseif (request()->bearerToken()) {
    $token = request()->bearerToken(); // fallback if using Bearer
}

\Log::info('WhySMS: POST data prepared', [
    'postData' => $postData,
    'user_id' => $user?->id,
    'token_value' => $token
]);

    try {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://bulk.whysms.com/api/v3/sms/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($postData), // use JSON here
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer ' . $postData['token'],
            ],
        ));

        \Log::info('WhySMS: Sending request to WhySMS');

        $result = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        \Log::info('WhySMS: Response received', ['raw_response' => $result, 'curl_error' => $err]);

        if (!$err) {
            $decoded = json_decode($result, true);

            \Log::info('WhySMS: Decoded response', ['decoded' => $decoded]);

            if (isset($decoded['status']) && $decoded['status'] === 'success') {
                \Log::info('WhySMS: SMS sent successfully');
                $response = 'success';
            } elseif (isset($decoded['message']) && str_contains($decoded['message'], 'limit')) {
                \Log::warning('WhySMS: Sending limit reached', ['receiver' => $receiver, 'message' => $decoded['message']]);
            } else {
                \Log::warning('WhySMS: Failed to send SMS', ['response' => $decoded]);
            }
        } else {
            \Log::error('WhySMS: CURL error occurred', ['error' => $err]);
        }
    } catch (\Exception $ex) {
        \Log::error('WhySMS: Exception caught during sending', [
            'exception_message' => $ex->getMessage(),
            'trace' => $ex->getTraceAsString()
        ]);
    }

    return $response;
}


    public static function two_factor($receiver, $otp): string
    {
        $config = self::get_settings('2factor');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $api_key = $config['api_key'];
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://2factor.in/API/V1/" . $api_key . "/SMS/" . $receiver . "/" . $otp . "",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if (!$err) {
                $response = 'success';
            } else {
                $response = 'error';
            }
        }
        return $response;
    }

    public static function msg_91($receiver, $otp): string
    {
        $config = self::get_settings('msg91');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $receiver = str_replace("+", "", $receiver);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.msg91.com/api/v5/otp?template_id=" . $config['template_id'] . "&mobile=" . $receiver . "&authkey=" . $config['auth_key'] . "",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "{\"OTP\":\"$otp\"}",
                CURLOPT_HTTPHEADER => array(
                    "content-type: application/json"
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if (!$err) {
                $response = 'success';
            } else {
                $response = 'error';
            }
        }
        return $response;
    }

    public static function alphanet_sms($receiver, $otp ,$message = null): string
    {
        $config = self::get_settings('alphanet_sms');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            if($message ==  null){
                $message = str_replace("#OTP#", $otp, $config['otp_template']);
            }

            $receiver = str_replace("+", "", $receiver);
            $api_key = $config['api_key'];
            $sender_id = $config['sender_id'] ?? null;


            $postfields = array(
                'api_key' => $api_key,
                'msg' => $message,
                'to' => $receiver
            );

            if ($sender_id) {
                $postfields['sender_id'] = $sender_id;
            }


            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.sms.net.bd/sendsms',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $postfields,
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ((int) data_get(json_decode($response,true),'error') === 0) {
                $response = 'success';
            } else {
                $response = 'error';
            }
        }
        return $response;
    }




    public static function get_settings($name)
    {
        $config = DB::table('settings')->where('key_name', $name)
        ->where('settings_type', 'sms_config')->first();

        if (isset($config) && !is_null($config->live_values)) {
            return json_decode($config->live_values, true);
        }
        return null;
    }
}
