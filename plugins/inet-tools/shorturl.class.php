<?php

// shorturl.class.php

// *************************************************************************
// *                                                                       *
// * (c) 2008-2011 Wolf Software Limited <info@wolf-software.com>          *
// * All Rights Reserved.                                                  *
// *                                                                       *
// * This program is free software: you can redistribute it and/or modify  *
// * it under the terms of the GNU General Public License as published by  *
// * the Free Software Foundation, either version 3 of the License, or     *
// * (at your option) any later version.                                   *
// *                                                                       *
// * This program is distributed in the hope that it will be useful,       *
// * but WITHOUT ANY WARRANTY; without even the implied warranty of        *
// * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
// * GNU General Public License for more details.                          *
// *                                                                       *
// * You should have received a copy of the GNU General Public License     *
// * along with this program.  If not, see <http://www.gnu.org/licenses/>. *
// *                                                                       *
// *************************************************************************

class shorturl
{
  private $class_name    = "Short URL";
  private $class_version = "1.0.0";
  private $class_author  = "Wolf Software";
  private $class_source  = "http://www.wolf-software.com/Downloads/shorturl_class";

  private $error;
  private $keyWarning    = false;
  private $apikey        = NULL;

  public function class_name()
    {
      return $this->class_name;
    }

  public function class_version()
    {
      return $this->class_version;
    }

  public function class_author()
    {
      return $this->class_author;
    }

  public function class_source()
    {
      return $this->class_source;
    }

  public function __construct()
    {
    }

  public function set_apikey($key)
    {
      $this->apikey = $key;
    }

  private function url_exists($url)
    {
      if (($url == '127.0.0.1') || ($url == 'localhost'))
        {
          return false;
        }
      $c = curl_init();
      curl_setopt($c, CURLOPT_URL, $url);
      curl_setopt($c, CURLOPT_HEADER, 1);
      curl_setopt($c, CURLOPT_NOBODY, 1);
      curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($c, CURLOPT_FRESH_CONNECT, 1);
      if (!curl_exec($c))
        {
          return false;
        }
      else
        {
          $info = curl_getinfo($c);

          if ($info['http_code'] >= 400)
            {
              return false;
            }
          return true;
        }
    }

  public function shorten($url, $service = 'goo.gl')
    {
      if ($this->url_exists($url) == false)
        {
          return "Invalid Url ($url)";
        }
      if ((substr($url, 0, 7) != 'http://') && (substr($url, 0, 8) != 'https://'))
        {
          $url = 'http://' . $url;
        }
      switch ($service)
        {
          case 'goo.gl':
              $postData = array('longUrl' => $url);

              if (!is_null($this->apikey))
                {
                  $postData['key'] = $this->apikey;
                }
              $jsonData = json_encode($postData);
              $curlObj = curl_init();
              curl_setopt($curlObj, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url');
              curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
              curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
              curl_setopt($curlObj, CURLOPT_HEADER, 0);
              curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
              curl_setopt($curlObj, CURLOPT_POST, 1);
              curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);
              $response = curl_exec($curlObj);
              curl_close($curlObj);
              $json = json_decode($response);
              if ($this->has_errors($json))
                {
                  return $this->error;
                }
              else
                {
                  return $json->id;
                }
              break;
          case 'tinyurl':
              $url = urlencode($url);
              $short_url = file_get_contents("http://tinyurl.com/api-create.php?url=" . $url);
              return $short_url;
              break;
          case 'is.gd':
              $url = urlencode($url);
              $short_url = file_get_contents("http://is.gd/api.php?longurl=" . $url);
              return $short_url;
              break;
          default:
              return "Unimplemented Method ($service)";
              break;
        }
    }

  private function has_errors($json)
    {
      if ($this->keyWarning)
        {
          if (is_null($this->apikey))
            {
              echo '<p>Currently you are not using an API key. It is recommended that you use one. <a href="http://code.google.com/apis/urlshortener/v1/authentication.html#key">Click here to learn more about the API key</a></p>';
            }
        }
      if (is_object($json))
        {
          if (isset($json->error))
            {
              foreach ($json->error->errors as $error)
                {
                  $this->error.= $error->message.':'.$error->location.'; ';
                }
              return true;
            }
        }
      else
        {
          $this->error = 'Malformed JSON response';
          return true;
        }
    }
}

?>
