<?php
/**
 * Query API: WP_Query class
 *
 * @package WordPress
 * @subpackage Query
 * @since 4.7.0
 */

/**
 * The WordPress Query class.
 *
 * @link https://codex.wordpress.org/Function_Reference/WP_Query Codex page.
 *
 * @since 1.5.0
 * @since 4.5.0 Removed the `$comments_popup` property.
 */
class WP_Query {

	/**
	 * Query vars set by the user
	 *
	 * @since 1.5.0
	 * @access public
	 * @var array
	 */
	public $query;

	/**
	 * Query vars, after parsing
	 *
	 * @since 1.5.0
	 * @access public
	 * @var array
	 */
	public $query_vars = array();

	/**
	 * Taxonomy query, as passed to get_tax_sql()
	 *
	 * @since 3.1.0
	 * @access public
	 * @var object WP_Tax_Query
	 */
	public $tax_query;

	/**
	 * Metadata query container
	 *
	 * @since 3.2.0
	 * @access public
	 * @var object WP_Meta_Query
	 */
	public $meta_query = false;

	/**
	 * Date query container
	 *
	 * @since 3.7.0
	 * @access public
	 * @var object WP_Date_Query
	 */
	public $date_query = false;

	/**
	 * Holds the data for a single object that is queried.
	 *
	 * Holds the contents of a post, page, category, attachment.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var object|array
	 */
	public $queried_object;

	/**
	 * The ID of the queried object.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var int
	 */
	public $queried_object_id;

	/**
	 * Get post database query.
	 *
	 * @since 2.0.1
	 * @access public
	 * @var string
	 */
	public $request;

	/**
	 * List of posts.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var array
	 */
	public $posts;

	/**
	 * The amount of posts for the current query.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var int
	 */
	public $post_count = 0;

	/**
	 * Index of the current item in the loop.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var int
	 */
	public $current_post = -1;

	/**
	 * Whether the loop has started and the caller is in the loop.
	 *
	 * @since 2.0.0
	 * @access public
	 * @var bool
	 */
	public $in_the_loop = false;

	/**
	 * The current post.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var WP_Post
	 */
	public $post;

	/**
	 * The list of comments for current post.
	 *
	 * @since 2.2.0
	 * @access public
	 * @var array
	 */
	public $comments;

	/**
	 * The amount of comments for the posts.
	 *
	 * @since 2.2.0
	 * @access public
	 * @var int
	 */
	public $comment_count = 0;

	/**
	 * The index of the comment in the comment loop.
	 *
	 * @since 2.2.0
	 * @access public
	 * @var int
	 */
	public $current_comment = -1;

	/**
	 * Current comment ID.
	 *
	 * @since 2.2.0
	 * @access public
	 * @var int
	 */
	public $comment;

	/**
	 * The amount of found posts for the current query.
	 *
	 * If limit clause was not used, equals $post_count.
	 *
	 * @since 2.1.0
	 * @access public
	 * @var int
	 */
	public $found_posts = 0;

	/**
	 * The amount of pages.
	 *
	 * @since 2.1.0
	 * @access public
	 * @var int
	 */
	public $max_num_pages = 0;

	/**
	 * The amount of comment pages.
	 *
	 * @since 2.7.0
	 * @access public
	 * @var int
	 */
	public $max_num_comment_pages = 0;

	/**
	 * Set if query is single post.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var bool
	 */
	public $is_single = false;

	/**
	 * Set if query is preview of blog.
	 *
	 * @since 2.0.0
	 * @access public
	 * @var bool
	 */
	public $is_preview = false;

	/**
	 * Set if query returns a page.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var bool
	 */
	public $is_page = false;

	/**
	 * Set if query is an archive list.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var bool
	 */
	public $is_archive = false;

	/**
	 * Set if query is part of a date.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var bool
	 */
	public $is_date = false;

	/**
	 * Set if query contains a year.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var bool
	 */
	public $is_year = false;

	/**
	 * Set if query contains a month.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var bool
	 */
	public $is_month = false;

	/**
	 * Set if query contains a day.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var bool
	 */
	public $is_day = false;

	/**
	 * Set if query contains time.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var bool
	 */
	public $is_time = false;

	/**
	 * Set if query contains an author.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var bool
	 */
	public $is_author = false;

	/**
	 * Set if query contains category.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var bool
	 */
	public $is_category = false;

	/**
	 * Set if query contains tag.
	 *
	 * @since 2.3.0
	 * @access public
	 * @var bool
	 */
	public $is_tag = false;

	/**
	 * Set if query contains taxonomy.
	 *
	 * @since 2.5.0
	 * @access public
	 * @var bool
	 */
	public $is_tax = false;

	/**
	 * Set if query was part of a search result.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var bool
	 */
	public $is_search = false;

	/**
	 * Set if query is feed display.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var bool
	 */
	public $is_feed = false;

	/**
	 * Set if query is comment feed display.
	 *
	 * @since 2.2.0
	 * @access public
	 * @var bool
	 */
	public $is_comment_feed = false;

	/**
	 * Set if query is trackback.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var bool
	 */
	public $is_trackback = false;

	/**
	 * Set if query is blog homepage.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var bool
	 */
	public $is_home = false;

	/**
	 * Set if query couldn't found anything.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var bool
	 */
	public $is_404 = false;

	/**
	 * Set if query is embed.
	 *
	 * @since 4.4.0
	 * @access public
	 * @var bool
	 */
	public $is_embed = false;

	/**
	 * Set if query is paged
	 *
	 * @since 1.5.0
	 * @access public
	 * @var bool
	 */
	public $is_paged = false;

	/**
	 * Set if query is part of administration page.
	 *
	 * @since 1.5.0
	 * @access public
	 * @var bool
	 */
	public $is_admin = false;

	/**
	 * Set if query is an attachment.
	 *
	 * @since 2.0.0
	 * @access public
	 * @var bool
	 */
	public $is_attachment = false;

	/**
	 * Set if is single, is a page, or is an attachment.
	 *
	 * @since 2.1.0
	 * @access public
	 * @var bool
	 */
	public $is_singular = false;

	/**
	 * Set if query is for robots.
	 *
	 * @since 2.1.0
	 * @access public
	 * @var bool
	 */
	public $is_robots = false;

	/**
	 * Set if query contains posts.
	 *
	 * Basically, the homepage if the option isn't set for the static homepage.
	 *
	 * @since 2.1.0
	 * @access public
	 * @var bool
	 */
	public $is_posts_page = false;

	/**
	 * Set if query is for a post type archive.
	 *
	 * @since 3.1.0
	 * @access public
	 * @var bool
	 */
	public $is_post_type_archive = false;

	/**
	 * Stores the ->query_vars state like md5(serialize( $this->query_vars ) ) so we know
	 * whether we have to re-parse because something has changed
	 *
	 * @since 3.1.0
	 * @access private
	 * @var bool|string
	 */
	private $query_vars_hash = false;

	/**
	 * Whether query vars have changed since the initial parse_query() call. Used to catch modifications to query vars made
	 * via pre_get_posts hooks.
	 *
	 * @since 3.1.1
	 * @access private
	 */
	private $query_vars_changed = true;

	/**
	 * Set if post thumbnails are cached
	 *
	 * @since 3.2.0
	 * @access public
	 * @var bool
	 */
	 public $thumbnails_cached = false;

	/**
	 * Cached list of search stopwords.
	 *
	 * @since 3.7.0
	 * @var array
	 */
	private $stopwords;

	private $compat_fields = array( 'query_vars_hash', 'query_vars_changed' );

	private $compat_methods = array( 'init_query_flags', 'parse_tax_query' );

	/**
	 * Magic3追加
	 */
	public $headTitle;			// HTMLヘッダのタイトル作成用
	public $lastPostFound;		// 一覧の最後の項目を検出したかどうか
	
	/**
	 * Resets query flags to false.
	 *
	 * The query flags are what page info WordPress was able to figure out.
	 *
	 * @since 2.0.0
	 * @access private
	 */
/*	private function init_query_flags() {
		$this->is_single = false;
		$this->is_preview = false;
		$this->is_page = false;
		$this->is_archive = false;
		$this->is_date = false;
		$this->is_year = false;
		$this->is_month = false;
		$this->is_day = false;
		$this->is_time = false;
		$this->is_author = false;
		$this->is_category = false;
		$this->is_tag = false;
		$this->is_tax = false;
		$this->is_search = false;
		$this->is_feed = false;
		$this->is_comment_feed = false;
		$this->is_trackback = false;
		$this->is_home = false;
		$this->is_404 = false;
		$this->is_paged = false;
		$this->is_admin = false;
		$this->is_attachment = false;
		$this->is_singular = false;
		$this->is_robots = false;
		$this->is_posts_page = false;
		$this->is_post_type_archive = false;
	}*/

	/**
	 * Initiates object properties and sets default values.
	 *
	 * @since 1.5.0
	 * @access public
	 */
/*	public function init() {
		unset($this->posts);
		unset($this->query);
		$this->query_vars = array();
		unset($this->queried_object);
		unset($this->queried_object_id);
		$this->post_count = 0;
		$this->current_post = -1;
		$this->in_the_loop = false;
		unset( $this->request );
		unset( $this->post );
		unset( $this->comments );
		unset( $this->comment );
		$this->comment_count = 0;
		$this->current_comment = -1;
		$this->found_posts = 0;
		$this->max_num_pages = 0;
		$this->max_num_comment_pages = 0;

		$this->init_query_flags();
	}*/

	/**
	 * Reparse the query vars.
	 *
	 * @since 1.5.0
	 * @access public
	 */
//	public function parse_query_vars() {
//		$this->parse_query();
//	}

