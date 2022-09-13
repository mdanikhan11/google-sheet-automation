<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\UserGoogleSheet;


class GoogleController extends Controller
{

    /**
     * Return the url of the google auth.
     * FE should call this and then direct to this url.
     *
     * @return JsonResponse
     */
    public function getAuthUrl(Request $request): JsonResponse
    {
        /**
         * Create google client
         */
        $client = $this->getClient();

        /**
         * Generate the url at google we redirect to
         */
        $authUrl = $client->createAuthUrl();

        /**
         * HTTP 200
         */
        return response()->json($authUrl, 200);
    } // getAuthUrl


    /**
     * Login and register
     * Gets registration data by calling google Oauth2 service
     *
     * @return JsonResponse
     */
    public function postLogin(Request $request): JsonResponse
    {

        /**
         * Get authcode from the query string
         */
        $authCode = urldecode($request->input('code'));
        // $authCode = urldecode($request->input('auth_code'));

        // session()->put('authCode',$authCode);
        /**
         * Google client
         */
        $client = $this->getClient();

        /**
         * Exchange auth code for access token
         * Note: if we set 'access type' to 'force' and our access is 'offline', we get a refresh token. we want that.
         */
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        /**
         * Set the access token with google. nb json
         */
        $client->setAccessToken(json_encode($accessToken));

        /**
         * Get user's data from google
         */
        $service = new \Google\Service\Oauth2($client);
        $userFromGoogle = $service->userinfo->get();

        if (auth()->check()) {
            $user = User::find(auth()->id());
            $user->provider_id = $userFromGoogle->id;
            $user->provider_name = 'google';
            $user->google_access_token_json = json_encode($accessToken);
            $user->email =  $userFromGoogle->email;
            $user->save();
        } else {
            /**
             * Select user if already exists
             */
            $user = User::where('provider_name', '=', 'google')
                ->where('provider_id', '=', $userFromGoogle->id)
                ->first();

            /**
             */
            if (!$user) {
                $user = User::create([
                    'provider_id' => $userFromGoogle->id,
                    'provider_name' => 'google',
                    'google_access_token_json' => json_encode($accessToken),
                    'name' => $userFromGoogle->name,
                    'email' => $userFromGoogle->email,
                    //'avatar' => $providerUser->picture, // in case you have an avatar and want to use google's
                ]);
            }
            /**
             * Save new access token for existing user
             */
            else {
                $user->google_access_token_json = json_encode($accessToken);
                $user->save();
            }
        }


        /**
         * Log in and return token
         * HTTP 201
         */
        // dd($accessToken);
        $token = $user->createToken("Google")->accessToken;
        return response()->json($token, 201);
    } // postLogin


    /**
     * Get meta data on a page of files in user's google drive
     *
     * @return JsonResponse
     */
    public function getDrive(Request $request): JsonResponse
    {
        /**
         * Get google api client for session user
         */
        $client = $this->getUserClient();

        /**
         * Create a service using the client
         * @see vendor/google/apiclient-services/src/
         */
        $service = new \Google\Service\Drive($client);

        /**
         * The arguments that we pass to the google api call
         */
        $parameters = [
            'pageSize' => 10,
        ];

        /**
         * Call google api to get a list of files in the drive
         */
        $results = $service->files->listFiles($parameters);

        /**
         * HTTP 200
         */
        return response()->json($results, 200);
    }


    /**
     * Gets a google client
     *
     * @return \Google_Client
     */
    private function getClient(): \Google_Client
    {
        $configJson = 'new.json';

        $applicationName = 'Google Sheet and Drive';

        // create the client
        $client = new \Google_Client();
        $client->setApplicationName($applicationName);
        $client->setAuthConfig($configJson);
        $client->setAccessType('offline'); // necessary for getting the refresh token
        $client->setApprovalPrompt('force'); // necessary for getting the refresh token
        // scopes determine what google endpoints we can access. keep it simple for now.
        $client->setScopes(
            [
                \Google\Service\Oauth2::USERINFO_PROFILE,
                \Google\Service\Oauth2::USERINFO_EMAIL,
                \Google\Service\Oauth2::OPENID,
                \Google\Service\Drive::DRIVE_METADATA_READONLY,
                \Google\Service\Sheets::SPREADSHEETS,
                \Google\Service\Drive::DRIVE,
            ]
        );
        $client->setIncludeGrantedScopes(true);
        return $client;
    } // getClient


    /**
     * Returns a google client that is logged into the current user
     *
     * @return \Google_Client
     */
    private function getUserClient(): \Google_Client
    {
        /**
         * Get Logged in user
         */

        $user = User::where('id', '=', auth()->guard('web')->user()->id)->first();

        /**
         * Strip slashes from the access token json
         * if you don't strip mysql's escaping, everything will seem to work
         * but you will not get a new access token from your refresh token
         */
        $accessTokenJson = stripslashes($user->google_access_token_json);

        /**
         * Get client and set access token
         */
        $client = $this->getClient();
        $client->setAccessToken($accessTokenJson);

        /**
         * Handle refresh
         */
        if ($client->isAccessTokenExpired()) {
            // fetch new access token
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            $client->setAccessToken($client->getAccessToken());

            // save new access token
            $user->google_access_token_json = json_encode($client->getAccessToken());
            $user->save();
        }

        return $client;
    } // getUserClient


