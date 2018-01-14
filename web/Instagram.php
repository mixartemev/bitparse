<?php

require_once 'Constants.php';

/*

//изменение email
https://i.instagram.com/api/v1/accounts/send_confirm_email/
e53ca1b91c56b5e710236eb7aede1b06af92597fc84a31a67ccc36f3ee8aa45f.{"_uuid":"6AEF916A-7F14-4F4F-B7F4-6F941FAD3F50","_csrftoken":"25d8e170608efc14d21ed4bbdf2b49e7","send_source":"edit_profile","_uid":"2082772377","email":"lana@phoader.ru"}


//подтверждение email
https://i.instagram.com/api/v1/accounts/confirm_email/Y6TcZBge/bGFuYUBwaG9hZGVyLnJ1/
9bf27bc27b4bb741bc1470dc3b2d32f1471fef3f6d3d290aa33368a90c184021.{"_uuid":"6AEF916A-7F14-4F4F-B7F4-6F941FAD3F50","nonce":"Y6TcZBge","_uid":"2082772377","_csrftoken":"25d8e170608efc14d21ed4bbdf2b49e7","encoded_email":"bGFuYUBwaG9hZGVyLnJ1"}

https://instagram.com/accounts/confirm_email/Y6TcZBge/bGFuYUBwaG9hZGVyLnJ1/?app_redirect=True


//запрос на подтверждение email
https://i.instagram.com/api/v1/accounts/send_confirm_email/
30da1caf4903a884ff377ad68ccdd92ddce48e2be5150cffdd2b8b64a1975ac9.{"_uuid":"6AEF916A-7F14-4F4F-B7F4-6F941FAD3F50","_csrftoken":"25d8e170608efc14d21ed4bbdf2b49e7","_uid":"2082772377","send_source":"edit_profile"}


//изменение телефона
https://i.instagram.com/api/v1/accounts/send_sms_code/
f140e5266537d2d699776ab9fd817650d2231a73b0e27da2b28ab3baa6bb2fd4.{"_uuid":"6AEF916A-7F14-4F4F-B7F4-6F941FAD3F50","_csrftoken":"25d8e170608efc14d21ed4bbdf2b49e7","device_id":"6AEF916A-7F14-4F4F-B7F4-6F941FAD3F50","_uid":"2082772377","phone_number":"+79228862258"}


//запрос на подтверждение кода СМС
https://i.instagram.com/api/v1/accounts/verify_sms_code/
a9695c49819865f9860fffdecdd0ccfafac73a0ef6684ef4858542fbfe38f05c.{"verification_code":"805473","_uuid":"6AEF916A-7F14-4F4F-B7F4-6F941FAD3F50","device_id":"6AEF916A-7F14-4F4F-B7F4-6F941FAD3F50","_uid":"2082772377","_csrftoken":"25d8e170608efc14d21ed4bbdf2b49e7","phone_number":"+79228862258"}


//request phone отправка номера
https://i.instagram.com/integrity/checkpoint/?next=instagram%3A%2F%2Fcheckpoint%2Fdismiss
csrfmiddlewaretoken=a85ed552ff873f15fd7781a389ce6ba5&phone_number=89851564092


//request phone отправка кода
https://i.instagram.com/integrity/checkpoint/?next=instagram%3A%2F%2Fcheckpoint%2Fdismiss
csrfmiddlewaretoken=a85ed552ff873f15fd7781a389ce6ba5&response_code=290738



*/



class Instagram
{
  protected $username;            // Instagram username
  protected $password;            // Instagram password
  protected $debug;               // Debug

  protected $uuid;                // UUID
  protected $device_id;           // Device ID
  protected $username_id;         // Username ID
  protected $token;               // _csrftoken
  protected $isLoggedIn = false;  // Session status
  protected $rank_token;          // Rank token
  protected $IGDataPath;          // Data storage path

  /**
   * Default class constructor.
   *
   * @param string $username
   *   Your Instagram username.
   * @param string $password
   *   Your Instagram password.
   * @param $debug
   *   Debug on or off, false by default.
   * @param $IGDataPath
   *  Default folder to store data, you can change it.
   */
  public function __construct($username, $password, $debug = false, $IGDataPath = null)
  {
      $this->username = $username;
      $this->password = $password;
      $this->debug = $debug;

      $this->uuid = $this->generateUUID(true);
      $this->device_id = $this->generateDeviceId(md5($username.$password));

      if (!is_null($IGDataPath)) {
          $this->IGDataPath = $IGDataPath;
      } else {
          $this->IGDataPath = __DIR__.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR;
      }

      if ((file_exists($this->IGDataPath."$this->username-cookies.dat")) && (file_exists($this->IGDataPath."$this->username-userId.dat"))
    && (file_exists($this->IGDataPath."$this->username-token.dat"))) {
          $this->isLoggedIn = true;
          $this->username_id = trim(file_get_contents($this->IGDataPath."$username-userId.dat"));
          $this->rank_token = $this->username_id.'_'.$this->uuid;
          $this->token = trim(file_get_contents($this->IGDataPath."$username-token.dat"));
      }
  }




