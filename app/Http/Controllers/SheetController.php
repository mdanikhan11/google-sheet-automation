<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserGoogleSheet;
use Illuminate\Http\Request;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;

class SheetController extends Controller
{

    public function index()
    {
    }

    public function handleGoogleCallback(Request $request)
    {
        /**
         * Get authcode from the query string
         */
        $authCode = urldecode($request->input('code'));


        dd($authCode);
    }



    public function create(Request $request)
    {
        /* Load pre-authorized user credentials from the environment.
           TODO(developer) - See https://developers.google.com/identity for
            guides on implementing OAuth2 for your application. */
        // $client = new Client();
        // $client->setAuthConfig('client_secret.json');
        // $client->addScope(Drive::DRIVE);
        // $authUrl = $client->createAuthUrl();
        // // dd($authUrl);
        // $authCode = "4/0AdQt8qiWIuu4dusLbpWHdMzmcZvzuOaXuad2fSI_X0LssviGSmUdkBMtNWztnukTZcQeNw";
        // // $authCode =trim(fgets(STDIN));
        // // Exchange authorization code for an access token.
        // $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        // $client->setAccessToken($accessToken);

        $client = $this->getUserClient();

        $service = new \Google_Service_Sheets($client);
        try{

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
            $newSpreadSheet->save();

            return redirect('/home')->with('status','Spreed Sheet Created Successfully');

                // printf("Spreadsheet ID: %s\n", $spreadsheet->spreadsheetId);
                // return $spreadsheet->spreadsheetId;
        }
        catch(Exception $e) {
            // TODO(developer) - handle error appropriately
            echo 'Message: ' .$e->getMessage();
          }
    }



    /**
     * Returns a google client that is logged into the current user
     *
     * @return \Google_Client
     */
    private function getUserClient():\Google_Client
    {
        /**
         * Get Logged in user
         */
        $user = User::where('id', '=', auth()->user()->id)->first();

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
}