    public function create_sheet(Request $request)
    {

        $client = $this->getUserClient();

        $service = new \Google_Service_Sheets($client);
        try {

            $spreadsheet = new \Google_Service_Sheets_Spreadsheet([
                'properties' => [
                    'title' => $request->title
                ]
            ]);
            $spreadsheet = $service->spreadsheets->create($spreadsheet, [
                'fields' => 'spreadsheetId'
            ]);
            $newSpreadSheet = new UserGoogleSheet();
            $newSpreadSheet->user_id = auth()->id();
            $newSpreadSheet->spreadsheet_id = $spreadsheet->spreadsheetId;
            $newSpreadSheet->title = $request->title;
            $newSpreadSheet->save();

            return redirect('/home')->with('status', 'Spreed Sheet Created Successfully');

            // printf("Spreadsheet ID: %s\n", $spreadsheet->spreadsheetId);
            // return $spreadsheet->spreadsheetId;
        } catch (Exception $e) {
            // TODO(developer) - handle error appropriately
            echo 'Message: ' . $e->getMessage();
        }
    }

    public function readGoogleSheet()
    {
        $dimensions = $this->getDimensions($this->spreadSheetId());
        $range = 'Sheet1!A1:' . $dimensions['colCount'];

        $data = $this->googleSheetService()
            ->spreadsheets_values
            ->batchGet($this->spreadSheetId(), ['ranges' => $range]);

        return $data->getValueRanges()[0]->values;
    }


    public function saveDataToSheet(array $data = null)
    {
        $data = [
            [
                "2",
                "asd salman",
                "qwqsalman@text.com",
                "test"
            ]
        ];
        $dimensions = $this->getDimensions($this->spreadSheetId());
        try {
            $body = new \Google_Service_Sheets_ValueRange([
                'values' => $data
            ]);

            $params = [
                'valueInputOption' => 'USER_ENTERED',
            ];

            $range = "A" . ($dimensions['rowCount'] + 1);
            $result = $this->googleSheetService()
                ->spreadsheets_values
                ->append($this->spreadSheetId(), $range, $body, $params);
            if (isset($result->getUpdates()->updatedRows) && $result->getUpdates()->updatedRows == 1) {
                $msg = [
                    'status' => true,
                ];
                // dd($result->getUpdates());
            }else{
                $msg = [
                    'status' => false,
                ];
            }
            return $msg;
            // printf("%d cells appended.", $result->getUpdates()->getUpdatedCells());
            // return $result;
        } catch (\Exception $e) {
            // TODO(developer) - handle error appropriately
            return  $msg = [
                'status' => false,
            ];
            // dd($e->getMessage());
        }
    }

    private function getDimensions($spreadSheetId)
    {
        $rowDimensions = $this->googleSheetService()->spreadsheets_values->batchGet(
            $spreadSheetId,
            ['ranges' => 'Sheet1!A:A', 'majorDimension' => 'COLUMNS']
        );

        //if data is present at nth row, it will return array till nth row
        //if all column values are empty, it returns null
        $rowMeta = $rowDimensions->getValueRanges()[0]->values;
        if (!$rowMeta) {
            return [
                'error' => true,
                'message' => 'missing row data'
            ];
        }

        $colDimensions = $this->googleSheetService()->spreadsheets_values->batchGet(
            $spreadSheetId,
            ['ranges' => 'Sheet1!1:1', 'majorDimension' => 'ROWS']
        );

        //if data is present at nth col, it will return array till nth col
        //if all column values are empty, it returns null
        $colMeta = $colDimensions->getValueRanges()[0]->values;
        if (!$colMeta) {
            return [
                'error' => true,
                'message' => 'missing row data'
            ];
        }

        return [
            'error' => false,
            'rowCount' => count($rowMeta[0]),
            'colCount' => $this->colLengthToColumnAddress(count($colMeta[0]))
        ];
    }


    private function colLengthToColumnAddress($number)
    {
        if ($number <= 0) return null;

        $letter = '';
        while ($number > 0) {
            $temp = ($number - 1) % 26;
            $letter = chr($temp + 65) . $letter;
            $number = ($number - $temp - 1) / 26;
        }
        return $letter;
    }

    private function googleSheetService()
    {
        return new \Google_Service_Sheets($this->getUserClient());
    }

    private function spreadSheetId()
    {
        return '1wX1r4h1AjLO3ExKTR3k3DZqTY1VDD0bavuVnm9wjWx0';
    }



}