	/**
	 * Fills in the query variables, which do not exist within the parameter.
	 *
	 * @since 2.1.0
	 * @since 4.4.0 Removed the `comments_popup` public query variable.
	 * @access public
	 *
	 * @param array $array Defined query variables.
	 * @return array Complete query variables with undefined ones filled in empty.
	 */
/*	public function fill_query_vars($array) {
		$keys = array(
			'error'
			, 'm'
			, 'p'
			, 'post_parent'
			, 'subpost'
			, 'subpost_id'
			, 'attachment'
			, 'attachment_id'
			, 'name'
			, 'static'
			, 'pagename'
			, 'page_id'
			, 'second'
			, 'minute'
			, 'hour'
			, 'day'
			, 'monthnum'
			, 'year'
			, 'w'
			, 'category_name'
			, 'tag'
			, 'cat'
			, 'tag_id'
			, 'author'
			, 'author_name'
			, 'feed'
			, 'tb'
			, 'paged'
			, 'meta_key'
			, 'meta_value'
			, 'preview'
			, 's'
			, 'sentence'
			, 'title'
			, 'fields'
			, 'menu_order'
			, 'embed'
		);

		foreach ( $keys as $key ) {
			if ( !isset($array[$key]) )
				$array[$key] = '';
		}

		$array_keys = array( 'category__in', 'category__not_in', 'category__and', 'post__in', 'post__not_in', 'post_name__in',
			'tag__in', 'tag__not_in', 'tag__and', 'tag_slug__in', 'tag_slug__and', 'post_parent__in', 'post_parent__not_in',
			'author__in', 'author__not_in' );

		foreach ( $array_keys as $key ) {
			if ( !isset($array[$key]) )
				$array[$key] = array();
		}
		return $array;
	}
*/
	/**
	 * Parse a query string and set query type booleans.
	 *
	 * @since 1.5.0
	 * @since 4.2.0 Introduced the ability to order by specific clauses of a `$meta_query`, by passing the clause's
	 *              array key to `$orderby`.
	 * @since 4.4.0 Introduced `$post_name__in` and `$title` parameters. `$s` was updated to support excluded
	 *              search terms, by prepending a hyphen.
	 * @since 4.5.0 Removed the `$comments_popup` parameter.
	 *              Introduced the `$comment_status` and `$ping_status` parameters.
	 *              Introduced `RAND(x)` syntax for `$orderby`, which allows an integer seed value to random sorts.
	 * @since 4.6.0 Added 'post_name__in' support for `$orderby`. Introduced the `$lazy_load_term_meta` argument.
	 * @access public
	 *
	 * @param string|array $query {
	 *     Optional. Array or string of Query parameters.
	 *
	 *     @type int          $attachment_id           Attachment post ID. Used for 'attachment' post_type.
	 *     @type int|string   $author                  Author ID, or comma-separated list of IDs.
	 *     @type string       $author_name             User 'user_nicename'.
	 *     @type array        $author__in              An array of author IDs to query from.
	 *     @type array        $author__not_in          An array of author IDs not to query from.
	 *     @type bool         $cache_results           Whether to cache post information. Default true.
	 *     @type int|string   $cat                     Category ID or comma-separated list of IDs (this or any children).
	 *     @type array        $category__and           An array of category IDs (AND in).
	 *     @type array        $category__in            An array of category IDs (OR in, no children).
	 *     @type array        $category__not_in        An array of category IDs (NOT in).
	 *     @type string       $category_name           Use category slug (not name, this or any children).
	 *     @type string       $comment_status          Comment status.
	 *     @type int          $comments_per_page       The number of comments to return per page.
	 *                                                 Default 'comments_per_page' option.
	 *     @type array        $date_query              An associative array of WP_Date_Query arguments.
	 *                                                 See WP_Date_Query::__construct().
	 *     @type int          $day                     Day of the month. Default empty. Accepts numbers 1-31.
	 *     @type bool         $exact                   Whether to search by exact keyword. Default false.
	 *     @type string|array $fields                  Which fields to return. Single field or all fields (string),
	 *                                                 or array of fields. 'id=>parent' uses 'id' and 'post_parent'.
	 *                                                 Default all fields. Accepts 'ids', 'id=>parent'.
	 *     @type int          $hour                    Hour of the day. Default empty. Accepts numbers 0-23.
	 *     @type int|bool     $ignore_sticky_posts     Whether to ignore sticky posts or not. Setting this to false
	 *                                                 excludes stickies from 'post__in'. Accepts 1|true, 0|false.
	 *                                                 Default 0|false.
	 *     @type int          $m                       Combination YearMonth. Accepts any four-digit year and month
	 *                                                 numbers 1-12. Default empty.
	 *     @type string       $meta_compare            Comparison operator to test the 'meta_value'.
	 *     @type string       $meta_key                Custom field key.
	 *     @type array        $meta_query              An associative array of WP_Meta_Query arguments. See WP_Meta_Query.
	 *     @type string       $meta_value              Custom field value.
	 *     @type int          $meta_value_num          Custom field value number.
	 *     @type int          $menu_order              The menu order of the posts.
	 *     @type int          $monthnum                The two-digit month. Default empty. Accepts numbers 1-12.
	 *     @type string       $name                    Post slug.
	 *     @type bool         $nopaging                Show all posts (true) or paginate (false). Default false.
	 *     @type bool         $no_found_rows           Whether to skip counting the total rows found. Enabling can improve
	 *                                                 performance. Default false.
	 *     @type int          $offset                  The number of posts to offset before retrieval.
	 *     @type string       $order                   Designates ascending or descending order of posts. Default 'DESC'.
	 *                                                 Accepts 'ASC', 'DESC'.
	 *     @type string|array $orderby                 Sort retrieved posts by parameter. One or more options may be
	 *                                                 passed. To use 'meta_value', or 'meta_value_num',
	 *                                                 'meta_key=keyname' must be also be defined. To sort by a
	 *                                                 specific `$meta_query` clause, use that clause's array key.
	 *                                                 Default 'date'. Accepts 'none', 'name', 'author', 'date',
	 *                                                 'title', 'modified', 'menu_order', 'parent', 'ID', 'rand',
	 *                                                 'RAND(x)' (where 'x' is an integer seed value),
	 *                                                 'comment_count', 'meta_value', 'meta_value_num', 'post__in',
	 *                                                 'post_name__in', 'post_parent__in', and the array keys
	 *                                                 of `$meta_query`.
	 *     @type int          $p                       Post ID.
	 *     @type int          $page                    Show the number of posts that would show up on page X of a
	 *                                                 static front page.
	 *     @type int          $paged                   The number of the current page.
	 *     @type int          $page_id                 Page ID.
	 *     @type string       $pagename                Page slug.
	 *     @type string       $perm                    Show posts if user has the appropriate capability.
	 *     @type string       $ping_status             Ping status.
	 *     @type array        $post__in                An array of post IDs to retrieve, sticky posts will be included
	 *     @type string       $post_mime_type          The mime type of the post. Used for 'attachment' post_type.
	 *     @type array        $post__not_in            An array of post IDs not to retrieve. Note: a string of comma-
	 *                                                 separated IDs will NOT work.
	 *     @type int          $post_parent             Page ID to retrieve child pages for. Use 0 to only retrieve
	 *                                                 top-level pages.
	 *     @type array        $post_parent__in         An array containing parent page IDs to query child pages from.
	 *     @type array        $post_parent__not_in     An array containing parent page IDs not to query child pages from.
	 *     @type string|array $post_type               A post type slug (string) or array of post type slugs.
	 *                                                 Default 'any' if using 'tax_query'.
	 *     @type string|array $post_status             A post status (string) or array of post statuses.
	 *     @type int          $posts_per_page          The number of posts to query for. Use -1 to request all posts.
	 *     @type int          $posts_per_archive_page  The number of posts to query for by archive page. Overrides
	 *                                                 'posts_per_page' when is_archive(), or is_search() are true.
	 *     @type array        $post_name__in           An array of post slugs that results must match.
	 *     @type string       $s                       Search keyword(s). Prepending a term with a hyphen will
	 *                                                 exclude posts matching that term. Eg, 'pillow -sofa' will
	 *                                                 return posts containing 'pillow' but not 'sofa'. The
	 *                                                 character used for exclusion can be modified using the
	 *                                                 the 'wp_query_search_exclusion_prefix' filter.
	 *     @type int          $second                  Second of the minute. Default empty. Accepts numbers 0-60.
	 *     @type bool         $sentence                Whether to search by phrase. Default false.
	 *     @type bool         $suppress_filters        Whether to suppress filters. Default false.
	 *     @type string       $tag                     Tag slug. Comma-separated (either), Plus-separated (all).
	 *     @type array        $tag__and                An array of tag ids (AND in).
	 *     @type array        $tag__in                 An array of tag ids (OR in).
	 *     @type array        $tag__not_in             An array of tag ids (NOT in).
	 *     @type int          $tag_id                  Tag id or comma-separated list of IDs.
	 *     @type array        $tag_slug__and           An array of tag slugs (AND in).
	 *     @type array        $tag_slug__in            An array of tag slugs (OR in). unless 'ignore_sticky_posts' is
	 *                                                 true. Note: a string of comma-separated IDs will NOT work.
	 *     @type array        $tax_query               An associative array of WP_Tax_Query arguments.
	 *                                                 See WP_Tax_Query->queries.
	 *     @type string       $title                   Post title.
	 *     @type bool         $update_post_meta_cache  Whether to update the post meta cache. Default true.
	 *     @type bool         $update_post_term_cache  Whether to update the post term cache. Default true.
	 *     @type bool         $lazy_load_term_meta     Whether to lazy-load term meta. Setting to false will
	 *                                                 disable cache priming for term meta, so that each
	 *                                                 get_term_meta() call will hit the database.
	 *                                                 Defaults to the value of `$update_post_term_cache`.
	 *     @type int          $w                       The week number of the year. Default empty. Accepts numbers 0-53.
	 *     @type int          $year                    The four-digit year. Default empty. Accepts any four-digit year.
	 * }
	 */
/*
	public function parse_query( $query =  '' ) {
		if ( ! empty( $query ) ) {
			$this->init();
			$this->query = $this->query_vars = wp_parse_args( $query );
		} elseif ( ! isset( $this->query ) ) {
			$this->query = $this->query_vars;
		}

		$this->query_vars = $this->fill_query_vars($this->query_vars);
		$qv = &$this->query_vars;
		$this->query_vars_changed = true;

		if ( ! empty($qv['robots']) )
			$this->is_robots = true;

		if ( ! is_scalar( $qv['p'] ) || $qv['p'] < 0 ) {
			$qv['p'] = 0;
			$qv['error'] = '404';
		} else {
			$qv['p'] = intval( $qv['p'] );
		}

		$qv['page_id'] =  absint($qv['page_id']);
		$qv['year'] = absint($qv['year']);
		$qv['monthnum'] = absint($qv['monthnum']);
		$qv['day'] = absint($qv['day']);
		$qv['w'] = absint($qv['w']);
		$qv['m'] = is_scalar( $qv['m'] ) ? preg_replace( '|[^0-9]|', '', $qv['m'] ) : '';
		$qv['paged'] = absint($qv['paged']);
		$qv['cat'] = preg_replace( '|[^0-9,-]|', '', $qv['cat'] ); // comma separated list of positive or negative integers
		$qv['author'] = preg_replace( '|[^0-9,-]|', '', $qv['author'] ); // comma separated list of positive or negative integers
		$qv['pagename'] = trim( $qv['pagename'] );
		$qv['name'] = trim( $qv['name'] );
		$qv['title'] = trim( $qv['title'] );
		if ( '' !== $qv['hour'] ) $qv['hour'] = absint($qv['hour']);
		if ( '' !== $qv['minute'] ) $qv['minute'] = absint($qv['minute']);
		if ( '' !== $qv['second'] ) $qv['second'] = absint($qv['second']);
		if ( '' !== $qv['menu_order'] ) $qv['menu_order'] = absint($qv['menu_order']);

		// Fairly insane upper bound for search string lengths.
		if ( ! is_scalar( $qv['s'] ) || ( ! empty( $qv['s'] ) && strlen( $qv['s'] ) > 1600 ) ) {
			$qv['s'] = '';
		}

		// Compat. Map subpost to attachment.
		if ( '' != $qv['subpost'] )
			$qv['attachment'] = $qv['subpost'];
		if ( '' != $qv['subpost_id'] )
			$qv['attachment_id'] = $qv['subpost_id'];

		$qv['attachment_id'] = absint($qv['attachment_id']);

		if ( ('' != $qv['attachment']) || !empty($qv['attachment_id']) ) {
			$this->is_single = true;
			$this->is_attachment = true;
		} elseif ( '' != $qv['name'] ) {
			$this->is_single = true;
		} elseif ( $qv['p'] ) {
			$this->is_single = true;
		} elseif ( ('' !== $qv['hour']) && ('' !== $qv['minute']) &&('' !== $qv['second']) && ('' != $qv['year']) && ('' != $qv['monthnum']) && ('' != $qv['day']) ) {
			// If year, month, day, hour, minute, and second are set, a single
			// post is being queried.
			$this->is_single = true;
		} elseif ( '' != $qv['static'] || '' != $qv['pagename'] || !empty($qv['page_id']) ) {
			$this->is_page = true;
			$this->is_single = false;
		} else {
			// Look for archive queries. Dates, categories, authors, search, post type archives.

			if ( isset( $this->query['s'] ) ) {
				$this->is_search = true;
			}

			if ( '' !== $qv['second'] ) {
				$this->is_time = true;
				$this->is_date = true;
			}

			if ( '' !== $qv['minute'] ) {
				$this->is_time = true;
				$this->is_date = true;
			}

			if ( '' !== $qv['hour'] ) {
				$this->is_time = true;
				$this->is_date = true;
			}

			if ( $qv['day'] ) {
				if ( ! $this->is_date ) {
					$date = sprintf( '%04d-%02d-%02d', $qv['year'], $qv['monthnum'], $qv['day'] );
					if ( $qv['monthnum'] && $qv['year'] && ! wp_checkdate( $qv['monthnum'], $qv['day'], $qv['year'], $date ) ) {
						$qv['error'] = '404';
					} else {
						$this->is_day = true;
						$this->is_date = true;
					}
				}
			}

			if ( $qv['monthnum'] ) {
				if ( ! $this->is_date ) {
					if ( 12 < $qv['monthnum'] ) {
						$qv['error'] = '404';
					} else {
						$this->is_month = true;
						$this->is_date = true;
					}
				}
			}

			if ( $qv['year'] ) {
				if ( ! $this->is_date ) {
					$this->is_year = true;
					$this->is_date = true;
				}
			}

			if ( $qv['m'] ) {
				$this->is_date = true;
				if ( strlen($qv['m']) > 9 ) {
					$this->is_time = true;
				} elseif ( strlen( $qv['m'] ) > 7 ) {
					$this->is_day = true;
				} elseif ( strlen( $qv['m'] ) > 5 ) {
					$this->is_month = true;
				} else {
					$this->is_year = true;
				}
			}

			if ( '' != $qv['w'] ) {
				$this->is_date = true;
			}

			$this->query_vars_hash = false;
			$this->parse_tax_query( $qv );
*/
/*			foreach ( $this->tax_query->queries as $tax_query ) {
				if ( ! is_array( $tax_query ) ) {
					continue;
				}

				if ( isset( $tax_query['operator'] ) && 'NOT IN' != $tax_query['operator'] ) {
					switch ( $tax_query['taxonomy'] ) {
						case 'category':
							$this->is_category = true;
							break;
						case 'post_tag':
							$this->is_tag = true;
							break;
						default:
							$this->is_tax = true;
					}
				}
			}
			unset( $tax_query );*/
/*
			if ( empty($qv['author']) || ($qv['author'] == '0') ) {
				$this->is_author = false;
			} else {
				$this->is_author = true;
			}

			if ( '' != $qv['author_name'] )
				$this->is_author = true;

			if ( !empty( $qv['post_type'] ) && ! is_array( $qv['post_type'] ) ) {
				$post_type_obj = get_post_type_object( $qv['post_type'] );
				if ( ! empty( $post_type_obj->has_archive ) )
					$this->is_post_type_archive = true;
			}

			if ( $this->is_post_type_archive || $this->is_date || $this->is_author || $this->is_category || $this->is_tag || $this->is_tax )
				$this->is_archive = true;
		}

		if ( '' != $qv['feed'] )
			$this->is_feed = true;

		if ( '' != $qv['embed'] ) {
			$this->is_embed = true;
		}

		if ( '' != $qv['tb'] )
			$this->is_trackback = true;

		if ( '' != $qv['paged'] && ( intval($qv['paged']) > 1 ) )
			$this->is_paged = true;

		// if we're previewing inside the write screen
		if ( '' != $qv['preview'] )
			$this->is_preview = true;

		if ( is_admin() )
			$this->is_admin = true;

		if ( false !== strpos($qv['feed'], 'comments-') ) {
			$qv['feed'] = str_replace('comments-', '', $qv['feed']);
			$qv['withcomments'] = 1;
		}

		$this->is_singular = $this->is_single || $this->is_page || $this->is_attachment;

		if ( $this->is_feed && ( !empty($qv['withcomments']) || ( empty($qv['withoutcomments']) && $this->is_singular ) ) )
			$this->is_comment_feed = true;

		if ( !( $this->is_singular || $this->is_archive || $this->is_search || $this->is_feed || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || $this->is_trackback || $this->is_404 || $this->is_admin || $this->is_robots ) )
			$this->is_home = true;

		// Correct is_* for page_on_front and page_for_posts
		if ( $this->is_home && 'page' == get_option('show_on_front') && get_option('page_on_front') ) {
			$_query = wp_parse_args($this->query);
			// pagename can be set and empty depending on matched rewrite rules. Ignore an empty pagename.
			if ( isset($_query['pagename']) && '' == $_query['pagename'] )
				unset($_query['pagename']);

			unset( $_query['embed'] );

			if ( empty($_query) || !array_diff( array_keys($_query), array('preview', 'page', 'paged', 'cpage') ) ) {
				$this->is_page = true;
				$this->is_home = false;
				$qv['page_id'] = get_option('page_on_front');
				// Correct <!--nextpage--> for page_on_front
				if ( !empty($qv['paged']) ) {
					$qv['page'] = $qv['paged'];
					unset($qv['paged']);
				}
			}
		}

		if ( '' != $qv['pagename'] ) {
			$this->queried_object = get_page_by_path( $qv['pagename'] );

			if ( $this->queried_object && 'attachment' == $this->queried_object->post_type ) {
				if ( preg_match( "/^[^%]*%(?:postname)%/", get_option( 'permalink_structure' ) ) ) {
					// See if we also have a post with the same slug
					$post = get_page_by_path( $qv['pagename'], OBJECT, 'post' );
					if ( $post ) {
						$this->queried_object = $post;
						$this->is_page = false;
						$this->is_single = true;
					}
				}
			}

			if ( ! empty( $this->queried_object ) ) {
				$this->queried_object_id = (int) $this->queried_object->ID;
			} else {
				unset( $this->queried_object );
			}

			if  ( 'page' == get_option('show_on_front') && isset($this->queried_object_id) && $this->queried_object_id == get_option('page_for_posts') ) {
				$this->is_page = false;
				$this->is_home = true;
				$this->is_posts_page = true;
			}
		}

		if ( $qv['page_id'] ) {
			if  ( 'page' == get_option('show_on_front') && $qv['page_id'] == get_option('page_for_posts') ) {
				$this->is_page = false;
				$this->is_home = true;
				$this->is_posts_page = true;
			}
		}

		if ( !empty($qv['post_type']) ) {
			if ( is_array($qv['post_type']) )
				$qv['post_type'] = array_map('sanitize_key', $qv['post_type']);
			else
				$qv['post_type'] = sanitize_key($qv['post_type']);
		}

		if ( ! empty( $qv['post_status'] ) ) {
			if ( is_array( $qv['post_status'] ) )
				$qv['post_status'] = array_map('sanitize_key', $qv['post_status']);
			else
				$qv['post_status'] = preg_replace('|[^a-z0-9_,-]|', '', $qv['post_status']);
		}

		if ( $this->is_posts_page && ( ! isset($qv['withcomments']) || ! $qv['withcomments'] ) )
			$this->is_comment_feed = false;

		$this->is_singular = $this->is_single || $this->is_page || $this->is_attachment;
		// Done correcting is_* for page_on_front and page_for_posts

		if ( '404' == $qv['error'] )
			$this->set_404();

		$this->is_embed = $this->is_embed && ( $this->is_singular || $this->is_404 );

		$this->query_vars_hash = md5( serialize( $this->query_vars ) );
		$this->query_vars_changed = false;
*/
		/**
		 * Fires after the main query vars have been parsed.
		 *
		 * @since 1.5.0
		 *
		 * @param WP_Query &$this The WP_Query instance (passed by reference).
		 */
//		do_action_ref_array( 'parse_query', array( &$this ) );
//	}

