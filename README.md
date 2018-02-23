# A Wild SteemConnect V2 Api Appears
A SteemConnect V2 Login Handler For PHP

#### Tutorial:

Ok, so firstly, go to the [SteemConnect Version 2 Dashboard](https://v2.steemconnect.com/dashboard) and log in with your steemit Posting, Memo or Master Key.
Next, click **My Apps** and if you already have one ignore the creation process below, unless you wish to create a new one.

- Click **+ New App**
- Enter a username for your app, like mine [**@cadawg.app**](https://v2.steemconnect.com/apps/@cadawg.app) (Click it to see it's app page).
- Sign in with your active / master key to pay the 3 STEEM Fee, you will need to have this in your balance.
- Next, Fill out the form with details about your app, and put the redirect URI as http://localhost/callback.php, http://localhost:8080/callback.php or whatever your local web server runs on, make sure to note it down EXACTLY, as the filename and location will have to be IDENTICAL when we provide it in the request.
- Click Save / Update / Green Button at the bottom.

Congratulations, you have your first SteemConnect Version 2 App.

Now, we need to 'create' your URL for authorising your app.

Decide what your app needs access to:

List Of Things You Can Request:
- login - Prove the Identity of a steem user
- offline - allows them to stay logged in / with the set ability for 7 days
- vote - Up, Down or Unvote a post or comment
- comment - Publish/Edit a post or comment
- comment_delete - Delete a post or comment
- comment_options - Add options for a post/comment
- custom_json - (Un)Follow, Ignore, reblog or any other custom_json operation
- claim_reward_balance - Claim reward on behalf of user (Goes into their balance)

To set which ones you want just supply them as a comma-separated list

The URL You Will Use Is:
https://v2.steemconnect.com/oauth2/authorize?client_id=CLIENT_ID&redirect_uri=REDIRECT_URI&scope=SCOPE

CLIENT_ID should be replaced with your app's username, without the @
REDIRECT_URI should be the redirect URI you specified earlier, just the same, ending in callback.php
SCOPE should be a comma separated list of things you need access to.

Now when you click this link it should ask you to sign in with a key, it depends on permissions you have requested.
For the sake of my tutorial, I am going to use login, offline as my scope, and for your script to work with it, I recommend that you use offline and login in your scope so that it will work in the same way.

My URL is:
https://v2.steemconnect.com/oauth2/authorize?client_id=cadawg.app&redirect_uri=http://localhost:8080/SteemApps/callback.php&scope=login%2Coffline
But of course, yours may vary. %2C is a comma, and that is why it appears like that in the URL. (You may need to replace comma with %2C if it doesn't work)

**Now, the reason you came here, the code to verify a user!**

Firstly, we are going to make a user verification file, called *getLogin.php* in the same directory as you are planning to put the callback in (probably www or htdocs).

Start this file with this if statement, to make sure that the session variable has an authorisation code.
We don't need to start the session as this file will be imported into files with session_start(); in them already
If it is not set then we return false, meaning that nobody is logged in, because, without an authorisation code, we can't verify them.

```
if (!isset($_SESSION['code'])) {
    return false;
} else {
```

Now, in this part of the statement, we know that they have an auth code set.
According to [their guide](https://github.com/steemit/steemconnect/wiki/OAuth-2), we just have to pass the code as the Authorization: header.
We get the code and set it into a variable which we can use.

```
$authstr = "authorization: " . $_SESSION['code'];
```

Now we need to make a CURL session so that we can retrieve the data
```
$check = curl_init();
```

Now We Need To Configure CURL with the correct parameters so that it works.

CURLOPT:
- _URL = Url To Connect To, This is the URL for the API which returns information about your user
- _RETURNTRANSFER = Return it instead of showing it straight to the page.
- _ENCODING = Encoding of text
- _MAXREDIRS = Maximum amount of redirects before stopping.
- _TIMEOUT = If no result in x seconds, stop the request
- _HTTP_VERSION = The current HTTP Version (1.1)
- _CUSTOMREQUEST = Type of request (POST/GET/Other)
- _POSTFIELDS = The body of the request, in this, there is none required.
- _HTTPHEADER = Headers to send with the request, Disables Caching, Sends Auth String & Tells it we are sending JSON

```
curl_setopt_array($check, array(
        CURLOPT_URL => "https://v2.steemconnect.com/api/me",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 1,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "{}",
        CURLOPT_HTTPHEADER => array(
            $authstr,
            "cache-control: no-cache",
            "content-type: application/json",
        ),
    ));
```

Fetch the result:
```
$result = curl_exec($check);
```

Close the CURL session:
```
curl_close($check);
```

Convert JSON to PHP Object
```
$_result = json_decode($result);
```

Return the user if it was found, and if not, it must have errored (Probably meaning no user exists) and returns false
```
if(isset($_result->user)) {
        return $_result->user;
    } else {
        return false;
    }
}
```

Now we need to create **callback.php** to accept the result and check it
*But wait, the URL has their username in it, so why do we need to look it up? This is because they could tamper with the URL and make it so that it has a different (wrong) username. NEVER Trust the user*

Start the file with the session_start(); command to start a session
```
<?php
session_start();
```

Next, we will create a function to use the state variable to redirect them, the state can be passed to the website and it will be passed back, allowing you to keep track of the page they were on.

So if you make a user sign in at:
https://v2.steemconnect.com/oauth2/authorize?[All the other stuff]&state=http://localhost:8080/lastpageyouwereon.php

The URL it returns back to will have the state get variable on the end, with the same value.
http://localhost:8080/SteemApps/callback.php?access_token=...&expires_in=...&state=http://localhost:8080/lastpageyouwereon.php

So we can redirect them back to where they were for convenience.
Since this is the last part of the file, we make it die(); for security.
Replace The [Your Homepage] With the URL of your homepage, this is where it redirects to if it doesn't have a state variable.

```
if (isset($_GET['state'])) {
        header("Location: " . $_GET['state']);
        die();
    } else {
        header("Location: [Your Homepage]");
        die();
    }
}
```

Now we need to make sure that both the required variables are set
```
if (isset($_GET['access_token']) and isset($_GET['expires_in'])) {
```

Set the session's code to the access_token from steemConnect
```
$_SESSION['code'] = $_GET['access_token'];
```

Check if the number of seconds it expires in is correct. (7 Days in seconds). And then set its expiry time to the current time since 1/1/70 00:00 in seconds (Epoch / Unix Time).
```
if ((integer) $_GET['expires_in'] == 604800) {
        $_SESSION['expires'] = time() + 604800;
    } else {
```

If invalid, destroy the session and redirect them to where they came from, and redirect them using our function
```
session_unset();
        session_regenerate_id(true);
        redirect();
    }
```

Now require our previously made file to check their access token is good:
```
$usr_name = require 'getLogin.php';
```

Check whether the username is valid from the file and set it, then redirect them.
```
if ($usr_name != false) {
        $_SESSION['user'] = $usr_name;
        redirect();
    } else {
```

If their username is invalid, destroy the session and redirect them
```
        session_unset();
        session_regenerate_id(true);
        redirect();
    }
}
```

Also, if your app's data somehow gets compromised and someone dodgy gets the access tokens of your users, you can go to:
https://v2.steemconnect.com/apps/@[your app name]
And click the Revoke Tokens button to invalidate all the user's tokens, making them useless.

![Revoke Tokens](https://res.cloudinary.com/hpiynhbhq/image/upload/v1518975125/wdoitrgpe67vyuunbmix.png)
