<?php

/**
 * Holds integration tests for the Posts Router
 *
 * @since 1.0.0
 *
 * @license MIT
 */

declare(strict_types=1);

namespace MyPlugin\Tests\Integration;

use MyPlugin\Tests\Helpers;
use Yoast\WPTestUtils\WPIntegration\TestCase;

if (! Helpers::isIntegrationTest()) {
    return;
}

/*
 * We need to provide the base test class to every integration test.
 * This will enable us to use all the WordPress test goodies, such as
 * factories and proper test cleanup.
 */
uses(TestCase::class);

beforeEach(function () {
    parent::setUp();

    // Set up a REST server instance.
    global $wp_rest_server;

    $this->server = $wp_rest_server = new \WP_REST_Server();
    do_action('rest_api_init', $this->server);
});

afterEach(function () {
    global $wp_rest_server;
    $wp_rest_server = null;

    parent::tearDown();
});

test('REST API endpoints registered', function () {
    $routes = $this->server->get_routes();

    expect($routes)
        ->toBeArray()
        ->toHaveKeys([
            '/my-plugin/v1',
            '/my-plugin/v1/posts',
            '/my-plugin/v1/posts/(?P<ID>[\\d]+)',
        ])
        ->and($routes['/my-plugin/v1/posts'])
        ->toHaveCount(1)
        ->and($routes['/my-plugin/v1/posts/(?P<ID>[\\d]+)'])
        ->toHaveCount(3);
})->group('api', 'posts');

test('Create a new post', function () {
    // Create user with correct permissions
    $userId = $this::factory()->user->create();
    $user = get_user_by('id', $userId);
    $user->add_cap('publish_posts');
    // Make request as that user
    wp_set_current_user($userId);
    $request = new \WP_REST_Request('POST', '/my-plugin/v1/posts');
    $request->set_body_params([
        'post_title' => 'My testing message',
        'post_status' => 'publish',
        'post_type' => 'post',
        'post_content' => '<p>Message body</p>',
    ]);
    $response = $this->server->dispatch($request);
    expect($response->get_status())->toBe(201);
    $postId = $response->get_data();
    // Check that the post details are correct
    expect(get_post($postId))
        ->toBeInstanceOf(\WP_Post::class)
        ->toHaveProperty('post_title', 'My testing message')
        ->toHaveProperty('post_status', 'publish')
        ->toHaveProperty('post_type', 'post')
        ->toHaveProperty('post_content', '<p>Message body</p>');
})->group('api', 'posts');

test('Update a post', function () {
    // Create user with correct permissions
    $userId = $this::factory()->user->create();
    $user = get_user_by('id', $userId);
    $user->add_cap('edit_published_posts');
    // Create post
    $postId = $this::factory()->post->create(['post_author' => $userId]);
    // Make request as that user
    wp_set_current_user($userId);
    $request = new \WP_REST_Request('PUT', "/my-plugin/v1/posts/{$postId}");
    $request->set_body_params([
        'post_title' => 'Updated post title',
        'post_status' => 'draft',
        'post_type' => 'post',
        'post_content' => '<p>New message body</p>',
    ]);
    $response = $this->server->dispatch($request);
    // Check that the post details were updated
    expect($response->get_status())->toBe(200)
        ->and(get_post($postId))
        ->toHaveProperty('post_title', 'Updated post title')
        ->toHaveProperty('post_status', 'draft')
        ->toHaveProperty('post_type', 'post')
        ->toHaveProperty('post_content', '<p>New message body</p>');
})->group('api', 'posts');

test('Delete a post', function () {
    // Create user with correct permissions
    $userId = $this::factory()->user->create();
    $user = get_user_by('id', $userId);
    $user->add_cap('delete_published_posts');
    // Create post
    $postId = $this::factory()->post->create(['post_author' => $userId]);
    // Make request as that user
    wp_set_current_user($userId);
    $request = new \WP_REST_Request('DELETE', "/my-plugin/v1/posts/{$postId}");
    $response = $this->server->dispatch($request);
    // Check that the post has been deleted
    expect($response->get_status())->toBe(200)
        ->and(get_post($postId))
        ->toHaveProperty('post_status', 'trash');
})->group('api', 'posts');

test('Trying to manipulate a post without enough permissions', function (string $method, string $route) {
    $userId = $this::factory()->user->create();
    wp_set_current_user($userId);
    $request = new \WP_REST_Request($method, $route);
    $response = $this->server->dispatch($request);
    expect($response->get_status())->toBe(403);
})->with([
    ['POST', '/my-plugin/v1/posts'],
    ['PUT', '/my-plugin/v1/posts/1'],
    ['DELETE', '/my-plugin/v1/posts/1'],
])->group('api', 'posts');