	/**
	 * Parses various taxonomy related query vars.
	 *
	 * For BC, this method is not marked as protected. See [28987].
	 *
	 * @access protected
	 * @since 3.1.0
	 *
	 * @param array $q The query variables. Passed by reference.
	 */
	public function parse_tax_query( &$q ) {
		if ( ! empty( $q['tax_query'] ) && is_array( $q['tax_query'] ) ) {
			$tax_query = $q['tax_query'];
		} else {
			$tax_query = array();
		}

		if ( !empty($q['taxonomy']) && !empty($q['term']) ) {
			$tax_query[] = array(
				'taxonomy' => $q['taxonomy'],
				'terms' => array( $q['term'] ),
				'field' => 'slug',
			);
		}

		foreach ( get_taxonomies( array() , 'objects' ) as $taxonomy => $t ) {
			if ( 'post_tag' == $taxonomy )
				continue;	// Handled further down in the $q['tag'] block

			if ( $t->query_var && !empty( $q[$t->query_var] ) ) {
				$tax_query_defaults = array(
					'taxonomy' => $taxonomy,
					'field' => 'slug',
				);

 				if ( isset( $t->rewrite['hierarchical'] ) && $t->rewrite['hierarchical'] ) {
					$q[$t->query_var] = wp_basename( $q[$t->query_var] );
				}

				$term = $q[$t->query_var];

				if ( is_array( $term ) ) {
					$term = implode( ',', $term );
				}

				if ( strpos($term, '+') !== false ) {
					$terms = preg_split( '/[+]+/', $term );
					foreach ( $terms as $term ) {
						$tax_query[] = array_merge( $tax_query_defaults, array(
							'terms' => array( $term )
						) );
					}
				} else {
					$tax_query[] = array_merge( $tax_query_defaults, array(
						'terms' => preg_split( '/[,]+/', $term )
					) );
				}
			}
		}

		// If querystring 'cat' is an array, implode it.
		if ( is_array( $q['cat'] ) ) {
			$q['cat'] = implode( ',', $q['cat'] );
		}

		// Category stuff
		if ( ! empty( $q['cat'] ) && ! $this->is_singular ) {
			$cat_in = $cat_not_in = array();

			$cat_array = preg_split( '/[,\s]+/', urldecode( $q['cat'] ) );
			$cat_array = array_map( 'intval', $cat_array );
			$q['cat'] = implode( ',', $cat_array );

			foreach ( $cat_array as $cat ) {
				if ( $cat > 0 )
					$cat_in[] = $cat;
				elseif ( $cat < 0 )
					$cat_not_in[] = abs( $cat );
			}

			if ( ! empty( $cat_in ) ) {
				$tax_query[] = array(
					'taxonomy' => 'category',
					'terms' => $cat_in,
					'field' => 'term_id',
					'include_children' => true
				);
			}

			if ( ! empty( $cat_not_in ) ) {
				$tax_query[] = array(
					'taxonomy' => 'category',
					'terms' => $cat_not_in,
					'field' => 'term_id',
					'operator' => 'NOT IN',
					'include_children' => true
				);
			}
			unset( $cat_array, $cat_in, $cat_not_in );
		}

		if ( ! empty( $q['category__and'] ) && 1 === count( (array) $q['category__and'] ) ) {
			$q['category__and'] = (array) $q['category__and'];
			if ( ! isset( $q['category__in'] ) )
				$q['category__in'] = array();
			$q['category__in'][] = absint( reset( $q['category__and'] ) );
			unset( $q['category__and'] );
		}

		if ( ! empty( $q['category__in'] ) ) {
			$q['category__in'] = array_map( 'absint', array_unique( (array) $q['category__in'] ) );
			$tax_query[] = array(
				'taxonomy' => 'category',
				'terms' => $q['category__in'],
				'field' => 'term_id',
				'include_children' => false
			);
		}

		if ( ! empty($q['category__not_in']) ) {
			$q['category__not_in'] = array_map( 'absint', array_unique( (array) $q['category__not_in'] ) );
			$tax_query[] = array(
				'taxonomy' => 'category',
				'terms' => $q['category__not_in'],
				'operator' => 'NOT IN',
				'include_children' => false
			);
		}

		if ( ! empty($q['category__and']) ) {
			$q['category__and'] = array_map( 'absint', array_unique( (array) $q['category__and'] ) );
			$tax_query[] = array(
				'taxonomy' => 'category',
				'terms' => $q['category__and'],
				'field' => 'term_id',
				'operator' => 'AND',
				'include_children' => false
			);
		}

		// If querystring 'tag' is array, implode it.
		if ( is_array( $q['tag'] ) ) {
			$q['tag'] = implode( ',', $q['tag'] );
		}

		// Tag stuff
		if ( '' != $q['tag'] && !$this->is_singular && $this->query_vars_changed ) {
			if ( strpos($q['tag'], ',') !== false ) {
				$tags = preg_split('/[,\r\n\t ]+/', $q['tag']);
				foreach ( (array) $tags as $tag ) {
					$tag = sanitize_term_field('slug', $tag, 0, 'post_tag', 'db');
					$q['tag_slug__in'][] = $tag;
				}
			} elseif ( preg_match('/[+\r\n\t ]+/', $q['tag'] ) || ! empty( $q['cat'] ) ) {
				$tags = preg_split('/[+\r\n\t ]+/', $q['tag']);
				foreach ( (array) $tags as $tag ) {
					$tag = sanitize_term_field('slug', $tag, 0, 'post_tag', 'db');
					$q['tag_slug__and'][] = $tag;
				}
			} else {
				$q['tag'] = sanitize_term_field('slug', $q['tag'], 0, 'post_tag', 'db');
				$q['tag_slug__in'][] = $q['tag'];
			}
		}

		if ( !empty($q['tag_id']) ) {
			$q['tag_id'] = absint( $q['tag_id'] );
			$tax_query[] = array(
				'taxonomy' => 'post_tag',
				'terms' => $q['tag_id']
			);
		}

		if ( !empty($q['tag__in']) ) {
			$q['tag__in'] = array_map('absint', array_unique( (array) $q['tag__in'] ) );
			$tax_query[] = array(
				'taxonomy' => 'post_tag',
				'terms' => $q['tag__in']
			);
		}

		if ( !empty($q['tag__not_in']) ) {
			$q['tag__not_in'] = array_map('absint', array_unique( (array) $q['tag__not_in'] ) );
			$tax_query[] = array(
				'taxonomy' => 'post_tag',
				'terms' => $q['tag__not_in'],
				'operator' => 'NOT IN'
			);
		}

		if ( !empty($q['tag__and']) ) {
			$q['tag__and'] = array_map('absint', array_unique( (array) $q['tag__and'] ) );
			$tax_query[] = array(
				'taxonomy' => 'post_tag',
				'terms' => $q['tag__and'],
				'operator' => 'AND'
			);
		}

		if ( !empty($q['tag_slug__in']) ) {
			$q['tag_slug__in'] = array_map('sanitize_title_for_query', array_unique( (array) $q['tag_slug__in'] ) );
			$tax_query[] = array(
				'taxonomy' => 'post_tag',
				'terms' => $q['tag_slug__in'],
				'field' => 'slug'
			);
		}

		if ( !empty($q['tag_slug__and']) ) {
			$q['tag_slug__and'] = array_map('sanitize_title_for_query', array_unique( (array) $q['tag_slug__and'] ) );
			$tax_query[] = array(
				'taxonomy' => 'post_tag',
				'terms' => $q['tag_slug__and'],
				'field' => 'slug',
				'operator' => 'AND'
			);
		}

//		$this->tax_query = new WP_Tax_Query( $tax_query );

		/**
		 * Fires after taxonomy-related query vars have been parsed.
		 *
		 * @since 3.7.0
		 *
		 * @param WP_Query $this The WP_Query instance.
		 */
//		do_action( 'parse_tax_query', $this );
	}