	/*
	{
		"status": "ok",
		"incoming_request": false,//запрос от него
		"outgoing_request": false,// запрос от меня
		"following": true, //подписан я
		"followed_by": true,//подписан он
		"blocking": false,
		"is_private": true //закрытый ли профиль
	}
	*/

  public function friendshipsShow($userId)
  {
      $data = json_encode(array(
        '_uuid'      => $this->uuid,
        '_uid'       => $this->username_id,
        'user_id'    => $userId,
        '_csrftoken' => $this->token,
    ));
      return $this->request("friendships/show/$userId/", $this->generateSignature($data));
  }



  /**
   * Login to Instagram.
   *
   * @return array
   *    Login data
   */
   
   
  public function login()
  {
      if (!$this->isLoggedIn) {
          $fetch = $this->request('si/fetch_headers/?challenge_type=signup&guid='.$this->generateUUID(false), null, true);
          preg_match('#Set-Cookie: csrftoken=([^;]+)#', $fetch[0], $token);

          $data = array(
			  'device_id'           => $this->device_id,
			  'guid'                => $this->uuid,
			  'phone_id'            => $this->generateUUID(true),
			  'username'            => $this->username,
			  'password'            => $this->password,
			  'login_attempt_count' => '0',
		  );

          $login = $this->request('accounts/login/', $this->generateSignature(json_encode($data)), true);

          $this->isLoggedIn = true;
          $this->username_id = $login[1]['logged_in_user']['pk'];
          file_put_contents($this->IGDataPath.$this->username.'-userId.dat', $this->username_id);
          $this->rank_token = $this->username_id.'_'.$this->uuid;
          preg_match('#Set-Cookie: csrftoken=([^;]+)#', $login[0], $match);
          $this->token = $match[1];
          file_put_contents($this->IGDataPath.$this->username.'-token.dat', $this->token);

          $this->syncFeatures();
          $this->autoCompleteUserList();
          $this->timelineFeed();
          $this->getv2Inbox();
          $this->getRecentActivity();

          return $login[1];
      }
      else { $login[1]['status']='logged'; return $login[1]; }

      $this->timelineFeed();
      $this->getv2Inbox();
      $this->getRecentActivity();
  }

    public function syncFeatures()
    {
        $data = json_encode(array(
        '_uuid'         => $this->uuid,
        '_uid'          => $this->username_id,
        'id'            => $this->username_id,
        '_csrftoken'    => $this->token,
        'experiments'   => Constants::EXPERIMENTS,
    ));

        return $this->request('qe/sync/', $this->generateSignature($data));
    }

    protected function autoCompleteUserList()
    {
        return $this->request('friendships/autocomplete_user_list/');
    }

    protected function timelineFeed()
    {
        return $this->request('feed/timeline/');
    }

    protected function megaphoneLog()
    {
        return $this->request('megaphone/log/');
    }

    protected function expose()
    {
        $data = json_encode(array(
        '_uuid'        => $this->uuid,
        '_uid'         => $this->username_id,
        'id'           => $this->username_id,
        '_csrftoken'   => $this->token,
        'experiment'   => 'ig_android_profile_contextual_feed',
    ));

        $this->request('qe/expose/', $this->generateSignature($data));
    }

  /**
   * Login to Instagram.
   *
   * @return bool
   *    Returns true if logged out correctly
   */
  public function logout()
  {
      $logout = $this->request('accounts/logout/');

      if ($logout == 'ok') {
          return true;
      } else {
          return false;
      }
  }

    /**
     * Upload photo to Instagram.
     *
     * @param string $photo
     *                        Path to your photo
     * @param string $caption
     *                        Caption to be included in your photo.
     *
     * @return array
     *               Upload data
     */
    public function uploadPhoto($photo, $caption = null)
    {
        $endpoint = Constants::API_URL.'upload/photo/';
        $boundary = $this->uuid;
        $bodies = array(
            array(
                'type' => 'form-data',
                'name' => 'upload_id',
                'data' => number_format(round(microtime(true) * 1000), 0, '', ''),
            ),
            array(
                'type' => 'form-data',
                'name' => '_uuid',
                'data' => $this->uuid,
            ),
            array(
                'type' => 'form-data',
                'name' => '_csrftoken',
                'data' => $this->token,
            ),
            array(
                'type' => 'form-data',
                'name' => 'image_compression',
              'data'   => '{"lib_name":"jt","lib_version":"1.3.0","quality":"70"}',
            ),
            array(
                'type'     => 'form-data',
                'name'     => 'photo',
                'data'     => file_get_contents($photo),
                'filename' => 'pending_media_'.number_format(round(microtime(true) * 1000), 0, '', '').'.jpg',
                'headers'  => array(
          'Content-Transfer-Encoding: binary',
                    'Content-type: application/octet-stream',
                ),
            ),
        );

        $data = $this->buildBody($bodies, $boundary);
        $headers = array(
                'Connection: close',
                'Accept: */*',
                'Content-type: multipart/form-data; boundary='.$boundary,
        'Content-Length: '.strlen($data),
        'Cookie2: $Version=1',
        'Accept-Language: en-US',
        'Accept-Encoding: gzip',
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, Constants::USER_AGENT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->IGDataPath."$this->username-cookies.dat");
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->IGDataPath."$this->username-cookies.dat");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $upload = json_decode(substr($resp, $header_len), true);

