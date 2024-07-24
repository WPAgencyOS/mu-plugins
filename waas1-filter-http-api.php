<?php
/**
 * @package waas1-filter-http-api
 */
/*
Plugin Name: waas1-filter-http-api.php
Plugin URI: https://waas1.com/
Description: Only allow whitelist of urls to make connections
Version: 1.0.0
Author: Erfan
Author URI: https://waas1.com/
License: GPLv2 or later
*/


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//if the call is from "wp-cli" don't run the code below
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	return;
}

//if the current page is to force check the updates don't run the code below
$currentUri = add_query_arg( NULL, NULL );
if( strpos($currentUri, '/wp-admin/update-core.php?force-check=1') !== false ){
  return;
}



add_action( 'muplugins_loaded', function(){
  add_filter( 'pre_http_request', 'july2122522_pre_http_request', 10, 3 );
});



function july2122522_pre_http_request( $preempt, $parsed_args, $url ){
  
  // Split the URL into useful parts.
  $url_data = parse_url( $url ); //will be an array
  
  if( !is_array($url_data) ){ //if url_data is not array return from here
    return $preempt;
  }
  
  if( !isset($url_data['host']) && $url_data['host'] == '' ){
    return $preempt;
  }

  $pluginName = 'waas1-filter-http-api.php';
  $timeStart = microtime(true);
  $errorLogArray = array( 'mu_plugin'=>$pluginName,'host'=>$url_data['host'], 'url_object'=>$url_data );
  
  
 
  $rulesCheckArray = [
  
    [
      'host'=>'api.wordpress.org',
      'url_path'=>[
        '/plugins/update-check/1.1/',
        '/themes/update-check/1.1/',
        '/core/version-check/1.7/',
        '/core/browse-happy/1.1/',
        '/core/serve-happy/1.0/',
        '/translations/core/1.0/',
      ]
    ],
    
    [
      'host'=>'bitbucket.org',
      'url_path'=>[
        '/learndash/learndash-add-ons/get/stable.zip',
      ]
    ],
    
    [
      'host'=>'api.github.com',
      'url_path'=>[
        '/learndash/learndash-add-ons/get/stable.zip',
      ]
    ],
    
    [
      'host'=>'my.elementor.com',
      'url_path'=>[
        '/api/v1/info/',
        '/api/v2/pro/info',
      ]
    ],
    
    [
      'host'=>'gravitywiz.com',
      'url_path'=>[
        '/gwapi/v2/',
        '/gwapi/v3/',
        '/gwapi/v4/',
        '/gwapi/v5/',
      ]
    ],
    
    [
      'host'=>'gravityapi.com',
      'url_path'=>[
        '/wp-content/plugins/gravitymanager/version.php',
        '/wp-json/gravityapi/v1/licenses/',
      ]
    ],
    
    [
      'host'=>'api.uipress.co',
      'url_path'=>[
        '/latest/',
      ]
    ],
    
    [
      'host'=>'shop.gutenberghub.com',
      'url_path'=>[
        '/wp-json/plugins/api/update',
      ]
    ],
    
    [
      'host'=>'packages.translationspress.com',
      'url_path'=>[
        '/rocketgenius/packages.json',
      ]
    ],
    
    [
      'host'=>'my.prestomade.com',
      'url_path'=>[
        '/index.php',
      ]
    ],
    
    [
      'host'=>'google-update.yithemes.com',
      'url_path'=>[
        '/plugin-xml.php',
      ]
    ],
    
    [
      'host'=>'rankmath.com',
      'url_path'=>[
        '/wp-json/rankmath/v1/updateCheck/',
        '/wp-json/rankmath/v1/versionCheck/',
      ]
    ],
    
    [
      'host'=>'plugin-updates.wp-migration.com',
      'url_path'=>[
        '/amazon-s3-extension.json',
      ]
    ],
    
    [
      'host'=>'api.appsero.com',
      'url_path'=>[
        '/update/',
      ]
    ],
    
    [
      'host'=>'api.nextendweb.com',
      'url_path'=>[
        '/v2/nextend-api/v2/product/nsl/plugin_information',
      ]
    ],
    
    [
      'host'=>'dokan.co',
      'url_path'=>[
        '/wp-json/org/promotions',
      ]
    ],
    
    [
      'host'=>'wedevs.com',
      'url_path'=>[
        '//account/tag/dokan/feed/',
      ]
    ],
    
    
    
    
    
    
    
    
    
    [
      'host'=>'update.buddyboss.com',
      'body_action'=>[
        'update_check',
        'theme_update',
      ]
    ],
    
    [
      'host'=>'support.learndash.com',
      'body_action'=>[
        'info',
      ]
    ],



    [
      'host'=>'api.wpdeveloper.com',
      'body_edd_action'=>[
        'get_version',
        'check_license',
      ]
    ],
    
    [
      'host'=>'www.uncannyowl.com',
      'body_edd_action'=>[
        'get_version',
      ]
    ],
    
    [
      'host'=>'api.wpmanageninja.com',
      'body_edd_action'=>[
        'get_version',
      ]
    ],
    
    [
      'host'=>'apiv2.wpmanageninja.com',
      'body_edd_action'=>[
        'get_version',
      ]
    ],
    
    
  ];
  
  
  
  $getBlockedHostName = july172024_checkForHttpRequests( $rulesCheckArray, $url_data, $parsed_args );
  if( isset($getBlockedHostName) && $getBlockedHostName != false ){
    return new WP_Error( 'http_request_block', 'disabled '.$getBlockedHostName.' using mu-plugin: '.$pluginName );
  }
  
  //if we are here it means request has been approved
  
  //log this approved request
  error_log( july172024_build_error($errorLogArray, $timeStart, 'approved') );
  return $preempt;
  

}//end function



function july172024_build_error( $errorLogArray, $timeStart, $action ){
  
  $errorLogArray['milliseconds'] = ( microtime(true) - $timeStart ) * 1000;
  $errorLogArray['action_taken'] = $action;
  return print_r( $errorLogArray, true );
  
}



function july172024_checkForHttpRequests( $rulesCheckArray, $url_data, $parsed_args ){
  

  foreach( $rulesCheckArray as $rulesCheck ){
  if( $url_data['host'] == $rulesCheck['host'] ){
    if( isset($rulesCheck['url_path']) && isset($url_data['path']) ){
      
      
      foreach( $rulesCheck['url_path'] as $ruleToReject ){
        if( $url_data['path'] == $ruleToReject ){
          
          return $rulesCheck['host'];
          
        }elseif( strpos($url_data['path'], $ruleToReject) !== false ){
          
          return $rulesCheck['host'];
          
        }
      }//foreach
      
      
      
    }elseif( isset($rulesCheck['body_action']) && isset($parsed_args['body']['action']) ){
      
      
      
      foreach( $rulesCheck['body_action'] as $ruleToReject ){
        if( $parsed_args['body']['action'] == $ruleToReject ){
          return $rulesCheck['host'];
        }
      }//foreach
      
      
      
    }elseif( isset($rulesCheck['body_edd_action']) && isset($parsed_args['body']['edd_action']) ){
      
      
      
      foreach( $rulesCheck['body_edd_action'] as $ruleToReject ){
        if( $parsed_args['body']['edd_action'] == $ruleToReject ){
          return $rulesCheck['host'];
        }
      }//foreach
      
      
      
    }
  }//if found the host
  }//foreach
  
  
  
  return false;
}


?>