	/**
	 * Generate SQL for the WHERE clause based on passed search terms.
	 *
	 * @since 3.7.0
	 *
	 * @param array $q Query variables.
	 * @return string WHERE clause.
	 */
/*	protected function parse_search( &$q ) {
		global $wpdb;

		$search = '';

		// added slashes screw with quote grouping when done early, so done later
		$q['s'] = stripslashes( $q['s'] );
		if ( empty( $_GET['s'] ) && $this->is_main_query() )
			$q['s'] = urldecode( $q['s'] );
		// there are no line breaks in <input /> fields
		$q['s'] = str_replace( array( "\r", "\n" ), '', $q['s'] );
		$q['search_terms_count'] = 1;
		if ( ! empty( $q['sentence'] ) ) {
			$q['search_terms'] = array( $q['s'] );
		} else {
			if ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $q['s'], $matches ) ) {
				$q['search_terms_count'] = count( $matches[0] );
				$q['search_terms'] = $this->parse_search_terms( $matches[0] );
				// if the search string has only short terms or stopwords, or is 10+ terms long, match it as sentence
				if ( empty( $q['search_terms'] ) || count( $q['search_terms'] ) > 9 )
					$q['search_terms'] = array( $q['s'] );
			} else {
				$q['search_terms'] = array( $q['s'] );
			}
		}

		$n = ! empty( $q['exact'] ) ? '' : '%';
		$searchand = '';
		$q['search_orderby_title'] = array();
*/
		/**
		 * Filters the prefix that indicates that a search term should be excluded from results.
		 *
		 * @since 4.7.0
		 *
		 * @param string $exclusion_prefix The prefix. Default '-'. Returning
		 *                                 an empty value disables exclusions.
		 */
/*		$exclusion_prefix = apply_filters( 'wp_query_search_exclusion_prefix', '-' );

		foreach ( $q['search_terms'] as $term ) {
			// If there is an $exclusion_prefix, terms prefixed with it should be excluded.
			$exclude = $exclusion_prefix && ( $exclusion_prefix === substr( $term, 0, 1 ) );
			if ( $exclude ) {
				$like_op  = 'NOT LIKE';
				$andor_op = 'AND';
				$term     = substr( $term, 1 );
			} else {
				$like_op  = 'LIKE';
				$andor_op = 'OR';
			}

			if ( $n && ! $exclude ) {
				$like = '%' . $wpdb->esc_like( $term ) . '%';
				$q['search_orderby_title'][] = $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", $like );
			}

			$like = $n . $wpdb->esc_like( $term ) . $n;
			$search .= $wpdb->prepare( "{$searchand}(({$wpdb->posts}.post_title $like_op %s) $andor_op ({$wpdb->posts}.post_excerpt $like_op %s) $andor_op ({$wpdb->posts}.post_content $like_op %s))", $like, $like, $like );
			$searchand = ' AND ';
		}

		if ( ! empty( $search ) ) {
			$search = " AND ({$search}) ";
//			if ( ! is_user_logged_in() ) {
//				$search .= " AND ({$wpdb->posts}.post_password = '') ";
//			}
		}

		return $search;
	}
*/
	/**
	 * Check if the terms are suitable for searching.
	 *
	 * Uses an array of stopwords (terms) that are excluded from the separate
	 * term matching when searching for posts. The list of English stopwords is
	 * the approximate search engines list, and is translatable.
	 *
	 * @since 3.7.0
	 *
	 * @param array $terms Terms to check.
	 * @return array Terms that are not stopwords.
	 */
/*	protected function parse_search_terms( $terms ) {
		$strtolower = function_exists( 'mb_strtolower' ) ? 'mb_strtolower' : 'strtolower';
		$checked = array();

		$stopwords = $this->get_search_stopwords();

		foreach ( $terms as $term ) {
			// keep before/after spaces when term is for exact match
			if ( preg_match( '/^".+"$/', $term ) )
				$term = trim( $term, "\"'" );
			else
				$term = trim( $term, "\"' " );

			// Avoid single A-Z and single dashes.
			if ( ! $term || ( 1 === strlen( $term ) && preg_match( '/^[a-z\-]$/i', $term ) ) )
				continue;

			if ( in_array( call_user_func( $strtolower, $term ), $stopwords, true ) )
				continue;

			$checked[] = $term;
		}

		return $checked;
	}*/

