<?php


namespace Inc\Base;

use Inc\Base\BaseController;
use Inc\Api\External\UntappdApi;
use Inc\Api\CreatePostApi;

class AjaxController extends BaseController
{
  private $api;
  private $createPost;

  public function register()
  {
    $this->api = new UntappdApi();
    $this->createPost = new CreatePostApi();

    add_action('wp_ajax_test_api', array($this, 'testAPI'));
    add_action('wp_ajax_get_menus', array($this, 'getMenus'));
    add_action('wp_ajax_sync_menu', array($this, 'syncMenu'));
  }

  public function testAPI()
  {
    $api_endpoint = $this->api->api_base_url . 'current_user';
    $api_response = $this->api->getRequest($api_endpoint);

    if (isset($api_response['error'])) {
      return wp_send_json_error(['message'=> $api_response['error']]);
    }

    update_option('nrd_untappd_importer_api_creds', 1);
    return wp_send_json_success(['message'=>'Successfully Connected']);
  }

  public function getMenus() 
  {
    $api_response = $this->api->getMenus();
    
    if (isset($api_response['error'])) {
      return wp_send_json_error(['message'=>$api_response['error']]);
    }

    update_option('nrd_untappd_importer_untappd_menu', $api_response);

    return wp_send_json_success(['message'=>'Menus Loaded', 'data' => $api_response]);
  }

  public function syncMenu()
  {
    if (!isset($_POST['item_id'])) {
      return wp_send_json_error(['message' => 'No menu id provided']);
    }

    $menu_id = sanitize_text_field($_POST['item_id']);
    $api_response = $this->api->getMenuItems($menu_id);

    if (isset($api_response['error'])) {
      return wp_send_json_error(['message' =>$api_response['error']]);
    }

    $this->createPost->syncPosts($api_response, $menu_id);

    return wp_send_json_success(['message'=>'Menu synced', 'data'=>$api_response]);
  }
}