<?php
namespace Derweili\WP_Recognition;

use Aws\Translate\TranslateClient;
use Aws\Exception\AwsException;

/**
 *
 */
class Translator
{

  function __construct()
  {
    $this->init_client();
  }

  function init_client(){

    $this->client = new TranslateClient([
      'region' => 'us-west-2',
      'version' => 'latest',
      'credentials' => array(
        'key' => WP_RECOGNITION_KEY,
        'secret'  => WP_RECOGNITION_SECRET,
      )
    ]);

  }

  function translate_text( $text ){

    try {
      $result = $this->client->translateText([
          'SourceLanguageCode' => 'en',
          'TargetLanguageCode' => WP_RECOGNITION_TARGET_LANGUAGE,
          'Text' => $text,
      ]);

      return $result->get('TranslatedText');

    }catch (AwsException $e) {
        // output error message if fails
        echo $e->getMessage();
        echo "\n";
    }

    return $text;

  }

}