	/**
	 * Retrieve stopwords used when parsing search terms.
	 *
	 * @since 3.7.0
	 *
	 * @return array Stopwords.
	 */
/*	protected function get_search_stopwords() {
		if ( isset( $this->stopwords ) )
			return $this->stopwords;
*/
		/* translators: This is a comma-separated list of very common words that should be excluded from a search,
		 * like a, an, and the. These are usually called "stopwords". You should not simply translate these individual
		 * words into your language. Instead, look for and provide commonly accepted stopwords in your language.
		 */
/*		$words = explode( ',', _x( 'about,an,are,as,at,be,by,com,for,from,how,in,is,it,of,on,or,that,the,this,to,was,what,when,where,who,will,with,www',
			'Comma-separated list of search stopwords in your language' ) );

		$stopwords = array();
		foreach ( $words as $word ) {
			$word = trim( $word, "\r\n\t " );
			if ( $word )
				$stopwords[] = $word;
		}
*/
		/**
		 * Filters stopwords used when parsing search terms.
		 *
		 * @since 3.7.0
		 *
		 * @param array $stopwords Stopwords.
		 */
/*		$this->stopwords = apply_filters( 'wp_search_stopwords', $stopwords );
		return $this->stopwords;
	}
*/
	/**
	 * Generate SQL for the ORDER BY condition based on passed search terms.
	 *
	 * @param array $q Query variables.
	 * @return string ORDER BY clause.
	 */
/*	protected function parse_search_order( &$q ) {
		global $wpdb;

		if ( $q['search_terms_count'] > 1 ) {
			$num_terms = count( $q['search_orderby_title'] );

			// If the search terms contain negative queries, don't bother ordering by sentence matches.
			$like = '';
			if ( ! preg_match( '/(?:\s|^)\-/', $q['s'] ) ) {
				$like = '%' . $wpdb->esc_like( $q['s'] ) . '%';
			}

			$search_orderby = '';

			// sentence match in 'post_title'
			if ( $like ) {
				$search_orderby .= $wpdb->prepare( "WHEN {$wpdb->posts}.post_title LIKE %s THEN 1 ", $like );
			}

			// sanity limit, sort as sentence when more than 6 terms
			// (few searches are longer than 6 terms and most titles are not)
			if ( $num_terms < 7 ) {
				// all words in title
				$search_orderby .= 'WHEN ' . implode( ' AND ', $q['search_orderby_title'] ) . ' THEN 2 ';
				// any word in title, not needed when $num_terms == 1
				if ( $num_terms > 1 )
					$search_orderby .= 'WHEN ' . implode( ' OR ', $q['search_orderby_title'] ) . ' THEN 3 ';
			}

			// Sentence match in 'post_content' and 'post_excerpt'.
			if ( $like ) {
				$search_orderby .= $wpdb->prepare( "WHEN {$wpdb->posts}.post_excerpt LIKE %s THEN 4 ", $like );
				$search_orderby .= $wpdb->prepare( "WHEN {$wpdb->posts}.post_content LIKE %s THEN 5 ", $like );
			}

			if ( $search_orderby ) {
				$search_orderby = '(CASE ' . $search_orderby . 'ELSE 6 END)';
			}
		} else {
			// single word or sentence search
			$search_orderby = reset( $q['search_orderby_title'] ) . ' DESC';
		}

		return $search_orderby;
	}
*/
	/**
	 * If the passed orderby value is allowed, convert the alias to a
	 * properly-prefixed orderby value.
	 *
	 * @since 4.0.0
	 * @access protected
	 *
	 * @param string $orderby Alias for the field to order by.
	 * @return string|false Table-prefixed value to used in the ORDER clause. False otherwise.
	 */
/*	protected function parse_orderby( $orderby ) {
		global $wpdb;

		// Used to filter values.
		$allowed_keys = array(
			'post_name', 'post_author', 'post_date', 'post_title', 'post_modified',
			'post_parent', 'post_type', 'name', 'author', 'date', 'title', 'modified',
			'parent', 'type', 'ID', 'menu_order', 'comment_count', 'rand',
		);

		$primary_meta_key = '';
		$primary_meta_query = false;
		$meta_clauses = $this->meta_query->get_clauses();
		if ( ! empty( $meta_clauses ) ) {
			$primary_meta_query = reset( $meta_clauses );

			if ( ! empty( $primary_meta_query['key'] ) ) {
				$primary_meta_key = $primary_meta_query['key'];
				$allowed_keys[] = $primary_meta_key;
			}

			$allowed_keys[] = 'meta_value';
			$allowed_keys[] = 'meta_value_num';
			$allowed_keys   = array_merge( $allowed_keys, array_keys( $meta_clauses ) );
		}

		// If RAND() contains a seed value, sanitize and add to allowed keys.
		$rand_with_seed = false;
		if ( preg_match( '/RAND\(([0-9]+)\)/i', $orderby, $matches ) ) {
			$orderby = sprintf( 'RAND(%s)', intval( $matches[1] ) );
			$allowed_keys[] = $orderby;
			$rand_with_seed = true;
		}

		if ( ! in_array( $orderby, $allowed_keys, true ) ) {
			return false;
		}

		switch ( $orderby ) {
			case 'post_name':
			case 'post_author':
			case 'post_date':
			case 'post_title':
			case 'post_modified':
			case 'post_parent':
			case 'post_type':
			case 'ID':
			case 'menu_order':
			case 'comment_count':
				$orderby_clause = "{$wpdb->posts}.{$orderby}";
				break;
			case 'rand':
				$orderby_clause = 'RAND()';
				break;
			case $primary_meta_key:
			case 'meta_value':
				if ( ! empty( $primary_meta_query['type'] ) ) {
					$orderby_clause = "CAST({$primary_meta_query['alias']}.meta_value AS {$primary_meta_query['cast']})";
				} else {
					$orderby_clause = "{$primary_meta_query['alias']}.meta_value";
				}
				break;
			case 'meta_value_num':
				$orderby_clause = "{$primary_meta_query['alias']}.meta_value+0";
				break;
			default:
				if ( array_key_exists( $orderby, $meta_clauses ) ) {
					// $orderby corresponds to a meta_query clause.
					$meta_clause = $meta_clauses[ $orderby ];
					$orderby_clause = "CAST({$meta_clause['alias']}.meta_value AS {$meta_clause['cast']})";
				} elseif ( $rand_with_seed ) {
					$orderby_clause = $orderby;
				} else {
					// Default: order by post field.
					$orderby_clause = "{$wpdb->posts}.post_" . sanitize_key( $orderby );
				}

				break;
		}

		return $orderby_clause;
	}
*/
	/**
	 * Parse an 'order' query variable and cast it to ASC or DESC as necessary.
	 *
	 * @since 4.0.0
	 * @access protected
	 *
	 * @param string $order The 'order' query variable.
	 * @return string The sanitized 'order' query variable.
	 */
/*	protected function parse_order( $order ) {
		if ( ! is_string( $order ) || empty( $order ) ) {
			return 'DESC';
		}

		if ( 'ASC' === strtoupper( $order ) ) {
			return 'ASC';
		} else {
			return 'DESC';
		}
	}*/

	/**
	 * Sets the 404 property and saves whether query is feed.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	public function set_404() {
		$is_feed = $this->is_feed;

//		$this->init_query_flags();
		$this->is_404 = true;

		$this->is_feed = $is_feed;
	}

	/**
	 * Retrieve query variable.
	 *
	 * @since 1.5.0
	 * @since 3.9.0 The `$default` argument was introduced.
	 *
	 * @access public
	 *
	 * @param string $query_var Query variable key.
	 * @param mixed  $default   Optional. Value to return if the query variable is not set. Default empty.
	 * @return mixed Contents of the query variable.
	 */
	public function get( $query_var, $default = '' ) {
		if ( isset( $this->query_vars[ $query_var ] ) ) {
			return $this->query_vars[ $query_var ];
		}

		return $default;
	}

	/**
	 * Set query variable.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @param string $query_var Query variable key.
	 * @param mixed  $value     Query variable value.
	 */
	public function set($query_var, $value) {
		$this->query_vars[$query_var] = $value;
	}

	/**
	 * Retrieve the posts based on query variables.
	 *
	 * There are a few filters and actions that can be used to modify the post
	 * database query.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @return array List of posts.
	 */
	public function get_posts() {
		global $gContentApi;
		global $gRequestManager;
		global $gPageManager;
		global $paged;

		// ########## このメソッドは画面出力前に実行される ##########
		// ##### URLパラメータを解析 #####
		$this->query_vars = array();
		// ページ番号
		$pageNo = absint($gRequestManager->trimValueOf('page'));
		$this->query_vars['paged'] = $pageNo;			// 一覧でのページ番号は$paged、１記事を複数分割した場合のページ番号は$page
		$paged = $pageNo;				// グローバル変数へ代入
		if ($pageNo > 1) $this->is_paged = true;
		
		// ##### DBからコンテンツを取得 #####
		// コンテンツタイプが設定されていない場合はダミーのWP_Postデータを作成
		$contentType = $gContentApi->getContentType();
		if (empty($contentType)){
			// WordPress専用描画ページの場合はWP_Postオブジェクト用のパラメータを取得
			$pageParams = array();
			if ($gContentApi->isWordPressSpecificPage()){
				$pageParams = $gContentApi->getWordPressSpecificPageParam();
			}
			
			// コンテンツタイプないページの場合はWordPressの「page」で画面を生成
			// WP_Postオブジェクトに変換して格納
			$post = new stdClass;
			$post->ID = 0;
			$post->post_type = 'page';
			$post->post_status = 'publish';
/*			$post->ID = $id;
			$post->post_author = '';
			$post->post_date = $date;
			$post->post_date_gmt = '';
			$post->post_password = '';
			$post->post_name = $title;		// エンコーディングが必要?
			$post->post_type = 'post';
			$post->post_status = 'publish';
			$post->to_ping = '';
			$post->pinged = '';
			$post->post_parent = 0;
			$post->menu_order = 0;
			// Magic3設定値追加
			$post->post_title = $title;
			$post->post_content = $contentHtml;
			$post->guid = $this->getContentUrl($id);	// 詳細画面URL
			$post->filter = 'raw';
			// Magic3用パラメータ
			$post->thumb_src = $thumbSrc;*/
			
			// ### WordPress専用描画ページのパラメータ ###
			if (!empty($pageParams)){
				$post->post_name = $pageParams['name'];
				$post->post_title = $pageParams['title'];
				$post->post_content = $pageParams['content'];
			}
		
			$wpPostObj = new WP_Post($post);
			$this->posts = array($wpPostObj);
			
			// 単体ページ表示を指定
			// ########## is_pageがtrueの場合のみMagic3のブロック出力を使用する。それ以外はWordPress側のレンダリング機能を使用する。
			$this->is_page = true;
		} else {
			$keywords = '';
			$category = null;			// カテゴリー。設定なしの場合はnullを設定。
		
			// ページタイプごとの処理
			// $this->is_singleの設定は前もってcontentApi側で行う
			$pageType = $gContentApi->getPageType();		// ページタイプ取得
			switch ($pageType){
			case 'single':			// ブログ単体記事ページの場合
				break;
			case 'page':			// ページタイプなしページまたは汎用コンテンツページの場合
//				$this->is_paged = true;			// pagedは複数ページの意味
				$this->is_page = true;
				break;
			case 'category':	// カテゴリー
				// ページの表示パラメータを設定
				$this->is_category = true;
				$this->is_archive = true;
				
				$value = absint($gRequestManager->trimValueOf(M3_REQUEST_PARAM_CATEGORY_ID));
				if ($value > 0){
					$category = $value;
					$this->query_vars['cat'] = $category;
				}
				break;
			case 'date':		// 年月日アーカイブ
				// ページの表示パラメータを設定
				$this->is_date = true;
				$this->is_archive = true;
				
				// パラメータエラーチェック
				$year = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_YEAR);		// 年指定
				if ($year <= 0) $year = '';
				$month = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_MONTH);		// 月指定
				if ($month <= 0) $month = '';
				$day = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_DAY);		// 日指定
				if ($day <= 0) $day = '';
				
				// $this->query_varsの値はget_query_var()等WordPress用のインターフェイスとして残す
				if (!empty($month) && !empty($day)){		// 日指定のとき
					$this->is_day = true;
					$this->query_vars['year'] = $year;
					$this->query_vars['monthnum'] = $month;
					$this->query_vars['day'] = $day;
		
					// データ取得用日時範囲
					$startDt = $year . '/' . $month . '/' . $day;
					$endDt = $gContentApi->getNextDay($year . '/' . $month . '/' . $day);
				} else if (!empty($month)){		// 月指定のとき
					$this->is_month = true;
					$this->query_vars['year'] = $year;
					$this->query_vars['monthnum'] = $month;
					
					// データ取得用日時範囲
					$startDt = $year . '/' . $month . '/1';
					$endDt = $gContentApi->getNextMonth($year . '/' . $month) . '/1';
				} else {		// 年指定のとき
					$this->is_year = true;
					$this->query_vars['year'] = $year;
					
					// データ取得用日時範囲
					$startDt = $year . '/1/1';
					$endDt = ($year + 1) . '/1/1';
				}
				break;
			case 'search':		// 検索結果表示の場合はキーワードを取得
				// ページの表示パラメータを設定
				$this->is_search = true;
				
				$keywords = $gRequestManager->trimValueOf('s');
				$this->query_vars['s'] = $keywords;
				
