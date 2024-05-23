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
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

// Dependency injection to enable us to Mock router
$router = $router ?? new Router('posts');

// Creates a post
$router->post('/', function (WP_REST_Request $request, WP_REST_Response $response): int|WP_Error {
    $response->set_status(201);
    $payload = $request->get_params();

    return wp_insert_post($payload, true);
})
    ->schema('Posts/CreateOrUpdate')
    ->hasCap('publish_posts');

// Fetches a single post
$router->get('(?P<ID>[\d]+)', function ($ID) {
    $post = get_post($ID);

    return $post ?: new WpError(404, 'Post not found');
})
    ->returns('Posts/Get')
    ->hasCap('read');

// Updates a post
$router->put('(?P<ID>[\d]+)', function (WP_REST_Request $request): int|WP_Error {
    $payload = $request->get_params();

    return wp_update_post($payload, true);
})
    ->schema('Posts/CreateOrUpdate')
    ->hasCap('edit_post', '{ID}');

// Deletes a post
$router->delete('(?P<ID>[\d]+)', function ($ID) {
    $post = wp_delete_post($ID);

    return $post ?: new WpError(500, 'Unable to delete post');
})
    ->returns('Posts/Get')
    ->hasCap('delete_post', '{ID}');

// IMPORTANT: If no service provider is used make sure to set a version to the $router and call
//            the following function here:
// $router->register();

// Used later on by the ApiProvider
return $router;
