<?php
namespace Mihdan\YandexTurboFeed;

use WP_REST_Server;
use WP_REST_Request;
use WP_Comment;
use WP_REST_Response;

class Comments {
	const NAME_SPACE = 'turbo/v1';
	public function setup_hooks() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}
	public function register_routes() {
		register_rest_route(
			self::NAME_SPACE, '/comments',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_comments' ],
				'permission_callback' => '__return_true',
				'args' => array(
					'limit' => array(
						'default'           => 10,
						'required'          => true,
						'type' => 'integer',
						'validate_callback' => function( $param, $request, $key ) {
							return is_numeric( $param );
						},
					),
					'offset' => array(
						'default'           => 0,
						'required'          => true,
						'type' => 'integer',
						'validate_callback' => function( $param, $request, $key ) {
							return is_numeric( $param );
						},
					),
					'ORIGINAL_URL' => array(
						'required'          => true,
						'format' => 'uri',
						'validate_callback' => function( $param, $request, $key ) {
							return filter_var( $param, FILTER_VALIDATE_URL );
						},
					),

				),
			)
		);

		register_rest_route(
			self::NAME_SPACE, '/comments',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'add_comment' ],
				'permission_callback' => '__return_true',
				'args' => array(
					'limit' => array(
						'default'           => 10,
						'required'          => true,
						'type' => 'integer',
						'validate_callback' => function( $param, $request, $key ) {
							return is_numeric( $param );
						},
					),
					'offset' => array(
						'default'           => 0,
						'required'          => true,
						'type' => 'integer',
						'validate_callback' => function( $param, $request, $key ) {
							return is_numeric( $param );
						},
					),
					'ORIGINAL_URL' => array(
						'required'          => true,
						'format' => 'uri',
						'validate_callback' => function( $param, $request, $key ) {
							return filter_var( $param, FILTER_VALIDATE_URL );
						},
					),

				),
			)
		);
	}

	public function prepare_comment( WP_Comment $comment, $options = array() ) {
		$prepared_comment = array(
			'id' => $comment->comment_ID,
			'name' => $comment->comment_author,
			'content' => sanitize_text_field( $comment->comment_content ),
			'date' => strtotime( $comment->comment_date ),
			'answer_to' => $comment->comment_parent ? $comment->comment_parent : null
		);

		if ( isset( $options['replies'] ) ) {
			$prepared_comment['replies'] = array_map(
				[ $this, 'prepare_comment' ],
				$comment->get_children(
					[
						'format' => 'flat',
						'hierarchical' => 'flat'
					]
				)
			);
		}

		return $prepared_comment;
	}

	public function get_comments( WP_REST_Request $request) {

		$post_id = url_to_postid( $request->get_param( 'ORIGINAL_URL' ) );
		$offset = $request->get_param( 'offset' );
		$limit = $request->get_param( 'limit' );

		if ( ! $post_id ) {
			return [
				'offset'   => $offset,
				'limit'    => $limit,
				'total'    => 0,
				'comments' => [],
			];
		}

		$comments = get_comments(
			[
				'post_id'      => $post_id,
				'hierarchical' => 'threaded',
				'order'        => 'ASC'
			]
		);

		return array(
			'offset' => $offset,
			'limit' => $limit,
			'total' => count( $comments ),
			'comments' => array_values(
				array_map(
					function( $comment ) {
						return $this->prepare_comment( $comment, [ 'replies' => true ] );
					},
					array_slice( $comments, $offset, $limit )
				)
			)
		);
	}

	public function add_comment( WP_REST_Request $request ) {
		$post_id = url_to_postid( $request->get_param( 'ORIGINAL_URL' ) );
		$body    = $request->get_body();

		if ( ! $post_id || ! $body ) {
			return new WP_REST_Response( [ 'response' => 'Не найдена оригинальная запись' ], 406 );
		}

		$data = json_decode( $body, true );

		$raw_comment = [];
		$raw_comment['comment_post_ID'] = $post_id;
		$raw_comment['comment_parent']  = isset( $data['answer_to'] ) ? $data['answer_to'] : 0;
		$raw_comment['comment']         = $data['text'];
		$raw_comment['author']          = 'Без имени';
		$raw_comment['email']           = time() . '@turbo.feed';

		$comment = wp_handle_comment_submission( $raw_comment );

		if ( is_wp_error( $comment ) ) {
			return new WP_REST_Response( [ 'response' => $comment->get_error_message() ], 406 );
		}

		return [
			'id'   => $comment->comment_ID,
			'date' => strtotime( $comment->comment_date )
		];
	}
}