				// 半角スペースで分割
				$keywords = explode(' ', $keywords);
				break;
			case 'author':		// 会員ページの場合
				// ページの表示パラメータを設定
				$this->is_author = true;
				$this->is_archive = true;
				break;
			case 'archive':		// 条件なし一覧の場合
			default:			// デフォルトの画面表示は一覧にする?
				// ページの表示パラメータを設定
				$this->is_archive = true;
				break;
			}
			
			$gContentApi->setCondition(array(), ''/*現在の言語*/, 0/*最大取得数(デフォルト)*/, $pageNo/*ページ番号*/, $keywords, $startDt/*期間開始日時*/, $endDt/*期間終了日時*/, $category);
			$this->posts = $gContentApi->getContentList();
			
			// コンテンツ総数を取得
			$viewCount = $gContentApi->getContentViewCount();		// １ページあたりの表示記事数
			$this->found_posts = $gContentApi->getContentCount();		// コンテンツ総数
			$this->max_num_pages = ceil($this->found_posts / $viewCount);		// 総ページ数
		}

		// Ensure that any posts added/modified via one of the filters above are
		// of the type WP_Post and are filtered.
		if ( $this->posts ) {
			$this->post_count = count( $this->posts );

//			$this->posts = array_map( 'get_post', $this->posts );

			$this->post = reset( $this->posts );
			
			// the_post()の前にget_post()が呼ばれることがあるのでグローバル変数に保存
			$GLOBALS['post'] = $this->post;			// ########## 注意 ##### the_post()の実行前に初期値を設定しておくのがよいか実験中 ##### 注意 ##########
		} else {
			$this->post_count = 0;
			$this->posts = array();
		}
		$this->lastPostFound = false;		// 一覧の最後の項目を検出したかどうかのフラグリセット。have_post()で更新。

		// ##### 画面のサブタイトルを設定。メインタイトルはサイト名。#####
		if ($this->is_singular()){			// 単体ページの場合
			// 表示するコンテンツデータから取得
			$gPageManager->setHeadSubTitle($this->post->post_title);
			
			// *** お問合わせ画面等ページタイプ属性がないページの場合はウィジェット側でサブタイトルを設定する ***
		} else {		// カテゴリーや年月アーカイブ、検索結果等の画面の場合
			// WordPressのwp_get_document_title()を使用してタイトルを作成
			$this->headTitle = '';			// HTMLヘッダのタイトル作成用
			add_filter('document_title_parts', 'm3get_simple_title');		// フィルター関数で出力を調整
			wp_get_document_title();		// タイトル生成
			remove_filter('document_title_parts', 'm3get_simple_title');

			// サブタイトルを設定
			if (!empty($this->headTitle)) $gPageManager->setHeadSubTitle($this->headTitle);
		}
		return $this->posts;
	}
	/**
	 * [WordPressテンプレート用API]コンテンツデータ取得
	 *
	 * @param string $query		データ取得用クエリー
	 * @return array			取得コンテンツ一覧
	 */
	public function get_direct_posts($query){
		global $gContentApi;
		global $gRequestManager;
		global $gPageManager;
//		global $paged;
		
		// ##### URLパラメータを解析 #####
		$this->query_vars = array();
//		// ページ番号
//		$pageNo = absint($gRequestManager->trimValueOf('page'));
//		$this->query_vars['paged'] = $pageNo;			// 一覧でのページ番号は$paged、１記事を複数分割した場合のページ番号は$page
//		$paged = $pageNo;				// グローバル変数へ代入
//		if ($pageNo > 1) $this->is_paged = true;
		
		// データタイプを取得
		$postType = 'post';
		$value = $query['post_type'];
		if (!empty($value)) $postType = $value;
		
		if ($postType == 'post'){			// ブログコンテンツの場合
		} else if ($postType == 'product'){			// 製品の場合
			$itemCount = 0;		// 項目数デフォルト
			$value = absint($query['posts_per_page']);			// 取得数
			if ($value > 0) $itemCount = $value;
		
			$gContentApi->setCondition(array(), ''/*現在の言語*/, $itemCount/*最大取得数(デフォルト)*/, 1/*ページ番号*/,
										''/*キーワード*/, null/*期間開始日時*/, null/*期間終了日時*/, null/*カテゴリー*/, M3_VIEW_TYPE_PRODUCT/*コンテンツタイプ*/);
			$this->posts = $gContentApi->getContentList(M3_VIEW_TYPE_PRODUCT);		// コンテンツタイプは製品を指定
		} else {
			// 汎用コンテンツの場合
			$value = absint($query['page_id']);			// 汎用コンテンツID
			if ($value > 0){
				$this->query_vars['page_id'] = $value;
			
				// 汎用コンテンツ取得
				$this->posts = $gContentApi->getPageContentList($query);
			}
		}

		// Ensure that any posts added/modified via one of the filters above are
		// of the type WP_Post and are filtered.
		if ( $this->posts ) {
			$this->post_count = count( $this->posts );

//			$this->posts = array_map( 'get_post', $this->posts );

			$this->post = reset( $this->posts );
			
			// the_post()の前にget_post()が呼ばれることがあるのでグローバル変数に保存
			$GLOBALS['post'] = $this->post;			// ########## 注意 ##### the_post()の実行前に初期値を設定しておくのがよいか実験中 ##### 注意 ##########
//			$GLOBALS['post_sub'] = $this->post;		// ########## サブループ用グローバルWP_Post型データ(Magic3で追加)                            ##########
		} else {
			$this->post_count = 0;
			$this->posts = array();
		}
/*		$this->lastPostFound = false;		// 一覧の最後の項目を検出したかどうかのフラグリセット。have_post()で更新。

		// ##### 画面のサブタイトルを設定。メインタイトルはサイト名。#####
		if ($this->is_singular()){			// 単体ページの場合
			// 表示するコンテンツデータから取得
			$gPageManager->setHeadSubTitle($this->post->post_title);
			
			// *** お問合わせ画面等ページタイプ属性がないページの場合はウィジェット側でサブタイトルを設定する ***
		} else {		// カテゴリーや年月アーカイブ、検索結果等の画面の場合
			// WordPressのwp_get_document_title()を使用してタイトルを作成
			$this->headTitle = '';			// HTMLヘッダのタイトル作成用
			add_filter('document_title_parts', 'm3get_simple_title');		// フィルター関数で出力を調整
			wp_get_document_title();		// タイトル生成
			remove_filter('document_title_parts', 'm3get_simple_title');

			// サブタイトルを設定
			if (!empty($this->headTitle)) $gPageManager->setHeadSubTitle($this->headTitle);
		}*/
		return $this->posts;
	} 
	/**
	 * Set up the amount of found posts and the number of pages (if limit clause was used)
	 * for the current query.
	 *
	 * @since 3.5.0
	 * @access private
	 *
	 * @param array  $q      Query variables.
	 * @param string $limits LIMIT clauses of the query.
	 */
/*	private function set_found_posts( $q, $limits ) {
		global $wpdb;
		// Bail if posts is an empty array. Continue if posts is an empty string,
		// null, or false to accommodate caching plugins that fill posts later.
		if ( $q['no_found_rows'] || ( is_array( $this->posts ) && ! $this->posts ) )
			return;

		if ( ! empty( $limits ) ) {
		*/
			/**
			 * Filters the query to run for retrieving the found posts.
			 *
			 * @since 2.1.0
			 *
			 * @param string   $found_posts The query to run to find the found posts.
			 * @param WP_Query &$this       The WP_Query instance (passed by reference).
			 */
//			$this->found_posts = $wpdb->get_var( apply_filters_ref_array( 'found_posts_query', array( 'SELECT FOUND_ROWS()', &$this ) ) );
/*		} else {
			$this->found_posts = count( $this->posts );
		}*/

		/**
		 * Filters the number of found posts for the query.
		 *
		 * @since 2.1.0
		 *
		 * @param int      $found_posts The number of posts found.
		 * @param WP_Query &$this       The WP_Query instance (passed by reference).
		 */