        curl_close($ch);

        if ($upload['status'] == 'fail') {
            throw new InstagramException($upload['message']);

            return;
        }

        if ($this->debug) {
            echo 'RESPONSE: '.substr($resp, $header_len)."\n\n";
        }

        $configure = $this->configure($upload['upload_id'], $photo, $caption);
        $this->expose();

        return $configure[1];
    }

    public function direct_share($media_id, $recipients, $text = null)
    {
        if (!is_array($recipients)) {
            $recipients = array($recipients);
        }

        $string = array();
        foreach ($recipients as $recipient) {
            $string[] = "\"$recipient\"";
        }

        $recipeint_users = implode(',', $string);

        $endpoint = Constants::API_URL.'direct_v2/threads/broadcast/media_share/?media_type=photo';
        $boundary = $this->uuid;
        $bodies = array(
            array(
                'type' => 'form-data',
                'name' => 'media_id',
                'data' => $media_id,
            ),
            array(
                'type' => 'form-data',
                'name' => 'recipient_users',
                'data' => "[[$recimient_users]]",
            ),
            array(
                'type' => 'form-data',
                'name' => 'client_context',
                'data' => $this->uuid,
            ),
            array(
                'type' => 'form-data',
                'name' => 'thread_ids',
                'data' => '["0"]',
            ),
            array(
                'type' => 'form-data',
                'name' => 'text',
                'data' => is_null($text) ? '' : $text,
            ),
        );

        $data = $this->buildBody($bodies, $boundary);
        $headers = array(
                'Proxy-Connection: keep-alive',
                'Connection: keep-alive',
                'Accept: */*',
                'Content-type: multipart/form-data; boundary='.$boundary,
                'Accept-Language: en-en',
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, Constants::USER_AGENT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->IGDataPath."$this->username-cookies.dat");
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->IGDataPath."$this->username-cookies.dat");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $upload = json_decode(substr($resp, $header_len), true);

        var_dump($upload);

        curl_close($ch);
    }

    protected function configure($upload_id, $photo, $caption = '')
    {
        $size = getimagesize($photo);

        $post = json_encode(array(
        'upload_id'          => $upload_id,
        'camera_model'       => 'HM1S',
        'source_type'        => 3,
        'date_time_original' => date('Y:m:d H:i:s'),
        'camera_make'        => 'XIAOMI',
        'edits'              => array(
          'crop_original_size' => array($size, $size),
          'crop_zoom'          => 1.3333334,
          'crop_center'        => array(0.0, -0.0),
        ),
        'extra' => array(
          'source_width'  => $size,
          'source_height' => $size,
        ),
        'device' => array(
          'manufacturer'    => 'Xiaomi',
          'model'           => 'HM 1SW',
          'android_version' => 18,
          'android_release' => '4.3',
        ),
        '_csrftoken'  => $this->token,
        '_uuid'       => $this->uuid,
        '_uid'        => $this->username_id,
        'caption'     => $caption,
     ));

        $post = str_replace('"crop_center":[0,0]', '"crop_center":[0.0,-0.0]', $post);

        $image = $this->request('media/configure/', $this->generateSignature($post));
    }

  /**
   * Edit media.
   *
   * @param string $mediaId
   *   Media id
   * @param string $captionText
   *   Caption text
   *
   * @return array
   *   edit media data
   */
  public function editMedia($mediaId, $captionText = '')
  {
      $data = json_encode(array(
        '_uuid'          => $this->uuid,
        '_uid'           => $this->username_id,
        '_csrftoken'     => $this->token,
        'caption_text'   => $captionText,
    ));

      return $this->request("media/$mediaId/edit_media/", $this->generateSignature($data));
  }

  /**
   * Delete photo or video.
   *
   * @param string $mediaId
   *   Media id
   *
   * @return array
   *   delete request data
   */
  public function mediaInfo($mediaId)
  {
      $data = json_encode(array(
        '_uuid'      => $this->uuid,
        '_uid'       => $this->username_id,
        '_csrftoken' => $this->token,
        'media_id'   => $mediaId,
    ));

      return $this->request("media/$mediaId/info/", $this->generateSignature($data));
  }

  /**
   * Delete photo or video.
   *
   * @param string $mediaId
   *   Media id
   *
   * @return array
   *   delete request data
   */
  public function deleteMedia($mediaId)
  {
      $data = json_encode(array(
        '_uuid'      => $this->uuid,
        '_uid'       => $this->username_id,
        '_csrftoken' => $this->token,
        'media_id'   => $mediaId,
    ));

      return $this->request("media/$mediaId/delete/", $this->generateSignature($data));
  }

  /**
   * Comment media.
   *
   * @param string $mediaId
   *   Media id
   * @param string $commentText
   *   Comment Text
   *
   * @return array
   *   comment media data
   */
  public function comment($mediaId, $commentText)
  {
      $data = json_encode(array(
        '_uuid'          => $this->uuid,
        '_uid'           => $this->username_id,
        '_csrftoken'     => $this->token,
        'comment_text'   => $commentText,
    ));

      return $this->request("media/$mediaId/comment/", $this->generateSignature($data));
  }

  /**
   * Delete Comment.
   *
   * @param string $mediaId
   *   Media ID
   * @param string $commentId
   *   Comment ID
   *
   * @return array
   *   Delete comment data
   */
  public function deleteComment($mediaId, $commentId)
  {
      $data = json_encode(array(
        '_uuid'          => $this->uuid,
        '_uid'           => $this->username_id,
        '_csrftoken'     => $this->token,
        'caption_text'   => $captionText,
    ));

      return $this->request("media/$mediaId/comment/$commentId/delete/", $this->generateSignature($data));
  }

  /**
   * Sets account to public.
   *
   * @param string $photo
   *   Path to photo
   */
  public function changeProfilePicture($photo)
  {
      if (is_null($photo)) {
          echo "Photo not valid\n\n";

          return;
      }

      $uData = json_encode(array(
      '_csrftoken' => $this->token,
      '_uuid'      => $this->uuid,
      '_uid'       => $this->username_id,
    ));

      $endpoint = Constants::API_URL.'accounts/change_profile_picture/';
      $boundary = $this->uuid;
      $bodies = array(
      array(
        'type' => 'form-data',
        'name' => 'ig_sig_key_version',
        'data' => Constants::SIG_KEY_VERSION,
      ),
      array(
        'type' => 'form-data',
        'name' => 'signed_body',
        'data' => hash_hmac('sha256', $uData, Constants::IG_SIG_KEY).$uData,
      ),
      array(
        'type'     => 'form-data',
        'name'     => 'profile_pic',
        'data'     => file_get_contents($photo),
        'filename' => 'profile_pic',
        'headers'  => array(
          'Content-type: application/octet-stream',
          'Content-Transfer-Encoding: binary',
        ),
      ),
    );

      $data = $this->buildBody($bodies, $boundary);
      $headers = array(
        'Proxy-Connection: keep-alive',
        'Connection: keep-alive',
        'Accept: */*',
        'Content-type: multipart/form-data; boundary='.$boundary,
        'Accept-Language: en-en',
        'Accept-Encoding: gzip, deflate',
    );

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $endpoint);
      curl_setopt($ch, CURLOPT_USERAGENT, Constants::USER_AGENT);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_HEADER, true);
      curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_COOKIEFILE, $this->IGDataPath."$this->username-cookies.dat");
      curl_setopt($ch, CURLOPT_COOKIEJAR, $this->IGDataPath."$this->username-cookies.dat");
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

      $resp = curl_exec($ch);
      $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
      $header = substr($resp, 0, $header_len);
      $upload = json_decode(substr($resp, $header_len), true);

      curl_close($ch);
  }

  /**
   * Remove profile picture.
   *
   * @return array
   *   status request data
   */
  public function removeProfilePicture()
  {
      $data = json_encode(array(
        '_uuid'      => $this->uuid,
        '_uid'       => $this->username_id,
        '_csrftoken' => $this->token,
    ));

      return $this->request('accounts/remove_profile_picture/', $this->generateSignature($data));
  }

  /**
   * Sets account to private.
   *
   * @return array
   *   status request data
   */
  public function setPrivateAccount()
  {
      $data = json_encode(array(
        '_uuid'      => $this->uuid,
        '_uid'       => $this->username_id,
        '_csrftoken' => $this->token,
    ));

      return $this->request('accounts/set_private/', $this->generateSignature($data));
  }

  /**
   * Sets account to public.
   *
   * @return array
   *   status request data
   */
  public function setPublicAccount()
  {
      $data = json_encode(array(
        '_uuid'      => $this->uuid,
        '_uid'       => $this->username_id,
        '_csrftoken' => $this->token,
    ));

      return $this->request('accounts/set_public/', $this->generateSignature($data));
  }

  /**
   * Get personal profile data.
   *
   * @return array
   *   profile data
   */
  public function getProfileData()
  {
      return $this->request('accounts/current_user/?edit=true', $this->generateSignature($data));
  }

  /**
   * Edit profile.
   *
   * @param string $url
   *   Url - website. "" for nothing
   * @param string $phone
   *   Phone number. "" for nothing
   * @param string $first_name
   *   Name. "" for nothing
   * @param string $email
   *   Email. Required.
   * @param int $gender
   *   Gender. male = 1 , female = 0
   *
   * @return array
   *   edit profile data
   */
  public function editProfile($url, $phone, $first_name, $biography, $email, $gender)
  {
      $data = json_encode(array(
        '_uuid'         => $this->uuid,
        '_uid'          => $this->username_id,
        '_csrftoken'    => $this->token,
        'external_url'  => $url,
        'phone_number'  => $phone,
        //'username'      => $this->username,
        'full_name'     => $first_name,
        'biography'     => $biography,
        'email'         => $email,
        'gender'        => $gender,
    ));

      return $this->request('accounts/edit_profile/', $this->generateSignature($data));
  }
  
