<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Http\Client\Response;

class SheetController extends Controller
{

    public function index()
    {
        return $this->getClient();
    }

    public function handleGoogleCallback(Request $request)
    {
        dd($request->all());
        // try {

        //     $user = Socialite::driver('google')->user();

        //     $finduser = User::where('social_id', $user->id)->where('signup_method', 'Google')->first();
        //     if ($finduser) {
        //         Auth::login($finduser);
        //         if(auth()->user()->id){
        //             $finduser->last_login = date('Y-m-d h:i:s');
        //             $finduser->save();
        //             return redirect()->route('front.project.all');
        //         }
        //     } else {
        //         if (!isset($user->email)) {
        //             $msg = [
        //                 'error' => 'Your Email Cannot be Found'
        //             ];
        //             return redirect(route('front.main'))->with($msg);
        //         }
        //         $newUser = new User();
        //         $newUser->full_name = $user->name;
        //         $newUser->password = \Hash::make(\Str::random(8));
        //         $newUser->email = $user->email;
        //         $newUser->social_id = $user->id;
        //         $newUser->signup_method = 'Google';
        //         $newUser->last_login = date('Y-m-d h:i:s');
        //         $newUser->is_active = 1;
        //         $newUser->save();

        //         Auth::login($newUser);
        //         if(auth()->user()->id){
        //             return redirect()->route('front.project.all');
        //         }
        //     }
        // } catch (\Exception $e) {
        //     // dd($e->getMessage());
        //     $msg = [
        //         'haserror' => $e->getMessage(),
        //     ];
        //     return redirect()->route('front.main')->with($msg);
        // }
    }


    function getClient()
    {
        $client = new Client();
        $client->setApplicationName('Google Sheets API PHP Quickstart');
        $client->setScopes('https://www.googleapis.com/auth/spreadsheets',Drive::DRIVE);
        $client->setAuthConfig('client_secret.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = 'token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {

                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

            } else {
                // dd('out');
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                print "<br><br><br>".$authUrl;
                print 'Enter verification code: ';
                $authCode = "4/0AdQt8qgWxuGfPv9JcEawIV-BeyU64LCCM3egR261lvtA1GPpW0h0_8ElBna9lJrVDlzTpg";

                // $authCode =trim(fgets(STDIN));
                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        // dd($client);
        // return response($client);
        // return $client;
    }

    function create($title)
    {
        /* Load pre-authorized user credentials from the environment.
           TODO(developer) - See https://developers.google.com/identity for
            guides on implementing OAuth2 for your application. */
        $client = new Client();
        $client->setAuthConfig('client_secret.json');
        $client->addScope(Drive::DRIVE);
        $authUrl = $client->createAuthUrl();
        // dd($authUrl);
        $authCode = "4/0AdQt8qiWIuu4dusLbpWHdMzmcZvzuOaXuad2fSI_X0LssviGSmUdkBMtNWztnukTZcQeNw";
        // $authCode =trim(fgets(STDIN));
        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        $client->setAccessToken($accessToken);


        $service = new \Google_Service_Sheets($client);
        try{

            $spreadsheet = new \Google_Service_Sheets_Spreadsheet([
                'properties' => [
                    'title' => $title
                    ]
                ]);
                $spreadsheet = $service->spreadsheets->create($spreadsheet, [
                    'fields' => 'spreadsheetId'
                ]);
                printf("Spreadsheet ID: %s\n", $spreadsheet->spreadsheetId);
                return $spreadsheet->spreadsheetId;
        }
        catch(Exception $e) {
            // TODO(developer) - handle error appropriately
            echo 'Message: ' .$e->getMessage();
          }
    }
}