//		$this->found_posts = apply_filters_ref_array( 'found_posts', array( $this->found_posts, &$this ) );
/*
		if ( ! empty( $limits ) )
			$this->max_num_pages = ceil( $this->found_posts / $q['posts_per_page'] );
	}*/

	/**
	 * Set up the next post and iterate current post index.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @return WP_Post Next post.
	 */
	public function next_post() {

		$this->current_post++;

		$this->post = $this->posts[$this->current_post];
		return $this->post;
	}

	/**
	 * Sets up the current post.
	 *
	 * Retrieves the next post, sets up the post, sets the 'in the loop'
	 * property to true.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @global WP_Post $post
	 */
	/***** テンプレートのからループで毎回実行され、グローバルの$postが更新される *****/
	public function the_post() {
		global $post;
		$this->in_the_loop = true;

//		if ( $this->current_post == -1 ) // loop has just started
			/**
			 * Fires once the loop is started.
			 *
			 * @since 2.0.0
			 *
			 * @param WP_Query &$this The WP_Query instance (passed by reference).
			 */
//			do_action_ref_array( 'loop_start', array( &$this ) );

		$post = $this->next_post();
		$this->setup_postdata( $post );
	}

	/**
	 * Determines whether there are more posts available in the loop.
	 *
	 * Calls the {@see 'loop_end'} action when the loop is complete.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @return bool True if posts are available, false if end of loop.
	 */
	public function have_posts() {
		if ( $this->current_post + 1 < $this->post_count ) {
			if ($this->current_post >= $this->post_count -2) $this->lastPostFound = true;		// ##### 一覧の最後の項目を検出 #####

			return true;
		} elseif ( $this->current_post + 1 == $this->post_count && $this->post_count > 0 ) {
			/**
			 * Fires once the loop has ended.
			 *
			 * @since 2.0.0
			 *
			 * @param WP_Query &$this The WP_Query instance (passed by reference).
			 */
//			do_action_ref_array( 'loop_end', array( &$this ) );
			// Do some cleaning up after the loop
			$this->rewind_posts();
		}

		$this->in_the_loop = false;
		return false;
	}

	/**
	 * Rewind the posts and reset post index.
	 *
	 * @since 1.5.0
	 * @access public
	 */
	public function rewind_posts() {
		$this->current_post = -1;
		if ( $this->post_count > 0 ) {
			$this->post = $this->posts[0];
		}
	}

	/**
	 * Iterate current comment index and return WP_Comment object.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @return WP_Comment Comment object.
	 */
	public function next_comment() {
		$this->current_comment++;

		$this->comment = $this->comments[$this->current_comment];
		return $this->comment;
	}

	/**
	 * Sets up the current comment.
	 *
	 * @since 2.2.0
	 * @access public
	 * @global WP_Comment $comment Current comment.
	 */
	public function the_comment() {
		global $comment;

//		$comment = $this->next_comment();

//		if ( $this->current_comment == 0 ) {
			/**
			 * Fires once the comment loop is started.
			 *
			 * @since 2.2.0
			 */
//			do_action( 'comment_loop_start' );
//		}
	}

	/**
	 * Whether there are more comments available.
	 *
	 * Automatically rewinds comments when finished.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @return bool True, if more comments. False, if no more posts.
	 */
	public function have_comments() {
		if ( $this->current_comment + 1 < $this->comment_count ) {
			return true;
		} elseif ( $this->current_comment + 1 == $this->comment_count ) {
			$this->rewind_comments();
		}

		return false;
	}

	/**
	 * Rewind the comments, resets the comment index and comment to first.
	 *
	 * @since 2.2.0
	 * @access public
	 */
	public function rewind_comments() {
		$this->current_comment = -1;
		if ( $this->comment_count > 0 ) {
			$this->comment = $this->comments[0];
		}
	}

	/**
	 * Sets up the WordPress query by parsing query string.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @param string $query URL query string.
	 * @return array List of posts.
	 */
	public function query($query = '') {
		// ##### テンプレート起動前にwp()から$queryなしでグローバルで一度実行される。その後、テンプレート内でWP_Queryオブジェクトを生成し$query付きで実行される。#####
//		$this->init();
//		$this->query = $this->query_vars = wp_parse_args( $query );
		if (empty($query)){
			return $this->get_posts();
		} else {
			// 汎用コンテンツを取得
			$posts = $this->get_direct_posts($query);
			
			// 汎用コンテンツが取得できない場合はデフォルトの取得方法でコンテンツを取得
			if (empty($posts)) $posts = $this->get_posts();
			return $posts;
		}
	}

	/**
	 * Retrieve queried object.
	 *
	 * If queried object is not set, then the queried object will be set from
	 * the category, tag, taxonomy, posts page, single post, page, or author
	 * query variable. After it is set up, it will be returned.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @return object
	 */
	public function get_queried_object() {
		if ( isset($this->queried_object) )
			return $this->queried_object;

		$this->queried_object = null;
		$this->queried_object_id = null;

		if ( $this->is_category || $this->is_tag || $this->is_tax ) {
			if ( $this->is_category ) {
				if ( $this->get( 'cat' ) ) {
					$term = get_term( $this->get( 'cat' ), 'category' );
				} elseif ( $this->get( 'category_name' ) ) {
					$term = get_term_by( 'slug', $this->get( 'category_name' ), 'category' );
				}
			} elseif ( $this->is_tag ) {
				if ( $this->get( 'tag_id' ) ) {
					$term = get_term( $this->get( 'tag_id' ), 'post_tag' );
				} elseif ( $this->get( 'tag' ) ) {
					$term = get_term_by( 'slug', $this->get( 'tag' ), 'post_tag' );
				}
			} else {
				// For other tax queries, grab the first term from the first clause.
				if ( ! empty( $this->tax_query->queried_terms ) ) {
					$queried_taxonomies = array_keys( $this->tax_query->queried_terms );
					$matched_taxonomy = reset( $queried_taxonomies );
					$query = $this->tax_query->queried_terms[ $matched_taxonomy ];

					if ( ! empty( $query['terms'] ) ) {
						if ( 'term_id' == $query['field'] ) {
							$term = get_term( reset( $query['terms'] ), $matched_taxonomy );
						} else {
							$term = get_term_by( $query['field'], reset( $query['terms'] ), $matched_taxonomy );
						}
					}
				}
			}

			if ( ! empty( $term ) && ! is_wp_error( $term ) )  {
				$this->queried_object = $term;
				$this->queried_object_id = (int) $term->term_id;

				if ( $this->is_category && 'category' === $this->queried_object->taxonomy )
					_make_cat_compat( $this->queried_object );
			}
		} elseif ( $this->is_post_type_archive ) {
			$post_type = $this->get( 'post_type' );
			if ( is_array( $post_type ) )
				$post_type = reset( $post_type );
			$this->queried_object = get_post_type_object( $post_type );
		} elseif ( $this->is_posts_page ) {
			$page_for_posts = get_option('page_for_posts');
			$this->queried_object = get_post( $page_for_posts );
			$this->queried_object_id = (int) $this->queried_object->ID;
		} elseif ( $this->is_singular && ! empty( $this->post ) ) {
			$this->queried_object = $this->post;
			$this->queried_object_id = (int) $this->post->ID;
		} elseif ( $this->is_author ) {
			$this->queried_object_id = (int) $this->get('author');
			$this->queried_object = get_userdata( $this->queried_object_id );
		}

		return $this->queried_object;
	}

	/**
	 * Retrieve ID of the current queried object.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @return int
	 */
	public function get_queried_object_id() {
		$this->get_queried_object();

		if ( isset($this->queried_object_id) ) {
			return $this->queried_object_id;
		}

		return 0;
	}

	/**
	 * Constructor.
	 *
	 * Sets up the WordPress query, if parameter is not empty.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @param string|array $query URL query string or array of vars.
	 */
	public function __construct( $query = '' ) {
		// ##### テンプレート起動前にwp()から$queryなしでグローバルで一度実行される。その後、テンプレート内で$query付きで任意に生成、実行される。#####
		if ( ! empty( $query ) ) {
			$this->query( $query );
		}
	}

	/**
	 * Make private properties readable for backward compatibility.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param string $name Property to get.
	 * @return mixed Property.
	 */
	public function __get( $name ) {
		if ( in_array( $name, $this->compat_fields ) ) {
			return $this->$name;
		}
	}

	/**
	 * Make private properties checkable for backward compatibility.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param string $name Property to check if set.
	 * @return bool Whether the property is set.
	 */
	public function __isset( $name ) {
		if ( in_array( $name, $this->compat_fields ) ) {
			return isset( $this->$name );
		}
	}

	/**
	 * Make private/protected methods readable for backward compatibility.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param callable $name      Method to call.
	 * @param array    $arguments Arguments to pass when calling.
	 * @return mixed|false Return value of the callback, false otherwise.
	 */
	public function __call( $name, $arguments ) {
		if ( in_array( $name, $this->compat_methods ) ) {
			return call_user_func_array( array( $this, $name ), $arguments );
		}
		return false;
	}

	/**
 	 * Is the query for an existing archive page?
 	 *
 	 * Month, Year, Category, Author, Post Type archive...
	 *
 	 * @since 3.1.0
 	 *
 	 * @return bool
 	 */
	public function is_archive() {
		return (bool) $this->is_archive;
	}

	/**
	 * Is the query for an existing post type archive page?
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $post_types Optional. Post type or array of posts types to check against.
	 * @return bool
	 */
	public function is_post_type_archive( $post_types = '' ) {
		if ( empty( $post_types ) || ! $this->is_post_type_archive )
			return (bool) $this->is_post_type_archive;

		$post_type = $this->get( 'post_type' );
		if ( is_array( $post_type ) )
			$post_type = reset( $post_type );
		$post_type_object = get_post_type_object( $post_type );

		return in_array( $post_type_object->name, (array) $post_types );
	}

	/**
	 * Is the query for an existing attachment page?
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $attachment Attachment ID, title, slug, or array of such.
	 * @return bool
	 */
	public function is_attachment( $attachment = '' ) {
		if ( ! $this->is_attachment ) {
			return false;
		}

		if ( empty( $attachment ) ) {
			return true;
		}

		$attachment = array_map( 'strval', (array) $attachment );

		$post_obj = $this->get_queried_object();

		if ( in_array( (string) $post_obj->ID, $attachment ) ) {
			return true;
		} elseif ( in_array( $post_obj->post_title, $attachment ) ) {
			return true;
		} elseif ( in_array( $post_obj->post_name, $attachment ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Is the query for an existing author archive page?
	 *
	 * If the $author parameter is specified, this function will additionally
	 * check if the query is for one of the authors specified.
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $author Optional. User ID, nickname, nicename, or array of User IDs, nicknames, and nicenames
	 * @return bool
	 */
	public function is_author( $author = '' ) {
		if ( !$this->is_author )
			return false;

		if ( empty($author) )
			return true;

		$author_obj = $this->get_queried_object();

		$author = array_map( 'strval', (array) $author );

		if ( in_array( (string) $author_obj->ID, $author ) )
			return true;
		elseif ( in_array( $author_obj->nickname, $author ) )
			return true;
		elseif ( in_array( $author_obj->user_nicename, $author ) )
			return true;

		return false;
	}

	/**
	 * Is the query for an existing category archive page?
	 *
	 * If the $category parameter is specified, this function will additionally
	 * check if the query is for one of the categories specified.
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $category Optional. Category ID, name, slug, or array of Category IDs, names, and slugs.
	 * @return bool
	 */
	public function is_category( $category = '' ) {
		if ( !$this->is_category )
			return false;

		if ( empty($category) )
			return true;

		$cat_obj = $this->get_queried_object();

		$category = array_map( 'strval', (array) $category );

		if ( in_array( (string) $cat_obj->term_id, $category ) )
			return true;
		elseif ( in_array( $cat_obj->name, $category ) )
			return true;
		elseif ( in_array( $cat_obj->slug, $category ) )
			return true;

		return false;
	}

	/**
	 * Is the query for an existing tag archive page?
	 *
	 * If the $tag parameter is specified, this function will additionally
	 * check if the query is for one of the tags specified.
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $tag Optional. Tag ID, name, slug, or array of Tag IDs, names, and slugs.
	 * @return bool
	 */
	public function is_tag( $tag = '' ) {
		if ( ! $this->is_tag )
			return false;

		if ( empty( $tag ) )
			return true;

		$tag_obj = $this->get_queried_object();

		$tag = array_map( 'strval', (array) $tag );

		if ( in_array( (string) $tag_obj->term_id, $tag ) )
			return true;
		elseif ( in_array( $tag_obj->name, $tag ) )
			return true;
		elseif ( in_array( $tag_obj->slug, $tag ) )
			return true;

		return false;
	}

	/**
	 * Is the query for an existing custom taxonomy archive page?
	 *
	 * If the $taxonomy parameter is specified, this function will additionally
	 * check if the query is for that specific $taxonomy.
	 *
	 * If the $term parameter is specified in addition to the $taxonomy parameter,
	 * this function will additionally check if the query is for one of the terms
	 * specified.
	 *
	 * @since 3.1.0
	 *
	 * @global array $wp_taxonomies
	 *
	 * @param mixed $taxonomy Optional. Taxonomy slug or slugs.
	 * @param mixed $term     Optional. Term ID, name, slug or array of Term IDs, names, and slugs.
	 * @return bool True for custom taxonomy archive pages, false for built-in taxonomies (category and tag archives).
	 */
	public function is_tax( $taxonomy = '', $term = '' ) {
		global $wp_taxonomies;

		if ( !$this->is_tax )
			return false;

		if ( empty( $taxonomy ) )
			return true;

		$queried_object = $this->get_queried_object();
		$tax_array = array_intersect( array_keys( $wp_taxonomies ), (array) $taxonomy );
		$term_array = (array) $term;

		// Check that the taxonomy matches.
		if ( ! ( isset( $queried_object->taxonomy ) && count( $tax_array ) && in_array( $queried_object->taxonomy, $tax_array ) ) )
			return false;

		// Only a Taxonomy provided.
		if ( empty( $term ) )
			return true;

		return isset( $queried_object->term_id ) &&
			count( array_intersect(
				array( $queried_object->term_id, $queried_object->name, $queried_object->slug ),
				$term_array
			) );
	}

	/**
	 * Whether the current URL is within the comments popup window.
	 *
	 * @since 3.1.0
	 * @deprecated 4.5.0
	 *
	 * @return bool
	 */
	public function is_comments_popup() {
		_deprecated_function( __FUNCTION__, '4.5.0' );

		return false;
	}

	/**
	 * Is the query for an existing date archive?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_date() {
		return (bool) $this->is_date;
	}

	/**
	 * Is the query for an existing day archive?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_day() {
		return (bool) $this->is_day;
	}

	/**
	 * Is the query for a feed?
	 *
	 * @since 3.1.0
	 *
	 * @param string|array $feeds Optional feed types to check.
	 * @return bool
	 */
	public function is_feed( $feeds = '' ) {
		if ( empty( $feeds ) || ! $this->is_feed )
			return (bool) $this->is_feed;
		$qv = $this->get( 'feed' );
		if ( 'feed' == $qv )
			$qv = get_default_feed();
		return in_array( $qv, (array) $feeds );
	}

	/**
	 * Is the query for a comments feed?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_comment_feed() {
		return (bool) $this->is_comment_feed;
	}

	/**
	 * Is the query for the front page of the site?
	 *
	 * This is for what is displayed at your site's main URL.
	 *
	 * Depends on the site's "Front page displays" Reading Settings 'show_on_front' and 'page_on_front'.
	 *
	 * If you set a static page for the front page of your site, this function will return
	 * true when viewing that page.
	 *
	 * Otherwise the same as @see WP_Query::is_home()
	 *
	 * @since 3.1.0
	 *
	 * @return bool True, if front of site.
	 */
	public function is_front_page() {
/*		// most likely case
		if ( 'posts' == get_option( 'show_on_front') && $this->is_home() )
			return true;
		elseif ( 'page' == get_option( 'show_on_front') && get_option( 'page_on_front' ) && $this->is_page( get_option( 'page_on_front' ) ) )
			return true;
		else
			return false;
		*/
		global $gContentApi;
		
		// ?以降のパラメータがない場合はフロントページとする
		$isFront = $gContentApi->isRootUrl();
		return $isFront;
	}

	/**
	 * Is the query for the blog homepage?
	 *
	 * This is the page which shows the time based blog content of your site.
	 *
	 * Depends on the site's "Front page displays" Reading Settings 'show_on_front' and 'page_for_posts'.
	 *
	 * If you set a static page for the front page of your site, this function will return
	 * true only on the page you set as the "Posts page".
	 *
	 * @see WP_Query::is_front_page()
	 *
	 * @since 3.1.0
	 *
	 * @return bool True if blog view homepage.
	 */
	public function is_home() {
//		return (bool) $this->is_home;
//		return $this->is_front_page();

		global $gContentApi;
		$isHome = $gContentApi->isHomeUrl();
		return $isHome;
	}

	/**
	 * Is the query for an existing month archive?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_month() {
		return (bool) $this->is_month;
	}

	/**
	 * Is the query for an existing single page?
	 *
	 * If the $page parameter is specified, this function will additionally
	 * check if the query is for one of the pages specified.
	 *
	 * @see WP_Query::is_single()
	 * @see WP_Query::is_singular()
	 *
	 * @since 3.1.0
	 *
	 * @param int|string|array $page Optional. Page ID, title, slug, path, or array of such. Default empty.
	 * @return bool Whether the query is for an existing single page.
	 */
	public function is_page( $page = '' ) {
		if ( !$this->is_page )
			return false;

		if ( empty( $page ) )
			return true;

		$page_obj = $this->get_queried_object();

		$page = array_map( 'strval', (array) $page );

		if ( in_array( (string) $page_obj->ID, $page ) ) {
			return true;
		} elseif ( in_array( $page_obj->post_title, $page ) ) {
			return true;
		} elseif ( in_array( $page_obj->post_name, $page ) ) {
			return true;
		} else {
			foreach ( $page as $pagepath ) {
				if ( ! strpos( $pagepath, '/' ) ) {
					continue;
				}
				$pagepath_obj = get_page_by_path( $pagepath );

				if ( $pagepath_obj && ( $pagepath_obj->ID == $page_obj->ID ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Is the query for paged result and not for the first page?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_paged() {
		return (bool) $this->is_paged;
	}

	/**
	 * Is the query for a post or page preview?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_preview() {
		return (bool) $this->is_preview;
	}

	/**
	 * Is the query for the robots file?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_robots() {
		return (bool) $this->is_robots;
	}

	/**
	 * Is the query for a search?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_search() {
		return (bool) $this->is_search;
	}

	/**
	 * Is the query for an existing single post?
	 *
	 * Works for any post type excluding pages.
	 *
	 * If the $post parameter is specified, this function will additionally
	 * check if the query is for one of the Posts specified.
	 *
	 * @see WP_Query::is_page()
	 * @see WP_Query::is_singular()
	 *
	 * @since 3.1.0
	 *
	 * @param int|string|array $post Optional. Post ID, title, slug, path, or array of such. Default empty.
	 * @return bool Whether the query is for an existing single post.
	 */
	public function is_single( $post = '' ) {
/*		if ( !$this->is_single )
			return false;

		if ( empty($post) )
			return true;

		$post_obj = $this->get_queried_object();

		$post = array_map( 'strval', (array) $post );

		if ( in_array( (string) $post_obj->ID, $post ) ) {
			return true;
		} elseif ( in_array( $post_obj->post_title, $post ) ) {
			return true;
		} elseif ( in_array( $post_obj->post_name, $post ) ) {
			return true;
		} else {
			foreach ( $post as $postpath ) {
				if ( ! strpos( $postpath, '/' ) ) {
					continue;
				}
				$postpath_obj = get_page_by_path( $postpath, OBJECT, $post_obj->post_type );

				if ( $postpath_obj && ( $postpath_obj->ID == $post_obj->ID ) ) {
					return true;
				}
			}
		}
		return false;
		*/
		return $this->is_single;
	}
	/**
	 * Is the query for an existing single post of any post type (post, attachment, page, ... )?
	 *
	 * If the $post_types parameter is specified, this function will additionally
	 * check if the query is for one of the Posts Types specified.
	 *
	 * @see WP_Query::is_page()
	 * @see WP_Query::is_single()
	 *
	 * @since 3.1.0
	 *
	 * @param string|array $post_types Optional. Post type or array of post types. Default empty.
	 * @return bool Whether the query is for an existing single post of any of the given post types.
	 */
	public function is_singular( $post_types = '' ) {
		//global $post;		// get_posts()が呼ばれていない、メインループに入っていない場合があるので使用不可
		global $gContentApi;
		
		// データタイプが指定されている場合はWP_Postデータタイプもチェック
		$postType = $gContentApi->getPostType();
//		if (empty($post_types) || $post_types == $postType){
		if (empty($post_types) || in_array($postType, (array)$post_types)){
			return $this->is_single || $this->is_page || $this->is_attachment;
		}
		return false;
/*		if ( empty( $post_types ) || !$this->is_singular )
			return (bool) $this->is_singular;

		$post_obj = $this->get_queried_object();

		return in_array( $post_obj->post_type, (array) $post_types );
		*/
	}

	/**
	 * Is the query for a specific time?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_time() {
		return (bool) $this->is_time;
	}

	/**
	 * Is the query for a trackback endpoint call?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_trackback() {
		return (bool) $this->is_trackback;
	}

	/**
	 * Is the query for an existing year archive?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_year() {
		return (bool) $this->is_year;
	}

	/**
	 * Is the query a 404 (returns no results)?
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_404() {
		return (bool) $this->is_404;
	}

	/**
	 * Is the query for an embedded post?
	 *
	 * @since 4.4.0
	 *
	 * @return bool
	 */
	public function is_embed() {
		return (bool) $this->is_embed;
	}

	/**
	 * Is the query the main query?
	 *
	 * @since 3.3.0
	 *
	 * @global WP_Query $wp_query Global WP_Query instance.
	 *
	 * @return bool
	 */
	public function is_main_query() {
		global $wp_the_query;
		return $wp_the_query === $this;
	}

	/**
	 * Set up global post data.
	 *
	 * @since 4.1.0
	 * @since 4.4.0 Added the ability to pass a post ID to `$post`.
	 *
	 * @global int             $id
	 * @global WP_User         $authordata
	 * @global string|int|bool $currentday
	 * @global string|int|bool $currentmonth
	 * @global int             $page
	 * @global array           $pages
	 * @global int             $multipage
	 * @global int             $more
	 * @global int             $numpages
	 *
	 * @param WP_Post|object|int $post WP_Post instance or Post ID/object.
	 * @return true True when finished.
	 */
	public function setup_postdata($post = null){
		global $id, $authordata, $currentday, $currentmonth, $page, $pages, $multipage, $more, $numpages;

		if ( ! ( $post instanceof WP_Post ) ) {
			$post = get_post( $post );
		}
		if (!$post)return;

		$id = (int) $post->ID;

		// ##### 現在取得中の記事に関連するユーザ情報を取得する #####
		$authordata = get_userdata($post->post_author);

		$currentday = mysql2date('d.m.y', $post->post_date, false);
		$currentmonth = mysql2date('m', $post->post_date, false);
		$numpages = 1;
		$multipage = 0;
		$page = $this->get( 'page' );
		if ( ! $page )
			$page = 1;

		/*
		 * Force full post content when viewing the permalink for the $post,
		 * or when on an RSS feed. Otherwise respect the 'more' tag.
		 */
		if ( $post->ID === get_queried_object_id() && ( $this->is_page() || $this->is_single() ) ) {
			$more = 1;
		} elseif ( $this->is_feed() ) {
			$more = 1;
		} else {
			$more = 0;
		}

		$content = $post->post_content;
		if ( false !== strpos( $content, '<!--nextpage-->' ) ) {
			$content = str_replace( "\n<!--nextpage-->\n", '<!--nextpage-->', $content );
			$content = str_replace( "\n<!--nextpage-->", '<!--nextpage-->', $content );
			$content = str_replace( "<!--nextpage-->\n", '<!--nextpage-->', $content );

			// Ignore nextpage at the beginning of the content.
			if ( 0 === strpos( $content, '<!--nextpage-->' ) )
				$content = substr( $content, 15 );

			$pages = explode('<!--nextpage-->', $content);
		} else {
			$pages = array( $post->post_content );
		}

		/**
		 * Filters the "pages" derived from splitting the post content.
		 *
		 * "Pages" are determined by splitting the post content based on the presence
		 * of `<!-- nextpage -->` tags.
		 *
		 * @since 4.4.0
		 *
		 * @param array   $pages Array of "pages" derived from the post content.
		 *                       of `<!-- nextpage -->` tags..
		 * @param WP_Post $post  Current post object.
		 */
		$pages = apply_filters( 'content_pagination', $pages, $post );

		$numpages = count( $pages );

		if ( $numpages > 1 ) {
			if ( $page > 1 ) {
				$more = 1;
			}
			$multipage = 1;
		} else {
	 		$multipage = 0;
	 	}

		/**
		 * Fires once the post data has been setup.
		 *
		 * @since 2.8.0
		 * @since 4.1.0 Introduced `$this` parameter.
		 *
		 * @param WP_Post  &$post The Post object (passed by reference).
		 * @param WP_Query &$this The current Query object (passed by reference).
		 */
		// ##### 製品情報(product)等の追加取得 #####
		do_action_ref_array( 'the_post', array( &$post, &$this ) );

		return true;
	}
	/**
	 * After looping through a nested query, this function
	 * restores the $post global to the current post in this query.
	 *
	 * @since 3.7.0
	 *
	 * @global WP_Post $post
	 */
/*	public function reset_postdata() {
		if ( ! empty( $this->post ) ) {
			$GLOBALS['post'] = $this->post;
			$this->setup_postdata( $this->post );
		}
	}*/

	/**
	 * Lazyload term meta for posts in the loop.
	 *
	 * @since 4.4.0
	 * @deprecated 4.5.0 See wp_queue_posts_for_term_meta_lazyload().
	 *
	 * @param mixed $check
	 * @param int   $term_id
	 * @return mixed
	 */
	public function lazyload_term_meta( $check, $term_id ) {
		_deprecated_function( __METHOD__, '4.5.0' );
		return $check;
	}

	/**
	 * Lazyload comment meta for comments in the loop.
	 *
	 * @since 4.4.0
	 * @deprecated 4.5.0 See wp_queue_comments_for_comment_meta_lazyload().
	 *
	 * @param mixed $check
	 * @param int   $comment_id
	 * @return mixed
	 */
	public function lazyload_comment_meta( $check, $comment_id ) {
		_deprecated_function( __METHOD__, '4.5.0' );
		return $check;
	}
}
/**
 * [WordPressテンプレート用API]タイトル変更用のフック関数
 *
 * @param string $src		元のタイトル
 * @return string     		変更後のタイトル
 */
function m3get_simple_title($title){
	global $wp_query;

	// トップページはサブタイトルなし
	if (!is_front_page()) $wp_query->headTitle = html_entity_decode($title['title']);			// エスケープ文字を戻す
	return $title;
}
