<?php
/**
 * Holds REST endpoints to manipulate posts
 *
 * @since 1.0.0
 *
 * @license MIT
 */

declare(strict_types=1);

namespace MyPlugin\Api\Routers;

use Wp\FastEndpoints\Helpers\WpError;
use Wp\FastEndpoints\Router;

// Dependency injection to enable us to Mock router
$router = $router ?? new Router('posts');

// Creates a post
$router->post('/', function (\WP_REST_Request $request): int|\WP_Error {
    $payload = $request->get_params();

    return wp_insert_post($payload);
})
    ->schema('Posts/CreateOrUpdate')
    ->hasCap('publish_posts');

// Fetches a single post
$router->get('(?P<post_id>[\d]+)', function (\WP_REST_Request $request) {
    $postId = $request->get_param('post_id');
    $post = get_post($postId);

    return $post ?: new WpError(404, 'Post not found');
})
    ->returns('Posts/Get')
    ->hasCap('read');

// Updates a post
$router->put('(?P<post_id>[\d]+)', function (\WP_REST_Request $request): int|\WP_Error {
    $payload = $request->get_params();
    $payload['ID'] = $request->get_param('post_id');

    return wp_update_post($payload);
})
    ->schema('Posts/CreateOrUpdate')
    ->hasCap('edit_post', '{post_id}');

// Deletes a post
$router->delete('(?P<post_id>[\d]+)', function (\WP_REST_Request $request) {
    $postId = $request->get_param('post_id');
    $post = wp_delete_post($postId);

    return $post ?: new WpError(500, 'Unable to delete post');
})
    ->returns('Posts/Get')
    ->hasCap('delete_post', '{post_id}');

// IMPORTANT: If no service provider is used make sure to set a version to the $router and call
//            the following function here:
// $router->register();

// Used later on by the ApiProvider
return $router;