/*
"_uuid":"6AEF916A-7F14-4F4F-B7F4-6F941FAD3F50",
"_csrftoken":"25d8e170608efc14d21ed4bbdf2b49e7",
"device_id":"6AEF916A-7F14-4F4F-B7F4-6F941FAD3F50",
"_uid":"2082772377",
"phone_number":"+79228862258"
*/
  public function sendSmsCode($phone)
  {
      $data = json_encode(array(
        '_uuid'         => $this->uuid,
        '_csrftoken'    => $this->token,
        '_uid'          => $this->username_id,
        'phone_number'  => $phone
    ));

      return $this->request('accounts/send_sms_code/', $this->generateSignature($data));
  }
  
  
/*
	"_uuid":"6AEF916A-7F14-4F4F-B7F4-6F941FAD3F50",
	"device_id":"6AEF916A-7F14-4F4F-B7F4-6F941FAD3F50",
	"_uid":"2082772377",
	"_csrftoken":"25d8e170608efc14d21ed4bbdf2b49e7",
	"phone_number":"+79228862258"
*/
  
  public function verifySmsCode($phone,$code)
  {
      $data = json_encode(array(
		'verification_code'	=> $code,
        '_uuid'         => $this->uuid,
        '_uid'          => $this->username_id,
        '_csrftoken'    => $this->token,
        'phone_number'  => $phone
    ));

      return $this->request('accounts/verify_sms_code/', $this->generateSignature($data));
  }
  
  
  
  
  
  
  
  
  

  public function editEmail($email)
  {
      $data = json_encode(array(
        '_uuid'         => $this->uuid,
        '_uid'          => $this->username_id,
        'email'         => $email,
        '_csrftoken'    => $this->token
    ));

      return $this->request('accounts/edit_email/', $this->generateSignature($data));
  }

  /**
   * Get username info.
   *
   * @param string $usernameId
   *   Username id
   *
   * @return array
   *   Username data
   */
  public function getUsernameInfo($usernameId)
  {
      return $this->request("users/$usernameId/info/");
  }

  /**
   * Get self username info.
   *
   * @return array
   *   Username data
   */
  public function getSelfUsernameInfo()
  {
      return $this->getUsernameInfo($this->username_id);
  }

  /**
   * Get recent activity.
   *
   * @return array
   *   Recent activity data
   */
  public function getRecentActivity()
  {
      $activity = $this->request('news/inbox/?');

      if ($activity['status'] != 'ok') {

          return;
      }

      return $activity;
  }

  /**
   * Get recent activity from accounts followed.
   *
   * @return array
   *   Recent activity data of follows
   */
  public function getFollowingRecentActivity()
  {
      $activity = $this->request('news/?');

      if ($activity['status'] != 'ok') {
          throw new InstagramException($activity['message']."\n");

          return;
      }

      return $activity;
  }

  /**
   * I dont know this yet.
   *
   * @return array
   *   v2 inbox data
   */
  public function getv2Inbox()
  {
      $inbox = $this->request('direct_v2/inbox/?');

      if (@$inbox['status'] != 'ok') {

          return;
      }

      return $inbox;
  }

  /**
   * Get user tags.
   *
   * @param string $usernameId
   *
   * @return array
   *   user tags data
   */
  public function getUserTags($usernameId)
  {
      $tags = $this->request("usertags/$usernameId/feed/?rank_token=$this->rank_token&ranked_content=true&");

      return $tags;
  }

  /**
   * Get self user tags.
   *
   * @return array
   *   self user tags data
   */
  public function getSelfUserTags()
  {
      return $this->getUserTags($this->username_id);
  }

  /**
   * Get tagged media.
   *
   * @param string $tag
   *
   * @return array
   */
  public function tagFeed($tag)
  {
      $userFeed = $this->request("feed/tag/$tag/?rank_token=$this->rank_token&ranked_content=true&");

      return $userFeed;
  }

  /**
   * Get media likers.
   *
   * @param string $mediaId
   *
   * @return array
   */
  public function getMediaLikers($mediaId)
  {
      $likers = $this->request("media/$mediaId/likers/?");
      if ($likers['status'] != 'ok') {
          throw new InstagramException($likers['message']."\n");

          return;
      }

      return $likers;
  }

  /**
   * Get user locations media.
   *
   * @param string $usernameId
   *   Username id
   *
   * @return array
   *   Geo Media data
   */
  public function getGeoMedia($usernameId)
  {
      $locations = $this->request("maps/user/$usernameId/");

      if ($locations['status'] != 'ok') {
          throw new InstagramException($locations['message']."\n");

          return;
      }

      return $locations;
  }

  /**
   * Get self user locations media.
   *
   * @return array
   *   Geo Media data
   */
  public function getSelfGeoMedia()
  {
      return $this->getGeoMedia($this->username_id);
  }

  /**
   * facebook user search.
   *
   * @param string $query
   *
   * @return array
   *   query data
   */
  public function fbUserSearch($query)
  {
      $query = $this->request("fbsearch/topsearch/?context=blended&query=$query&rank_token=$this->rank_token");

      if ($query['status'] != 'ok') {
          throw new InstagramException($query['message']."\n");

          return;
      }

      return $query;
  }

  /**
   * Search users.
   *
   * @param string $query
   *
   * @return array
   *   query data
   */
  public function searchUsers($query)
  {
      $query = $this->request('users/search/?ig_sig_key_version='.Constants::SIG_KEY_VERSION."&is_typeahead=true&query=$query&rank_token=$this->rank_token");

      if ($query[1]['status'] != 'ok') {
          throw new InstagramException($query['message']."\n");

          return;
      }

      return $query[1];
  }
  
  public function searchUsersId($query2)
  {
      $query = $this->request('users/search/?ig_sig_key_version='.Constants::SIG_KEY_VERSION."&npq=1&q==$query2");
	  
	for($i=0;$i<$query[1]['num_results'];$i++) {
		if($query[1]['users'][$i]['username']==$query2)  break;
	}

      return $query[1]['users'][$i]['pk'];
  }

  /**
   * Search users using addres book.
   *
   * @param array $contacts
   *
   * @return array
   *   query data
   */
  public function syncFromAdressBook($contacts)
  {
      $data = array(
        'contacts'  => json_encode($contacts, true),
      );

      return $this->request('address_book/link/?include=extra_display_name,thumbnails', $data);
  }

  /**
   * Search tags.
   *
   * @param string $query
   *
   * @return array
   *   query data
   */
  public function searchTags($query)
  {
      $query = $this->request("tags/search/?is_typeahead=true&q=$query&rank_token=$this->rank_token");

      if ($query['status'] != 'ok') {
          throw new InstagramException($query['message']."\n");

          return;
      }

      return $query;
  }

  /**
   * Get timeline data.
   *
   * @return array
   *   timeline data
   */
  public function getTimeline($next = false)
  {
	  if($next) $timeline = $this->request('feed/timeline/?rank_token='.$this->rank_token.'&ranked_content=true&max_id='.$next.'');
	  else $timeline = $this->request('feed/timeline/?rank_token=$this->rank_token&ranked_content=true');

      return $timeline[1];
  }

  /**
   * Get user feed.
   *
   * @param string $usernameId
   *    Username id
   *
   * @return array
   *   User feed data
   */
  public function getUserFeed($usernameId,$next = false)
  {
      if(strlen($next)>2) $userFeed = $this->request("feed/user/$usernameId/?max_id=".$next."&rank_token=$this->rank_token&ranked_content=true&");
	  else $userFeed = $this->request("feed/user/$usernameId/?rank_token=$this->rank_token&ranked_content=true");

      return $userFeed;
  }

  /**
  * Get hashtag feed
  *
  * @param String $hashtagString
  *    Hashtag string, not including the #
  *
  * @return array
  *   Hashtag feed data
  */
  public function getHashtagFeed($hashtagString, $maxid = null)
  {
    if (is_null($maxid)) {
      $endpoint = "feed/tag/$hashtagString/?rank_token=$this->rank_token&ranked_content=true&";
    } else {
      $endpoint = "feed/tag/$hashtagString/?max_id=" . $maxid . "&rank_token=$this->rank_token&ranked_content=true&";
    }

    $hashtagFeed = $this->request($endpoint);

    if ($hashtagFeed['status'] != 'ok')
    {
      throw new InstagramException($hashtagFeed['message'] . "\n");
      return;
    }

    return $hashtagFeed;
  }

  /**
   * Get locations
   *
   * @param String $query
   *    search query
   *
   * @return array
   *   Location location data
   */
  public function searchLocation($query)
  {
      $endpoint = "fbsearch/places/?rank_token=$this->rank_token&query=" . $query;

      $locationFeed = $this->request($endpoint);

      return $locationFeed;
  }

  /**
  * Get location feed
  *
  * @param String $locationId
  *    location id
  *
  * @return array
  *   Location feed data
  */
  public function getLocationFeed($locationId, $maxid = null)
  {
    if (is_null($maxid)) {
      $endpoint = "feed/location/$locationId/?rank_token=$this->rank_token&ranked_content=true&";
    } else {
      $endpoint = "feed/location/$locationId/?max_id=" . $maxid . "&rank_token=$this->rank_token&ranked_content=true&";
    }

    $locationFeed = $this->request($endpoint);

    return $locationFeed;
  }

  /**
   * Get self user feed.
   *
   * @return array
   *   User feed data
   */
  public function getSelfUserFeed()
  {
      return $this->getUserFeed($this->username_id);
  }

  /**
   * Get popular feed.
   *
   * @return array
   *   popular feed data
   */
  public function getPopularFeed()
  {
      $popularFeed = $this->request("feed/popular/?people_teaser_supported=1&rank_token=$this->rank_token&ranked_content=true&");

      if ($popularFeed['status'] != 'ok') {
          throw new InstagramException($popularFeed['message']."\n");

          return;
      }

      return $popularFeed;
  }

   /**
    * Get user followings.
    *
    * @param string $usernameId
    *   Username id
    *
    * @return array
    *   followers data
    */
   public function getUserFollowings($usernameId, $maxid = null)
   {
       return $this->request("friendships/$usernameId/following/?max_id=$maxid&ig_sig_key_version=".Constants::SIG_KEY_VERSION."&rank_token=$this->rank_token");
   }

  /**
   * Get user followers.
   *
   * @param string $usernameId
   *   Username id
   *
   * @return array
   *   followers data
   */
  public function getUserFollowers($usernameId, $maxid = null)
  {
	  $q = $this->request("friendships/$usernameId/followers/?max_id=$maxid&ig_sig_key_version=".Constants::SIG_KEY_VERSION."&rank_token=$this->rank_token");
      return $q[1];
  }

  /**
   * Get self user followers.
   *
   * @return array
   *   followers data
   */
  public function getSelfUserFollowers()
  {
      return $this->getUserFollowers($this->username_id);
  }

  /**
   * Get the users we are following.
   *
   * @return array
   *   users we are following data
   */
  public function getUsersFollowing()
  {
      return $this->request('friendships/following/?ig_sig_key_version='.Constants::SIG_KEY_VERSION."&rank_token=$this->rank_token");
  }

  /**
   * Like photo or video.
   *
   * @param string $mediaId
   *   Media id
   *
   * @return array
   *   status request
   */
  public function like($mediaId)
  {
      $data = json_encode(array(
        '_uuid'      => $this->uuid,
        '_uid'       => $this->username_id,
        '_csrftoken' => $this->token,
        'media_id'   => $mediaId,
    ));

      return $this->request("media/$mediaId/like/", $this->generateSignature($data));
  }

  /**
   * Unlike photo or video.
   *
   * @param string $mediaId
   *   Media id
   *
   * @return array
   *   status request
   */
  public function unlike($mediaId)
  {
      $data = json_encode(array(
        '_uuid'      => $this->uuid,
        '_uid'       => $this->username_id,
        '_csrftoken' => $this->token,
        'media_id'   => $mediaId,
    ));

      return $this->request("media/$mediaId/unlike/", $this->generateSignature($data));
  }

  /**
   * Get media comments.
   *
   * @param string $mediaId
   *   Media id
   *
   * @return array
   *   Media comments data
   */
  public function getMediaComments($mediaId)
  {
      return $this->request("media/$mediaId/comments/?");
  }

  /**
   * Set name and phone (Optional).
   *
   * @param string $name
   * @param string $phone
   *
   * @return array
   *   Set status data
   */
  public function setNameAndPhone($name = '', $phone = '')
  {
      $data = json_encode(array(
        '_uuid'         => $this->uuid,
        '_uid'          => $this->username_id,
        'first_name'    => $name,
        'phone_number'  => $phone,
        '_csrftoken'    => $this->token,
    ));

      return $this->request('accounts/set_phone_and_name/', $this->generateSignature($data));
  }

  /**
   * Get direct share.
   *
   * @return array
   *   Direct share data
   */
  public function getDirectShare()
  {
      return $this->request('direct_share/inbox/?');
  }

  /**
   * Backups all your uploaded photos :).
   */
  public function backup()
  {
      $myUploads = $this->getSelfUserFeed();
      foreach ($myUploads['items'] as $item) {
          if (!is_dir($this->IGDataPath.'backup/'."$this->username-".date('Y-m-d'))) {
              mkdir($this->IGDataPath.'backup/'."$this->username-".date('Y-m-d'));
          }
          file_put_contents($this->IGDataPath.'backup/'."$this->username-".date('Y-m-d').'/'.$item['id'].'.jpg',
      file_get_contents($item['image_versions2']['candidates'][0]['url']));
      }
  }

  /**
   * Follow.
   *
   * @param string $userId
   *
   * @return array
   *   Friendship status data
   */
  public function follow($userId)
  {
      $data = json_encode(array(
        '_uuid'      => $this->uuid,
        '_uid'       => $this->username_id,
        'user_id'    => $userId,
        '_csrftoken' => $this->token,
    ));

      return $this->request("friendships/create/$userId/", $this->generateSignature($data));
  }

  /**
   * Unfollow.
   *
   * @param string $userId
   *
   * @return array
   *   Friendship status data
   */
  public function unfollow($userId)
  {
      $data = json_encode(array(
        '_uuid'      => $this->uuid,
        '_uid'       => $this->username_id,
        'user_id'    => $userId,
        '_csrftoken' => $this->token,
    ));

      return $this->request("friendships/destroy/$userId/", $this->generateSignature($data));
  }

  /**
   * Block.
   *
   * @param string $userId
   *
   * @return array
   *   Friendship status data
   */
  public function block($userId)
  {
      $data = json_encode(array(
        '_uuid'      => $this->uuid,
        '_uid'       => $this->username_id,
        'user_id'    => $userId,
        '_csrftoken' => $this->token,
    ));

      return $this->request("friendships/block/$userId/", $this->generateSignature($data));
  }

  /**
   * Unblock.
   *
   * @param string $userId
   *
   * @return array
   *   Friendship status data
   */
  public function unblock($userId)
  {
      $data = json_encode(array(
        '_uuid'      => $this->uuid,
        '_uid'       => $this->username_id,
        'user_id'    => $userId,
        '_csrftoken' => $this->token,
    ));

      return $this->request("friendships/unblock/$userId/", $this->generateSignature($data));
  }

  /**
   * Get liked media.
   *
   * @return array
   *   Liked media data
   */
  public function getLikedMedia()
  {
      return $this->request('feed/liked/?');
  }

    public function generateSignature($data)
    {
        $hash = hash_hmac('sha256', $data, Constants::IG_SIG_KEY);

        return 'ig_sig_key_version='.Constants::SIG_KEY_VERSION.'&signed_body='.$hash.'.'.urlencode($data);
    }

    public function generateDeviceId($seed)
    {
        // Neutralize username/password -> device correlation
        $volatile_seed = filemtime(__DIR__);
        return 'android-'.substr(md5($seed.$volatile_seed), 16);
    }

    public function generateUUID($type)
    {
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0x0fff) | 0x4000,
      mt_rand(0, 0x3fff) | 0x8000,
      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );

        return $type ? $uuid : str_replace('-', '', $uuid);
    }

    protected function buildBody($bodies, $boundary)
    {
        $body = '';
        foreach ($bodies as $b) {
            $body .= '--'.$boundary."\r\n";
            $body .= 'Content-Disposition: '.$b['type'].'; name="'.$b['name'].'"';
            if (isset($b['filename'])) {
                $ext = pathinfo($b['filename'], PATHINFO_EXTENSION);
                $body .= '; filename="'.'pending_media_'.number_format(round(microtime(true) * 1000), 0, '', '').'.'.$ext.'"';
            }
            if (isset($b['headers']) && is_array($b['headers'])) {
                foreach ($b['headers'] as $header) {
                    $body .= "\r\n".$header;
                }
            }

            $body .= "\r\n\r\n".$b['data']."\r\n";
        }
        $body .= '--'.$boundary.'--';

        return $body;
    }

    protected function request($endpoint, $post = null, $login = false)
    {
        if (!$this->isLoggedIn && !$login) {
            throw new InstagramException("Not logged in\n");

            return;
        }

        $headers = array(
        'Connection: close',
        'Accept: */*',
        'Content-type: application/x-www-form-urlencoded; charset=UTF-8',
        'Cookie2: $Version=1',
        'Accept-Language: ru',
    );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, Constants::API_URL.$endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, Constants::USER_AGENT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->IGDataPath."$this->username-cookies.dat");
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->IGDataPath."$this->username-cookies.dat");

        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $body = substr($resp, $header_len);

        curl_close($ch);

        if ($this->debug) {
            echo "REQUEST: $endpoint\n";
            if (!is_null($post)) {
                if (!is_array($post)) {
                    echo 'DATA: '.urldecode($post)."\n";
                }
            }
            echo "RESPONSE: $body\n\n";
        }

        return array($header, json_decode($body, true));
    }
}
